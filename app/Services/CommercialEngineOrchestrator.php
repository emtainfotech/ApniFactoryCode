<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
use App\Models\SellerListing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CommercialEngineOrchestrator
 *
 * Centralized integration orchestrator unifying:
 * 1. Milestone M3 Phase 3.1: Dynamic Price Revision Engine (Authoritative pricing & precedence)
 * 2. Milestone M3 Phase 3.2: Commission & Platform Fee Engine (5-tier rule selection & fee snapshots)
 */
class CommercialEngineOrchestrator
{
    public function __construct(
        protected DynamicPriceRevisionService $pricingService,
        protected CommissionCalculationService $commissionService
    ) {
    }

    /**
     * Resolves end-to-end commercial calculation for a SKU order line.
     * Combines authoritative price resolution and 5-tier commission rule selection.
     */
    public function calculateOrderLineCommercials(
        SellerListing $listing,
        int $quantity,
        ?int $buyerId = null,
        ?float $unitPrice = null,
        ?Carbon $timestamp = null
    ): array {
        $time = $timestamp ? $timestamp->copy() : now();
        $qty = max(1, $quantity);

        // 1. Authoritative Price Resolution (Phase 3.1)
        if ($unitPrice !== null) {
            $effectiveUnitPrice = round($unitPrice, 2);
            $priceResolution = [
                'effective_price'  => $effectiveUnitPrice,
                'price_tier'       => 'explicit_order_price',
                'rule_id'          => 'PR-ORDER-DIRECT',
                'source_reference' => 'order_line_input',
            ];
        } else {
            $priceResolution = $this->pricingService->resolveCurrentEffectivePrice($listing, $buyerId, $qty);
            $effectiveUnitPrice = (float)$priceResolution['effective_price'];
        }

        // 2. Authoritative Commission Rule Resolution (Phase 3.2)
        $commissionResolution = $this->commissionService->resolveRuleForSku($listing, $time);

        // 3. Line-Item Commission & Fee Breakdown
        $commissionBreakdown = $this->commissionService->calculateLineCommission(
            $listing,
            $qty,
            $effectiveUnitPrice,
            $commissionResolution['rule'],
            $time
        );

        return [
            'listing' => [
                'id'                  => $listing->id,
                'seller_sku'          => $listing->seller_sku,
                'product_id'          => $listing->product_id,
                'product_name'        => $listing->product ? $listing->product->name : 'N/A',
                'company_id'          => $listing->company_id,
                'company_name'        => $listing->company ? $listing->company->name : 'N/A',
                'unit_of_measurement' => $listing->unit_of_measurement,
                'catalogue_seller_price' => (float)$listing->seller_price,
                'catalogue_selling_price' => (float)$listing->selling_price,
            ],
            'price_resolution' => [
                'resolved_unit_price' => $effectiveUnitPrice,
                'price_tier'          => $priceResolution['price_tier'],
                'price_rule_id'       => $priceResolution['rule_id'],
                'source_reference'    => $priceResolution['source_reference'],
            ],
            'commission_resolution' => [
                'rule_id'               => $commissionResolution['rule_id'],
                'rule_code'             => $commissionResolution['rule_code'],
                'rule_name'             => $commissionResolution['rule_name'],
                'scope_type'            => $commissionResolution['scope_type'],
                'scope_id'              => $commissionResolution['scope_id'],
                'priority'              => $commissionResolution['priority'],
                'resolution_reason'     => $commissionResolution['reason'],
                'commission_percentage' => $commissionResolution['commission_percentage'],
                'fixed_fee'             => $commissionResolution['fixed_fee'],
                'calculation_basis'     => $commissionResolution['calculation_basis'],
                'gst_rate'              => $commissionResolution['gst_rate'],
                'is_gst_inclusive'      => $commissionResolution['is_gst_inclusive'],
            ],
            'financial_breakdown' => [
                'quantity'           => $qty,
                'unit_price'         => $effectiveUnitPrice,
                'taxable_base'       => $commissionBreakdown['base_amount'],
                'commission_amount'  => $commissionBreakdown['commission_amount'],
                'gst_on_service'     => $commissionBreakdown['gst_amount'],
                'total_platform_fee' => $commissionBreakdown['total_platform_fee'],
                'net_seller_payable' => $commissionBreakdown['seller_payable'],
            ],
            'calculated_at' => $time->toIso8601String(),
        ];
    }

    /**
     * Atomically process and snapshot order line commercials.
     * Guarantees immutability and idempotency.
     */
    public function snapshotOrderCommercials(
        Order $order,
        SellerListing $listing,
        int $quantity,
        float $unitPrice,
        ?int $orderDetailId = null
    ): OrderCommissionSnapshot {
        return DB::transaction(function () use ($order, $listing, $quantity, $unitPrice, $orderDetailId) {
            return $this->commissionService->snapshotOrderCommission(
                $order,
                $listing,
                $quantity,
                $unitPrice,
                $orderDetailId
            );
        });
    }

    /**
     * Get underlying pricing service.
     */
    public function getPricingService(): DynamicPriceRevisionService
    {
        return $this->pricingService;
    }

    /**
     * Get underlying commission service.
     */
    public function getCommissionService(): CommissionCalculationService
    {
        return $this->commissionService;
    }
}
