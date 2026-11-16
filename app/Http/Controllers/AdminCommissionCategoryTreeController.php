<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CommissionAuditLog;
use App\Models\CommissionRule;
use App\Models\MainCategory;
use App\Models\SubCatgory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminCommissionCategoryTreeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function ensureAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Platform Admin access required.');
        }
    }

    /**
     * Display the Category & Subcategory Commission Tree Manager.
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $mainCategories = MainCategory::orderBy('sequence', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Load all categories grouped by main category
        $allCategories = Category::orderBy('sequence', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Load all subcategories grouped by category
        $allSubcategories = SubCatgory::orderBy('name', 'asc')->get();

        // Map hierarchy
        $subcategoriesByCat = $allSubcategories->groupBy(function ($item) {
            return $item->category_id ?: $item->cid;
        });

        $categoriesByMain = $allCategories->groupBy(function ($item) {
            return $item->maincategory_id ?: $item->mid;
        });

        foreach ($mainCategories as $mainCat) {
            $cats = $categoriesByMain->get($mainCat->id, collect());
            foreach ($cats as $cat) {
                $cat->nested_subcategories = $subcategoriesByCat->get($cat->id, collect());
            }
            $mainCat->child_categories = $cats;
        }

        $totalSubcategories = $allSubcategories->count();
        $totalCategories = $allCategories->count();
        $configuredOverrides = $allCategories->whereNotNull('commission_rate')->count() 
            + $allSubcategories->whereNotNull('commission_rate')->count();

        return view('admin.commissions.categories', compact(
            'mainCategories',
            'totalCategories',
            'totalSubcategories',
            'configuredOverrides'
        ));
    }

    /**
     * Update commission rate for Main Category, Category, or Nested Subcategory.
     * Supports automatic downward cascade across all children.
     */
    public function update(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'level'           => 'required|in:main,category,subcategory',
            'id'              => 'required|integer',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'cascade'         => 'nullable|boolean',
        ]);

        $level = $request->input('level');
        $id = (int)$request->input('id');
        $rate = $request->filled('commission_rate') ? round((float)$request->input('commission_rate'), 2) : null;
        $cascade = (bool)$request->input('cascade', false);

        DB::beginTransaction();
        try {
            $affectedCategories = 0;
            $affectedSubcategories = 0;
            $entityName = '';

            if ($level === 'main') {
                $mainCat = MainCategory::findOrFail($id);
                $entityName = $mainCat->name;
                $mainCat->update(['commission_rate' => $rate]);

                // Also record or update active commission rule for this scope
                $this->syncCommissionRule('category', null, $rate, "Default for Main Category: {$mainCat->name}");

                if ($cascade && $rate !== null) {
                    // Update all categories under this main category
                    $cats = Category::where('maincategory_id', $id)->orWhere('mid', $id)->get();
                    $catIds = $cats->pluck('id')->toArray();
                    $affectedCategories = Category::whereIn('id', $catIds)->update(['commission_rate' => $rate]);

                    // Update all subcategories under those categories
                    $affectedSubcategories = SubCatgory::where(function ($q) use ($id, $catIds) {
                        $q->where('maincategory_id', $id)
                          ->orWhere('mid', $id)
                          ->orWhereIn('category_id', $catIds)
                          ->orWhereIn('cid', $catIds);
                    })->update(['commission_rate' => $rate]);

                    // Sync commission rules for each category
                    foreach ($catIds as $cid) {
                        $this->syncCommissionRule('category', $cid, $rate, "Cascaded from Main Category #{$id}");
                    }
                }
            } elseif ($level === 'category') {
                $cat = Category::findOrFail($id);
                $entityName = $cat->name;
                $cat->update(['commission_rate' => $rate]);

                $this->syncCommissionRule('category', $id, $rate, "Category Rule: {$cat->name}");

                if ($cascade && $rate !== null) {
                    $affectedSubcategories = SubCatgory::where('category_id', $id)
                        ->orWhere('cid', $id)
                        ->update(['commission_rate' => $rate]);

                    $subcatIds = SubCatgory::where('category_id', $id)->orWhere('cid', $id)->pluck('id');
                    foreach ($subcatIds as $sid) {
                        $this->syncCommissionRule('subcategory', $sid, $rate, "Cascaded from Category #{$id}");
                    }
                }
            } elseif ($level === 'subcategory') {
                $subcat = SubCatgory::findOrFail($id);
                $entityName = $subcat->name;
                $subcat->update(['commission_rate' => $rate]);

                $this->syncCommissionRule('subcategory', $id, $rate, "Subcategory Rule: {$subcat->name}");
                $affectedSubcategories = 1;
            }

            DB::commit();

            return response()->json([
                'status'                 => true,
                'message'                => "Commission rate for '{$entityName}' updated to " . ($rate !== null ? "{$rate}%" : 'Inherited') . ($cascade ? " (Cascaded to {$affectedCategories} categories & {$affectedSubcategories} subcategories)" : ''),
                'commission_rate'        => $rate,
                'cascade'                => $cascade,
                'affected_categories'    => $affectedCategories,
                'affected_subcategories' => $affectedSubcategories,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Error updating commission rate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to synchronize centralized CommissionRule record for marketplace calculation engine.
     */
    private function syncCommissionRule(string $scopeType, ?int $scopeId, ?float $rate, string $name): void
    {
        if ($rate === null) {
            if ($scopeId !== null) {
                CommissionRule::where('scope_type', $scopeType)
                    ->where('scope_id', $scopeId)
                    ->update(['status' => 'inactive']);
            }
            return;
        }

        $rule = CommissionRule::where('scope_type', $scopeType);
        if ($scopeId !== null) {
            $rule->where('scope_id', $scopeId);
        } else {
            $rule->whereNull('scope_id');
        }
        $existing = $rule->first();

        if ($existing) {
            $existing->update([
                'commission_percentage' => $rate,
                'status'                => 'active',
                'name'                  => $name,
            ]);
        } else {
            CommissionRule::create([
                'rule_code'             => 'CR-' . strtoupper(Str::random(6)),
                'name'                  => $name,
                'scope_type'            => $scopeType,
                'scope_id'              => $scopeId,
                'commission_percentage' => $rate,
                'fixed_fee'             => 0,
                'calculation_basis'     => 'taxable_product_value',
                'gst_rate'              => 18.00,
                'is_gst_inclusive'      => false,
                'effective_from'        => now(),
                'status'                => 'active',
                'created_by_user_id'    => auth()->id(),
                'approved_by_user_id'   => auth()->id(),
                'remarks'               => 'Auto-created from Admin Category Commission Tree Manager',
            ]);
        }
    }
}

