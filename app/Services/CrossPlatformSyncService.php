<?php

namespace App\Services;

use App\Jobs\SyncPlatformIntegrationEventJob;
use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Company;
use App\Models\Customer;
use App\Models\IntegrationChannelMapping;
use App\Models\IntegrationReconciliationRecord;
use App\Models\IntegrationSyncAuditLog;
use App\Models\IntegrationSyncEvent;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
use App\Models\OrderTaxSnapshot;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\TaxMaster;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CrossPlatformSyncService
{
    public const CHANNEL_LARAVEL_BACKEND = 'laravel_backend';
    public const CHANNEL_FLUTTER_MOBILE  = 'flutter_mobile';
    public const CHANNEL_CRM_EXTERNAL    = 'crm_external';
    public const CHANNEL_ADMIN_PANEL     = 'admin_panel';
    public const CHANNEL_SELLER_PANEL    = 'seller_panel';
    public const CHANNEL_PUBLIC_WEBSITE  = 'public_website';
    public const CHANNEL_PAYMENT_GATEWAY = 'payment_gateway';
    public const CHANNEL_LEGACY_V1       = 'legacy_v1_attributes';

    /**
     * Authoritative field-ownership permissions per external/channel inbound sync.
     * Financial, commission, tax, order-total and settlement fields cannot be overwritten externally.
     */
    protected array $inboundWritableFields = [
        'crm_external' => [
            'customer' => ['location'],
            'seller'   => ['city', 'state', 'pincode'],
        ],
        'flutter_mobile' => [
            'customer' => ['name', 'location', 'deviceid'],
        ],
        'seller_panel' => [
            'sku'   => ['stock_quantity', 'dispatch_lead_days', 'min_order_quantity', 'order_multiple'],
            'stock' => ['stock_quantity'],
        ],
        'admin_panel' => [
            'seller'   => ['name', 'city', 'state', 'pincode', 'status', 'comission'],
            'product'  => ['name', 'title', 'description', 'status', 'hsncode', 'tax'],
            'category' => ['name', 'title', 'status', 'sequence'],
            'variant'  => ['variant_name', 'pack_litres', 'status'],
            'sku'      => ['stock_quantity', 'selling_price', 'seller_price', 'status'],
            'customer' => ['name', 'location', 'status'],
            'stock'    => ['stock_quantity'],
        ],
    ];

    /**
     * Approved public storefront allowlist per entity (strips internal cost, commission, KYC & private PII).
     */
    protected array $publicAllowlist = [
        'product' => ['id', 'name', 'slug', 'title', 'brand_id', 'category_id', 'subcategory_id', 'image', 'description', 'status', 'approval_status'],
        'variant' => ['id', 'product_id', 'variant_sku', 'variant_name', 'pack_litres', 'image', 'status'],
        'sku'     => ['listing_id', 'id', 'product_id', 'product_variant_id', 'seller_sku', 'selling_price', 'mrp', 'min_order_quantity', 'order_multiple', 'case_pack_quantity', 'allow_unit_orders', 'allow_carton_orders', 'dispatch_lead_days', 'status', 'in_stock', 'stock_status', 'seller_name'],
        'seller'  => ['id', 'name', 'city', 'state', 'photo', 'status'],
        'category'=> ['id', 'name', 'title', 'image', 'maincategory_id', 'status'],
    ];

    /**
     * Resolve canonical identity & authoritative state for any of the 11 core marketplace entities.
     */
    public function getCanonicalIdentityMap(string $entityType, int $canonicalId): array
    {
        $entityType = strtolower(trim($entityType));

        [$canonicalRef, $authoritativeTable, $summary] = match ($entityType) {
            'seller' => (function () use ($canonicalId) {
                $company = Company::with('user:id,name,email')->findOrFail($canonicalId);
                return [
                    $company->gst ?: ($company->user?->email ?: 'SELLER-' . $company->id),
                    'companies + users',
                    ['company_id' => $company->id, 'user_id' => $company->user_id, 'name' => $company->name, 'status' => $company->status, 'comission' => (float)$company->comission],
                ];
            })(),
            'product' => (function () use ($canonicalId) {
                $product = Product::findOrFail($canonicalId);
                return [
                    $product->slug ?: 'PROD-' . $product->id,
                    'products',
                    ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'category_id' => $product->category_id, 'status' => $product->status, 'approval_status' => $product->approval_status],
                ];
            })(),
            'variant' => (function () use ($canonicalId) {
                $variant = ProductVariant::findOrFail($canonicalId);
                return [
                    $variant->variant_sku,
                    'product_variants',
                    ['id' => $variant->id, 'product_id' => $variant->product_id, 'variant_sku' => $variant->variant_sku, 'status' => $variant->status],
                ];
            })(),
            'sku', 'price', 'stock' => (function () use ($canonicalId) {
                $listing = SellerListing::with('variant:id,variant_sku')->findOrFail($canonicalId);
                return [
                    $listing->seller_sku ?: ($listing->variant?->variant_sku ?: 'SKU-' . $listing->id),
                    'seller_listings',
                    [
                        'listing_id'      => $listing->id,
                        'product_id'      => $listing->product_id,
                        'variant_id'      => $listing->product_variant_id,
                        'variant_sku'     => $listing->variant?->variant_sku,
                        'seller_sku'      => $listing->seller_sku,
                        'selling_price'   => (float)$listing->selling_price,
                        'stock_quantity'  => (int)$listing->stock_quantity,
                        'available_stock' => (int)$listing->available_stock,
                        'status'          => $listing->status,
                    ],
                ];
            })(),
            'category' => (function () use ($canonicalId) {
                $cat = Category::findOrFail($canonicalId);
                return [
                    $cat->name,
                    'categories',
                    ['id' => $cat->id, 'name' => $cat->name, 'maincategory_id' => $cat->maincategory_id, 'status' => $cat->status],
                ];
            })(),
            'customer' => (function () use ($canonicalId) {
                $customer = Customer::findOrFail($canonicalId);
                return [
                    $customer->email ?: ($customer->mobile ?: 'CUST-' . $customer->id),
                    'customers',
                    ['id' => $customer->id, 'name' => $customer->name, 'location' => $customer->location, 'status' => $customer->status],
                ];
            })(),
            'order' => (function () use ($canonicalId) {
                $order = Order::findOrFail($canonicalId);
                return [
                    $order->orderno,
                    'orders',
                    ['id' => $order->id, 'orderno' => $order->orderno, 'customer_id' => $order->customer_id, 'payment_status' => $order->payment_status, 'grandtotal' => (float)$order->grandtotal],
                ];
            })(),
            'commission' => (function () use ($canonicalId) {
                $snap = OrderCommissionSnapshot::find($canonicalId);
                if ($snap) {
                    return ['COMM-SNAP-' . $snap->id, 'order_commission_snapshots', ['id' => $snap->id, 'order_id' => $snap->order_id, 'commission_percentage' => (float)$snap->commission_percentage, 'commission_amount' => (float)$snap->commission_amount]];
                }
                $rule = CommissionRule::findOrFail($canonicalId);
                return [$rule->rule_code, 'commission_rules', ['id' => $rule->id, 'rule_code' => $rule->rule_code, 'commission_percentage' => (float)$rule->commission_percentage, 'status' => $rule->status]];
            })(),
            'tax' => (function () use ($canonicalId) {
                $snap = OrderTaxSnapshot::find($canonicalId);
                if ($snap) {
                    return ['TAX-SNAP-' . $snap->id, 'order_tax_snapshots', ['id' => $snap->id, 'order_id' => $snap->order_id, 'hsn_code' => $snap->hsn_code, 'tax_rate' => (float)$snap->tax_rate, 'total_tax_amount' => (float)$snap->total_tax_amount]];
                }
                $master = TaxMaster::findOrFail($canonicalId);
                return [$master->hsn_code, 'tax_masters', ['id' => $master->id, 'hsn_code' => $master->hsn_code, 'gst_rate' => (float)$master->gst_rate, 'approval_status' => $master->approval_status]];
            })(),
            default => throw ValidationException::withMessages([
                'entity_type' => ["Unsupported canonical entity type '{$entityType}'."],
            ]),
        };

        $mappings = IntegrationChannelMapping::where('entity_type', $entityType)
            ->where('canonical_id', $canonicalId)
            ->get();

        return [
            'entity_type'         => $entityType,
            'canonical_id'        => $canonicalId,
            'canonical_ref'       => $canonicalRef,
            'authoritative_table' => $authoritativeTable,
            'summary'             => $summary,
            'channel_mappings'    => $mappings,
        ];
    }

    /**
     * Register or update a canonical <-> external/legacy channel ID mapping without creating duplicate masters.
     */
    public function registerChannelMapping(
        string $entityType,
        int $canonicalId,
        string $canonicalRef,
        string $channel,
        string $externalId,
        ?string $externalRef = null,
        ?string $payloadHash = null,
        ?User $actor = null
    ): IntegrationChannelMapping {
        $mapping = IntegrationChannelMapping::updateOrCreate(
            [
                'entity_type'  => $entityType,
                'canonical_id' => $canonicalId,
                'channel'      => $channel,
            ],
            [
                'canonical_ref'  => $canonicalRef,
                'external_id'    => $externalId,
                'external_ref'   => $externalRef,
                'payload_hash'   => $payloadHash,
                'mapping_status' => IntegrationChannelMapping::STATUS_ACTIVE,
                'last_synced_at' => now(),
            ]
        );

        $this->logAudit(null, null, $entityType, $canonicalRef, 'mapping_registered', $actor, $channel, null, $mapping->toArray());

        return $mapping;
    }

    /**
     * Strip all non-public attributes (seller cost, commission rates, KYC/payout details, private contact fields).
     */
    public function sanitizePublicPayload(string $entityType, array $raw): array
    {
        $entityType = strtolower(trim($entityType));
        $allowed = $this->publicAllowlist[$entityType] ?? ['id', 'name', 'status'];
        $clean = array_intersect_key($raw, array_flip($allowed));

        foreach (['seller_price', 'commission_rate', 'comission', 'payout_details', 'gst', 'mobile', 'email', 'admin_notes'] as $forbidden) {
            unset($clean[$forbidden]);
        }

        return $clean;
    }

    /**
     * Redact bearer tokens, passwords, secrets, and API keys before writing logs or diagnostics.
     */
    public function sanitizeErrorMessage(string $message): string
    {
        $redacted = preg_replace('/(Bearer\s+)[A-Za-z0-9\-\._~\+\/]+=*/i', '$1[REDACTED]', $message) ?? $message;
        $redacted = preg_replace('/(api[_-]?key|secret|token|password)\s*[:=]\s*[^\s,;]+/i', '$1=[REDACTED]', $redacted) ?? $redacted;

        return Str::limit($redacted, 1000);
    }

    public function redactSensitiveData(array $payload): array
    {
        $sensitiveKeys = ['password', 'token', 'access_token', 'api_key', 'secret', 'encKey', 'Authorization', 'payout_details'];
        $sanitized = [];

        foreach ($payload as $key => $val) {
            $isSensitive = false;
            foreach ($sensitiveKeys as $needle) {
                if (stripos((string)$key, $needle) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($val)) {
                $sanitized[$key] = $this->redactSensitiveData($val);
            } else {
                $sanitized[$key] = is_string($val) ? $this->sanitizeErrorMessage($val) : $val;
            }
        }

        return $sanitized;
    }

    /**
     * Transactional outbox writer with strict idempotency deduplication.
     */
    public function recordAndDispatchSyncEvent(
        string $targetChannel,
        string $entityType,
        ?int $canonicalId,
        ?string $canonicalRef,
        string $action,
        array $payload = [],
        ?string $idempotencyKey = null,
        ?User $actor = null,
        string $direction = IntegrationSyncEvent::DIRECTION_OUTBOUND,
        bool $dispatchAsync = true
    ): array {
        $redactedPayload = $this->redactSensitiveData($payload);
        $payloadHash = hash('sha256', json_encode($redactedPayload));
        $resolvedKey = $idempotencyKey ?: "{$direction}:{$targetChannel}:{$entityType}:{$canonicalId}:{$action}:{$payloadHash}";

        $existing = IntegrationSyncEvent::where('idempotency_key', $resolvedKey)->first();
        if ($existing) {
            return ['event' => $existing, 'is_duplicate' => true];
        }

        $event = IntegrationSyncEvent::create([
            'event_ref'       => 'SYNC-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
            'idempotency_key' => $resolvedKey,
            'direction'       => $direction,
            'source_channel'  => self::CHANNEL_LARAVEL_BACKEND,
            'target_channel'  => $targetChannel,
            'entity_type'     => $entityType,
            'canonical_id'    => $canonicalId,
            'canonical_ref'   => $canonicalRef,
            'action'          => $action,
            'payload_json'    => $redactedPayload,
            'payload_hash'    => $payloadHash,
            'status'          => IntegrationSyncEvent::STATUS_PENDING,
            'attempts'        => 0,
            'max_attempts'    => (int)config('marketplace_integration.default_max_attempts', 3),
            'actor_user_id'   => $actor?->id,
            'actor_channel'   => self::CHANNEL_LARAVEL_BACKEND,
            'conflict_policy' => config('marketplace_integration.conflict_policy', 'authoritative_reject_and_log_diff'),
        ]);

        $this->logAudit($event->id, null, $entityType, $canonicalRef, 'event_enqueued', $actor, $targetChannel, null, [
            'event_ref'       => $event->event_ref,
            'idempotency_key' => $event->idempotency_key,
        ]);

        if ($dispatchAsync) {
            SyncPlatformIntegrationEventJob::dispatch($event->id);
        }

        return ['event' => $event->fresh(), 'is_duplicate' => false];
    }

    /**
     * Execute delivery/projection for a queued integration sync event.
     */
    public function processSyncEvent(IntegrationSyncEvent $event): IntegrationSyncEvent
    {
        $event->increment('attempts');
        $event->update(['status' => IntegrationSyncEvent::STATUS_PROCESSING]);

        try {
            // Safe gating for external CRM synchronization until CRM API contract & credentials are approved
            if ($event->target_channel === self::CHANNEL_CRM_EXTERNAL) {
                $isGstCheck = ($event->action === 'gst_check_log');
                if (!$isGstCheck && !config('marketplace_integration.crm_sync_enabled', false)) {
                    $event->update([
                        'status'       => IntegrationSyncEvent::STATUS_SKIPPED_DISABLED,
                        'processed_at' => now(),
                        'last_error'   => 'CRM sync skipped safely: APNI_CRM_SYNC_ENABLED is false pending approval.',
                    ]);
                    $this->logAudit($event->id, null, $event->entity_type, $event->canonical_ref, 'crm_sync_skipped_disabled', null, $event->target_channel);
                    return $event->fresh();
                }

                if ($isGstCheck) {
                    $event->update([
                        'status'       => IntegrationSyncEvent::STATUS_PROCESSED,
                        'processed_at' => now(),
                        'last_error'   => null,
                    ]);
                    return $event->fresh();
                }

                $token = config('services.crm.api_token');
                $baseUrl = rtrim((string)config('services.crm.base_url'), '/');
                if (empty($token) || empty($baseUrl)) {
                    throw new \RuntimeException('CRM sync enabled without APNI_CRM_API_TOKEN or APNI_CRM_BASE_URL.');
                }

                $resp = Http::withToken($token)
                    ->timeout((int)config('marketplace_integration.crm_timeout_seconds', 5))
                    ->post("{$baseUrl}/api/v1/sync/{$event->entity_type}", $event->payload_json ?? []);

                if (!$resp->successful()) {
                    $event->update(['last_http_status' => $resp->status()]);
                    throw new \RuntimeException("CRM HTTP {$resp->status()} error during {$event->entity_type} sync.");
                }
            }

            if ($event->target_channel === self::CHANNEL_LEGACY_V1 && $event->entity_type === 'sku' && $event->canonical_id) {
                $listing = SellerListing::find($event->canonical_id);
                if ($listing) {
                    $this->projectListingToMappedLegacyAttribute($listing, $event->actor_user_id ? User::find($event->actor_user_id) : null);
                }
            }

            $event->update([
                'status'       => IntegrationSyncEvent::STATUS_PROCESSED,
                'processed_at' => now(),
                'last_error'   => null,
            ]);

            $this->logAudit($event->id, null, $event->entity_type, $event->canonical_ref, 'event_processed', null, $event->target_channel);
        } catch (\Throwable $e) {
            $sanitized = $this->sanitizeErrorMessage($e->getMessage());
            $dead = $event->attempts >= $event->max_attempts;

            $event->update([
                'status'        => $dead ? IntegrationSyncEvent::STATUS_DEAD_LETTER : IntegrationSyncEvent::STATUS_FAILED,
                'last_error'    => $sanitized,
                'next_retry_at' => $dead ? null : now()->addSeconds(60),
            ]);

            $this->logAudit($event->id, null, $event->entity_type, $event->canonical_ref, $dead ? 'event_dead_letter' : 'event_failed', null, $event->target_channel, null, [
                'error' => $sanitized,
            ]);
        }

        return $event->fresh();
    }

    /**
     * Synchronize authoritative SellerListing price/stock changes -> mapped legacy ProductAttributes (zero silent overwrite).
     */
    public function syncListingToLegacyAttribute(SellerListing $listing, ?User $actor = null, string $reason = 'canonical_sync'): array
    {
        $syncRes = $this->recordAndDispatchSyncEvent(
            self::CHANNEL_LEGACY_V1,
            'sku',
            $listing->id,
            $listing->seller_sku ?: ('SKU-' . $listing->id),
            $reason,
            [
                'listing_id'     => $listing->id,
                'seller_price'   => (float)$listing->seller_price,
                'selling_price'  => (float)$listing->selling_price,
                'stock_quantity' => (int)$listing->stock_quantity,
            ],
            "proj:sku:{$listing->id}:{$reason}:" . md5("{$listing->seller_price}|{$listing->selling_price}|{$listing->stock_quantity}|{$listing->status}"),
            $actor,
            IntegrationSyncEvent::DIRECTION_PROJECTION,
            false
        );

        $event = $this->processSyncEvent($syncRes['event']);

        return ['event' => $event, 'is_duplicate' => $syncRes['is_duplicate']];
    }

    protected function projectListingToMappedLegacyAttribute(SellerListing $listing, ?User $actor = null): ?IntegrationReconciliationRecord
    {
        if (!config('marketplace_integration.legacy_attribute_projection_enabled', true)) {
            return null;
        }

        $attr = $this->resolveMappedLegacyAttribute($listing);
        if (!$attr) {
            return null;
        }

        $this->registerChannelMapping(
            'sku',
            $listing->id,
            $listing->seller_sku ?: ('SKU-' . $listing->id),
            self::CHANNEL_LEGACY_V1,
            (string)$attr->id,
            "product_attributes#{$attr->id}",
            md5("{$listing->seller_price}|{$listing->selling_price}|{$listing->commission_rate}"),
            $actor
        );

        $attr->update([
            'seller_price'    => (string)$listing->seller_price,
            'commission_rate' => $listing->commission_rate,
            'price'           => (string)$listing->selling_price,
            'oldprice'        => (string)($listing->mrp ?: $listing->selling_price),
        ]);

        return null;
    }

    protected function resolveMappedLegacyAttribute(SellerListing $listing): ?ProductAttributes
    {
        $mapping = IntegrationChannelMapping::where('entity_type', 'sku')
            ->where('canonical_id', $listing->id)
            ->where('channel', self::CHANNEL_LEGACY_V1)
            ->first();

        if ($mapping && is_numeric($mapping->external_id)) {
            return ProductAttributes::find((int)$mapping->external_id);
        }

        $variant = $listing->variant ?: ProductVariant::find($listing->product_variant_id);
        if (!$variant || !$variant->shade_card_id || !$variant->box_packing_id) {
            return null;
        }

        $matches = ProductAttributes::where('product_id', $listing->product_id)
            ->where('color', $variant->shade_card_id)
            ->where('quantity', $variant->box_packing_id)
            ->get();

        // Only project automatically when match is 100% unambiguous (exact single row)
        if ($matches->count() === 1) {
            return $matches->first();
        }

        if ($matches->count() > 1) {
            $this->recordReconciliationDiff(
                'sku',
                $listing->id,
                $listing->seller_sku ?: ('SKU-' . $listing->id),
                self::CHANNEL_LARAVEL_BACKEND,
                self::CHANNEL_LEGACY_V1,
                IntegrationReconciliationRecord::TYPE_UNMAPPED_LEGACY_SKU,
                ['listing_id' => $listing->id, 'selling_price' => (float)$listing->selling_price],
                ['ambiguous_attribute_ids' => $matches->pluck('id')->all()],
                "Ambiguous legacy product_attributes matches ({$matches->count()}) found for SellerListing #{$listing->id}.",
                'medium'
            );
        }

        return null;
    }

    /**
     * Controlled inbound channel update handler: enforces idempotency, field ownership, and optimistic conflict checks.
     * NEVER performs silent last-write-wins overwrites.
     */
    public function processInboundSync(
        string $sourceChannel,
        string $entityType,
        int $canonicalId,
        array $incomingFields,
        string $idempotencyKey,
        ?User $actor = null,
        ?string $expectedUpdatedAt = null
    ): array {
        $sourceChannel = strtolower(trim($sourceChannel));
        $entityType = strtolower(trim($entityType));

        if ($existing = IntegrationSyncEvent::where('idempotency_key', $idempotencyKey)->first()) {
            return [
                'status'       => $existing->status === IntegrationSyncEvent::STATUS_REJECTED_CONFLICT ? 'conflict' : 'idempotent_replay',
                'is_duplicate' => true,
                'event'        => $existing,
            ];
        }

        $model = match ($entityType) {
            'customer' => Customer::findOrFail($canonicalId),
            'seller'   => Company::findOrFail($canonicalId),
            'sku', 'stock' => SellerListing::findOrFail($canonicalId),
            'product'  => Product::findOrFail($canonicalId),
            'category' => Category::findOrFail($canonicalId),
            'variant'  => ProductVariant::findOrFail($canonicalId),
            'commission', 'tax', 'order' => null,
            default    => throw ValidationException::withMessages(['entity_type' => ["Unsupported inbound sync entity '{$entityType}'."]]),
        };

        $permitted = $this->inboundWritableFields[$sourceChannel][$entityType] ?? [];
        $disallowed = array_diff(array_keys($incomingFields), $permitted);

        if (!empty($disallowed) || $model === null) {
            $reason = "Channel '{$sourceChannel}' attempted unauthorized modification of protected field(s): " . implode(', ', array_keys($incomingFields));
            $event = IntegrationSyncEvent::create([
                'event_ref'       => 'SYNC-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
                'idempotency_key' => $idempotencyKey,
                'direction'       => IntegrationSyncEvent::DIRECTION_INBOUND,
                'source_channel'  => $sourceChannel,
                'target_channel'  => self::CHANNEL_LARAVEL_BACKEND,
                'entity_type'     => $entityType,
                'canonical_id'    => $canonicalId,
                'canonical_ref'   => strtoupper($entityType) . "-{$canonicalId}",
                'action'          => 'inbound_sync_rejected',
                'payload_json'    => $this->redactSensitiveData($incomingFields),
                'payload_hash'    => hash('sha256', json_encode($incomingFields)),
                'status'          => IntegrationSyncEvent::STATUS_REJECTED_CONFLICT,
                'last_error'      => $reason,
                'actor_user_id'   => $actor?->id,
                'actor_channel'   => $sourceChannel,
            ]);

            $diff = $this->recordReconciliationDiff(
                $entityType,
                $canonicalId,
                $event->canonical_ref,
                self::CHANNEL_LARAVEL_BACKEND,
                $sourceChannel,
                IntegrationReconciliationRecord::TYPE_UNAUTHORIZED_OVERRIDE,
                $model ? $model->only(array_keys($incomingFields)) : ['authoritative_owner' => 'laravel_backend_immutable'],
                $this->redactSensitiveData($incomingFields),
                $reason,
                'high'
            );

            $this->logAudit($event->id, $diff->id, $entityType, $event->canonical_ref, 'inbound_field_override_rejected', $actor, $sourceChannel);

            return ['status' => 'rejected_ownership', 'event' => $event, 'reconciliation' => $diff];
        }

        // Optimistic concurrency / conflict detection (never silent last-write-wins!)
        if ($expectedUpdatedAt !== null && $model->updated_at) {
            $currentIso = $model->updated_at->toIso8601String();
            if ($currentIso !== $expectedUpdatedAt && $model->updated_at->format('Y-m-d H:i:s') !== $expectedUpdatedAt) {
                $reason = "Concurrent conflict detected on {$entityType} #{$canonicalId}: expected updated_at '{$expectedUpdatedAt}', authoritative is '{$currentIso}'.";
                $event = IntegrationSyncEvent::create([
                    'event_ref'       => 'SYNC-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
                    'idempotency_key' => $idempotencyKey,
                    'direction'       => IntegrationSyncEvent::DIRECTION_INBOUND,
                    'source_channel'  => $sourceChannel,
                    'target_channel'  => self::CHANNEL_LARAVEL_BACKEND,
                    'entity_type'     => $entityType,
                    'canonical_id'    => $canonicalId,
                    'canonical_ref'   => strtoupper($entityType) . "-{$canonicalId}",
                    'action'          => 'inbound_sync_conflict',
                    'payload_json'    => $this->redactSensitiveData($incomingFields),
                    'payload_hash'    => hash('sha256', json_encode($incomingFields)),
                    'status'          => IntegrationSyncEvent::STATUS_REJECTED_CONFLICT,
                    'last_error'      => $reason,
                    'actor_user_id'   => $actor?->id,
                    'actor_channel'   => $sourceChannel,
                ]);

                $diff = $this->recordReconciliationDiff(
                    $entityType,
                    $canonicalId,
                    $event->canonical_ref,
                    self::CHANNEL_LARAVEL_BACKEND,
                    $sourceChannel,
                    IntegrationReconciliationRecord::TYPE_CONCURRENT_CONFLICT,
                    ['updated_at' => $currentIso] + $model->only($permitted),
                    ['expected_updated_at' => $expectedUpdatedAt] + $incomingFields,
                    $reason,
                    'high'
                );

                $this->logAudit($event->id, $diff->id, $entityType, $event->canonical_ref, 'inbound_concurrent_conflict', $actor, $sourceChannel);

                return ['status' => 'conflict', 'event' => $event, 'reconciliation' => $diff];
            }
        }

        // Validated authorized write
        return DB::transaction(function () use ($model, $incomingFields, $sourceChannel, $entityType, $canonicalId, $idempotencyKey, $actor) {
            $before = $model->only(array_keys($incomingFields));
            $model->update($incomingFields);

            $event = IntegrationSyncEvent::create([
                'event_ref'       => 'SYNC-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
                'idempotency_key' => $idempotencyKey,
                'direction'       => IntegrationSyncEvent::DIRECTION_INBOUND,
                'source_channel'  => $sourceChannel,
                'target_channel'  => self::CHANNEL_LARAVEL_BACKEND,
                'entity_type'     => $entityType,
                'canonical_id'    => $canonicalId,
                'canonical_ref'   => strtoupper($entityType) . "-{$canonicalId}",
                'action'          => 'inbound_sync_applied',
                'payload_json'    => $this->redactSensitiveData($incomingFields),
                'payload_hash'    => hash('sha256', json_encode($incomingFields)),
                'status'          => IntegrationSyncEvent::STATUS_PROCESSED,
                'attempts'        => 1,
                'processed_at'    => now(),
                'actor_user_id'   => $actor?->id,
                'actor_channel'   => $sourceChannel,
            ]);

            $this->logAudit($event->id, null, $entityType, $event->canonical_ref, 'inbound_sync_applied', $actor, $sourceChannel, $before, $model->fresh()->only(array_keys($incomingFields)));

            return ['status' => 'processed', 'is_duplicate' => false, 'event' => $event, 'entity' => $model->fresh()];
        });
    }

    /**
     * Scan cross-channel data for canonical <-> legacy price/stock drift without modifying production records silently.
     */
    public function detectCrossChannelDiscrepancies(int $limit = 100): array
    {
        $detected = [];

        $mappings = IntegrationChannelMapping::where('entity_type', 'sku')
            ->where('channel', self::CHANNEL_LEGACY_V1)
            ->where('mapping_status', IntegrationChannelMapping::STATUS_ACTIVE)
            ->limit($limit)
            ->get();

        foreach ($mappings as $map) {
            $listing = SellerListing::find($map->canonical_id);
            $attr = is_numeric($map->external_id) ? ProductAttributes::find((int)$map->external_id) : null;
            if (!$listing || !$attr) {
                continue;
            }

            if (abs((float)$listing->selling_price - (float)$attr->price) >= 0.01) {
                $detected[] = $this->recordReconciliationDiff(
                    'sku',
                    $listing->id,
                    $listing->seller_sku ?: ('SKU-' . $listing->id),
                    self::CHANNEL_LARAVEL_BACKEND,
                    self::CHANNEL_LEGACY_V1,
                    IntegrationReconciliationRecord::TYPE_PRICE_DRIFT,
                    ['listing_id' => $listing->id, 'selling_price' => (float)$listing->selling_price, 'seller_price' => (float)$listing->seller_price],
                    ['attribute_id' => $attr->id, 'price' => (float)$attr->price, 'seller_price' => (float)$attr->seller_price],
                    "Selling price drift between canonical SellerListing #{$listing->id} (Rs {$listing->selling_price}) and legacy ProductAttributes #{$attr->id} (Rs {$attr->price}).",
                    'high'
                );
            }
        }

        // Also check Paid Orders missing commission/settlement records where applicable
        $unsettledPaid = Order::where('payment_status', 'Paid')
            ->whereNull('parent_order_id')
            ->doesntHave('settlement')
            ->doesntHave('childOrders')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        foreach ($unsettledPaid as $ord) {
            $detected[] = $this->recordReconciliationDiff(
                'order',
                $ord->id,
                $ord->orderno,
                self::CHANNEL_LARAVEL_BACKEND,
                self::CHANNEL_LEGACY_V1,
                IntegrationReconciliationRecord::TYPE_STATUS_DRIFT,
                ['order_id' => $ord->id, 'orderno' => $ord->orderno, 'payment_status' => $ord->payment_status],
                ['settlement_missing' => true],
                "Paid order #{$ord->orderno} has no SellerSettlement ledger record (typically created by legacy v1 placeorder path).",
                'medium'
            );
        }

        return [
            'scanned_mappings' => $mappings->count(),
            'diffs_recorded'   => count($detected),
            'records'          => $detected,
        ];
    }

    public function recordReconciliationDiff(
        string $entityType,
        ?int $canonicalId,
        ?string $canonicalRef,
        string $sourceChannel,
        string $targetChannel,
        string $mismatchType,
        array $authoritativeSnapshot,
        array $channelSnapshot,
        string $summary,
        string $severity = 'medium'
    ): IntegrationReconciliationRecord {
        $existingOpen = IntegrationReconciliationRecord::where('entity_type', $entityType)
            ->where('canonical_id', $canonicalId)
            ->where('target_channel', $targetChannel)
            ->where('mismatch_type', $mismatchType)
            ->whereIn('status', [IntegrationReconciliationRecord::STATUS_OPEN, IntegrationReconciliationRecord::STATUS_INVESTIGATING])
            ->first();

        if ($existingOpen) {
            $existingOpen->update([
                'authoritative_snapshot' => $authoritativeSnapshot,
                'channel_snapshot'       => $channelSnapshot,
                'discrepancy_summary'    => $summary,
                'severity'               => $severity,
            ]);
            return $existingOpen->fresh();
        }

        $record = IntegrationReconciliationRecord::create([
            'reconciliation_ref'     => 'RECON-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
            'entity_type'            => $entityType,
            'canonical_id'           => $canonicalId,
            'canonical_ref'          => $canonicalRef,
            'source_channel'         => $sourceChannel,
            'target_channel'         => $targetChannel,
            'mismatch_type'          => $mismatchType,
            'authoritative_snapshot' => $authoritativeSnapshot,
            'channel_snapshot'       => $channelSnapshot,
            'discrepancy_summary'    => $summary,
            'severity'               => $severity,
            'status'                 => IntegrationReconciliationRecord::STATUS_OPEN,
        ]);

        $this->logAudit(null, $record->id, $entityType, $canonicalRef, 'discrepancy_detected', null, $targetChannel, null, [
            'mismatch_type' => $mismatchType,
            'summary'       => $summary,
        ]);

        return $record;
    }

    /**
     * Safe retry policy: refuses blind retry on financial/order-changing entities unless explicitly audited override.
     */
    public function retrySyncEvent(int $eventId, User $admin, bool $reviewedFinancialOverride = false, ?string $notes = null): IntegrationSyncEvent
    {
        if (!$admin->isAdmin()) {
            throw ValidationException::withMessages(['authorization' => ['Forbidden: Administrator access required.']]);
        }

        $event = IntegrationSyncEvent::findOrFail($eventId);
        $restrictedTypes = config('marketplace_integration.non_auto_retryable_entity_types', ['payment', 'order_financial', 'commission', 'tax', 'settlement', 'payout']);

        if (in_array($event->entity_type, $restrictedTypes, true) && !$reviewedFinancialOverride) {
            throw ValidationException::withMessages([
                'safety_policy' => ["Blind retry blocked for financial/state-sensitive entity '{$event->entity_type}'. Reconcile manually or confirm audited override."],
            ]);
        }

        $event->update([
            'status' => IntegrationSyncEvent::STATUS_RETRYING,
        ]);

        $this->logAudit($event->id, null, $event->entity_type, $event->canonical_ref, 'manual_retry_triggered', $admin, $event->target_channel, null, [
            'notes' => $notes ?: 'Admin initiated safe sync retry',
        ]);

        return $this->processSyncEvent($event->fresh());
    }

    public function resolveReconciliation(int $recordId, string $action, string $notes, User $admin): IntegrationReconciliationRecord
    {
        if (!$admin->isAdmin()) {
            throw ValidationException::withMessages(['authorization' => ['Forbidden: Administrator access required.']]);
        }

        $record = IntegrationReconciliationRecord::findOrFail($recordId);
        $beforeStatus = $record->status;

        $newStatus = match ($action) {
            'reconcile_to_authoritative' => IntegrationReconciliationRecord::STATUS_RECONCILED,
            'mark_investigating'         => IntegrationReconciliationRecord::STATUS_INVESTIGATING,
            'ignore'                     => IntegrationReconciliationRecord::STATUS_IGNORED,
            'escalate'                   => IntegrationReconciliationRecord::STATUS_ESCALATED,
            default => throw ValidationException::withMessages(['action' => ["Unsupported reconciliation resolution action '{$action}'."]]),
        };

        if ($action === 'reconcile_to_authoritative' && $record->entity_type === 'sku' && $record->canonical_id) {
            $listing = SellerListing::find($record->canonical_id);
            if ($listing) {
                $this->projectListingToMappedLegacyAttribute($listing, $admin);
            }
        }

        $record->update([
            'status'              => $newStatus,
            'resolution_action'   => $action,
            'resolution_notes'    => $notes,
            'resolved_by_user_id' => $admin->id,
            'resolved_at'         => now(),
        ]);

        $this->logAudit(null, $record->id, $record->entity_type, $record->canonical_ref, 'reconciliation_resolved', $admin, $record->target_channel, ['status' => $beforeStatus], ['status' => $newStatus, 'action' => $action, 'notes' => $notes]);

        return $record->fresh();
    }

    public function listEvents(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = IntegrationSyncEvent::with('actor:id,name,email')->orderByDesc('id');

        if (!empty($filters['target_channel'])) {
            $query->where('target_channel', $filters['target_channel']);
        }
        if (!empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $s = trim((string)$filters['search']);
            $query->where(fn($q) => $q->where('event_ref', 'like', "%{$s}%")->orWhere('canonical_ref', 'like', "%{$s}%")->orWhere('idempotency_key', 'like', "%{$s}%"));
        }

        return $query->paginate(min(max(1, $perPage), 100));
    }

    public function listReconciliationRecords(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = IntegrationReconciliationRecord::with('resolver:id,name,email')->orderByDesc('id');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }
        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        return $query->paginate(min(max(1, $perPage), 100));
    }

    public function getAdminDashboardSummary(): array
    {
        $channels = array_keys(config('marketplace_integration.channels', []));
        $channelHealth = [];

        foreach ($channels as $ch) {
            $lastSuccess = IntegrationSyncEvent::where('target_channel', $ch)
                ->where('status', IntegrationSyncEvent::STATUS_PROCESSED)
                ->max('processed_at');
            $failedCount = IntegrationSyncEvent::where('target_channel', $ch)
                ->whereIn('status', [IntegrationSyncEvent::STATUS_FAILED, IntegrationSyncEvent::STATUS_DEAD_LETTER])
                ->count();
            $channelHealth[$ch] = [
                'channel'       => $ch,
                'last_success'  => $lastSuccess,
                'failed_count'  => $failedCount,
            ];
        }

        return [
            'authoritative_source'   => config('marketplace_integration.authoritative_source', 'laravel_backend'),
            'crm_sync_enabled'       => (bool)config('marketplace_integration.crm_sync_enabled', false),
            'total_events'           => IntegrationSyncEvent::count(),
            'processed_events'       => IntegrationSyncEvent::where('status', IntegrationSyncEvent::STATUS_PROCESSED)->count(),
            'failed_or_dead_letter'  => IntegrationSyncEvent::whereIn('status', [IntegrationSyncEvent::STATUS_FAILED, IntegrationSyncEvent::STATUS_DEAD_LETTER])->count(),
            'rejected_conflicts'     => IntegrationSyncEvent::where('status', IntegrationSyncEvent::STATUS_REJECTED_CONFLICT)->count(),
            'open_reconciliations'   => IntegrationReconciliationRecord::whereIn('status', [IntegrationReconciliationRecord::STATUS_OPEN, IntegrationReconciliationRecord::STATUS_INVESTIGATING])->count(),
            'active_mappings_count'  => IntegrationChannelMapping::where('mapping_status', IntegrationChannelMapping::STATUS_ACTIVE)->count(),
            'last_successful_sync'   => IntegrationSyncEvent::where('status', IntegrationSyncEvent::STATUS_PROCESSED)->max('processed_at'),
            'channel_health'         => array_values($channelHealth),
            'source_of_truth_matrix' => config('marketplace_integration.source_of_truth_matrix', []),
        ];
    }

    protected function logAudit(
        ?int $syncEventId,
        ?int $reconId,
        string $entityType,
        ?string $canonicalRef,
        string $action,
        ?User $actor,
        ?string $channel,
        ?array $before = null,
        ?array $after = null,
        ?string $notes = null
    ): void {
        IntegrationSyncAuditLog::create([
            'sync_event_id'            => $syncEventId,
            'reconciliation_record_id' => $reconId,
            'entity_type'              => $entityType,
            'canonical_ref'            => $canonicalRef,
            'action'                   => $action,
            'actor_user_id'            => $actor?->id,
            'actor_role'               => $actor ? ($actor->isAdmin() ? 'admin' : 'user') : 'system',
            'channel'                  => $channel,
            'before_state'             => $before,
            'after_state'              => $after,
            'notes'                    => $notes,
            'ip_address'               => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}
