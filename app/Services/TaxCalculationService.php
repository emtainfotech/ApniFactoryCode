<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTaxSnapshot;
use App\Models\TaxMaster;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaxCalculationService
{
    public function __construct(
        protected TaxMasterService $taxMasterService
    ) {
    }

    /**
     * Calculate line-level GST breakdown based on approved HSN classification and place of supply.
     */
    public function calculateLineTax(
        array $lineData,
        string $sellerState,
        string $buyerState,
        ?string $transactionDate = null
    ): array {
        $hsnCode = trim($lineData['hsn_code'] ?? '');

        if (empty($hsnCode)) {
            throw ValidationException::withMessages([
                'hsn_code' => ['Missing HSN/SAC code on order line.'],
            ]);
        }

        // 1. Authoritative tax master lookup for transaction date
        $taxMaster = $this->taxMasterService->getEffectiveTax($hsnCode, $transactionDate);

        if (!$taxMaster) {
            throw ValidationException::withMessages([
                'hsn_code' => ["Missing or unapproved GST classification for HSN '{$hsnCode}' on date " . ($transactionDate ?? date('Y-m-d')) . '.'],
            ]);
        }

        // 2. Determine Place of Supply
        $normSeller = trim(strtolower($sellerState));
        $normBuyer  = trim(strtolower($buyerState));

        $isInterstate = ($normSeller !== $normBuyer) || empty($normSeller) || empty($normBuyer);
        $placeOfSupply = !empty($buyerState) ? $buyerState : ($isInterstate ? 'Inter-State' : $sellerState);

        // 3. Taxable base calculation
        $unitPrice = (float) ($lineData['unit_price'] ?? 0);
        $quantity = (int) ($lineData['quantity'] ?? 1);
        $discountAmount = (float) ($lineData['discount_amount'] ?? 0);

        $taxableAmount = round(($unitPrice * $quantity) - $discountAmount, 2);
        if ($taxableAmount < 0) {
            $taxableAmount = 0.00;
        }

        // 4. Rate and component splitting
        $taxRate = (float) $taxMaster->gst_rate;

        $cgstRate = 0.00;
        $cgstAmount = 0.00;
        $sgstRate = 0.00;
        $sgstAmount = 0.00;
        $igstRate = 0.00;
        $igstAmount = 0.00;

        if ($isInterstate) {
            // Inter-State: 100% IGST
            $igstRate = $taxRate;
            $igstAmount = round(($taxableAmount * $igstRate) / 100, 2);
        } else {
            // Intra-State: 50% CGST + 50% SGST
            $cgstRate = round($taxRate / 2, 2);
            $cgstAmount = round(($taxableAmount * $cgstRate) / 100, 2);

            $sgstRate = round($taxRate / 2, 2);
            $sgstAmount = round(($taxableAmount * $sgstRate) / 100, 2);
        }

        $totalTaxAmount = round($cgstAmount + $sgstAmount + $igstAmount, 2);
        $grossAmount = round($taxableAmount + $totalTaxAmount, 2);

        return [
            'tax_master_id'       => $taxMaster->id,
            'hsn_code'            => $taxMaster->hsn_code,
            'tax_description'     => $taxMaster->description,
            'tax_rate'            => $taxRate,
            'taxable_amount'      => $taxableAmount,
            'is_interstate'       => $isInterstate,
            'place_of_supply'     => $placeOfSupply,
            'seller_state'        => $sellerState,
            'buyer_state'         => $buyerState,
            'cgst_rate'           => $cgstRate,
            'cgst_amount'         => $cgstAmount,
            'sgst_rate'           => $sgstRate,
            'sgst_amount'         => $sgstAmount,
            'igst_rate'           => $igstRate,
            'igst_amount'         => $igstAmount,
            'total_tax_amount'    => $totalTaxAmount,
            'gross_amount'        => $grossAmount,
            'tax_master_snapshot' => [
                'id'               => $taxMaster->id,
                'hsn_code'         => $taxMaster->hsn_code,
                'description'      => $taxMaster->description,
                'gst_rate'         => (float)$taxMaster->gst_rate,
                'type'             => $taxMaster->type,
                'effective_from'   => $taxMaster->effective_from->toDateString(),
                'effective_to'     => $taxMaster->effective_to?->toDateString(),
                'source_reference' => $taxMaster->source_reference,
                'version'          => $taxMaster->version,
            ],
            // Preserve line item reference details
            'product_name'        => $lineData['product_name'] ?? 'Product',
            'sku'                 => $lineData['sku'] ?? null,
            'product_id'          => $lineData['product_id'] ?? null,
            'order_detail_id'     => $lineData['order_detail_id'] ?? null,
            'quantity'            => $quantity,
            'unit_price'          => $unitPrice,
            'discount_amount'     => $discountAmount,
        ];
    }

    /**
     * Calculate multi-line invoice tax summary.
     */
    public function calculateOrderTax(
        array $lines,
        string $sellerState,
        string $buyerState,
        ?string $transactionDate = null
    ): array {
        $calculatedLines = [];
        $totalTaxable = 0.00;
        $totalCgst = 0.00;
        $totalSgst = 0.00;
        $totalIgst = 0.00;
        $totalTax = 0.00;
        $totalGross = 0.00;

        foreach ($lines as $line) {
            $lineResult = $this->calculateLineTax($line, $sellerState, $buyerState, $transactionDate);
            $calculatedLines[] = $lineResult;

            $totalTaxable += $lineResult['taxable_amount'];
            $totalCgst    += $lineResult['cgst_amount'];
            $totalSgst    += $lineResult['sgst_amount'];
            $totalIgst    += $lineResult['igst_amount'];
            $totalTax     += $lineResult['total_tax_amount'];
            $totalGross   += $lineResult['gross_amount'];
        }

        $totalTaxable = round($totalTaxable, 2);
        $totalCgst    = round($totalCgst, 2);
        $totalSgst    = round($totalSgst, 2);
        $totalIgst    = round($totalIgst, 2);
        $totalTax     = round($totalTax, 2);
        $totalGross   = round($totalGross, 2);

        // Indian invoice standard: round off to nearest rupee
        $invoiceTotal = round($totalGross);
        $roundOff = round($invoiceTotal - $totalGross, 2);

        return [
            'lines'   => $calculatedLines,
            'summary' => [
                'total_taxable_amount' => $totalTaxable,
                'total_cgst_amount'    => $totalCgst,
                'total_sgst_amount'    => $totalSgst,
                'total_igst_amount'    => $totalIgst,
                'total_tax_amount'     => $totalTax,
                'round_off'            => $roundOff,
                'invoice_total'        => $invoiceTotal,
                'is_interstate'        => !empty($calculatedLines) ? $calculatedLines[0]['is_interstate'] : false,
                'place_of_supply'      => !empty($calculatedLines) ? $calculatedLines[0]['place_of_supply'] : $buyerState,
            ],
        ];
    }

    /**
     * Capture immutable Order Tax Snapshots for a confirmed order.
     */
    public function captureOrderTaxSnapshot(
        Order $order,
        array $lines,
        string $sellerState,
        string $buyerState,
        ?string $transactionDate = null
    ): Collection {
        return DB::transaction(function () use ($order, $lines, $sellerState, $buyerState, $transactionDate) {
            $calc = $this->calculateOrderTax($lines, $sellerState, $buyerState, $transactionDate);
            $createdSnapshots = collect();

            foreach ($calc['lines'] as $line) {
                $snapshot = OrderTaxSnapshot::create([
                    'order_id'            => $order->id,
                    'order_detail_id'     => $line['order_detail_id'] ?? null,
                    'orderno'             => $order->orderno,
                    'seller_user_id'      => $order->user_id,
                    'customer_id'         => $order->customer_id,
                    'product_id'          => $line['product_id'] ?? null,
                    'product_name'        => $line['product_name'],
                    'sku'                 => $line['sku'],
                    'hsn_code'            => $line['hsn_code'],
                    'tax_master_id'       => $line['tax_master_id'],
                    'tax_description'     => $line['tax_description'],
                    'tax_rate'            => $line['tax_rate'],
                    'taxable_amount'      => $line['taxable_amount'],
                    'is_interstate'       => $line['is_interstate'],
                    'place_of_supply'     => $line['place_of_supply'],
                    'seller_state'        => $line['seller_state'],
                    'buyer_state'         => $line['buyer_state'],
                    'cgst_rate'           => $line['cgst_rate'],
                    'cgst_amount'         => $line['cgst_amount'],
                    'sgst_rate'           => $line['sgst_rate'],
                    'sgst_amount'         => $line['sgst_amount'],
                    'igst_rate'           => $line['igst_rate'],
                    'igst_amount'         => $line['igst_amount'],
                    'total_tax_amount'    => $line['total_tax_amount'],
                    'gross_amount'        => $line['gross_amount'],
                    'tax_master_snapshot' => $line['tax_master_snapshot'],
                    'calculated_at'       => now(),
                ]);

                $createdSnapshots->push($snapshot);
            }

            return $createdSnapshots;
        });
    }
}

