<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommissionAuditLog;
use App\Models\CommissionRule;
use App\Models\OrderCommissionSnapshot;
use App\Models\SellerListing;
use App\Services\CommissionCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminCommissionRuleApiController extends Controller
{
    protected CommissionCalculationService $commissionService;

    public function __construct(CommissionCalculationService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    protected function checkAdmin(Request $request): ?JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Administrator access required.',
            ], 403);
        }
        return null;
    }

    /**
     * GET /api/v2/admin/commissions/rules
     * List all commission rules with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $query = CommissionRule::with(['creator:id,name,email', 'approver:id,name,email'])
            ->orderBy('id', 'desc');

        if ($request->filled('scope_type')) {
            $query->where('scope_type', $request->scope_type);
        }

        if ($request->filled('scope_id')) {
            $query->where('scope_id', $request->scope_id);
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

        $perPage = min((int)$request->get('per_page', 20), 100);
        $rules = $query->paginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Commission rules retrieved successfully.',
            'data'    => $rules,
        ]);
    }

    /**
     * POST /api/v2/admin/commissions/rules
     * Create a new commercial commission agreement / rule.
     */
    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'scope_type'            => 'required|string|in:sku,seller,subcategory,category,global',
            'scope_id'              => 'nullable|integer',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'fixed_fee'             => 'nullable|numeric|min:0',
            'calculation_basis'     => 'nullable|string|in:taxable_product_value,selling_price_gross,net_seller_payable',
            'gst_rate'              => 'nullable|numeric|min:0|max:100',
            'is_gst_inclusive'      => 'nullable|boolean',
            'effective_from'        => 'required|date',
            'effective_to'          => 'nullable|date|after:effective_from',
            'status'                => 'nullable|string|in:draft,pending_approval,active,scheduled',
            'agreement_ref'         => 'nullable|string|max:100',
            'remarks'               => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $rule = $this->commissionService->createRule($request->all(), $request->user());

            return response()->json([
                'status'  => true,
                'message' => "Commission rule {$rule->rule_code} created successfully.",
                'data'    => $rule,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create commission rule: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/v2/admin/commissions/rules/{id}
     * Inspect rule details and audit logs.
     */
    public function show(Request $request, $id): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $rule = CommissionRule::with(['creator', 'approver', 'auditLogs.actor'])->find($id);

        if (!$rule) {
            return response()->json([
                'status'  => false,
                'message' => 'Commission rule not found.',
            ], 404);
        }

        $conflicts = $this->commissionService->detectConflicts($rule);

        return response()->json([
            'status'  => true,
            'message' => 'Commission rule details retrieved.',
            'data'    => [
                'rule'      => $rule,
                'conflicts' => $conflicts,
            ],
        ]);
    }

    /**
     * POST /api/v2/admin/commissions/rules/{id}/approve
     * Approve pending commission rule.
     */
    public function approve(Request $request, $id): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $rule = CommissionRule::find($id);
        if (!$rule) {
            return response()->json(['status' => false, 'message' => 'Commission rule not found.'], 404);
        }

        try {
            $approved = $this->commissionService->approveRule($rule, $request->user());
            return response()->json([
                'status'  => true,
                'message' => "Commission rule {$rule->rule_code} approved.",
                'data'    => $approved,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v2/admin/commissions/rules/{id}/reject
     * Reject pending commission rule with reason.
     */
    public function reject(Request $request, $id): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:5|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'A valid rejection reason is required.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $rule = CommissionRule::find($id);
        if (!$rule) {
            return response()->json(['status' => false, 'message' => 'Commission rule not found.'], 404);
        }

        try {
            $rejected = $this->commissionService->rejectRule($rule, $request->user(), $request->reason);
            return response()->json([
                'status'  => true,
                'message' => "Commission rule {$rule->rule_code} rejected.",
                'data'    => $rejected,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v2/admin/commissions/rules/{id}/deactivate
     * Deactivate an active commission rule.
     */
    public function deactivate(Request $request, $id): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $rule = CommissionRule::find($id);
        if (!$rule) {
            return response()->json(['status' => false, 'message' => 'Commission rule not found.'], 404);
        }

        $deactivated = $this->commissionService->deactivateRule($rule, $request->user(), $request->input('reason'));

        return response()->json([
            'status'  => true,
            'message' => "Commission rule {$rule->rule_code} deactivated.",
            'data'    => $deactivated,
        ]);
    }

    /**
     * POST /api/v2/admin/commissions/preview-resolution
     * Test deterministic rule resolution for any seller listing SKU.
     */
    public function previewResolution(Request $request): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $request->validate([
            'seller_listing_id' => 'required|integer',
            'quantity'          => 'nullable|integer|min:1',
            'unit_price'        => 'nullable|numeric|min:0',
        ]);

        $listing = SellerListing::with(['product.category', 'company'])->find($request->seller_listing_id);
        if (!$listing) {
            return response()->json(['status' => false, 'message' => 'Seller listing not found.'], 404);
        }

        $qty = (int)($request->get('quantity', 1));
        $price = $request->filled('unit_price') ? (float)$request->unit_price : (float)$listing->seller_price;

        $calc = $this->commissionService->calculateLineCommission($listing, $qty, $price);

        return response()->json([
            'status'  => true,
            'message' => 'Commission calculation preview resolved successfully.',
            'data'    => [
                'listing'     => [
                    'id'           => $listing->id,
                    'seller_sku'   => $listing->seller_sku,
                    'product_name' => $listing->product ? $listing->product->name : 'N/A',
                    'seller_name'  => $listing->company ? $listing->company->name : 'N/A',
                ],
                'calculation' => $calc,
            ],
        ]);
    }

    /**
     * GET /api/v2/admin/commissions/orders/{orderId}/breakdown
     * View frozen commission snapshots for an order.
     */
    public function orderBreakdown(Request $request, $orderId): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $snapshots = OrderCommissionSnapshot::with(['listing', 'product', 'company'])
            ->where('order_id', $orderId)
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Order commission breakdown retrieved.',
            'data'    => $snapshots,
        ]);
    }

    /**
     * GET /api/v2/admin/commissions/audit-logs
     * View centralized commission audit history.
     */
    public function auditLogs(Request $request): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $query = CommissionAuditLog::with(['rule', 'actor:id,name,email'])
            ->orderBy('id', 'desc');

        if ($request->filled('rule_id')) {
            $query->where('commission_rule_id', $request->rule_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        $logs = $query->paginate(25);

        return response()->json([
            'status'  => true,
            'message' => 'Commission audit logs retrieved.',
            'data'    => $logs,
        ]);
    }
}

