<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\FinancialLedger;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
use App\Models\OrderTaxSnapshot;
use App\Models\SellerSettlement;
use App\Models\TaxMaster;
use App\Models\Transection;
use App\Models\User;
use App\Services\FinancialLedgerService;
use App\Services\InvoiceService;
use App\Services\PaymentGatewayService;
use App\Services\TaxCalculationService;
use App\Services\TaxMasterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Phase4_4_TaxMasterAndFinancialSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $seller1;
    protected User $seller2;
    protected Company $company1;
    protected Company $company2;
    protected Customer $customer1;
    protected Order $parentOrder;
    protected Order $childOrder1;
    protected Order $childOrder2;

    protected TaxMasterService $taxMasterService;
    protected TaxCalculationService $taxCalculationService;
    protected FinancialLedgerService $financialLedgerService;
    protected InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taxMasterService = app(TaxMasterService::class);
        $this->taxCalculationService = app(TaxCalculationService::class);
        $this->financialLedgerService = app(FinancialLedgerService::class);
        $this->invoiceService = app(InvoiceService::class);

        // Admin User
        $this->adminUser = User::create([
            'name'     => 'Finance Admin',
            'email'    => 'admin.finance@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        // Seller 1 (Delhi)
        $this->seller1 = User::create([
            'name'     => 'Delhi Industrial Coatings',
            'email'    => 'seller1@delhicoatings.in',
            'password' => bcrypt('password123'),
        ]);

        $this->company1 = Company::create([
            'name'      => 'Delhi Industrial Coatings Pvt Ltd',
            'user_id'   => $this->seller1->id,
            'email'     => 'info@delhicoatings.in',
            'mobile'    => '9811111111',
            'gst'       => '07AAAAA1111A1Z1',
            'city'      => 'New Delhi',
            'state'     => 'Delhi',
            'pincode'   => '110020',
            'comission' => 10.00,
            'status'    => 'Active',
        ]);

        // Seller 2 (Maharashtra)
        $this->seller2 = User::create([
            'name'     => 'Mumbai Polymers Ltd',
            'email'    => 'seller2@mumbaipolymers.in',
            'password' => bcrypt('password123'),
        ]);

        $this->company2 = Company::create([
            'name'      => 'Mumbai Polymers Ltd',
            'user_id'   => $this->seller2->id,
            'email'     => 'info@mumbaipolymers.in',
            'mobile'    => '9822222222',
            'gst'       => '27BBBBB2222B1Z2',
            'city'      => 'Mumbai',
            'state'     => 'Maharashtra',
            'pincode'   => '400001',
            'comission' => 12.00,
            'status'    => 'Active',
        ]);

        // Buyer Customer (Delhi)
        $this->customer1 = Customer::create([
            'name'     => 'Contractor Buildcorp Delhi',
            'email'    => 'buyer@buildcorp.in',
            'mobile'   => '9876543210',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);

        // Multi-Seller Orders
        $this->parentOrder = Order::create([
            'orderno'        => 'ORD-2026-M4-001',
            'customer_id'    => $this->customer1->id,
            'user_id'        => $this->seller1->id,
            'address'        => 'Site 10, Okhla Phase III, New Delhi - 110020',
            'grandtotal'     => 30000.00,
            'netamount'      => 25423.73,
            'taxamount'      => 4576.27,
            'payment_status' => 'Pending',
        ]);

        $this->childOrder1 = Order::create([
            'parent_order_id' => $this->parentOrder->id,
            'orderno'         => 'ORD-2026-M4-001A',
            'customer_id'     => $this->customer1->id,
            'user_id'         => $this->seller1->id, // Delhi seller
            'address'         => 'Site 10, Okhla Phase III, New Delhi',
            'grandtotal'      => 18000.00,
            'payment_status'  => 'Pending',
        ]);

        $this->childOrder2 = Order::create([
            'parent_order_id' => $this->parentOrder->id,
            'orderno'         => 'ORD-2026-M4-001B',
            'customer_id'     => $this->customer1->id,
            'user_id'         => $this->seller2->id, // Maharashtra seller
            'address'         => 'Site 10, Okhla Phase III, New Delhi',
            'grandtotal'      => 12000.00,
            'payment_status'  => 'Pending',
        ]);
    }

    /**
     * TEST 1: Tax master effective dating selects correct version based on transaction date.
     */
    public function test_tax_master_effective_dating(): void
    {
        // Version 1: 12% effective 2026-01-01 to 2026-06-30
        $v1 = TaxMaster::create([
            'hsn_code'         => '3208',
            'description'      => 'Paints and Varnishes based on synthetic polymers',
            'gst_rate'         => 12.00,
            'type'             => 'goods',
            'effective_from'   => '2026-01-01',
            'effective_to'     => '2026-06-30',
            'approval_status'  => TaxMaster::STATUS_APPROVED,
            'version'          => 1,
        ]);

        // Version 2: 18% effective 2026-07-01 onwards
        $v2 = TaxMaster::create([
            'hsn_code'            => '3208',
            'description'         => 'Paints and Varnishes updated rate',
            'gst_rate'            => 18.00,
            'type'                => 'goods',
            'effective_from'      => '2026-07-01',
            'effective_to'        => null,
            'approval_status'     => TaxMaster::STATUS_APPROVED,
            'version'             => 2,
            'previous_version_id' => $v1->id,
        ]);

        // Look up on 2026-03-15 -> Should return Version 1 (12%)
        $taxMarch = $this->taxMasterService->getEffectiveTax('3208', '2026-03-15');
        $this->assertNotNull($taxMarch);
        $this->assertEquals(1, $taxMarch->version);
        $this->assertEquals(12.00, (float)$taxMarch->gst_rate);

        // Look up on 2026-08-01 -> Should return Version 2 (18%)
        $taxAugust = $this->taxMasterService->getEffectiveTax('3208', '2026-08-01');
        $this->assertNotNull($taxAugust);
        $this->assertEquals(2, $taxAugust->version);
        $this->assertEquals(18.00, (float)$taxAugust->gst_rate);
    }

    /**
     * TEST 2: Intra-state tax calculation applies 50% CGST and 50% SGST.
     */
    public function test_intra_state_tax_calculation(): void
    {
        TaxMaster::create([
            'hsn_code'        => '3208',
            'description'     => 'Solvent-based paints',
            'gst_rate'        => 18.00,
            'type'            => 'goods',
            'effective_from'  => '2026-01-01',
            'approval_status' => TaxMaster::STATUS_APPROVED,
        ]);

        // Seller State: Delhi, Buyer State: Delhi (Intra-State)
        $lineData = [
            'hsn_code'        => '3208',
            'unit_price'      => 5000.00,
            'quantity'        => 2,
            'discount_amount' => 1000.00, // Taxable = 10,000 - 1,000 = 9,000.00
            'product_name'    => 'Epoxy Floor Coating 20L',
        ];

        $calc = $this->taxCalculationService->calculateLineTax($lineData, 'Delhi', 'Delhi', '2026-11-01');

        $this->assertFalse($calc['is_interstate']);
        $this->assertEquals(9000.00, $calc['taxable_amount']);
        $this->assertEquals(18.00, $calc['tax_rate']);

        // CGST = 9% of 9000 = 810.00
        $this->assertEquals(9.00, $calc['cgst_rate']);
        $this->assertEquals(810.00, $calc['cgst_amount']);

        // SGST = 9% of 9000 = 810.00
        $this->assertEquals(9.00, $calc['sgst_rate']);
        $this->assertEquals(810.00, $calc['sgst_amount']);

        // IGST = 0.00
        $this->assertEquals(0.00, $calc['igst_amount']);
        $this->assertEquals(1620.00, $calc['total_tax_amount']);
        $this->assertEquals(10620.00, $calc['gross_amount']);
    }

    /**
     * TEST 3: Inter-state tax calculation applies 100% IGST.
     */
    public function test_inter_state_tax_calculation(): void
    {
        TaxMaster::create([
            'hsn_code'        => '3208',
            'description'     => 'Solvent-based paints',
            'gst_rate'        => 18.00,
            'type'            => 'goods',
            'effective_from'  => '2026-01-01',
            'approval_status' => TaxMaster::STATUS_APPROVED,
        ]);

        // Seller State: Maharashtra, Buyer State: Delhi (Inter-State)
        $lineData = [
            'hsn_code'        => '3208',
            'unit_price'      => 5000.00,
            'quantity'        => 2,
            'discount_amount' => 1000.00, // Taxable = 9,000.00
        ];

        $calc = $this->taxCalculationService->calculateLineTax($lineData, 'Maharashtra', 'Delhi', '2026-11-01');

        $this->assertTrue($calc['is_interstate']);
        $this->assertEquals(9000.00, $calc['taxable_amount']);
        $this->assertEquals(18.00, $calc['tax_rate']);

        // IGST = 18% of 9000 = 1620.00
        $this->assertEquals(18.00, $calc['igst_rate']);
        $this->assertEquals(1620.00, $calc['igst_amount']);

        // CGST & SGST must be 0
        $this->assertEquals(0.00, $calc['cgst_amount']);
        $this->assertEquals(0.00, $calc['sgst_amount']);
        $this->assertEquals(1620.00, $calc['total_tax_amount']);
        $this->assertEquals(10620.00, $calc['gross_amount']);
    }

    /**
     * TEST 4: Historical order tax snapshots remain immutable when tax master changes.
     */
    public function test_historical_tax_snapshot_immutability(): void
    {
        $taxMaster = TaxMaster::create([
            'hsn_code'        => '3209',
            'description'     => 'Water-based acrylic coatings',
            'gst_rate'        => 18.00,
            'type'            => 'goods',
            'effective_from'  => '2026-01-01',
            'approval_status' => TaxMaster::STATUS_APPROVED,
        ]);

        $line = [
            'hsn_code'     => '3209',
            'unit_price'   => 2000.00,
            'quantity'     => 5, // 10,000.00
            'product_name' => 'Premium Acrylic Emulsion',
        ];

        // Capture snapshot at confirmation
        $snapshots = $this->taxCalculationService->captureOrderTaxSnapshot(
            $this->childOrder1,
            [$line],
            'Delhi',
            'Delhi'
        );

        $this->assertCount(1, $snapshots);
        $snapshot = $snapshots->first();
        $this->assertEquals(18.00, (float)$snapshot->tax_rate);
        $this->assertEquals(900.00, (float)$snapshot->cgst_amount);
        $this->assertEquals(900.00, (float)$snapshot->sgst_amount);
        $this->assertEquals(1800.00, (float)$snapshot->total_tax_amount);

        // Now modify the tax master (e.g. rate changes to 28% or 12%)
        $taxMaster->update(['gst_rate' => 28.00]);

        // Verify the order's stored snapshot was NOT altered
        $storedSnapshot = OrderTaxSnapshot::where('order_id', $this->childOrder1->id)->first();
        $this->assertEquals(18.00, (float)$storedSnapshot->tax_rate);
        $this->assertEquals(1800.00, (float)$storedSnapshot->total_tax_amount);
    }

    /**
     * TEST 5: Missing or unapproved HSN classification blocks checkout calculation.
     */
    public function test_missing_or_unapproved_classification_blocks_calculation(): void
    {
        $this->expectException(ValidationException::class);

        $lineData = [
            'hsn_code'   => '999999', // Non-existent HSN code
            'unit_price' => 1000.00,
            'quantity'   => 1,
        ];

        $this->taxCalculationService->calculateLineTax($lineData, 'Delhi', 'Delhi');
    }

    /**
     * TEST 6: Tax master effective date overlap prevention.
     */
    public function test_tax_master_date_overlap_validation(): void
    {
        $this->expectException(ValidationException::class);

        // Record 1: 2026-01-01 to 2026-12-31
        TaxMaster::create([
            'hsn_code'        => '3208',
            'description'     => 'Paint',
            'gst_rate'        => 18.00,
            'type'            => 'goods',
            'effective_from'  => '2026-01-01',
            'effective_to'    => '2026-12-31',
            'approval_status' => TaxMaster::STATUS_APPROVED,
        ]);

        // Attempting to create an overlapping record for HSN 3208: 2026-06-01 to 2026-08-31
        $this->taxMasterService->createRecord([
            'hsn_code'        => '3208',
            'description'     => 'Overlapping Paint',
            'gst_rate'        => 12.00,
            'effective_from'  => '2026-06-01',
            'effective_to'    => '2026-08-31',
            'approval_status' => TaxMaster::STATUS_APPROVED,
        ], $this->adminUser->id, 'admin');
    }

    /**
     * TEST 7: HSN/SAC format validation.
     */
    public function test_hsn_sac_format_validation(): void
    {
        // Valid HSN: 4 digits, 6 digits, 8 digits
        $this->assertTrue(TaxMaster::isValidHsnOrSac('3208'));
        $this->assertTrue(TaxMaster::isValidHsnOrSac('320810'));
        $this->assertTrue(TaxMaster::isValidHsnOrSac('32081090'));

        // Valid SAC: 6 digits starting with 99
        $this->assertTrue(TaxMaster::isValidHsnOrSac('998311'));

        // Invalid HSN: 3 digits, 5 digits, 7 digits, letters, special characters
        $this->assertFalse(TaxMaster::isValidHsnOrSac('320'));
        $this->assertFalse(TaxMaster::isValidHsnOrSac('12345'));
        $this->assertFalse(TaxMaster::isValidHsnOrSac('1234567'));
        $this->assertFalse(TaxMaster::isValidHsnOrSac('INVALID'));
    }

    /**
     * TEST 8: Tax master approval workflow and permissions.
     */
    public function test_tax_master_approval_workflow(): void
    {
        // Seller submits classification
        $record = $this->taxMasterService->createRecord([
            'hsn_code'       => '3210',
            'description'    => 'Water Pigments',
            'gst_rate'       => 18.00,
            'effective_from' => '2026-01-01',
        ], $this->seller1->id, 'seller', $this->seller1->name);

        $this->assertEquals(TaxMaster::STATUS_PENDING, $record->approval_status);
        $this->assertFalse($record->isApproved());

        // Admin approves classification
        $approved = $this->taxMasterService->approveRecord($record, $this->adminUser->id, 'Finance Admin');
        $this->assertEquals(TaxMaster::STATUS_APPROVED, $approved->approval_status);
        $this->assertTrue($approved->isApproved());
        $this->assertNotNull($approved->last_verified_at);
    }

    /**
     * TEST 9: Double-entry financial ledger posting upon order payment capture.
     */
    public function test_double_entry_financial_ledger_posting(): void
    {
        $txn = Transection::create([
            'payment_ref'          => 'PAY-2026-TEST-001',
            'order_id'             => $this->parentOrder->id,
            'parent_order_id'      => $this->parentOrder->id,
            'order_no'             => $this->parentOrder->orderno,
            'customer_id'          => $this->customer1->id,
            'user_id'              => $this->seller1->id,
            'gateway_name'         => 'razorpay',
            'amount'               => 30000.00,
            'currency'             => 'INR',
            'status'               => 'successful',
            'verification_status'  => 'verified',
            'reconciliation_status'=> 'matched',
            'initiated_at'         => now(),
            'verified_at'          => now(),
        ]);

        // Record payment settlement
        $settlements = $this->financialLedgerService->recordOrderPayment($this->parentOrder, $txn);

        $this->assertCount(2, $settlements); // Two child orders for Seller 1 and Seller 2

        // Verify Seller 1 ledger entries
        $seller1Entries = FinancialLedger::where('seller_user_id', $this->seller1->id)->get();
        $this->assertGreaterThanOrEqual(2, $seller1Entries->count());

        $payableEntry = $seller1Entries->where('entry_type', FinancialLedger::TYPE_SELLER_PAYABLE)->first();
        $this->assertNotNull($payableEntry);
        $this->assertEquals(18000.00, (float)$payableEntry->amount);
        $this->assertEquals(FinancialLedger::DIRECTION_CREDIT, $payableEntry->direction);

        $commEntry = $seller1Entries->where('entry_type', FinancialLedger::TYPE_PLATFORM_COMMISSION)->first();
        $this->assertNotNull($commEntry);
        $this->assertEquals(FinancialLedger::DIRECTION_DEBIT, $commEntry->direction);
    }

    /**
     * TEST 10: Seller settlement lifecycle and payout hold period.
     */
    public function test_seller_settlement_lifecycle(): void
    {
        $txn = Transection::create([
            'payment_ref' => 'PAY-2026-TEST-002',
            'order_id'    => $this->childOrder1->id,
            'amount'      => 18000.00,
            'status'      => 'successful',
        ]);

        $settlements = $this->financialLedgerService->recordOrderPayment($this->childOrder1, $txn);
        $settlement = $settlements[0];

        $this->assertEquals(SellerSettlement::STATUS_ON_HOLD, $settlement->settlement_status);
        $this->assertNotNull($settlement->hold_until);

        // Admin updates status to eligible
        $updated = $this->financialLedgerService->updateSettlementStatus(
            $settlement,
            SellerSettlement::STATUS_ELIGIBLE,
            null,
            $this->adminUser->id
        );
        $this->assertEquals(SellerSettlement::STATUS_ELIGIBLE, $updated->settlement_status);

        // Admin processes payout
        $completed = $this->financialLedgerService->updateSettlementStatus(
            $updated,
            SellerSettlement::STATUS_COMPLETED,
            'UTR-HDFC-12345678',
            $this->adminUser->id
        );
        $this->assertEquals(SellerSettlement::STATUS_COMPLETED, $completed->settlement_status);
        $this->assertEquals('UTR-HDFC-12345678', $completed->payout_reference);

        // Payout released entry logged in ledger
        $payoutEntry = FinancialLedger::where('seller_user_id', $this->seller1->id)
            ->where('entry_type', FinancialLedger::TYPE_PAYOUT_RELEASED)
            ->first();
        $this->assertNotNull($payoutEntry);
        $this->assertEquals(FinancialLedger::DIRECTION_DEBIT, $payoutEntry->direction);
    }

    /**
     * TEST 11: Payout idempotency prevents duplicate disbursements.
     */
    public function test_payout_idempotency_prevents_duplicate_entries(): void
    {
        $settlement = SellerSettlement::create([
            'settlement_ref'           => 'SETTLE-TEST-999',
            'parent_order_id'          => $this->parentOrder->id,
            'order_id'                 => $this->childOrder1->id,
            'seller_user_id'           => $this->seller1->id,
            'gross_order_amount'       => 18000.00,
            'commission_amount'        => 1800.00,
            'commission_gst_amount'    => 324.00,
            'total_platform_deduction' => 2124.00,
            'net_seller_payable'       => 15876.00,
            'settlement_status'        => SellerSettlement::STATUS_COMPLETED,
            'payout_reference'         => 'UTR-FIRST-001',
        ]);

        $ledgerCountBefore = FinancialLedger::count();

        // Attempt second completion
        $this->financialLedgerService->updateSettlementStatus(
            $settlement,
            SellerSettlement::STATUS_COMPLETED,
            'UTR-DUPLICATE-002',
            $this->adminUser->id
        );

        $this->assertEquals($ledgerCountBefore, FinancialLedger::count());
    }

    /**
     * TEST 12: Multi-tenant seller ledger and settlement isolation.
     */
    public function test_seller_ledger_multi_tenant_isolation(): void
    {
        // Create ledger entry for Seller 1
        FinancialLedger::create([
            'entry_ref'       => 'LEDGER-S1-001',
            'seller_user_id'  => $this->seller1->id,
            'entry_type'      => FinancialLedger::TYPE_SELLER_PAYABLE,
            'direction'       => FinancialLedger::DIRECTION_CREDIT,
            'amount'          => 10000.00,
            'running_balance' => 10000.00,
            'source_process'  => 'test',
        ]);

        // Create ledger entry for Seller 2
        FinancialLedger::create([
            'entry_ref'       => 'LEDGER-S2-001',
            'seller_user_id'  => $this->seller2->id,
            'entry_type'      => FinancialLedger::TYPE_SELLER_PAYABLE,
            'direction'       => FinancialLedger::DIRECTION_CREDIT,
            'amount'          => 20000.00,
            'running_balance' => 20000.00,
            'source_process'  => 'test',
        ]);

        // Seller 1 queries their ledger
        $resp1 = $this->actingAs($this->seller1)->getJson('/api/v2/seller/ledger');
        $resp1->assertStatus(200);
        $data1 = $resp1->json('data');
        $this->assertCount(1, $data1);
        $this->assertEquals('LEDGER-S1-001', $data1[0]['entry_ref']);

        // Seller 2 queries their ledger
        $resp2 = $this->actingAs($this->seller2)->getJson('/api/v2/seller/ledger');
        $resp2->assertStatus(200);
        $data2 = $resp2->json('data');
        $this->assertCount(1, $data2);
        $this->assertEquals('LEDGER-S2-001', $data2[0]['entry_ref']);
    }

    /**
     * TEST 13: Goods Tax Invoice generation based on immutable tax snapshot.
     */
    public function test_goods_tax_invoice_generation(): void
    {
        TaxMaster::create([
            'hsn_code'        => '3208',
            'description'     => 'Paint',
            'gst_rate'        => 18.00,
            'type'            => 'goods',
            'effective_from'  => '2026-01-01',
            'approval_status' => TaxMaster::STATUS_APPROVED,
        ]);

        // Capture snapshot first
        $this->taxCalculationService->captureOrderTaxSnapshot(
            $this->childOrder1,
            [[
                'hsn_code'     => '3208',
                'unit_price'   => 15254.24,
                'quantity'     => 1,
                'product_name' => 'Industrial Protective Coating',
            ]],
            'Delhi',
            'Delhi'
        );

        // Generate Goods Invoice
        $invoice = $this->invoiceService->generateGoodsInvoice($this->childOrder1);

        $this->assertEquals(Invoice::TYPE_GOODS_TAX_INVOICE, $invoice->invoice_type);
        $this->assertEquals(Invoice::ISSUER_SELLER, $invoice->issuer_type);
        $this->assertEquals($this->company1->name, $invoice->issuer_name);
        $this->assertEquals($this->customer1->name, $invoice->recipient_name);
        $this->assertStringStartsWith('INV-G-', $invoice->invoice_no);
        $this->assertCount(1, $invoice->items);

        // Re-generate should return the same invoice (idempotent)
        $invoice2 = $this->invoiceService->generateGoodsInvoice($this->childOrder1);
        $this->assertEquals($invoice->id, $invoice2->id);
    }

    /**
     * TEST 14: Platform Commission Invoice generation by Apni Factory.
     */
    public function test_platform_commission_invoice_generation(): void
    {
        $snapshot = OrderCommissionSnapshot::create([
            'order_id'              => $this->childOrder1->id,
            'orderno'               => $this->childOrder1->orderno,
            'seller_user_id'        => $this->seller1->id,
            'commission_percentage' => 10.00,
            'base_amount'           => 18000.00,
            'commission_amount'     => 1800.00,
            'gst_rate'              => 18.00,
            'gst_amount'            => 324.00,
            'total_platform_fee'    => 2124.00,
            'seller_payable'        => 15876.00,
        ]);

        $invoice = $this->invoiceService->generateCommissionInvoice($this->childOrder1, $snapshot);

        $this->assertEquals(Invoice::TYPE_PLATFORM_COMMISSION_INVOICE, $invoice->invoice_type);
        $this->assertEquals(Invoice::ISSUER_PLATFORM, $invoice->issuer_type);
        $this->assertEquals('Apni Factory Private Limited', $invoice->issuer_name);
        $this->assertEquals($this->company1->name, $invoice->recipient_name);
        $this->assertEquals(1800.00, (float)$invoice->taxable_amount);
        $this->assertEquals(324.00, (float)$invoice->total_tax_amount);
        $this->assertEquals(2124.00, (float)$invoice->total_amount);
    }

    /**
     * TEST 15: Admin and Seller Web Portal views accessibility.
     */
    public function test_admin_and_seller_web_views_accessible(): void
    {
        // Tax Master index
        $resp1 = $this->actingAs($this->adminUser)->get('/admin/finance/tax-master');
        $resp1->assertStatus(200);
        $resp1->assertSee('GST / HSN Tax Master');

        // Settlements index
        $resp2 = $this->actingAs($this->adminUser)->get('/admin/finance/settlements');
        $resp2->assertStatus(200);
        $resp2->assertSee('Seller Settlements');

        // Financial Ledger index
        $resp3 = $this->actingAs($this->adminUser)->get('/admin/finance/ledger');
        $resp3->assertStatus(200);
        $resp3->assertSee('Financial Ledger');

        // Seller Settlements view
        $resp4 = $this->actingAs($this->seller1)->get('/seller/finance/settlements');
        $resp4->assertStatus(200);
        $resp4->assertSee('My Settlements &amp; Payouts', false);
    }
}

