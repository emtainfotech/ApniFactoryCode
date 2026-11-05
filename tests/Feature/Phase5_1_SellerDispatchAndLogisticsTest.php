<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SellerFulfilment;
use App\Models\SellerFulfilmentHistory;
use App\Models\User;
use App\Services\SellerFulfilmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase5_1_SellerDispatchAndLogisticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $seller1;
    protected User $seller2;
    protected User $seller3;
    protected Company $company1;
    protected Company $company2;
    protected Company $company3;
    protected User $buyerUser1;
    protected User $buyerUser2;
    protected Customer $customer1;
    protected Customer $customer2;
    protected Order $parentOrder;
    protected Order $childOrder1;
    protected Order $childOrder2;
    protected Order $childOrder3;

    protected SellerFulfilmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->service = app(SellerFulfilmentService::class);

        // 1. Create Admin
        $this->adminUser = User::create([
            'name'     => 'Logistics Admin',
            'email'    => 'admin.logistics@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. Create 3 distinct Sellers
        $this->seller1 = User::create([
            'name'     => 'Jaipur Fabrics Pvt Ltd',
            'email'    => 'seller1@jaipurfabrics.in',
            'password' => bcrypt('password123'),
        ]);
        $this->company1 = Company::create([
            'user_id' => $this->seller1->id,
            'name'    => 'Jaipur Fabrics Plant 1',
            'address' => 'Plot 101, Sitapura Industrial Area',
            'city'    => 'Jaipur',
            'state'   => 'Rajasthan',
            'pincode' => '302022',
            'phone'   => '0141223344',
            'status'  => 'active',
        ]);

        $this->seller2 = User::create([
            'name'     => 'Delhi Ceramic Works',
            'email'    => 'seller2@delhiceramics.in',
            'password' => bcrypt('password123'),
        ]);
        $this->company2 = Company::create([
            'user_id' => $this->seller2->id,
            'name'    => 'Delhi Ceramic Godown',
            'address' => 'Shed 12, Okhla Phase III',
            'city'    => 'New Delhi',
            'state'   => 'Delhi',
            'pincode' => '110020',
            'phone'   => '011998877',
            'status'  => 'active',
        ]);

        $this->seller3 = User::create([
            'name'     => 'Mumbai Tools & Hardware',
            'email'    => 'seller3@mumbaitools.in',
            'password' => bcrypt('password123'),
        ]);
        $this->company3 = Company::create([
            'user_id' => $this->seller3->id,
            'name'    => 'Mumbai Central Warehouse',
            'address' => 'Unit 5, TTC Industrial Area, Mahape',
            'city'    => 'Navi Mumbai',
            'state'   => 'Maharashtra',
            'pincode' => '400710',
            'phone'   => '022334455',
            'status'  => 'active',
        ]);

        // 3. Create Buyers
        $this->buyerUser1 = User::create([
            'name'     => 'Apex Construction Hub',
            'email'    => 'procurement@apexconstruction.com',
            'password' => bcrypt('password123'),
        ]);
        $this->buyerUser2 = User::create([
            'name'     => 'Zenith Retailers Ltd',
            'email'    => 'stores@zenithretail.com',
            'password' => bcrypt('password123'),
        ]);

        $this->customer1 = Customer::create([
            'id'       => $this->buyerUser1->id,
            'name'     => 'Apex Construction Hub',
            'email'    => 'procurement@apexconstruction.com',
            'mobile'   => '9876500001',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);

        $this->customer2 = Customer::create([
            'id'       => $this->buyerUser2->id,
            'name'     => 'Zenith Retailers Ltd',
            'email'    => 'stores@zenithretail.com',
            'mobile'   => '9876500002',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);

        // 4. Create Multi-Seller Parent Order & 3 Child Orders
        $this->parentOrder = Order::create([
            'orderno'        => 'ORD-2026-MULTI-001',
            'customer_id'    => $this->buyerUser1->id,
            'user_id'        => $this->seller1->id,
            'address'        => 'Transport Nagar Gate 4, Warehouse 8, Jaipur',
            'grandtotal'     => 75000.00,
            'netamount'      => 65000.00,
            'payment_status' => 'Paid',
        ]);

        $this->childOrder1 = Order::create([
            'orderno'         => 'ORD-2026-MULTI-001-S1',
            'parent_order_id' => $this->parentOrder->id,
            'customer_id'     => $this->buyerUser1->id,
            'user_id'         => $this->seller1->id,
            'address'         => 'Transport Nagar Gate 4, Warehouse 8, Jaipur',
            'grandtotal'      => 25000.00,
            'netamount'       => 22000.00,
            'payment_status'  => 'Paid',
        ]);
        OrderDetail::create([
            'order_id'    => $this->childOrder1->id,
            'orderno'     => $this->childOrder1->orderno,
            'customer_id' => $this->buyerUser1->id,
            'productname' => 'Heavy Cotton Canvas (Rolls)',
            'hsn'         => '5208',
            'attribute'   => json_encode([['qty' => 10, 'price' => 2500]]),
        ]);

        $this->childOrder2 = Order::create([
            'orderno'         => 'ORD-2026-MULTI-001-S2',
            'parent_order_id' => $this->parentOrder->id,
            'customer_id'     => $this->buyerUser1->id,
            'user_id'         => $this->seller2->id,
            'address'         => 'Transport Nagar Gate 4, Warehouse 8, Jaipur',
            'grandtotal'      => 30000.00,
            'netamount'       => 26000.00,
            'payment_status'  => 'Paid',
        ]);
        OrderDetail::create([
            'order_id'    => $this->childOrder2->id,
            'orderno'     => $this->childOrder2->orderno,
            'customer_id' => $this->buyerUser1->id,
            'productname' => 'Vitrified Floor Tiles (Boxes)',
            'hsn'         => '6907',
            'attribute'   => json_encode([['qty' => 20, 'price' => 1500]]),
        ]);

        $this->childOrder3 = Order::create([
            'orderno'         => 'ORD-2026-MULTI-001-S3',
            'parent_order_id' => $this->parentOrder->id,
            'customer_id'     => $this->buyerUser1->id,
            'user_id'         => $this->seller3->id,
            'address'         => 'Transport Nagar Gate 4, Warehouse 8, Jaipur',
            'grandtotal'      => 20000.00,
            'netamount'       => 17000.00,
            'payment_status'  => 'Paid',
        ]);
        OrderDetail::create([
            'order_id'    => $this->childOrder3->id,
            'orderno'     => $this->childOrder3->orderno,
            'customer_id' => $this->buyerUser1->id,
            'productname' => 'Industrial Heavy Wrenches',
            'hsn'         => '8204',
            'attribute'   => json_encode([['qty' => 15, 'price' => 1333.33]]),
        ]);
    }

    /**
     * Test 1: Three distinct seller fulfilments created for multi-seller parent order.
     */
    public function test_multi_seller_three_fulfilments_created_for_parent_order(): void
    {
        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);
        $f2 = $this->service->getOrCreateFulfilment($this->childOrder2);
        $f3 = $this->service->getOrCreateFulfilment($this->childOrder3);

        $this->assertDatabaseCount('seller_fulfilments', 3);

        $this->assertEquals($this->childOrder1->id, $f1->order_id);
        $this->assertEquals($this->seller1->id, $f1->seller_user_id);
        $this->assertEquals($this->parentOrder->id, $f1->parent_order_id);

        $this->assertEquals($this->childOrder2->id, $f2->order_id);
        $this->assertEquals($this->seller2->id, $f2->seller_user_id);
        $this->assertEquals($this->parentOrder->id, $f2->parent_order_id);

        $this->assertEquals($this->childOrder3->id, $f3->order_id);
        $this->assertEquals($this->seller3->id, $f3->seller_user_id);
        $this->assertEquals($this->parentOrder->id, $f3->parent_order_id);

        // Refs must be unique
        $this->assertNotEquals($f1->fulfilment_ref, $f2->fulfilment_ref);
        $this->assertNotEquals($f2->fulfilment_ref, $f3->fulfilment_ref);
    }

    /**
     * Test 2: Independent logistics data maintained per seller (origin, transporter, LR, status).
     */
    public function test_independent_logistics_data_per_seller(): void
    {
        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);
        $f2 = $this->service->getOrCreateFulfilment($this->childOrder2);
        $f3 = $this->service->getOrCreateFulfilment($this->childOrder3);

        // Seller 1 records Jaipur factory origin & transporter
        $this->service->updateDispatchOrigin($f1, [
            'type'    => 'factory',
            'name'    => 'Jaipur Textiles Shed 1',
            'address' => 'RIICO Industrial Area, Sitapura',
            'city'    => 'Jaipur',
            'state'   => 'Rajasthan',
            'pincode' => '302022',
        ], $this->seller1->id, 'Seller 1');

        $this->service->updateTransporterDetails($f1, [
            'name'        => 'Jaipur Golden Transport Co',
            'contact'     => '9829012345',
            'tracking_no' => 'JGT-9988',
        ], $this->seller1->id, 'Seller 1');

        // Seller 2 records Delhi godown origin & transporter
        $this->service->updateDispatchOrigin($f2, [
            'type'    => 'godown',
            'name'    => 'Okhla Godown 4B',
            'address' => 'Okhla Phase III',
            'city'    => 'New Delhi',
            'state'   => 'Delhi',
            'pincode' => '110020',
        ], $this->seller2->id, 'Seller 2');

        $this->service->updateTransporterDetails($f2, [
            'name'        => 'SafeX Cargo Express',
            'contact'     => '9811098765',
            'tracking_no' => 'SFX-112233',
        ], $this->seller2->id, 'Seller 2');

        $f1->refresh();
        $f2->refresh();
        $f3->refresh();

        $this->assertEquals('Jaipur Golden Transport Co', $f1->transporter_name);
        $this->assertEquals('SafeX Cargo Express', $f2->transporter_name);
        $this->assertNull($f3->transporter_name);

        $this->assertEquals('Jaipur Textiles Shed 1', $f1->dispatch_origin_name);
        $this->assertEquals('Okhla Godown 4B', $f2->dispatch_origin_name);
    }

    /**
     * Test 3: Buyer-Paid freight terms explicitly visible and stored without platform freight fee.
     */
    public function test_buyer_paid_freight_terms_enforced_without_platform_freight(): void
    {
        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);

        $this->assertEquals(SellerFulfilment::FREIGHT_TERMS_TO_PAY, $f1->freight_terms);
        $this->assertEquals('To-Pay (Buyer-Paid Freight)', $f1->freight_terms);

        // Verify API output displays freight terms
        $response = $this->actingAs($this->seller1)->getJson("/api/v2/seller/fulfilment/{$this->childOrder1->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.freight_terms', 'To-Pay (Buyer-Paid Freight)');
    }

    /**
     * Test 4: LR upload is dispatch evidence and does NOT mark order delivered.
     */
    public function test_lr_document_upload_is_dispatch_evidence_and_does_not_mark_order_delivered(): void
    {
        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);
        $this->assertEquals(SellerFulfilment::STATUS_PENDING_ACCEPTANCE, $f1->fulfilment_status);

        $fakeFile = UploadedFile::fake()->create('bilty_scan_2026.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->seller1)->postJson("/api/v2/seller/fulfilment/{$this->childOrder1->id}/upload-lr", [
            'lr_document' => $fakeFile,
            'lr_number'   => 'LR-JP-99482',
        ]);

        $response->assertStatus(200);
        $f1->refresh();

        $this->assertTrue($f1->hasLrDocument());
        $this->assertEquals('LR-JP-99482', $f1->lr_number);
        $this->assertNotNull($f1->lr_document_path);
        $this->assertEquals('application/pdf', $f1->lr_mime_type);

        // Critical BRD Assertion: LR upload must NOT mark order delivered
        $this->assertNotEquals(SellerFulfilment::STATUS_DELIVERED, $f1->fulfilment_status);
        $this->assertEquals(SellerFulfilment::STATUS_PENDING_ACCEPTANCE, $f1->fulfilment_status);
    }

    /**
     * Test 5: Seller A cannot update Seller B's dispatch or upload LR (tenant isolation).
     */
    public function test_seller_a_cannot_update_seller_b_dispatch_or_upload_lr(): void
    {
        // Seller 1 tries to access / mutate Seller 2's order
        $response = $this->actingAs($this->seller1)->postJson("/api/v2/seller/fulfilment/{$this->childOrder2->id}/dispatch", [
            'transporter_name' => 'Unauthorized Carrier',
            'package_count'    => 5,
        ]);

        $response->assertStatus(403);

        // Seller 1 tries to upload LR to Seller 2's order
        $fakeFile = UploadedFile::fake()->create('bilty.jpg', 150, 'image/jpeg');
        $uploadResp = $this->actingAs($this->seller1)->postJson("/api/v2/seller/fulfilment/{$this->childOrder2->id}/upload-lr", [
            'lr_document' => $fakeFile,
            'lr_number'   => 'LR-HACK-001',
        ]);

        $uploadResp->assertStatus(403);
    }

    /**
     * Test 6: Invalid document type or oversized file rejected during LR upload.
     */
    public function test_invalid_document_type_or_oversized_file_rejected(): void
    {
        // Executable file should be blocked
        $fakeExe = UploadedFile::fake()->create('payload.exe', 100, 'application/x-msdownload');
        $resp1 = $this->actingAs($this->seller1)->postJson("/api/v2/seller/fulfilment/{$this->childOrder1->id}/upload-lr", [
            'lr_document' => $fakeExe,
        ]);
        $resp1->assertStatus(422);

        // Oversized file > 5MB should be blocked
        $oversizedFile = UploadedFile::fake()->create('huge_bilty.pdf', 6000, 'application/pdf'); // 6MB
        $resp2 = $this->actingAs($this->seller1)->postJson("/api/v2/seller/fulfilment/{$this->childOrder1->id}/upload-lr", [
            'lr_document' => $oversizedFile,
        ]);
        $resp2->assertStatus(422);
    }

    /**
     * Test 7: Missing required dispatch information blocks status transition to dispatched.
     */
    public function test_missing_required_dispatch_information_blocks_status_transition(): void
    {
        // Missing transporter_name and package_count
        $resp = $this->actingAs($this->seller1)->postJson("/api/v2/seller/fulfilment/{$this->childOrder1->id}/dispatch", [
            'notes' => 'Attempting incomplete dispatch',
        ]);

        $resp->assertStatus(422);
        $resp->assertJsonValidationErrors(['transporter_name', 'package_count']);

        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);
        $this->assertFalse($f1->isDispatched());
    }

    /**
     * Test 8: Parent order aggregates status correctly while preserving independent seller records.
     */
    public function test_parent_order_aggregates_status_without_collapsing_seller_records(): void
    {
        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);
        $f2 = $this->service->getOrCreateFulfilment($this->childOrder2);
        $f3 = $this->service->getOrCreateFulfilment($this->childOrder3);

        // State 0: 0/3 dispatched -> 'pending'
        $summary0 = $this->service->getParentOrderLogisticsSummary($this->parentOrder);
        $this->assertEquals(3, $summary0['total_sellers']);
        $this->assertEquals(0, $summary0['dispatched_count']);
        $this->assertEquals('pending', $summary0['aggregated_status']);

        // Seller 1 completes dispatch
        $this->service->recordDispatch($f1, [
            'transporter_name' => 'Jaipur Express',
            'lr_number'        => 'LR-JP-001',
            'package_count'    => 4,
            'dispatched_at'    => now(),
        ], $this->seller1->id, 'Seller 1');

        // State 1: 1/3 dispatched -> 'partially_dispatched'
        $summary1 = $this->service->getParentOrderLogisticsSummary($this->parentOrder);
        $this->assertEquals(1, $summary1['dispatched_count']);
        $this->assertEquals('partially_dispatched', $summary1['aggregated_status']);

        // Sellers 2 and 3 complete dispatch
        $this->service->recordDispatch($f2, [
            'transporter_name' => 'SafeX Delhi',
            'lr_number'        => 'LR-DL-002',
            'package_count'    => 6,
            'dispatched_at'    => now(),
        ], $this->seller2->id, 'Seller 2');

        $this->service->recordDispatch($f3, [
            'transporter_name' => 'BlueDart Mumbai',
            'lr_number'        => 'LR-MB-003',
            'package_count'    => 2,
            'dispatched_at'    => now(),
        ], $this->seller3->id, 'Seller 3');

        // State 2: 3/3 dispatched -> 'all_dispatched'
        $summary2 = $this->service->getParentOrderLogisticsSummary($this->parentOrder);
        $this->assertEquals(3, $summary2['dispatched_count']);
        $this->assertEquals('all_dispatched', $summary2['aggregated_status']);

        // Buyer acknowledges all 3 consignments
        $this->service->acknowledgeReceipt($f1, $this->buyerUser1->id, 'Apex Buyer');
        $this->service->acknowledgeReceipt($f2, $this->buyerUser1->id, 'Apex Buyer');
        $this->service->acknowledgeReceipt($f3, $this->buyerUser1->id, 'Apex Buyer');

        // State 3: 3/3 acknowledged -> 'completed'
        $summary3 = $this->service->getParentOrderLogisticsSummary($this->parentOrder);
        $this->assertEquals(3, $summary3['acknowledged_count']);
        $this->assertEquals('completed', $summary3['aggregated_status']);

        // Verify independent records are still distinct and preserved
        $this->assertCount(3, $summary3['sellers']);
        $this->assertEquals('Jaipur Express', $summary3['sellers'][0]['transporter_name']);
        $this->assertEquals('SafeX Delhi', $summary3['sellers'][1]['transporter_name']);
        $this->assertEquals('BlueDart Mumbai', $summary3['sellers'][2]['transporter_name']);
    }

    /**
     * Test 9: Buyer acknowledgement records customer ID, actor name, and timestamp.
     */
    public function test_buyer_acknowledgement_records_actor_and_timestamp(): void
    {
        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);
        $this->service->recordDispatch($f1, [
            'transporter_name' => 'Jaipur Cargo',
            'lr_number'        => 'LR-9988',
            'package_count'    => 3,
        ], $this->seller1->id, 'Seller 1');

        $response = $this->actingAs($this->buyerUser1)->postJson("/api/v2/buyer/fulfilment/{$f1->id}/acknowledge", [
            'notes' => 'Goods received in good condition at Transport Nagar warehouse.',
        ]);

        $response->assertStatus(200);
        $f1->refresh();

        $this->assertEquals(SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED, $f1->fulfilment_status);
        $this->assertNotNull($f1->buyer_acknowledged_at);
        $this->assertEquals('Goods received in good condition at Transport Nagar warehouse.', $f1->buyer_acknowledgement_notes);

        // Verify audit log entry
        $this->assertDatabaseHas('seller_fulfilment_history', [
            'seller_fulfilment_id' => $f1->id,
            'action'               => 'buyer_acknowledged_receipt',
            'actor_id'             => $this->buyerUser1->id,
            'new_status'           => SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED,
        ]);
    }

    /**
     * Test 10: Buyer dispute / shortage reporting linked directly to specific seller order.
     */
    public function test_buyer_dispute_shortage_damage_reporting_linked_to_seller(): void
    {
        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);
        $f2 = $this->service->getOrCreateFulfilment($this->childOrder2);

        $response = $this->actingAs($this->buyerUser1)->postJson("/api/v2/buyer/fulfilment/{$f1->id}/dispute", [
            'dispute_type' => 'shortage_reported',
            'reason'       => '2 rolls of canvas missing from package #3 upon handover.',
        ]);

        $response->assertStatus(200);
        $f1->refresh();
        $f2->refresh();

        $this->assertEquals('shortage_reported', $f1->dispute_status);
        $this->assertEquals('2 rolls of canvas missing from package #3 upon handover.', $f1->dispute_reason);
        $this->assertNotNull($f1->dispute_reported_at);

        // Seller 2's consignment must NOT be affected
        $this->assertEquals(SellerFulfilment::DISPUTE_NONE, $f2->dispute_status);
    }

    /**
     * Test 11: Dispatch notification triggered to buyer upon dispatch.
     */
    public function test_buyer_notified_on_seller_dispatch(): void
    {
        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);

        $this->service->recordDispatch($f1, [
            'transporter_name' => 'Express Logistics',
            'lr_number'        => 'LR-EXPRESS-1234',
            'package_count'    => 5,
        ], $this->seller1->id, 'Seller 1');

        // Check audit log shows dispatch notification recorded
        $this->assertDatabaseHas('seller_fulfilment_history', [
            'seller_fulfilment_id' => $f1->id,
            'action'               => 'order_dispatched',
            'new_status'           => SellerFulfilment::STATUS_DISPATCHED,
        ]);
    }

    /**
     * Test 12: Admin logistics overview and seller dispatch web screens accessible.
     */
    public function test_admin_logistics_and_seller_dispatch_web_screens_accessible(): void
    {
        $f1 = $this->service->getOrCreateFulfilment($this->childOrder1);

        // Admin overview
        $adminResp = $this->actingAs($this->adminUser)->get('/admin/logistics/fulfilments');
        $adminResp->assertStatus(200);
        $adminResp->assertSee('Multi-Seller Fulfilments');

        // Admin detail
        $adminShowResp = $this->actingAs($this->adminUser)->get("/admin/logistics/fulfilments/{$f1->id}");
        $adminShowResp->assertStatus(200);
        $adminShowResp->assertSee($f1->fulfilment_ref);

        // Seller 1 visits their dispatch screen
        $sellerResp = $this->actingAs($this->seller1)->get("/seller/orders/{$this->childOrder1->id}/dispatch");
        $sellerResp->assertStatus(200);
        $sellerResp->assertSee('To-Pay (Buyer-Paid Freight)');

        // Seller 2 attempts to visit Seller 1's dispatch screen -> 403 Forbidden
        $unauthResp = $this->actingAs($this->seller2)->get("/seller/orders/{$this->childOrder1->id}/dispatch");
        $unauthResp->assertStatus(403);
    }
}
