<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Services\ProductCatalogueApprovalService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminCatalogueReviewWebController extends Controller
{
    public function __construct(
        private ProductCatalogueApprovalService $approvalService
    ) {
        $this->middleware('auth');
    }

    private function ensureAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Forbidden: Administrator privileges required for this catalogue review area.');
        }
    }

    /**
     * Display the Administrative Catalogue Review Queue.
     */
    public function queue(Request $request)
    {
        $this->ensureAdmin();

        $currentStatus = $request->input('status', 'pending');
        $filters = $request->only(['category_id', 'brand_id', 'company_id', 'search', 'date_from', 'date_to']);

        if ($currentStatus === 'pending') {
            $filters['status'] = null; // triggers pending_review & resubmitted
        } elseif ($currentStatus !== 'all') {
            $filters['status'] = $currentStatus;
        } else {
            $filters['status'] = 'all';
        }

        $products = $this->approvalService->getReviewQueue($filters, 15);

        // Counts for tabs
        $counts = [
            'pending'               => Product::whereIn('approval_status', ['pending_review', 'resubmitted'])->count(),
            'pending_review'        => Product::where('approval_status', 'pending_review')->count(),
            'resubmitted'           => Product::where('approval_status', 'resubmitted')->count(),
            'corrections_requested' => Product::where('approval_status', 'corrections_requested')->count(),
            'rejected'              => Product::where('approval_status', 'rejected')->count(),
            'approved'              => Product::where('approval_status', 'approved')->count(),
            'all'                   => Product::count(),
        ];

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands     = Brand::orderBy('name')->get(['id', 'name']);
        $companies  = Company::orderBy('name')->get(['id', 'name']);

        return view('admin.catalogue.review-queue', compact(
            'products',
            'counts',
            'currentStatus',
            'categories',
            'brands',
            'companies'
        ));
    }

    /**
     * Show comprehensive product review detail page for admin moderation.
     */
    public function show(int $id)
    {
        $this->ensureAdmin();

        $product = Product::findOrFail($id);
        $details = $this->approvalService->getReviewDetails($product);

        return view('admin.catalogue.review-detail', $details);
    }

    /**
     * Admin Action: Approve product and publish to live marketplace.
     */
    public function approve(Request $request, int $id)
    {
        $this->ensureAdmin();

        $product = Product::findOrFail($id);

        try {
            $this->approvalService->approveProduct($product, auth()->user(), $request->input('notes'));

            return redirect()->route('admin.catalogue.reviews')
                ->with('success', "Product '{$product->name}' has been approved and published to the live marketplace.");
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: 'Product approval failed validation.';
            return back()->with('error', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin Action: Reject product submission with reason.
     */
    public function reject(Request $request, int $id)
    {
        $this->ensureAdmin();

        $request->validate([
            'reason' => 'required|string|min:3|max:1000',
        ]);

        $product = Product::findOrFail($id);

        try {
            $this->approvalService->rejectProduct($product, auth()->user(), $request->input('reason'));

            return redirect()->route('admin.catalogue.reviews')
                ->with('success', "Product '{$product->name}' has been rejected. Seller notified.");
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: 'Product rejection failed.';
            return back()->with('error', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin Action: Request corrections from seller with instructions.
     */
    public function requestCorrections(Request $request, int $id)
    {
        $this->ensureAdmin();

        $request->validate([
            'instructions' => 'required|string|min:3|max:1000',
        ]);

        $product = Product::findOrFail($id);

        try {
            $this->approvalService->requestCorrections($product, auth()->user(), $request->input('instructions'));

            return redirect()->route('admin.catalogue.reviews')
                ->with('success', "Corrections requested for product '{$product->name}'. Product returned to seller.");
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: 'Request corrections failed.';
            return back()->with('error', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

