<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
use App\Models\OrderTaxSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    /**
     * Generate or retrieve an immutable Goods Tax Invoice for a seller child fulfilment order.
     */
    public function generateGoodsInvoice(Order $order): Invoice
    {
        // 1. Idempotency: Return existing invoice if already issued
        $existing = Invoice::where('order_id', $order->id)
            ->where('invoice_type', Invoice::TYPE_GOODS_TAX_INVOICE)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($order) {
            // 2. Fetch immutable tax snapshots for this order
            $taxSnapshots = OrderTaxSnapshot::where('order_id', $order->id)->get();

            if ($taxSnapshots->isEmpty()) {
                throw ValidationException::withMessages([
                    'order' => ["Cannot generate invoice: No immutable tax snapshots found for order #{$order->orderno}."],
                ]);
            }

            // 3. Resolve Seller (Company) and Buyer (Customer) Details
            $sellerCompany = DB::table('companies')->where('user_id', $order->user_id)->first();
            $customer = $order->customer;

            $issuerName = $sellerCompany ? $sellerCompany->name : 'Seller #' . $order->user_id;
            $issuerGstin = $sellerCompany ? $sellerCompany->gst : null;
            $issuerAddress = $sellerCompany ? "{$sellerCompany->city}, {$sellerCompany->state} - {$sellerCompany->pincode}" : null;
            $issuerState = $sellerCompany ? $sellerCompany->state : ($taxSnapshots->first()->seller_state ?? 'Delhi');

            $recipientName = $customer ? $customer->name : 'Valued Buyer';
            $recipientGstin = null; // B2B registered GSTIN if available
            $recipientAddress = $order->address;
            $recipientState = $taxSnapshots->first()->buyer_state ?? 'Delhi';
            $placeOfSupply = $taxSnapshots->first()->place_of_supply ?? $recipientState;

            // 4. Aggregate Tax Totals from Snapshots
            $taxableAmount = (float) $taxSnapshots->sum('taxable_amount');
            $cgstAmount    = (float) $taxSnapshots->sum('cgst_amount');
            $sgstAmount    = (float) $taxSnapshots->sum('sgst_amount');
            $igstAmount    = (float) $taxSnapshots->sum('igst_amount');
            $totalTax      = (float) $taxSnapshots->sum('total_tax_amount');
            $totalGross    = $taxableAmount + $totalTax;

            $invoiceTotal  = round($totalGross);
            $roundOff      = round($invoiceTotal - $totalGross, 2);

            $prefix = config('marketplace_tax.goods_invoice_prefix', 'INV-G-');
            $invoiceNo = $prefix . date('Ymd') . '-' . strtoupper(Str::random(6));

            // 5. Create Invoice Header
            $invoice = Invoice::create([
                'invoice_no'        => $invoiceNo,
                'invoice_type'      => Invoice::TYPE_GOODS_TAX_INVOICE,
                'parent_order_id'   => $order->parent_order_id ?: $order->id,
                'order_id'          => $order->id,
                'seller_user_id'    => $order->user_id,
                'customer_id'       => $order->customer_id,
                'company_id'        => $sellerCompany ? $sellerCompany->id : null,
                'issuer_type'       => Invoice::ISSUER_SELLER,
                'issuer_name'       => $issuerName,
                'issuer_gstin'      => $issuerGstin,
                'issuer_address'    => $issuerAddress,
                'issuer_state'      => $issuerState,
                'recipient_name'    => $recipientName,
                'recipient_gstin'   => $recipientGstin,
                'recipient_address' => $recipientAddress,
                'recipient_state'   => $recipientState,
                'place_of_supply'   => $placeOfSupply,
                'taxable_amount'    => $taxableAmount,
                'cgst_amount'       => $cgstAmount,
                'sgst_amount'       => $sgstAmount,
                'igst_amount'       => $igstAmount,
                'total_tax_amount'  => $totalTax,
                'round_off'         => $roundOff,
                'total_amount'      => $invoiceTotal,
                'status'            => Invoice::STATUS_ISSUED,
                'issued_at'         => now(),
            ]);

            // 6. Create Invoice Line Items from Snapshots
            foreach ($taxSnapshots as $snap) {
                InvoiceItem::create([
                    'invoice_id'      => $invoice->id,
                    'order_detail_id' => $snap->order_detail_id,
                    'product_name'    => $snap->product_name ?? 'Product Item',
                    'sku'             => $snap->sku,
                    'hsn_code'        => $snap->hsn_code,
                    'quantity'        => 1, // Normalized line quantity
                    'unit'            => 'pack',
                    'unit_price'      => (float) $snap->taxable_amount,
                    'discount_amount' => 0.00,
                    'taxable_value'   => (float) $snap->taxable_amount,
                    'tax_rate'        => (float) $snap->tax_rate,
                    'cgst_amount'     => (float) $snap->cgst_amount,
                    'sgst_amount'     => (float) $snap->sgst_amount,
                    'igst_amount'     => (float) $snap->igst_amount,
                    'total_amount'    => (float) $snap->gross_amount,
                ]);
            }

            return $invoice->load('items');
        });
    }

    /**
     * Generate or retrieve a Platform Commission Invoice issued by ApniFactory to the Seller.
     */
    public function generateCommissionInvoice(Order $order, ?OrderCommissionSnapshot $snapshot = null): Invoice
    {
        $existing = Invoice::where('order_id', $order->id)
            ->where('invoice_type', Invoice::TYPE_PLATFORM_COMMISSION_INVOICE)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($order, $snapshot) {
            $sellerCompany = DB::table('companies')->where('user_id', $order->user_id)->first();

            $commissionAmount = $snapshot ? (float)$snapshot->commission_amount : round(((float)$order->grandtotal * 0.10), 2);
            $commissionGst    = $snapshot ? (float)$snapshot->gst_amount : round(($commissionAmount * 0.18), 2);
            $totalAmount      = round($commissionAmount + $commissionGst, 2);

            $prefix = config('marketplace_tax.commission_invoice_prefix', 'INV-C-');
            $invoiceNo = $prefix . date('Ymd') . '-' . strtoupper(Str::random(6));

            $invoice = Invoice::create([
                'invoice_no'        => $invoiceNo,
                'invoice_type'      => Invoice::TYPE_PLATFORM_COMMISSION_INVOICE,
                'parent_order_id'   => $order->parent_order_id ?: $order->id,
                'order_id'          => $order->id,
                'seller_user_id'    => $order->user_id,
                'customer_id'       => $order->customer_id,
                'company_id'        => $sellerCompany ? $sellerCompany->id : null,
                'issuer_type'       => Invoice::ISSUER_PLATFORM,
                'issuer_name'       => config('marketplace_tax.platform_legal_name', 'Apni Factory Private Limited'),
                'issuer_gstin'      => config('marketplace_tax.platform_gstin', '07AAAAA0000A1Z5'),
                'issuer_address'    => config('marketplace_tax.platform_address', 'Apni Factory Corporate Tower, New Delhi'),
                'issuer_state'      => config('marketplace_tax.platform_state', 'Delhi'),
                'recipient_name'    => $sellerCompany ? $sellerCompany->name : 'Seller #' . $order->user_id,
                'recipient_gstin'   => $sellerCompany ? $sellerCompany->gst : null,
                'recipient_address' => $sellerCompany ? "{$sellerCompany->city}, {$sellerCompany->state}" : 'N/A',
                'recipient_state'   => $sellerCompany ? $sellerCompany->state : 'Delhi',
                'place_of_supply'   => $sellerCompany ? $sellerCompany->state : 'Delhi',
                'taxable_amount'    => $commissionAmount,
                'cgst_amount'       => ($sellerCompany && $sellerCompany->state === 'Delhi') ? round($commissionGst / 2, 2) : 0.00,
                'sgst_amount'       => ($sellerCompany && $sellerCompany->state === 'Delhi') ? round($commissionGst / 2, 2) : 0.00,
                'igst_amount'       => (!$sellerCompany || $sellerCompany->state !== 'Delhi') ? $commissionGst : 0.00,
                'total_tax_amount'  => $commissionGst,
                'round_off'         => 0.00,
                'total_amount'      => $totalAmount,
                'status'            => Invoice::STATUS_ISSUED,
                'issued_at'         => now(),
            ]);

            InvoiceItem::create([
                'invoice_id'      => $invoice->id,
                'product_name'    => 'Marketplace Facilitation & Commission Services',
                'sku'             => 'PLATFORM-COMM',
                'hsn_code'        => '998311', // SAC code for online marketplace services
                'quantity'        => 1,
                'unit'            => 'service',
                'unit_price'      => $commissionAmount,
                'discount_amount' => 0.00,
                'taxable_value'   => $commissionAmount,
                'tax_rate'        => 18.00,
                'cgst_amount'     => $invoice->cgst_amount,
                'sgst_amount'     => $invoice->sgst_amount,
                'igst_amount'     => $invoice->igst_amount,
                'total_amount'    => $totalAmount,
            ]);

            return $invoice->load('items');
        });
    }
}

