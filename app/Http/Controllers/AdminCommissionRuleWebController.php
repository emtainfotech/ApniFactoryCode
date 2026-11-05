<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CommissionAuditLog;
use App\Models\CommissionRule;
use App\Models\Company;
use App\Models\OrderCommissionSnapshot;
use App\Models\SellerListing;
use App\Models\SubCatgory;
use App\Services\CommissionCalculationService;
use Illuminate\Http\Request;

class AdminCommissionRuleWebController extends Controller
{
    public function __construct(private CommissionCalculationService $commissionService)
    {
        $this->middleware('auth');
    }

    private function checkAdmin(): void
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Platform Admin access required.');
        }
    }

    /**
     * Commission rules listing.
     */
    public function index(Request $request)
    {
        $this->checkAdmin();

        $query = CommissionRule::with(['creator', 'approver'])->orderBy('id', 'desc');

        if ($request->filled('scope_type')) {
            $query->where('scope_type', $request->scope_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('rule_code', 'like', "%{$term}%")
                  ->orWhere('agreement_ref', 'like', "%{$term}%");
            });
        }

        $rules = $query->paginate(15);

        $counts = [
            'total'            => CommissionRule::count(),
            'active'           => CommissionRule::where('status', 'active')->count(),
            'pending_approval' => CommissionRule::where('status', 'pending_approval')->count(),
            'scheduled'        => CommissionRule::where('status', 'scheduled')->count(),
        ];

        return view('admin.commissions.index', compact('rules', 'counts'));
    }

    /**
     * Create commission rule form.
     */
    public function create()
    {
        $this->checkAdmin();

        $companies = Company::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $subcategories = SubCatgory::orderBy('name')->get(['id', 'name', 'category_id']);
        $listings = SellerListing::with('product')->where('status', 'Active')->limit(100)->get();

        return view('admin.commissions.create', compact('companies', 'categories', 'subcategories', 'listings'));
    }

    /**
     * Store new commission rule.
     */
    public function store(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'name'                  => 'required|string|max:255',
            'scope_type'            => 'required|string|in:sku,seller,subcategory,category,global',
            'scope_id'              => 'nullable|integer',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'fixed_fee'             => 'nullable|numeric|min:0',
            'calculation_basis'     => 'nullable|string|in:taxable_product_value,selling_price_gross,net_seller_payable',
            'gst_rate'              => 'nullable|numeric|min:0|max:100',
            'effective_from'        => 'required|date',
            'effective_to'          => 'nullable|date|after:effective_from',
            'agreement_ref'         => 'nullable|string|max:100',
            'remarks'               => 'nullable|string|max:1000',
        ]);

        try {
            $rule = $this->commissionService->createRule($request->all(), auth()->user());
            return redirect()->route('admin.commissions.rules.show', $rule->id)
                ->with('success', "Commission rule {$rule->rule_code} created successfully.");
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show commission rule details & conflict inspection.
     */
    public function show($id)
    {
        $this->checkAdmin();

        $rule = CommissionRule::with(['creator', 'approver', 'auditLogs.actor'])->findOrFail($id);
        $conflicts = $this->commissionService->detectConflicts($rule);

        return view('admin.commissions.show', compact('rule', 'conflicts'));
    }

    /**
     * Approve commission rule.
     */
    public function approve(Request $request, $id)
    {
        $this->checkAdmin();

        $rule = CommissionRule::findOrFail($id);

        try {
            $this->commissionService->approveRule($rule, auth()->user());
            return redirect()->route('admin.commissions.rules.show', $id)
                ->with('success', "Commission rule {$rule->rule_code} approved successfully.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject commission rule.
     */
    public function reject(Request $request, $id)
    {
        $this->checkAdmin();

        $request->validate(['reason' => 'required|string|min:5|max:1000']);
        $rule = CommissionRule::findOrFail($id);

        try {
            $this->commissionService->rejectRule($rule, auth()->user(), $request->reason);
            return redirect()->route('admin.commissions.rules.show', $id)
                ->with('success', "Commission rule {$rule->rule_code} rejected.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Deactivate commission rule.
     */
    public function deactivate(Request $request, $id)
    {
        $this->checkAdmin();

        $rule = CommissionRule::findOrFail($id);
        $this->commissionService->deactivateRule($rule, auth()->user(), $request->input('reason'));

        return redirect()->route('admin.commissions.rules.show', $id)
            ->with('success', "Commission rule {$rule->rule_code} deactivated.");
    }

    /**
     * View order commission snapshot breakdown as Admin.
     */
    public function orderBreakdown($orderId)
    {
        $this->checkAdmin();

        $snapshots = OrderCommissionSnapshot::with(['listing', 'product', 'company'])
            ->where('order_id', $orderId)
            ->get();

        return view('admin.commissions.order-breakdown', compact('orderId', 'snapshots'));
    }
}
