<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerCatalogueBulkOperation;
use App\Services\SellerCatalogueBulkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerCatalogueBulkApiController extends Controller
{
    public function __construct(private SellerCatalogueBulkService $bulkService)
    {
    }

    /**
     * POST /api/v2/seller/catalogue/bulk/preview
     * Stage 1: Generate preview of bulk changes without modifying data.
     */
    public function preview(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company account associated with authenticated user.'], 403);
        }

        $request->validate([
            'operation_type'     => 'required|string|in:status_update,field_update,archive,restore',
            'target_entity'      => 'nullable|string|in:listing,product',
            'selection_mode'     => 'required|string|in:ids,filter',
            'selection_criteria' => 'nullable|array',
            'ids'                => 'nullable|array',
            'filters'            => 'nullable|array',
            'update_payload'     => 'nullable|array',
            'payload'            => 'nullable|array',
        ]);

        $params = $request->all();
        if (!isset($params['selection_criteria'])) {
            $params['selection_criteria'] = $request->input('ids') ?: $request->input('filters', []);
        }
        if (!isset($params['update_payload'])) {
            $params['update_payload'] = $request->input('payload', []);
        }

        $operation = $this->bulkService->preview($company, $user, $params);

        return response()->json([
            'status'  => true,
            'message' => $operation->canConfirm()
                ? "Preview generated successfully. {$operation->eligible_count} of {$operation->total_matching} records eligible."
                : "Preview generated with warnings. No records are eligible for the requested operation.",
            'data'    => [
                'operation_id'     => $operation->id,
                'status'           => $operation->status,
                'operation_type'   => $operation->operation_type,
                'target_entity'    => $operation->target_entity,
                'total_matching'   => $operation->total_matching,
                'eligible_count'   => $operation->eligible_count,
                'ineligible_count' => $operation->ineligible_count,
                'can_confirm'      => $operation->canConfirm(),
                'preview_sample'   => $operation->preview_data['preview_sample'] ?? [],
                'ineligible_items' => $operation->preview_data['ineligible_items'] ?? [],
            ],
        ], 200);
    }

    /**
     * POST /api/v2/seller/catalogue/bulk/{id}/confirm
     * Stage 2: Confirm and execute the previewed bulk operation.
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        // IDOR prevention: find strictly by company_id
        $operation = SellerCatalogueBulkOperation::where('company_id', $company->id)->findOrFail($id);

        if (!$operation->canConfirm()) {
            return response()->json([
                'status'  => false,
                'message' => "Bulk operation #{$operation->id} cannot be confirmed (status: {$operation->status}, eligible: {$operation->eligible_count}).",
            ], 422);
        }

        $async = $request->boolean('async', false);
        $executed = $this->bulkService->confirm($company, $user, $operation->id, $async);

        return response()->json([
            'status'  => true,
            'message' => $executed->status === 'processing'
                ? "Bulk operation queued for asynchronous processing."
                : "Bulk operation processed successfully.",
            'data'    => [
                'operation_id'    => $executed->id,
                'status'          => $executed->status,
                'processed_count' => $executed->processed_count,
                'success_count'   => $executed->success_count,
                'failed_count'    => $executed->failed_count,
                'results_summary' => $executed->results_summary,
                'completed_at'    => $executed->completed_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/v2/seller/catalogue/bulk/status
     * Direct status update shortcut.
     */
    public function statusUpdate(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        $request->validate([
            'status'         => 'required|string|in:Active,Inactive,Archived,active,inactive,archived',
            'selection_mode' => 'nullable|string|in:ids,filter',
            'ids'            => 'nullable|array',
            'filters'        => 'nullable|array',
            'target_entity'  => 'nullable|string|in:listing,product',
            'confirm'        => 'nullable|boolean',
        ]);

        $params = $request->all();
        $params['selection_mode'] = $params['selection_mode'] ?? (isset($params['ids']) || isset($params['selection_criteria']) ? 'ids' : 'filter');
        $params['selection_criteria'] = $params['selection_criteria'] ?? ($params['selection_mode'] === 'ids' ? ($params['ids'] ?? []) : ($params['filters'] ?? []));

        $result = $this->bulkService->directStatusUpdate($company, $user, $params);

        return response()->json([
            'status' => true,
            'data'   => $result,
        ]);
    }

    /**
     * POST /api/v2/seller/catalogue/bulk/fields
     * Direct field update shortcut.
     */
    public function fieldUpdate(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        $request->validate([
            'field'          => 'required|string',
            'action'         => 'nullable|string|in:set,increase_percent,decrease_percent,increase_fixed,decrease_fixed',
            'value'          => 'required',
            'selection_mode' => 'nullable|string|in:ids,filter',
            'ids'            => 'nullable|array',
            'filters'        => 'nullable|array',
            'target_entity'  => 'nullable|string|in:listing,product',
            'confirm'        => 'nullable|boolean',
        ]);

        $params = $request->all();
        $params['selection_mode'] = $params['selection_mode'] ?? (isset($params['ids']) || isset($params['selection_criteria']) ? 'ids' : 'filter');
        $params['selection_criteria'] = $params['selection_criteria'] ?? ($params['selection_mode'] === 'ids' ? ($params['ids'] ?? []) : ($params['filters'] ?? []));
        $params['update_payload'] = [
            'field'  => $request->input('field'),
            'action' => $request->input('action', 'set'),
            'value'  => $request->input('value'),
        ];

        $result = $this->bulkService->directFieldUpdate($company, $user, $params);

        return response()->json([
            'status' => true,
            'data'   => $result,
        ]);
    }

    /**
     * POST /api/v2/seller/catalogue/bulk/archive
     */
    public function archive(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        $params = $request->all();
        $params['selection_mode'] = $params['selection_mode'] ?? (isset($params['ids']) || isset($params['selection_criteria']) ? 'ids' : 'filter');
        $params['selection_criteria'] = $params['selection_criteria'] ?? ($params['selection_mode'] === 'ids' ? ($params['ids'] ?? []) : ($params['filters'] ?? []));

        $result = $this->bulkService->directArchive($company, $user, $params);

        return response()->json([
            'status' => true,
            'data'   => $result,
        ]);
    }

    /**
     * POST /api/v2/seller/catalogue/bulk/restore
     */
    public function restore(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        $params = $request->all();
        $params['selection_mode'] = $params['selection_mode'] ?? (isset($params['ids']) || isset($params['selection_criteria']) ? 'ids' : 'filter');
        $params['selection_criteria'] = $params['selection_criteria'] ?? ($params['selection_mode'] === 'ids' ? ($params['ids'] ?? []) : ($params['filters'] ?? []));

        $result = $this->bulkService->directRestore($company, $user, $params);

        return response()->json([
            'status' => true,
            'data'   => $result,
        ]);
    }

    /**
     * GET /api/v2/seller/catalogue/bulk/{id}
     * Inspect single bulk operation status and execution diagnostics.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        // IDOR prevention
        $operation = SellerCatalogueBulkOperation::where('company_id', $company->id)->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'id'                 => $operation->id,
                'status'             => $operation->status,
                'operation_type'     => $operation->operation_type,
                'target_entity'      => $operation->target_entity,
                'selection_mode'     => $operation->selection_mode,
                'total_matching'     => $operation->total_matching,
                'eligible_count'     => $operation->eligible_count,
                'ineligible_count'   => $operation->ineligible_count,
                'processed_count'    => $operation->processed_count,
                'success_count'      => $operation->success_count,
                'failed_count'       => $operation->failed_count,
                'update_payload'     => $operation->update_payload,
                'preview_data'       => $operation->preview_data,
                'results_summary'    => $operation->results_summary,
                'error_summary'      => $operation->error_summary,
                'started_at'         => $operation->started_at?->toIso8601String(),
                'completed_at'       => $operation->completed_at?->toIso8601String(),
                'created_at'         => $operation->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/v2/seller/catalogue/bulk/history
     * Paginated list of bulk operations.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        $operations = SellerCatalogueBulkOperation::where('company_id', $company->id)
            ->with('user:id,name,email')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->through(fn($op) => [
                'id'              => $op->id,
                'operation_type'  => $op->operation_type,
                'target_entity'   => $op->target_entity,
                'selection_mode'  => $op->selection_mode,
                'status'          => $op->status,
                'total_matching'  => $op->total_matching,
                'eligible_count'  => $op->eligible_count,
                'success_count'   => $op->success_count,
                'failed_count'    => $op->failed_count,
                'requested_by'    => $op->user ? ['id' => $op->user->id, 'name' => $op->user->name] : null,
                'created_at'      => $op->created_at?->toIso8601String(),
                'completed_at'    => $op->completed_at?->toIso8601String(),
            ]);

        return response()->json([
            'status' => true,
            'data'   => $operations,
        ]);
    }
}
