<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Notification;
use App\Models\PriceRevision;
use App\Models\PriceRevisionItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DynamicPriceRevisionService
{
    /**
     * Standard normalized unit map.
     */
    protected const UNIT_NORMALIZATION_MAP = [
        'l' => 'Litre',
        'ltr' => 'Litre',
        'litre' => 'Litre',
        'litres' => 'Litre',
        'liter' => 'Litre',
        'liters' => 'Litre',
        'kg' => 'Kg',
        'kgs' => 'Kg',
        'kilogram' => 'Kg',
        'kilograms' => 'Kg',
        'g' => 'Gram',
        'gm' => 'Gram',
        'gram' => 'Gram',
        'grams' => 'Gram',
        'pc' => 'Piece',
        'pcs' => 'Piece',
        'piece' => 'Piece',
        'pieces' => 'Piece',
        'unit' => 'Piece',
        'units' => 'Piece',
        'box' => 'Box',
        'boxes' => 'Box',
        'carton' => 'Box',
        'cartons' => 'Box',
        'm' => 'Meter',
        'mtr' => 'Meter',
        'meter' => 'Meter',
        'meters' => 'Meter',
        'metre' => 'Meter',
    ];

    /**
     * Normalize unit string for exact compatibility matching.
     */
    public function normalizeUnit(?string $unit): string
    {
        if (!$unit) {
            return 'Piece';
        }
        $clean = strtolower(trim($unit));
        return self::UNIT_NORMALIZATION_MAP[$clean] ?? ucfirst($clean);
    }

    /**
     * Check unit compatibility between SKU unit and price basis unit.
     */
    public function isUnitCompatible(?string $skuUnit, ?string $basisUnit): bool
    {
        if (empty($basisUnit)) {
            return true;
        }
        return $this->normalizeUnit($skuUnit) === $this->normalizeUnit($basisUnit);
    }

    /**
     * Apply explicit rounding rule to a floating point value.
     */
    public function applyRounding(float $value, string $rule = 'round_half'): float
    {
        switch ($rule) {
            case 'none':
                return round($value, 2);

            case 'round_integer':
                return round($value);

            case 'round_to_99':
                return floor($value) + 0.99;

            case 'round_half':
            default:
                return round($value * 2) / 2;
        }
    }

    /**
     * Calculate proposed price and rounding difference for a single SKU.
     */
    public function calculateProposedPrice(
        float $oldPrice,
        string $method,
        ?float $value,
        string $direction = 'increase',
        string $roundingRule = 'round_half',
        ?string $priceBasisUnit = null,
        ?string $skuUnit = null,
        ?float $explicitPrice = null
    ): array {
        $isCompatible = true;
        $errorMessage = null;

        if ($method === 'percentage') {
            $pct = abs($value ?? 0.0);
            if ($direction === 'increase') {
                $unrounded = $oldPrice * (1 + ($pct / 100));
            } else {
                $unrounded = $oldPrice * (1 - ($pct / 100));
            }
            $proposed = $this->applyRounding($unrounded, $roundingRule);
            $roundingDiff = round($proposed - $unrounded, 4);

        } elseif ($method === 'flat_per_unit') {
            $isCompatible = $this->isUnitCompatible($skuUnit, $priceBasisUnit);
            if (!$isCompatible) {
                return [
                    'is_compatible' => false,
                    'proposed_price' => $oldPrice,
                    'unrounded_price' => $oldPrice,
                    'rounding_difference' => 0.0,
                    'error_message' => sprintf(
                        "Unit mismatch: SKU is in '%s', but revision basis is '%s'. Implicit unit conversion is prohibited per BRD Section 6.1.",
                        $skuUnit ?? 'Unknown',
                        $priceBasisUnit ?? 'Unknown'
                    ),
                ];
            }

            $flat = abs($value ?? 0.0);
            if ($direction === 'increase') {
                $unrounded = $oldPrice + $flat;
            } else {
                $unrounded = $oldPrice - $flat;
            }
            $proposed = $this->applyRounding($unrounded, $roundingRule);
            $roundingDiff = round($proposed - $unrounded, 4);

        } elseif ($method === 'explicit_list') {
            if ($explicitPrice === null) {
                return [
                    'is_compatible' => false,
                    'proposed_price' => $oldPrice,
                    'unrounded_price' => $oldPrice,
                    'rounding_difference' => 0.0,
                    'error_message' => 'Explicit price not specified for SKU.',
                ];
            }
            $unrounded = $explicitPrice;
            $proposed = round($explicitPrice, 2);
            $roundingDiff = round($proposed - $unrounded, 4);

        } else {
            return [
                'is_compatible' => false,
                'proposed_price' => $oldPrice,
                'unrounded_price' => $oldPrice,
                'rounding_difference' => 0.0,
                'error_message' => "Unsupported revision method: '{$method}'.",
            ];
        }

        // Zero or negative price prevention
        if ($proposed <= 0) {
            $isCompatible = false;
            $errorMessage = sprintf("Proposed price (₹%.2f) must be greater than zero.", $proposed);
        }

        return [
            'is_compatible' => $isCompatible,
            'proposed_price' => $proposed,
            'unrounded_price' => round($unrounded, 4),
            'rounding_difference' => $roundingDiff,
            'error_message' => $errorMessage,
        ];
    }

    /**
     * Query seller listings matching criteria within company isolation.
     */
    public function getEligibleListingsQuery(Company $company, array $filters = []): Builder
    {
        $query = SellerListing::with(['product', 'variant'])
            ->where('company_id', $company->id)
            ->where('status', '!=', 'Archived');

        if (!empty($filters['category_id'])) {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        if (!empty($filters['subcategory_id'])) {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('subcategory_id', $filters['subcategory_id']);
            });
        }

        if (!empty($filters['brand_id'])) {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('brand_id', $filters['brand_id']);
            });
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['product_variant_id'])) {
            $query->where('product_variant_id', $filters['product_variant_id']);
        }

        if (!empty($filters['seller_listing_ids']) && is_array($filters['seller_listing_ids'])) {
            $query->whereIn('id', $filters['seller_listing_ids']);
        }

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('seller_sku', 'like', "%{$term}%")
                  ->orWhere('barcode', 'like', "%{$term}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$term}%"));
            });
        }

        return $query;
    }

    /**
     * Non-mutating preview generation with validation and rounding metrics.
     */
    public function previewRevision(Company $company, array $data): array
    {
        $method = $data['revision_method'] ?? 'percentage';
        $direction = $data['revision_direction'] ?? 'increase';
        $value = isset($data['revision_value']) ? (float)$data['revision_value'] : null;
        $basisUnit = $data['price_basis_unit'] ?? null;
        $roundingRule = $data['rounding_rule'] ?? 'round_half';
        $explicitPrices = $data['explicit_prices'] ?? []; // ['sku_id' => price] or ['listing_id' => price]

        $query = $this->getEligibleListingsQuery($company, $data['filters'] ?? []);
        $listings = $query->get();

        $items = [];
        $totalEvaluated = 0;
        $totalEligible = 0;
        $totalIncompatible = 0;
        $totalInvalid = 0;

        foreach ($listings as $listing) {
            $totalEvaluated++;

            $explicitPrice = null;
            if ($method === 'explicit_list') {
                if (isset($explicitPrices[$listing->id])) {
                    $explicitPrice = (float)$explicitPrices[$listing->id];
                } elseif (!empty($listing->seller_sku) && isset($explicitPrices[$listing->seller_sku])) {
                    $explicitPrice = (float)$explicitPrices[$listing->seller_sku];
                }
            }

            $calc = $this->calculateProposedPrice(
                (float)$listing->seller_price,
                $method,
                $value,
                $direction,
                $roundingRule,
                $basisUnit,
                $listing->unit_of_measurement,
                $explicitPrice
            );

            $oldSellerPrice = (float)$listing->seller_price;
            $newSellerPrice = (float)$calc['proposed_price'];
            $commRate = (float)$listing->commission_rate;
            $oldSellingPrice = (float)$listing->selling_price;
            $newSellingPrice = round($newSellerPrice * (1 + ($commRate / 100)), 2);

            $mrp = $listing->mrp ? (float)$listing->mrp : null;
            $mrpError = null;
            if ($mrp !== null && $newSellerPrice > $mrp) {
                $calc['is_compatible'] = false;
                $mrpError = sprintf("Proposed seller price (₹%.2f) exceeds MRP (₹%.2f).", $newSellerPrice, $mrp);
            }

            $validationStatus = 'valid';
            $validationError = $calc['error_message'] ?? $mrpError;

            if (!$calc['is_compatible']) {
                if (str_contains((string)$validationError, 'Unit mismatch')) {
                    $validationStatus = 'incompatible_unit';
                    $totalIncompatible++;
                } else {
                    $validationStatus = 'invalid';
                    $totalInvalid++;
                }
            } else {
                $totalEligible++;
            }

            $items[] = [
                'seller_listing_id' => $listing->id,
                'seller_sku' => $listing->seller_sku,
                'product_id' => $listing->product_id,
                'product_name' => $listing->product ? $listing->product->name : 'N/A',
                'product_variant_id' => $listing->product_variant_id,
                'variant_sku' => $listing->variant ? $listing->variant->variant_sku : 'N/A',
                'pack_litres' => $listing->variant ? (float)$listing->variant->pack_litres : null,
                'unit_of_measurement' => $listing->unit_of_measurement,
                'price_basis' => $basisUnit ?: $listing->unit_of_measurement,
                'old_seller_price' => $oldSellerPrice,
                'new_seller_price' => $newSellerPrice,
                'old_selling_price' => $oldSellingPrice,
                'new_selling_price' => $newSellingPrice,
                'mrp' => $mrp,
                'rounding_difference' => $calc['rounding_difference'],
                'is_unit_compatible' => $calc['is_compatible'],
                'validation_status' => $validationStatus,
                'validation_error' => $validationError,
            ];
        }

        $previewToken = (string) Str::uuid();
        $previewData = [
            'preview_token' => $previewToken,
            'company_id' => $company->id,
            'summary' => [
                'total_evaluated' => $totalEvaluated,
                'total_eligible' => $totalEligible,
                'total_incompatible' => $totalIncompatible,
                'total_invalid' => $totalInvalid,
            ],
            'parameters' => [
                'revision_method' => $method,
                'revision_direction' => $direction,
                'revision_value' => $value,
                'price_basis_unit' => $basisUnit,
                'rounding_rule' => $roundingRule,
                'filters' => $data['filters'] ?? [],
            ],
            'items' => $items,
        ];

        // Cache preview for 2 hours
        Cache::put("price_preview:{$previewToken}", $previewData, now()->addHours(2));

        return $previewData;
    }

    /**
     * Create or schedule a price revision with mandatory effective timestamp.
     */
    public function createRevision(Company $company, User $user, array $data): PriceRevision
    {
        // Generate or fetch preview
        $previewToken = $data['preview_token'] ?? null;
        $previewData = $previewToken ? Cache::get("price_preview:{$previewToken}") : null;

        if (!$previewData) {
            $previewData = $this->previewRevision($company, $data);
            $previewToken = $previewData['preview_token'];
        }

        // Timezone parsing (India Standard Time: Asia/Kolkata)
        $effectiveAtInput = $data['effective_at'] ?? now();
        if ($effectiveAtInput instanceof Carbon) {
            $effectiveAt = $effectiveAtInput->copy()->utc();
        } elseif (is_string($effectiveAtInput) && preg_match('/(Z|[+-]\d{2}:?\d{2})$/', trim($effectiveAtInput))) {
            $effectiveAt = Carbon::parse($effectiveAtInput)->utc();
        } else {
            $effectiveAt = Carbon::parse($effectiveAtInput, 'Asia/Kolkata')->utc();
        }

        $approvalRequired = isset($data['approval_required'])
            ? (bool)$data['approval_required']
            : ($data['require_approval'] ?? false);

        // Initial status determination
        $initialStatus = 'scheduled';
        if ($approvalRequired) {
            $initialStatus = 'pending_approval';
        }

        $revisionCode = 'REV-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        return DB::transaction(function () use (
            $company,
            $user,
            $data,
            $previewData,
            $previewToken,
            $effectiveAt,
            $approvalRequired,
            $initialStatus,
            $revisionCode
        ) {
            $revision = PriceRevision::create([
                'revision_code' => $revisionCode,
                'company_id' => $company->id,
                'created_by_user_id' => $user->id,
                'revision_method' => $previewData['parameters']['revision_method'],
                'revision_direction' => $previewData['parameters']['revision_direction'],
                'revision_value' => $previewData['parameters']['revision_value'],
                'price_basis_unit' => $previewData['parameters']['price_basis_unit'],
                'rounding_rule' => $previewData['parameters']['rounding_rule'],
                'status' => $initialStatus,
                'approval_required' => $approvalRequired,
                'effective_at' => $effectiveAt,
                'reason' => $data['reason'] ?? 'Dynamic Price Revision',
                'selection_criteria' => $previewData['parameters']['filters'] ?? [],
                'preview_token' => $previewToken,
                'total_skus' => $previewData['summary']['total_evaluated'],
                'affected_skus_count' => $previewData['summary']['total_eligible'],
                'incompatible_skus_count' => $previewData['summary']['total_incompatible'],
                'failed_skus_count' => $previewData['summary']['total_invalid'],
                'idempotency_key' => $data['idempotency_key'] ?? (string) Str::uuid(),
            ]);

            foreach ($previewData['items'] as $item) {
                PriceRevisionItem::create([
                    'price_revision_id' => $revision->id,
                    'seller_listing_id' => $item['seller_listing_id'],
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'seller_sku' => $item['seller_sku'],
                    'unit_of_measurement' => $item['unit_of_measurement'],
                    'is_unit_compatible' => $item['is_unit_compatible'],
                    'old_seller_price' => $item['old_seller_price'],
                    'new_seller_price' => $item['new_seller_price'],
                    'old_selling_price' => $item['old_selling_price'],
                    'new_selling_price' => $item['new_selling_price'],
                    'old_mrp' => $item['mrp'],
                    'new_mrp' => $item['mrp'],
                    'rounding_difference' => $item['rounding_difference'],
                    'status' => $item['is_unit_compatible'] ? 'pending' : 'skipped',
                    'error_message' => $item['validation_error'],
                ]);
            }

            // If effective time has already arrived and no approval required, activate immediately
            if ($initialStatus === 'scheduled' && $effectiveAt->isPast()) {
                $this->executeRevision($revision);
            }

            // Notification
            try {
                Notification::create([
                    'user_id' => $user->id,
                    'customer_id' => $user->id,
                    'title' => "Price Revision Scheduled: {$revision->revision_code}",
                    'msg' => sprintf(
                        "Revision %s with %d eligible SKUs is scheduled for effective activation at %s (IST).",
                        $revision->revision_code,
                        $revision->affected_skus_count,
                        $effectiveAt->setTimezone('Asia/Kolkata')->format('d M Y, h:i A')
                    ),
                    'msgread' => 0,
                    'type' => 'seller',
                ]);
            } catch (\Throwable $e) {
                Log::warning("Notification creation failed for revision {$revision->id}: {$e->getMessage()}");
            }

            return $revision;
        });
    }

    /**
     * Execute price revision idempotently.
     * Guaranteed safe for retries: if revision is already active, returns immediately without duplicate increases!
     */
    public function executeRevision(PriceRevision $revision): bool
    {
        // 1. Idempotency guard: already active
        if ($revision->status === 'active') {
            Log::info("Price revision {$revision->revision_code} is already active. Skipping duplicate execution.");
            return true;
        }

        // 2. State guard: must be scheduled or approved
        if (!in_array($revision->status, ['scheduled', 'approved'])) {
            Log::warning("Price revision {$revision->revision_code} cannot be executed in state '{$revision->status}'.");
            return false;
        }

        // 3. Approval guard
        if ($revision->approval_required && $revision->status === 'pending_approval') {
            Log::warning("Price revision {$revision->revision_code} cannot execute while awaiting approval.");
            return false;
        }

        return DB::transaction(function () use ($revision) {
            // Re-lock revision row to ensure concurrency safety
            $lockedRevision = PriceRevision::where('id', $revision->id)->lockForUpdate()->first();
            if ($lockedRevision->status === 'active') {
                return true;
            }

            $appliedCount = 0;
            $failedCount = 0;
            $items = PriceRevisionItem::where('price_revision_id', $lockedRevision->id)
                ->where('status', 'pending')
                ->where('is_unit_compatible', true)
                ->get();

            foreach ($items as $item) {
                try {
                    $listing = SellerListing::where('id', $item->seller_listing_id)
                        ->where('company_id', $lockedRevision->company_id)
                        ->first();

                    if (!$listing) {
                        $item->update([
                            'status' => 'failed',
                            'error_message' => 'Listing not found or company mismatch.',
                        ]);
                        $failedCount++;
                        continue;
                    }

                    // Apply new seller price and recompute selling price
                    $listing->seller_price = $item->new_seller_price;
                    $listing->selling_price = $item->new_selling_price;
                    if ($item->new_mrp !== null) {
                        $listing->mrp = $item->new_mrp;
                    }
                    $listing->save();

                    $item->update([
                        'status' => 'applied',
                        'error_message' => null,
                    ]);
                    $appliedCount++;
                } catch (\Throwable $e) {
                    $item->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                    $failedCount++;
                }
            }

            $lockedRevision->update([
                'status' => 'active',
                'activated_at' => now(),
                'affected_skus_count' => $appliedCount,
                'failed_skus_count' => $lockedRevision->failed_skus_count + $failedCount,
                'execution_log' => [
                    'executed_at' => now()->toIso8601String(),
                    'applied_skus' => $appliedCount,
                    'failed_skus' => $failedCount,
                ],
            ]);

            Log::info("Price revision {$lockedRevision->revision_code} successfully activated. Applied: {$appliedCount}, Failed: {$failedCount}.");

            return true;
        });
    }

    /**
     * Approve pending price revision.
     */
    public function approveRevision(PriceRevision $revision, User $approver): PriceRevision
    {
        if ($revision->status !== 'pending_approval') {
            throw new \DomainException("Revision {$revision->revision_code} is not awaiting approval (current state: {$revision->status}).");
        }

        $revision->update([
            'status' => 'scheduled',
            'approved_by_user_id' => $approver->id,
        ]);

        // If effective date has already arrived, execute immediately
        if ($revision->effective_at && $revision->effective_at->isPast()) {
            $this->executeRevision($revision);
        }

        return $revision->fresh();
    }

    /**
     * Reject pending price revision.
     */
    public function rejectRevision(PriceRevision $revision, User $approver, string $reason): PriceRevision
    {
        if ($revision->status !== 'pending_approval') {
            throw new \DomainException("Revision {$revision->revision_code} is not awaiting approval (current state: {$revision->status}).");
        }

        $revision->update([
            'status' => 'rejected',
            'approved_by_user_id' => $approver->id,
            'rejection_reason' => $reason,
        ]);

        return $revision->fresh();
    }

    /**
     * Cancel scheduled or pending price revision.
     */
    public function cancelRevision(PriceRevision $revision, User $user): PriceRevision
    {
        if (!in_array($revision->status, ['draft', 'pending_approval', 'scheduled'])) {
            throw new \DomainException("Revision {$revision->revision_code} cannot be cancelled in state '{$revision->status}'. Active revisions cannot be cancelled.");
        }

        $revision->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $revision->fresh();
    }

    /**
     * Resolve current effective price according to documented deterministic precedence.
     * Precedence:
     * Tier 1: Accepted Contractual RFQ Quote (PR-TIER-1-RFQ)
     * Tier 2: Active Scheduled Price Revision (PR-TIER-2-REVISION)
     * Tier 3: Quantity / Volume Slab Discount (PR-TIER-3-VOLUME-SLAB)
     * Tier 4: Promotional Coupon Discount (PR-TIER-4-PROMO)
     * Tier 5: Base Catalogue Listing Price (PR-TIER-5-CATALOGUE-BASE)
     */
    public function resolveCurrentEffectivePrice(SellerListing $listing, ?int $buyerId = null, int $quantity = 1): array
    {
        // Tier 1: Accepted Contractual RFQ Quote
        if ($buyerId) {
            $rfqQuote = DB::table('rfq_quotes')
                ->join('rfq_requests', 'rfq_quotes.rfq_request_id', '=', 'rfq_requests.id')
                ->where('rfq_requests.customer_id', $buyerId)
                ->where('rfq_quotes.company_id', $listing->company_id)
                ->where('rfq_quotes.status', 'accepted')
                ->where('rfq_quotes.valid_until', '>=', now()->toDateString())
                ->first();

            if ($rfqQuote) {
                return [
                    'effective_price' => (float)$rfqQuote->offered_unit_price,
                    'price_tier' => 'contractual_negotiated',
                    'rule_id' => 'PR-TIER-1-RFQ',
                    'source_reference' => "rfq_quote_id:{$rfqQuote->id}",
                ];
            }
        }

        // Tier 3: Volume / Quantity Slabs
        $slab = DB::table('product_volume_slabs')
            ->where('product_id', $listing->product_id)
            ->where('seller_user_id', $listing->seller_user_id)
            ->where('status', 'active')
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
            })
            ->first();

        if ($slab) {
            $slabPrice = (float)$listing->selling_price;
            if ($slab->discount_type === 'percentage') {
                $slabPrice = round($slabPrice * (1 - ($slab->discount_value / 100)), 2);
            } elseif ($slab->discount_type === 'fixed_unit_price') {
                $slabPrice = (float)$slab->discount_value;
            }

            return [
                'effective_price' => $slabPrice,
                'price_tier' => 'volume_slab',
                'rule_id' => 'PR-TIER-3-VOLUME-SLAB',
                'source_reference' => "slab_id:{$slab->id}",
            ];
        }

        // Default: Base Catalogue Listing Price
        return [
            'effective_price' => (float)$listing->selling_price,
            'price_tier' => 'catalogue_base',
            'rule_id' => 'PR-TIER-5-CATALOGUE-BASE',
            'source_reference' => "seller_listing_id:{$listing->id}",
        ];
    }
}
