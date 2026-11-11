<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V2\BaseApiController;
use App\Services\CrossPlatformSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminIntegrationSyncApiController extends BaseApiController
{
    public function __construct(
        protected CrossPlatformSyncService $syncService
    ) {
    }

    /**
     * GET /api/v2/admin/integrations/summary
     */
    public function summary(): JsonResponse
    {
        return $this->sendResponse(
            $this->syncService->getAdminDashboardSummary(),
            'Cross-platform synchronization summary retrieved.'
        );
    }

    /**
     * GET /api/v2/admin/integrations/canonical/{entityType}/{id}
     */
    public function canonicalEntity(string $entityType, int $id): JsonResponse
    {
        try {
            $data = $this->syncService->getCanonicalIdentityMap($entityType, $id);
            return $this->sendResponse($data, 'Authoritative canonical entity state retrieved.');
        } catch (ValidationException $e) {
            return $this->sendError('Validation error.', $e->errors(), 422);
        }
    }

    /**
     * GET /api/v2/admin/integrations/events
     */
    public function events(Request $request): JsonResponse
    {
        $filters = $request->only(['target_channel', 'entity_type', 'status', 'search']);
        $paginated = $this->syncService->listEvents($filters, (int)$request->input('per_page', 25));

        return $this->sendResponse($paginated->items(), 'Sync events retrieved.', 200, [
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }

    /**
     * POST /api/v2/admin/integrations/events/{id}/retry
     */
    public function retryEvent(Request $request, int $id): JsonResponse
    {
        try {
            $override = (bool)$request->boolean('reviewed_financial_override', false);
            $event = $this->syncService->retrySyncEvent($id, $request->user(), $override, $request->input('notes'));
            return $this->sendResponse($event, 'Sync event processed cleanly.');
        } catch (ValidationException $e) {
            return $this->sendError('Sync retry blocked.', $e->errors(), 422);
        }
    }

    /**
     * POST /api/v2/admin/integrations/mappings
     */
    public function registerMapping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_type'   => 'required|string|max:40',
            'canonical_id'  => 'required|integer|min:1',
            'canonical_ref' => 'required|string|max:120',
            'channel'       => 'required|string|max:40',
            'external_id'   => 'required|string|max:120',
            'external_ref'  => 'nullable|string|max:120',
        ]);

        $mapping = $this->syncService->registerChannelMapping(
            $validated['entity_type'],
            (int)$validated['canonical_id'],
            $validated['canonical_ref'],
            $validated['channel'],
            $validated['external_id'],
            $validated['external_ref'] ?? null,
            null,
            $request->user()
        );

        return $this->sendResponse($mapping, 'Canonical channel mapping registered.', 201);
    }

    /**
     * POST /api/v2/admin/integrations/inbound-sync
     */
    public function inboundSync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_channel'      => 'required|string|max:40',
            'entity_type'         => 'required|string|max:40',
            'canonical_id'        => 'required|integer|min:1',
            'fields'              => 'required|array|min:1',
            'idempotency_key'     => 'required|string|max:120',
            'expected_updated_at' => 'nullable|string|max:40',
        ]);

        try {
            $result = $this->syncService->processInboundSync(
                $validated['source_channel'],
                $validated['entity_type'],
                (int)$validated['canonical_id'],
                $validated['fields'],
                $validated['idempotency_key'],
                $request->user(),
                $validated['expected_updated_at'] ?? null
            );

            if (in_array($result['status'], ['rejected_ownership', 'conflict'], true)) {
                return $this->sendError('Inbound sync rejected due to field ownership or concurrency conflict.', $result, 422);
            }

            return $this->sendResponse($result, 'Inbound synchronization processed.');
        } catch (ValidationException $e) {
            return $this->sendError('Inbound sync validation error.', $e->errors(), 422);
        }
    }

    /**
     * GET /api/v2/admin/integrations/reconciliation
     */
    public function reconciliation(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'entity_type', 'severity']);
        $paginated = $this->syncService->listReconciliationRecords($filters, (int)$request->input('per_page', 25));

        return $this->sendResponse($paginated->items(), 'Reconciliation discrepancies retrieved.', 200, [
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }

    /**
     * POST /api/v2/admin/integrations/reconciliation/scan
     */
    public function scanDiscrepancies(): JsonResponse
    {
        $scan = $this->syncService->detectCrossChannelDiscrepancies();
        return $this->sendResponse($scan, 'Cross-channel consistency scan completed.');
    }

    /**
     * POST /api/v2/admin/integrations/reconciliation/{id}/resolve
     */
    public function resolveReconciliation(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:reconcile_to_authoritative,mark_investigating,ignore,escalate',
            'notes'  => 'required|string|min:3|max:1000',
        ]);

        try {
            $record = $this->syncService->resolveReconciliation($id, $validated['action'], $validated['notes'], $request->user());
            return $this->sendResponse($record, 'Reconciliation discrepancy updated cleanly.');
        } catch (ValidationException $e) {
            return $this->sendError('Reconciliation error.', $e->errors(), 422);
        }
    }
}

