<?php

namespace App\Services;

use App\Jobs\ProcessSellerCatalogueBulkOperation;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Company;
use App\Models\CompanyAuditLog;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\SellerCatalogueBulkOperation;
use App\Models\SellerListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SellerCatalogueBulkService
{
    public const MAX_BATCH_SIZE = 500;

    /**
     * Permitted fields for bulk field update and their required permissions.
     */
    protected array $permittedFields = [
        // Pricing fields
        'seller_price' => [
            'type'       => 'decimal',
            'permission' => 'pricing.update',
            'min'        => 0.01,
            'label'      => 'Wholesale Factory Price',
        ],
        'commission_rate' => [
            'type'       => 'decimal',
            'permission' => 'pricing.update',
            'min'        => 0.00,
            'max'        => 100.00,
            'label'      => 'Commission Rate (%)',
        ],
        'selling_price' => [
            'type'       => 'decimal',
            'permission' => 'pricing.update',
            'min'        => 0.01,
            'label'      => 'Customer Selling Price',
        ],
        'mrp' => [
            'type'       => 'decimal',
            'permission' => 'pricing.update',
            'min'        => 0.01,
            'label'      => 'Maximum Retail Price (MRP)',
        ],
        // Inventory fields
        'stock_quantity' => [
            'type'       => 'integer',
            'permission' => 'inventory.update',
            'min'        => 0,
            'label'      => 'Available Stock Quantity',
        ],
        'low_stock_threshold' => [
            'type'       => 'integer',
            'permission' => 'inventory.update',
            'min'        => 0,
            'label'      => 'Low Stock Alert Threshold',
        ],
        'dispatch_lead_days' => [
            'type'       => 'integer',
            'permission' => 'inventory.update',
            'min'        => 1,
            'max'        => 30,
            'label'      => 'Dispatch Lead Days',
        ],
        // Order Packaging fields
        'min_order_quantity' => [
            'type'       => 'integer',
            'permission' => 'products.edit',
            'min'        => 1,
            'label'      => 'Minimum Order Quantity (MOQ)',
        ],
        'order_multiple' => [
            'type'       => 'integer',
            'permission' => 'products.edit',
            'min'        => 1,
            'label'      => 'Order Multiples',
        ],
        'case_pack_quantity' => [
            'type'       => 'integer',
            'permission' => 'products.edit',
            'min'        => 1,
            'label'      => 'Case Pack Quantity',
        ],
        'allow_unit_orders' => [
            'type'       => 'boolean',
            'permission' => 'products.edit',
            'label'      => 'Allow Unit Orders',
        ],
        'allow_carton_orders' => [
            'type'       => 'boolean',
            'permission' => 'products.edit',
            'label'      => 'Allow Carton Orders',
        ],
        // Dynamic Attribute update
        'dynamic_attribute' => [
            'type'       => 'attribute',
            'permission' => 'products.edit',
            'label'      => 'Dynamic Attribute',
        ],
    ];

    /**
     * Forbidden / platform-managed fields that sellers cannot modify in bulk.
     */
    protected array $forbiddenFields = [
        'id', 'product_id', 'company_id', 'seller_user_id', 'product_variant_id',
        'created_at', 'updated_at', 'deleted_at', 'slug', 'category_id',
        'subcategory_id', 'brand_id', 'tax', 'hsncode', 'adminstatus', 'approval_status',
    ];

    /**
     * Normalize status strings to database values ('Active', 'Deactive', 'Archived').
     */
    public function normalizeStatus(string $status): string
    {
        $s = strtolower(trim($status));
        if (in_array($s, ['inactive', 'deactive', '0'], true)) {
            return 'Deactive';
        }
        if (in_array($s, ['active', '1'], true)) {
            return 'Active';
        }
        if (in_array($s, ['archived', 'archive'], true)) {
            return 'Archived';
        }
        return ucfirst($s);
    }

    /**
     * Normalize polymorphic field update payloads to standardized internal descriptor.
     */
    public function normalizeUpdatePayload(string $operationType, array $payload): array
    {
        if (in_array($operationType, ['status_update', 'archive', 'restore'], true)) {
            if (isset($payload['status'])) {
                $payload['status'] = $this->normalizeStatus($payload['status']);
            }
            return $payload;
        }

        if ($operationType === 'field_update') {
            // Already in descriptor format: field, action, value
            if (isset($payload['field'])) {
                return $payload;
            }

            // Price adjustment types
            if (isset($payload['price_adjustment_type'])) {
                $action = match ($payload['price_adjustment_type']) {
                    'percentage_decrease' => 'decrease_percent',
                    'percentage_increase' => 'increase_percent',
                    'fixed_set'           => 'set',
                    default               => 'set',
                };
                return [
                    'field'  => 'seller_price',
                    'action' => $action,
                    'value'  => $payload['price_adjustment_value'] ?? ($payload['value'] ?? 0),
                    'mrp'    => $payload['mrp'] ?? null,
                ];
            }

            // Stock adjustment types
            if (isset($payload['stock_adjustment_type'])) {
                $action = match ($payload['stock_adjustment_type']) {
                    'increase' => 'increase_fixed',
                    'decrease' => 'decrease_fixed',
                    default    => 'set',
                };
                return [
                    'field'  => 'stock_quantity',
                    'action' => $action,
                    'value'  => $payload['stock_quantity'] ?? ($payload['value'] ?? 0),
                ];
            }

            // Direct field key in payload (e.g. ['seller_price' => 350.00])
            foreach (array_keys($this->permittedFields) as $permittedField) {
                if (array_key_exists($permittedField, $payload)) {
                    return [
                        'field'  => $permittedField,
                        'action' => 'set',
                        'value'  => $payload[$permittedField],
                    ];
                }
            }
        }

        return $payload;
    }

    /**
     * Stage 1: Generate Preview of a bulk operation without mutating data.
     */
    public function preview(Company $company, User $user, array $params): SellerCatalogueBulkOperation
    {
        $operationType = $params['operation_type'] ?? 'status_update';
        $targetEntity  = $params['target_entity'] ?? 'listing';
        $selectionMode = $params['selection_mode'] ?? 'ids';
        $criteria      = $params['selection_criteria'] ?? ($params['ids'] ?? $params['filters'] ?? []);
        $rawPayload    = $params['update_payload'] ?? ($params['payload'] ?? []);

        // Validate basic inputs
        $this->validateOperationRequest($operationType, $targetEntity, $selectionMode, $criteria, $rawPayload);

        $updatePayload = $this->normalizeUpdatePayload($operationType, $rawPayload);

        // Enforce permissions for requested operation & fields
        $this->authorizeOperation($user, $operationType, $updatePayload);

        // Resolve matching records strictly scoped to tenant company
        $records = $this->resolveRecords($company, $targetEntity, $selectionMode, $criteria);

        if ($records->isEmpty()) {
            throw ValidationException::withMessages([
                'selection' => 'No matching catalogue records found for the specified criteria.',
            ]);
        }

        if ($records->count() > self::MAX_BATCH_SIZE) {
            throw ValidationException::withMessages([
                'selection' => 'Selection matches ' . $records->count() . ' records. Bulk operations are capped at ' . self::MAX_BATCH_SIZE . ' records per batch. Please narrow your filter criteria.',
            ]);
        }

        // Assess eligibility for each item and generate preview deltas
        $eligibleCount   = 0;
        $ineligibleCount = 0;
        $ineligibleItems = [];
        $previewSample   = [];
        $eligibleIds     = [];

        foreach ($records as $item) {
            $check = $this->evaluateItemEligibility($item, $targetEntity, $operationType, $updatePayload, $company, $user);

            if ($check['is_eligible']) {
                $eligibleCount++;
                $eligibleIds[] = $item->id;
                if (count($previewSample) < 25) {
                    $previewSample[] = [
                        'id'         => $item->id,
                        'identifier' => $this->getItemIdentifier($item, $targetEntity),
                        'field'      => $check['field'],
                        'old_value'  => $check['old_value'],
                        'new_value'  => $check['new_value'],
                        'details'    => $check['details'] ?? null,
                    ];
                }
            } else {
                $ineligibleCount++;
                if (count($ineligibleItems) < 50) {
                    $ineligibleItems[] = [
                        'id'         => $item->id,
                        'identifier' => $this->getItemIdentifier($item, $targetEntity),
                        'reason'     => $check['reason'],
                    ];
                }
            }
        }

        // Create operation record in 'previewed' status
        return SellerCatalogueBulkOperation::create([
            'company_id'         => $company->id,
            'user_id'            => $user->id,
            'operation_type'     => $operationType,
            'target_entity'      => $targetEntity,
            'selection_mode'     => $selectionMode,
            'selection_criteria' => $criteria,
            'update_payload'     => $updatePayload,
            'status'             => 'previewed',
            'total_matching'     => $records->count(),
            'eligible_count'     => $eligibleCount,
            'ineligible_count'   => $ineligibleCount,
            'preview_data'       => [
                'eligible_ids'     => $eligibleIds,
                'preview_sample'   => $previewSample,
                'ineligible_items' => $ineligibleItems,
            ],
            'ip_address'         => request() ? request()->ip() : null,
        ]);
    }

    /**
     * Stage 2: Confirm and execute a previewed bulk operation.
     */
    public function confirm(Company $company, User $user, int $operationId, bool $async = false): SellerCatalogueBulkOperation
    {
        // IDOR prevention: must belong to company
        $operation = SellerCatalogueBulkOperation::where('company_id', $company->id)->findOrFail($operationId);

        if (!$operation->canConfirm()) {
            throw ValidationException::withMessages([
                'operation' => "Bulk operation #{$operation->id} cannot be confirmed (status: [{$operation->status}], eligible: [{$operation->eligible_count}]).",
            ]);
        }

        // Re-authorize at execution time
        $this->authorizeOperation($user, $operation->operation_type, $operation->update_payload);

        if ($async || $operation->eligible_count > 100) {
            $operation->update(['status' => 'processing', 'started_at' => now()]);
            ProcessSellerCatalogueBulkOperation::dispatch($operation);
            return $operation->fresh();
        }

        return $this->executeOperation($operation);
    }

    /**
     * Execute the validated operation across eligible records in atomic batches.
     */
    public function executeOperation(SellerCatalogueBulkOperation $operation): SellerCatalogueBulkOperation
    {
        $operation->update([
            'status'     => 'processing',
            'started_at' => $operation->started_at ?: now(),
        ]);

        $company       = $operation->company;
        $targetEntity  = $operation->target_entity;
        $operationType = $operation->operation_type;
        $updatePayload = $operation->update_payload;
        $eligibleIds   = $operation->preview_data['eligible_ids'] ?? [];

        if (empty($eligibleIds)) {
            $operation->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
            return $operation;
        }

        // Re-fetch records matching eligible IDs
        $records = $this->resolveRecordsByIds($company, $targetEntity, $eligibleIds);

        $processedCount = 0;
        $successCount   = 0;
        $failedCount    = 0;
        $resultsItems   = [];

        // Process in chunks inside database transactions
        $chunks = $records->chunk(50);

        foreach ($chunks as $chunk) {
            DB::transaction(function () use (
                $chunk,
                $targetEntity,
                $operationType,
                $updatePayload,
                $company,
                &$processedCount,
                &$successCount,
                &$failedCount,
                &$resultsItems
            ) {
                foreach ($chunk as $item) {
                    $processedCount++;
                    try {
                        $auditItem = $this->applyChangeToItem($item, $targetEntity, $operationType, $updatePayload, $company);
                        $successCount++;
                        if (count($resultsItems) < 100) {
                            $resultsItems[] = [
                                'id'         => $item->id,
                                'identifier' => $this->getItemIdentifier($item, $targetEntity),
                                'status'     => 'success',
                                'old_value'  => $auditItem['old_value'],
                                'new_value'  => $auditItem['new_value'],
                            ];
                        }
                    } catch (\Throwable $e) {
                        $failedCount++;
                        Log::warning("Bulk op #{$item->id} failure: " . $e->getMessage());
                        if (count($resultsItems) < 100) {
                            $resultsItems[] = [
                                'id'         => $item->id,
                                'identifier' => $this->getItemIdentifier($item, $targetEntity),
                                'status'     => 'failed',
                                'error'      => $e->getMessage(),
                            ];
                        }
                    }
                }
            });
        }

        $finalStatus = $failedCount === 0 ? 'completed' : ($successCount > 0 ? 'partial_success' : 'failed');

        $operation->update([
            'status'          => $finalStatus,
            'processed_count' => $processedCount,
            'success_count'   => $successCount,
            'failed_count'    => $failedCount,
            'results_summary' => [
                'total_processed' => $processedCount,
                'total_success'   => $successCount,
                'total_failed'    => $failedCount,
                'sample_results'  => $resultsItems,
            ],
            'completed_at'    => now(),
        ]);

        // Record Company Audit Log entry
        $this->recordCompanyAuditLog($operation, $company);

        return $operation->fresh();
    }

    /**
     * Direct Status Update Shortcut (preview or execute immediately).
     */
    public function directStatusUpdate(Company $company, User $user, array $params): array
    {
        $status = $this->normalizeStatus($params['status'] ?? '');
        if (!in_array($status, ['Active', 'Deactive', 'Archived'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Status must be one of: Active, Inactive, Archived.',
            ]);
        }

        $params['operation_type'] = $status === 'Archived' ? 'archive' : 'status_update';
        $params['update_payload'] = ['status' => $status];

        $preview = $this->preview($company, $user, $params);

        if (!empty($params['confirm']) && $params['confirm'] === true) {
            $completed = $this->confirm($company, $user, $preview->id, false);
            return [
                'operation_id'    => $completed->id,
                'status'          => $completed->status,
                'success_count'   => $completed->success_count,
                'failed_count'    => $completed->failed_count,
                'results_summary' => $completed->results_summary,
            ];
        }

        return [
            'operation_id'     => $preview->id,
            'status'           => 'previewed',
            'total_matching'   => $preview->total_matching,
            'eligible_count'   => $preview->eligible_count,
            'ineligible_count' => $preview->ineligible_count,
            'ineligible_items' => $preview->preview_data['ineligible_items'] ?? [],
            'preview_sample'   => $preview->preview_data['preview_sample'] ?? [],
        ];
    }

    /**
     * Direct Field Update Shortcut.
     */
    public function directFieldUpdate(Company $company, User $user, array $params): array
    {
        $params['operation_type'] = 'field_update';
        $preview = $this->preview($company, $user, $params);

        if (!empty($params['confirm']) && $params['confirm'] === true) {
            $completed = $this->confirm($company, $user, $preview->id, false);
            return [
                'operation_id'    => $completed->id,
                'status'          => $completed->status,
                'success_count'   => $completed->success_count,
                'failed_count'    => $completed->failed_count,
                'results_summary' => $completed->results_summary,
            ];
        }

        return [
            'operation_id'     => $preview->id,
            'status'           => 'previewed',
            'total_matching'   => $preview->total_matching,
            'eligible_count'   => $preview->eligible_count,
            'ineligible_count' => $preview->ineligible_count,
            'ineligible_items' => $preview->preview_data['ineligible_items'] ?? [],
            'preview_sample'   => $preview->preview_data['preview_sample'] ?? [],
        ];
    }

    /**
     * Direct Archive Shortcut.
     */
    public function directArchive(Company $company, User $user, array $params): array
    {
        $params['status'] = 'Archived';
        return $this->directStatusUpdate($company, $user, $params);
    }

    /**
     * Direct Restore Shortcut (from Archived to Deactive).
     */
    public function directRestore(Company $company, User $user, array $params): array
    {
        $params['operation_type'] = 'restore';
        $params['update_payload'] = ['status' => 'Deactive'];
        $preview = $this->preview($company, $user, $params);

        if (!empty($params['confirm']) && $params['confirm'] === true) {
            $completed = $this->confirm($company, $user, $preview->id, false);
            return [
                'operation_id'    => $completed->id,
                'status'          => $completed->status,
                'success_count'   => $completed->success_count,
                'failed_count'    => $completed->failed_count,
                'results_summary' => $completed->results_summary,
            ];
        }

        return [
            'operation_id'     => $preview->id,
            'status'           => 'previewed',
            'total_matching'   => $preview->total_matching,
            'eligible_count'   => $preview->eligible_count,
            'ineligible_count' => $preview->ineligible_count,
            'ineligible_items' => $preview->preview_data['ineligible_items'] ?? [],
            'preview_sample'   => $preview->preview_data['preview_sample'] ?? [],
        ];
    }

    // ==========================================
    // INTERNAL HELPER LOGIC
    // ==========================================

    /**
     * Resolve matching records strictly scoped to the tenant seller company.
     */
    public function resolveRecords(Company $company, string $targetEntity, string $selectionMode, array $criteria): Collection
    {
        $ownerUserId = $company->user_id;

        if ($targetEntity === 'product') {
            $query = Product::where('user_id', $ownerUserId);
        } else {
            // Default: 'listing'
            $query = SellerListing::where('company_id', $company->id)->with(['product', 'variant']);
        }

        if ($selectionMode === 'ids') {
            $ids = is_array($criteria) ? array_filter(array_map('intval', $criteria)) : [];
            $ids = array_unique($ids);
            if (empty($ids)) {
                return new Collection();
            }
            return $query->whereIn('id', $ids)->get();
        }

        // Selection by filter
        $this->applyFiltersToQuery($query, $criteria, $targetEntity);

        return $query->take(self::MAX_BATCH_SIZE + 1)->get();
    }

    /**
     * Re-fetch specific records by IDs strictly scoped to tenant.
     */
    protected function resolveRecordsByIds(Company $company, string $targetEntity, array $ids): Collection
    {
        if ($targetEntity === 'product') {
            return Product::where('user_id', $company->user_id)->whereIn('id', $ids)->get();
        }

        return SellerListing::where('company_id', $company->id)
            ->with(['product', 'variant'])
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Apply filter criteria to Eloquent query.
     */
    protected function applyFiltersToQuery(Builder $query, array $filters, string $targetEntity): void
    {
        if (!empty($filters['status'])) {
            $statusVal = $this->normalizeStatus($filters['status']);
            $query->where('status', $statusVal);
        }

        if ($targetEntity === 'listing') {
            if (!empty($filters['category_id'])) {
                $query->whereHas('product', fn($q) => $q->where('category_id', (int)$filters['category_id']));
            }
            if (!empty($filters['subcategory_id'])) {
                $query->whereHas('product', fn($q) => $q->where('subcategory_id', (int)$filters['subcategory_id']));
            }
            if (!empty($filters['brand_id'])) {
                $query->whereHas('product', fn($q) => $q->where('brand_id', (int)$filters['brand_id']));
            }
            if (!empty($filters['seller_sku'])) {
                $query->where('seller_sku', 'LIKE', '%' . trim($filters['seller_sku']) . '%');
            }
            if (!empty($filters['search'])) {
                $s = trim($filters['search']);
                $query->where(function ($q) use ($s) {
                    $q->where('seller_sku', 'LIKE', "%{$s}%")
                      ->orWhereHas('product', fn($pq) => $pq->where('name', 'LIKE', "%{$s}%")->orWhere('title', 'LIKE', "%{$s}%"));
                });
            }
        } else {
            // Product query
            if (!empty($filters['category_id'])) {
                $query->where('category_id', (int)$filters['category_id']);
            }
            if (!empty($filters['subcategory_id'])) {
                $query->where('subcategory_id', (int)$filters['subcategory_id']);
            }
            if (!empty($filters['brand_id'])) {
                $query->where('brand_id', (int)$filters['brand_id']);
            }
            if (!empty($filters['search'])) {
                $s = trim($filters['search']);
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'LIKE', "%{$s}%")->orWhere('title', 'LIKE', "%{$s}%");
                });
            }
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Evaluate eligibility of a single item for the requested operation.
     */
    protected function evaluateItemEligibility(
        Model $item,
        string $targetEntity,
        string $operationType,
        array $payload,
        Company $company,
        User $user
    ): array {
        // 1. Verify tenant ownership
        if ($targetEntity === 'listing') {
            if ($item->company_id !== $company->id) {
                return ['is_eligible' => false, 'reason' => 'Item does not belong to your seller company.'];
            }
        } else {
            if ($item->user_id !== $company->user_id) {
                return ['is_eligible' => false, 'reason' => 'Product definition is not owned by your company.'];
            }
        }

        // 2. Status update / Archive / Restore evaluations
        if (in_array($operationType, ['status_update', 'archive', 'restore'], true)) {
            $targetStatus = $this->normalizeStatus($payload['status'] ?? '');

            if ($operationType === 'archive') {
                $targetStatus = 'Archived';
            } elseif ($operationType === 'restore') {
                $targetStatus = 'Deactive';
            }

            $currentStatus = $this->normalizeStatus($item->status ?? '');

            // Redundant status check
            if ($currentStatus === $targetStatus) {
                return [
                    'is_eligible' => false,
                    'reason'      => "Item is already in [{$targetStatus}] status.",
                ];
            }

            // Restore requires current status to be Archived
            if ($operationType === 'restore' && $currentStatus !== 'Archived') {
                return [
                    'is_eligible' => false,
                    'reason'      => "Only Archived items can be restored (current status: [{$currentStatus}]).",
                ];
            }

            // Activating checks
            if ($targetStatus === 'Active') {
                if ($targetEntity === 'listing') {
                    // Check pricing validity
                    if ((float)$item->seller_price <= 0 || (float)$item->selling_price <= 0) {
                        return [
                            'is_eligible' => false,
                            'reason'      => 'Listing cannot be activated because price is ₹0.00. Please set a positive selling price first.',
                        ];
                    }
                    // Check parent product status
                    if ($item->product) {
                        $parentStatus = $this->normalizeStatus($item->product->status ?? '');
                        if ($parentStatus !== 'Active') {
                            return [
                                'is_eligible' => false,
                                'reason'      => 'Listing cannot be activated because parent product [' . ($item->product->name ?? 'Product') . '] is deactivated by administration.',
                            ];
                        }
                        if (!empty($item->product->approval_status) && $item->product->approval_status !== 'approved') {
                            return [
                                'is_eligible' => false,
                                'reason'      => 'Listing cannot be activated because parent product [' . ($item->product->name ?? 'Product') . '] is not approved (current status: ' . $item->product->approval_status . ').',
                            ];
                        }
                    }
                } else {
                    // Product activation
                    if (empty($item->name) || empty($item->category_id)) {
                        return [
                            'is_eligible' => false,
                            'reason'      => 'Product lacks mandatory name or category assignment.',
                        ];
                    }
                }
            }

            return [
                'is_eligible' => true,
                'field'       => 'status',
                'old_value'   => $currentStatus,
                'new_value'   => $targetStatus,
            ];
        }

        // 3. Field update evaluations
        if ($operationType === 'field_update') {
            $field  = $payload['field'] ?? '';
            $action = $payload['action'] ?? 'set';
            $val    = $payload['value'] ?? null;

            if (empty($field)) {
                return ['is_eligible' => false, 'reason' => 'No target update field specified.'];
            }

            if (in_array($field, $this->forbiddenFields, true)) {
                return ['is_eligible' => false, 'reason' => "Field [{$field}] is platform-managed and cannot be bulk modified."];
            }

            if (!isset($this->permittedFields[$field])) {
                return ['is_eligible' => false, 'reason' => "Field [{$field}] is not supported for bulk modification."];
            }

            $fieldRule = $this->permittedFields[$field];

            // Evaluate value calculation
            if ($field === 'dynamic_attribute') {
                return $this->evaluateDynamicAttributeEligibility($item, $targetEntity, $payload);
            }

            $currentVal = $item->{$field} ?? 0;
            $newVal     = $this->computeNewFieldValue($currentVal, $action, $val, $fieldRule);

            if ($newVal === null) {
                return ['is_eligible' => false, 'reason' => "Invalid value or calculation mode for field [{$field}]."];
            }

            // Range validation
            if (isset($fieldRule['min']) && $newVal < $fieldRule['min']) {
                return ['is_eligible' => false, 'reason' => "Calculated value [{$newVal}] is below minimum allowed [{$fieldRule['min']}]."];
            }
            if (isset($fieldRule['max']) && $newVal > $fieldRule['max']) {
                return ['is_eligible' => false, 'reason' => "Calculated value [{$newVal}] exceeds maximum allowed [{$fieldRule['max']}]."];
            }

            // MRP cross-check if seller_price is being modified
            if ($field === 'seller_price' && $targetEntity === 'listing') {
                $currentMrp = (float)($item->mrp ?: 0);
                if ($currentMrp > 0 && $newVal > $currentMrp) {
                    return [
                        'is_eligible' => false,
                        'reason'      => "New seller price (₹{$newVal}) would exceed current MRP (₹{$currentMrp}).",
                    ];
                }
            }

            return [
                'is_eligible' => true,
                'field'       => $field,
                'old_value'   => $currentVal,
                'new_value'   => $newVal,
            ];
        }

        return ['is_eligible' => false, 'reason' => 'Unsupported operation type.'];
    }

    /**
     * Compute newly calculated field value based on action mode.
     */
    protected function computeNewFieldValue(mixed $current, string $action, mixed $val, array $rule): mixed
    {
        $type = $rule['type'] ?? 'decimal';

        if ($type === 'boolean') {
            return (bool)$val;
        }

        if ($action === 'set') {
            if ($type === 'integer') {
                return is_numeric($val) ? (int)$val : null;
            }
            return is_numeric($val) ? round((float)$val, 2) : null;
        }

        if (!is_numeric($current) || !is_numeric($val)) {
            return null;
        }

        $c = (float)$current;
        $v = (float)$val;

        $res = match ($action) {
            'increase_percent' => $c * (1 + $v / 100),
            'decrease_percent' => max(0, $c * (1 - $v / 100)),
            'increase_fixed'   => $c + $v,
            'decrease_fixed'   => max(0, $c - $v),
            default            => null,
        };

        if ($res === null) {
            return null;
        }

        return $type === 'integer' ? (int)round($res) : round($res, 2);
    }

    /**
     * Dynamic Attribute eligibility evaluation.
     */
    protected function evaluateDynamicAttributeEligibility(Model $item, string $targetEntity, array $payload): array
    {
        $productId = $targetEntity === 'product' ? $item->id : ($item->product_id ?? null);
        if (!$productId) {
            return ['is_eligible' => false, 'reason' => 'No parent product associated with this item.'];
        }

        $attrKey = $payload['attribute_id'] ?? ($payload['attribute_code'] ?? null);
        $attrVal = $payload['attribute_value'] ?? ($payload['value'] ?? null);

        if (!$attrKey || $attrVal === null) {
            return ['is_eligible' => false, 'reason' => 'Attribute key or value is missing.'];
        }

        $attr = is_numeric($attrKey)
            ? Attribute::whereIn('status', ['Active', '1', 1])->find((int)$attrKey)
            : Attribute::whereIn('status', ['Active', '1', 1])->where('code', $attrKey)->first();

        if (!$attr) {
            return ['is_eligible' => false, 'reason' => "Dynamic attribute [{$attrKey}] is not active or registered."];
        }

        // Validate option if select type
        if (in_array($attr->type, ['select', 'single_select', 'multi_select', 'multi-select'], true)) {
            $opt = AttributeOption::where('attribute_id', $attr->id)
                ->where(function ($q) use ($attrVal) {
                    $q->where('id', is_numeric($attrVal) ? (int)$attrVal : 0)
                      ->orWhere('label', $attrVal)
                      ->orWhere('value', $attrVal);
                })->first();

            if (!$opt) {
                return ['is_eligible' => false, 'reason' => "Option [{$attrVal}] is not permitted for attribute [{$attr->name}]."];
            }
        }

        return [
            'is_eligible' => true,
            'field'       => "dynamic_attribute:{$attr->code}",
            'old_value'   => 'Current Attribute Value',
            'new_value'   => (string)$attrVal,
            'details'     => ['attribute_id' => $attr->id, 'attribute_code' => $attr->code],
        ];
    }

    /**
     * Apply verified change to a single item inside transaction.
     */
    protected function applyChangeToItem(
        Model $item,
        string $targetEntity,
        string $operationType,
        array $payload,
        Company $company
    ): array {
        if (in_array($operationType, ['status_update', 'archive', 'restore'], true)) {
            $oldStatus = $this->normalizeStatus($item->status ?? '');
            $newStatus = $this->normalizeStatus($payload['status'] ?? '');

            if ($operationType === 'archive') {
                $newStatus = 'Archived';
            } elseif ($operationType === 'restore') {
                $newStatus = 'Deactive';
            }

            $item->update(['status' => $newStatus]);

            return ['old_value' => $oldStatus, 'new_value' => $newStatus];
        }

        if ($operationType === 'field_update') {
            $field  = $payload['field'];
            $action = $payload['action'] ?? 'set';
            $val    = $payload['value'];

            if ($field === 'dynamic_attribute') {
                $attrId = $payload['attribute_id'] ?? null;
                $attr = is_numeric($attrId)
                    ? Attribute::find((int)$attrId)
                    : Attribute::where('code', $payload['attribute_code'] ?? '')->first();

                $productId = $targetEntity === 'product' ? $item->id : $item->product_id;
                $opt = AttributeOption::where('attribute_id', $attr->id)
                    ->where(function ($q) use ($val) {
                        $q->where('id', is_numeric($val) ? (int)$val : 0)
                          ->orWhere('label', $val)
                          ->orWhere('value', $val);
                    })->first();

                $oldVal = ProductAttributeValue::where('product_id', $productId)->where('attribute_id', $attr->id)->first();
                $oldStr = $oldVal ? ($oldVal->option?->label ?? $oldVal->text_value) : null;

                ProductAttributeValue::updateOrCreate(
                    ['product_id' => $productId, 'attribute_id' => $attr->id],
                    [
                        'attribute_option_id' => $opt?->id,
                        'text_value'          => $opt ? null : (string)$val,
                        'number_value'        => is_numeric($val) ? (float)$val : null,
                    ]
                );

                return ['old_value' => $oldStr ?: 'None', 'new_value' => (string)($opt?->label ?? $val)];
            }

            $rule = $this->permittedFields[$field];
            $oldVal = $item->{$field};
            $newVal = $this->computeNewFieldValue($oldVal, $action, $val, $rule);

            $updateData = [$field => $newVal];

            // Recompute selling price if seller_price or commission_rate changes on listing
            if ($targetEntity === 'listing' && in_array($field, ['seller_price', 'commission_rate'], true)) {
                $sPrice = $field === 'seller_price' ? $newVal : (float)$item->seller_price;
                $cRate  = $field === 'commission_rate' ? $newVal : (float)$item->commission_rate;
                $newSellingPrice = SellerListing::calculateSellingPrice($sPrice, $cRate);
                $updateData['selling_price'] = $newSellingPrice;

                // Adjust MRP if new selling price exceeds current MRP
                if ((float)$item->mrp < $newSellingPrice) {
                    $updateData['mrp'] = round($newSellingPrice * 1.15, 2);
                }
            }

            $item->update($updateData);

            return ['old_value' => $oldVal, 'new_value' => $newVal];
        }

        throw new \InvalidArgumentException("Invalid operation type [{$operationType}].");
    }

    /**
     * Get a human-readable identifier for an item.
     */
    protected function getItemIdentifier(Model $item, string $targetEntity): string
    {
        if ($targetEntity === 'listing') {
            return $item->seller_sku ?: ($item->product?->name ? $item->product->name . " (#{$item->id})" : "Listing #{$item->id}");
        }

        return $item->name ?: "Product #{$item->id}";
    }

    /**
     * Validate operation request structure.
     */
    protected function validateOperationRequest(
        string $operationType,
        string $targetEntity,
        string $selectionMode,
        mixed $criteria,
        mixed $payload
    ): void {
        if (!in_array($operationType, ['status_update', 'field_update', 'archive', 'restore'], true)) {
            throw ValidationException::withMessages([
                'operation_type' => "Unsupported operation type [{$operationType}].",
            ]);
        }

        if (!in_array($targetEntity, ['listing', 'product'], true)) {
            throw ValidationException::withMessages([
                'target_entity' => "Target entity must be either 'listing' or 'product'.",
            ]);
        }

        if (!in_array($selectionMode, ['ids', 'filter'], true)) {
            throw ValidationException::withMessages([
                'selection_mode' => "Selection mode must be either 'ids' or 'filter'.",
            ]);
        }

        if ($selectionMode === 'ids' && (empty($criteria) || !is_array($criteria))) {
            throw ValidationException::withMessages([
                'selection_criteria' => 'Selection criteria must contain a non-empty list of IDs.',
            ]);
        }

        if ($selectionMode === 'ids' && is_array($criteria) && count($criteria) > self::MAX_BATCH_SIZE) {
            throw ValidationException::withMessages([
                'ids' => 'Bulk operations are capped at ' . self::MAX_BATCH_SIZE . ' records per batch.',
            ]);
        }

        // Check for forbidden / platform-managed fields
        if ($operationType === 'field_update' && is_array($payload)) {
            $keysToCheck = array_keys($payload);
            if (isset($payload['field'])) {
                $keysToCheck[] = $payload['field'];
            }
            foreach ($keysToCheck as $k) {
                if (in_array(strtolower($k), $this->forbiddenFields, true)) {
                    throw ValidationException::withMessages([
                        'payload' => "Field [{$k}] is platform-managed and cannot be bulk modified.",
                    ]);
                }
            }
        }
    }

    /**
     * Authorize user permissions for requested operation & fields.
     */
    protected function authorizeOperation(User $user, string $operationType, array $payload): void
    {
        if ($user->isAdmin() || $user->isSellerOwner()) {
            return;
        }

        // Status update / Archive / Restore
        if ($operationType === 'status_update') {
            if (!$user->hasSellerPermission('products.toggle_listing')) {
                abort(403, 'Permission denied: products.toggle_listing required to change catalogue status.');
            }
        } elseif ($operationType === 'archive' || $operationType === 'restore') {
            if (!$user->hasSellerPermission('products.edit') || !$user->hasSellerPermission('products.toggle_listing')) {
                abort(403, 'Permission denied: products.edit and products.toggle_listing required to archive or restore catalogue items.');
            }
        } elseif ($operationType === 'field_update') {
            $field = $payload['field'] ?? null;
            if (!$field || !isset($this->permittedFields[$field])) {
                return;
            }

            $reqPerm = $this->permittedFields[$field]['permission'];
            if (!$user->hasSellerPermission($reqPerm)) {
                abort(403, "Permission denied: {$reqPerm} is required to update [{$field}].");
            }
        }
    }

    /**
     * Record execution event in CompanyAuditLog for audit history.
     */
    protected function recordCompanyAuditLog(SellerCatalogueBulkOperation $operation, Company $company): void
    {
        try {
            CompanyAuditLog::logChange(
                $company->id,
                'catalogue_bulk_' . $operation->operation_type,
                "Bulk {$operation->operation_type} executed",
                "Successfully updated {$operation->success_count} catalogue records (failed: {$operation->failed_count}).",
                $operation->preview_data['preview_sample'] ?? [],
                $operation->results_summary ?? [],
                $operation->user_id,
                $operation->ip_address
            );
        } catch (\Throwable $e) {
            Log::warning("Could not record CompanyAuditLog for bulk op #{$operation->id}: " . $e->getMessage());
        }
    }
}
