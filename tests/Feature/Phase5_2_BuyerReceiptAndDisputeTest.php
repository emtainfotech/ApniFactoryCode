<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\FulfilmentClaim;
use App\Models\FulfilmentClaimEvidence;
use App\Models\FulfilmentClaimHistory;
use App\Models\FulfilmentClaimItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SellerFulfilment;
use App\Models\SellerFulfilmentHistory;
use App\Models\Transection;
use App\Models\User;
use App\Services\BuyerDisputeService;
use App\Services\SellerFulfilmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase5_2_BuyerReceiptAndDisputeTest extends TestCase
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
    protected SellerFulfilment $fulfilment1;
    protected SellerFulfilment $fulfilment2;
    protected SellerFulfilment $fulfilment3;
    protected OrderDetail $orderDetail1;
    protected OrderDetail $orderDetail2;

    protected SellerFulfilmentService $fulfilmentService;
    protected BuyerDisputeService $disputeService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->fulfilmentService = app(SellerFulfilmentService::class);
        $this->disputeService = app(BuyerDisputeService::class);

        // 1. Admin User
        $this->adminUser = User::create([
            'name'     => 'Operations Admin',
            'email'    => 'admin.ops@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. Sellers & Companies
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
            'name'     => 'Mumbai Hardware Corp',
            'email'    => 'seller3@mumbaihardware.in',
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

        // 3. Buyers
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

        // 4. Parent Order & Child Orders
        $this->parentOrder = Order::create([
            'orderno'        => 'ORD-2026-MULTI-002',
            'customer_id'    => $this->buyerUser1->id,
            'user_id'        => $this->seller1->id,
            'address'        => 'Transport Nagar Gate 4, Warehouse 8, Jaipur',
            'grandtotal'     => 85000.00,
            'netamount'      => 75000.00,
            'payment_status' => 'Paid',
        ]);

        // Record a transaction for parent order
        Transection::create([
            'order_id'       => $this->parentOrder->id,
            'customer_id'    => $this->buyerUser1->id,
            'amount'         => 85000.00,
            'status'         => 'success',
            'payment_source' => 'Razorpay Gateway',
        ]);

        $this->childOrder1 = Order::create([
            'orderno'         => 'ORD-2026-MULTI-002-S1',
            'parent_order_id' => $this->parentOrder->id,
            'customer_id'     => $this->buyerUser1->id,
            'user_id'         => $this->seller1->id,
            'address'         => 'Transport Nagar Gate 4, Warehouse 8, Jaipur',
            'grandtotal'      => 35000.00,
            'netamount'       => 30000.00,
            'payment_status'  => 'Paid',
        ]);
        $this->orderDetail1 = OrderDetail::create([
            'order_id'    => $this->childOrder1->id,
            'orderno'     => $this->childOrder1->orderno,
            'customer_id' => $this->buyerUser1->id,
            'productname' => 'Heavy Canvas Fabric',
            'hsn'         => '5208',
            'attribute'   => json_encode([['qty' => 10, 'price' => 3500]]),
        ]);

        $this->childOrder2 = Order::create([
            'orderno'         => 'ORD-2026-MULTI-002-S2',
            'parent_order_id' => $this->parentOrder->id,
            'customer_id'     => $this->buyerUser1->id,
            'user_id'         => $this->seller2->id,
            'address'         => 'Transport Nagar Gate 4, Warehouse 8, Jaipur',
            'grandtotal'      => 30000.00,
            'netamount'       => 27000.00,
            'payment_status'  => 'Paid',
        ]);
        $this->orderDetail2 = OrderDetail::create([
            'order_id'    => $this->childOrder2->id,
            'orderno'     => $this->childOrder2->orderno,
            'customer_id' => $this->buyerUser1->id,
            'productname' => 'Premium Vitrified Tiles',
            'hsn'         => '6907',
            'attribute'   => json_encode([['qty' => 20, 'price' => 1500]]),
        ]);

        $this->childOrder3 = Order::create([
            'orderno'         => 'ORD-2026-MULTI-002-S3',
            'parent_order_id' => $this->parentOrder->id,
            'customer_id'     => $this->buyerUser1->id,
            'user_id'         => $this->seller3->id,
            'address'         => 'Transport Nagar Gate 4, Warehouse 8, Jaipur',
            'grandtotal'      => 20000.00,
            'netamount'       => 18000.00,
            'payment_status'  => 'Paid',
        ]);

        // 5. Initialize Fulfilments
        $this->fulfilment1 = $this->fulfilmentService->getOrCreateFulfilment($this->childOrder1);
        $this->fulfilment2 = $this->fulfilmentService->getOrCreateFulfilment($this->childOrder2);
        $this->fulfilment3 = $this->fulfilmentService->getOrCreateFulfilment($this->childOrder3);

        // Pre-dispatch fulfilment 1 & 2 for test scenarios
        $this->fulfilmentService->recordDispatch($this->fulfilment1, [
            'transporter_name'        => 'VRL Logistics Ltd',
            'transporter_contact'     => '9876543210',
            'transporter_tracking_no' => 'VRL-778899',
            'lr_number'               => 'LR-JAIPUR-101',
            'package_count'           => 10,
            'weight_volume'           => '250 kg',
            'vehicle_number'          => 'RJ-14-GA-9988',
        ], $this->seller1->id, 'Jaipur Fabrics Logistics');

        $this->fulfilmentService->recordDispatch($this->fulfilment2, [
            'transporter_name'        => 'TCI Freight Express',
            'transporter_contact'     => '9811223344',
            'transporter_tracking_no' => 'TCI-445566',
            'lr_number'               => 'LR-DELHI-202',
            'package_count'           => 20,
            'weight_volume'           => '400 kg',
            'vehicle_number'          => 'DL-1L-AA-1234',
        ], $this->seller2->id, 'Delhi Ceramics Dispatch');
    }

    /**
     * Scenario 1: Buyer acknowledges receipt against their own seller fulfilment order.
     */
    public function test_buyer_acknowledges_receipt_against_own_seller_fulfilment(): void
    {
        $this->actingAs($this->buyerUser1);

        $response = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment1->id}/acknowledge", [
            'notes' => 'Shipment received at Transport Nagar Gate 4 warehouse. Unloaded 10 bales.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $this->fulfilment1->refresh();
        $this->assertEquals(SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED, $this->fulfilment1->fulfilment_status);
        $this->assertNotNull($this->fulfilment1->buyer_acknowledged_at);
        $this->assertEquals('Shipment received at Transport Nagar Gate 4 warehouse. Unloaded 10 bales.', $this->fulfilment1->buyer_acknowledgement_notes);

        // Verify audit log
        $this->assertDatabaseHas('seller_fulfilment_history', [
            'seller_fulfilment_id' => $this->fulfilment1->id,
            'action'               => 'buyer_acknowledged_receipt',
            'actor_role'           => 'buyer',
            'actor_id'             => $this->buyerUser1->id,
            'new_status'           => SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED,
        ]);
    }

    /**
     * Scenario 2: Buyer cannot acknowledge another buyer's seller fulfilment order (HTTP 403).
     */
    public function test_buyer_cannot_acknowledge_another_buyers_fulfilment(): void
    {
        // Authenticated as Buyer 2 (Zenith Retailers), attempting to acknowledge Buyer 1's fulfilment
        $this->actingAs($this->buyerUser2);

        $response = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment1->id}/acknowledge", [
            'notes' => 'Attempting unauthorized handover confirmation.',
        ]);

        $response->assertStatus(403);

        $this->fulfilment1->refresh();
        $this->assertNotEquals(SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED, $this->fulfilment1->fulfilment_status);
    }

    /**
     * Scenario 3: Seller cannot acknowledge receipt on behalf of the buyer.
     */
    public function test_seller_cannot_acknowledge_receipt_on_behalf_of_buyer(): void
    {
        // Seller 1 attempts to call the buyer receipt acknowledgement endpoint
        $this->actingAs($this->seller1);

        $response = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment1->id}/acknowledge", [
            'notes' => 'Seller trying to force delivery acknowledgement.',
        ]);

        $response->assertStatus(403);

        $this->fulfilment1->refresh();
        $this->assertNotEquals(SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED, $this->fulfilment1->fulfilment_status);
    }

    /**
     * Scenario 4: LR/Bilty upload alone does NOT mark the order as delivered or buyer-received.
     */
    public function test_lr_upload_alone_does_not_mark_order_delivered_or_buyer_received(): void
    {
        $this->actingAs($this->seller1);

        $lrFile = UploadedFile::fake()->create('bilty_receipt.pdf', 300, 'application/pdf');

        $response = $this->postJson("/api/v2/seller/fulfilment/{$this->childOrder1->id}/upload-lr", [
            'lr_document' => $lrFile,
            'lr_number'   => 'LR-DOC-99001',
        ]);

        $response->assertStatus(200);

        $this->fulfilment1->refresh();
        // Crucial BRD requirement: LR is dispatch evidence, NOT proof of delivery or buyer acceptance.
        $this->assertEquals(SellerFulfilment::STATUS_DISPATCHED, $this->fulfilment1->fulfilment_status);
        $this->assertNull($this->fulfilment1->buyer_acknowledged_at);
        $this->assertNotEquals(SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED, $this->fulfilment1->fulfilment_status);
    }

    /**
     * Scenario 5: Buyer reports shortage against their own seller fulfilment order.
     */
    public function test_buyer_reports_shortage_against_seller_fulfilment(): void
    {
        $this->actingAs($this->buyerUser1);

        $response = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment1->id}/claim", [
            'claim_type'  => 'shortage',
            'description' => 'Received only 7 rolls of Heavy Canvas Fabric instead of the 10 rolls ordered.',
            'items'       => [
                [
                    'order_detail_id' => $this->orderDetail1->id,
                    'product_name'    => 'Heavy Canvas Fabric',
                    'ordered_qty'     => 10,
                    'received_qty'    => 7,
                    'affected_qty'    => 3,
                    'condition_notes' => 'Package seal intact, but 3 rolls missing from consignment pallet.',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => true,
            ])
            ->assertJsonPath('data.claim_type', 'shortage')
            ->assertJsonPath('data.claim_status', 'submitted');

        $claimId = $response->json('data.id');

        $this->assertDatabaseHas('fulfilment_claims', [
            'id'                   => $claimId,
            'seller_fulfilment_id' => $this->fulfilment1->id,
            'claim_type'           => 'shortage',
            'claim_status'         => 'submitted',
        ]);

        $this->assertDatabaseHas('fulfilment_claim_items', [
            'fulfilment_claim_id' => $claimId,
            'ordered_qty'         => 10,
            'received_qty'        => 7,
            'affected_qty'        => 3,
        ]);

        $this->fulfilment1->refresh();
        $this->assertEquals(SellerFulfilment::DISPUTE_SHORTAGE, $this->fulfilment1->dispute_status);
        $this->assertNotNull($this->fulfilment1->dispute_reported_at);

        // Verify audit log
        $this->assertDatabaseHas('fulfilment_claim_history', [
            'fulfilment_claim_id' => $claimId,
            'action'              => 'claim_submitted',
            'actor_role'          => 'buyer',
            'new_status'          => 'submitted',
        ]);
    }

    /**
     * Scenario 6: Buyer reports damage and attaches valid photographic evidence.
     */
    public function test_buyer_reports_damage_and_attaches_photographic_evidence(): void
    {
        $this->actingAs($this->buyerUser1);

        $damagePhoto1 = UploadedFile::fake()->create('broken_tiles_palette.jpg', 800, 'image/jpeg');
        $damagePhoto2 = UploadedFile::fake()->create('damaged_carton.png', 640, 'image/png');

        $response = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment2->id}/claim", [
            'claim_type'  => 'damage',
            'description' => 'Multiple boxes were crushed and fractured during transit in the truck.',
            'items'       => [
                [
                    'order_detail_id' => $this->orderDetail2->id,
                    'product_name'    => 'Premium Vitrified Tiles',
                    'ordered_qty'     => 20,
                    'received_qty'    => 20,
                    'affected_qty'    => 6,
                    'condition_notes' => '6 boxes contain broken and cracked tiles.',
                ],
            ],
            'evidence'    => [$damagePhoto1, $damagePhoto2],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.claim_type', 'damage');

        $claimId = $response->json('data.id');

        $this->assertDatabaseHas('fulfilment_claims', [
            'id'                   => $claimId,
            'seller_fulfilment_id' => $this->fulfilment2->id,
            'claim_type'           => 'damage',
        ]);

        $this->assertDatabaseHas('fulfilment_claim_evidence', [
            'fulfilment_claim_id' => $claimId,
            'uploader_role'       => 'buyer',
            'file_name'           => 'broken_tiles_palette.jpg',
        ]);
        $this->assertDatabaseHas('fulfilment_claim_evidence', [
            'fulfilment_claim_id' => $claimId,
            'uploader_role'       => 'buyer',
            'file_name'           => 'damaged_carton.png',
        ]);

        $this->fulfilment2->refresh();
        $this->assertEquals(SellerFulfilment::DISPUTE_DAMAGE, $this->fulfilment2->dispute_status);
    }

    /**
     * Scenario 7: Unsupported file attachment types and oversized attachments are rejected (HTTP 422).
     */
    public function test_invalid_attachment_types_and_oversized_attachments_rejected(): void
    {
        $this->actingAs($this->buyerUser1);

        // 1. Executable file (.exe)
        $maliciousFile = UploadedFile::fake()->create('exploit.exe', 100, 'application/x-msdownload');

        $res1 = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment1->id}/claim", [
            'claim_type'  => 'damage',
            'description' => 'Testing malicious file rejection.',
            'evidence'    => [$maliciousFile],
        ]);
        $res1->assertStatus(422)
            ->assertJsonValidationErrors(['evidence.0']);

        // 2. Oversized file (> 5MB)
        $oversizedFile = UploadedFile::fake()->create('huge_scan.pdf', 6000, 'application/pdf');

        $res2 = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment1->id}/claim", [
            'claim_type'  => 'damage',
            'description' => 'Testing oversized document rejection.',
            'evidence'    => [$oversizedFile],
        ]);
        $res2->assertStatus(422)
            ->assertJsonValidationErrors(['evidence.0']);
    }

    /**
     * Scenario 8: Affected quantity cannot exceed ordered quantity (HTTP 422).
     */
    public function test_affected_quantity_cannot_exceed_ordered_quantity(): void
    {
        $this->actingAs($this->buyerUser1);

        $response = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment1->id}/claim", [
            'claim_type'  => 'shortage',
            'description' => 'Claiming more items than ordered.',
            'items'       => [
                [
                    'order_detail_id' => $this->orderDetail1->id,
                    'ordered_qty'     => 10,
                    'received_qty'    => 0,
                    'affected_qty'    => 15, // > 10 ordered!
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.affected_qty']);
    }

    /**
     * Scenario 9: Buyer cannot access or view another buyer's dispute claim (HTTP 403).
     */
    public function test_buyer_cannot_access_another_buyers_dispute_claim(): void
    {
        // Create claim by Buyer 1
        $claim = $this->disputeService->submitClaim(
            $this->fulfilment1,
            'shortage',
            'Missing 2 bales upon arrival.',
            $this->buyerUser1->id,
            'Apex Construction'
        );

        // Buyer 2 attempts to inspect Buyer 1's claim
        $this->actingAs($this->buyerUser2);

        $response = $this->getJson("/api/v2/buyer/claims/{$claim->id}");
        $response->assertStatus(403);
    }

    /**
     * Scenario 10: Seller cannot view or respond to claims of another seller (HTTP 403).
     */
    public function test_seller_cannot_view_or_respond_to_claims_of_another_seller(): void
    {
        // Claim belongs to Seller 1
        $claim = $this->disputeService->submitClaim(
            $this->fulfilment1,
            'shortage',
            'Seller 1 consignment shortage claim.',
            $this->buyerUser1->id,
            'Apex Construction'
        );

        // Seller 2 attempts to inspect Seller 1's claim
        $this->actingAs($this->seller2);

        $showResponse = $this->getJson("/api/v2/seller/claims/{$claim->id}");
        $showResponse->assertStatus(403);

        // Seller 2 attempts to respond to Seller 1's claim
        $respondResponse = $this->postJson("/api/v2/seller/claims/{$claim->id}/respond", [
            'response' => 'Unauthorized response attempt from Seller 2.',
        ]);
        $respondResponse->assertStatus(403);
    }

    /**
     * Scenario 11: Admin central dispute console and status update workflow.
     */
    public function test_admin_central_dispute_console_and_status_update(): void
    {
        $claim = $this->disputeService->submitClaim(
            $this->fulfilment1,
            'damage',
            'Goods damaged in transit.',
            $this->buyerUser1->id,
            'Apex Construction'
        );

        $this->actingAs($this->adminUser);

        // 1. List claims in central console
        $listResponse = $this->getJson('/api/v2/admin/claims');
        $listResponse->assertStatus(200)
            ->assertJsonStructure(['status', 'metrics', 'data']);

        // 2. Transition from submitted to under_review
        $reviewResponse = $this->postJson("/api/v2/admin/claims/{$claim->id}/status", [
            'status'      => 'under_review',
            'admin_notes' => 'Investigating with VRL Logistics Jaipur branch regarding transit handling.',
        ]);
        $reviewResponse->assertStatus(200)
            ->assertJsonPath('data.claim_status', 'under_review');

        // 3. Transition from under_review to resolved_settled
        $resolveResponse = $this->postJson("/api/v2/admin/claims/{$claim->id}/status", [
            'status'             => 'resolved_settled',
            'admin_notes'        => 'Transporter admitted damage during unloading. Carrier claim filed.',
            'resolution_summary' => 'Claim approved for 3 damaged units. Settlement credit pending formal approval.',
        ]);
        $resolveResponse->assertStatus(200)
            ->assertJsonPath('data.claim_status', 'resolved_settled')
            ->assertJsonPath('data.resolution_summary', 'Claim approved for 3 damaged units. Settlement credit pending formal approval.');

        $claim->refresh();
        $this->assertEquals(FulfilmentClaim::STATUS_RESOLVED, $claim->claim_status);
        $this->assertEquals($this->adminUser->id, $claim->resolved_by_user_id);
        $this->assertNotNull($claim->resolved_at);
    }

    /**
     * Scenario 12: Duplicate claim submissions with idempotency key return original claim without duplicates.
     */
    public function test_duplicate_claim_submissions_with_idempotency_key_prevent_duplicates(): void
    {
        $this->actingAs($this->buyerUser1);
        $idempotencyKey = 'IDEM-CLAIM-TEST-XYZ-9988';

        $payload = [
            'claim_type'      => 'shortage',
            'description'     => 'Network retry idempotency validation test.',
            'idempotency_key' => $idempotencyKey,
            'items'           => [
                [
                    'order_detail_id' => $this->orderDetail1->id,
                    'ordered_qty'     => 10,
                    'received_qty'    => 8,
                    'affected_qty'    => 2,
                ],
            ],
        ];

        // First submission
        $res1 = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment1->id}/claim", $payload);
        $res1->assertStatus(201);
        $firstClaimId = $res1->json('data.id');

        // Duplicate submission on network retry
        $res2 = $this->postJson("/api/v2/buyer/fulfilment/{$this->fulfilment1->id}/claim", $payload);
        $res2->assertStatus(201);
        $secondClaimId = $res2->json('data.id');

        // Must be the exact same claim record
        $this->assertEquals($firstClaimId, $secondClaimId);
        $this->assertEquals(1, FulfilmentClaim::where('idempotency_key', $idempotencyKey)->count());
    }

    /**
     * Scenario 13: Claim status transitions produce complete audit trail in fulfilment_claim_history.
     */
    public function test_claim_status_transitions_produce_complete_audit_trail(): void
    {
        // 1. Buyer submits claim
        $claim = $this->disputeService->submitClaim(
            $this->fulfilment1,
            'wrong_item',
            'Received wrong specification canvas roll.',
            $this->buyerUser1->id,
            'Apex Construction'
        );

        // 2. Seller responds
        $this->disputeService->submitSellerResponse(
            $claim,
            'We dispatched SKU-Canvas-A1 as per batch tag #5544.',
            $this->seller1->id,
            'Jaipur Fabrics Seller Team'
        );

        // 3. Admin updates status
        $this->disputeService->updateClaimStatusByAdmin(
            $claim,
            'under_review',
            $this->adminUser->id,
            'Ops Team Leader',
            'Requesting batch dispatch logs from seller.'
        );

        // 4. Admin resolves claim
        $this->disputeService->updateClaimStatusByAdmin(
            $claim,
            'resolved_settled',
            $this->adminUser->id,
            'Ops Team Leader',
            'Internal note: Discrepancy confirmed in seller catalogue SKU.',
            'Replacement agreed by manufacturer for next dispatch.'
        );

        // Verify history records
        $history = FulfilmentClaimHistory::where('fulfilment_claim_id', $claim->id)
            ->orderBy('id', 'asc')
            ->get();

        $this->assertCount(4, $history);

        $this->assertEquals('claim_submitted', $history[0]->action);
        $this->assertEquals('buyer', $history[0]->actor_role);
        $this->assertEquals(FulfilmentClaim::STATUS_SUBMITTED, $history[0]->new_status);

        $this->assertEquals('seller_response_submitted', $history[1]->action);
        $this->assertEquals('seller', $history[1]->actor_role);
        $this->assertEquals(FulfilmentClaim::STATUS_SELLER_RESPONDED, $history[1]->new_status);

        $this->assertEquals('claim_status_updated', $history[2]->action);
        $this->assertEquals('admin', $history[2]->actor_role);
        $this->assertEquals(FulfilmentClaim::STATUS_UNDER_REVIEW, $history[2]->new_status);

        $this->assertEquals('claim_status_updated', $history[3]->action);
        $this->assertEquals('admin', $history[3]->actor_role);
        $this->assertEquals(FulfilmentClaim::STATUS_RESOLVED, $history[3]->new_status);
    }

    /**
     * Scenario 14: Internal admin operations notes are NOT exposed to buyer or seller.
     */
    public function test_internal_admin_notes_are_not_exposed_to_buyer_or_seller(): void
    {
        $claim = $this->disputeService->submitClaim(
            $this->fulfilment1,
            'damage',
            'Transit damage observed.',
            $this->buyerUser1->id,
            'Apex Construction'
        );

        // Admin updates status with sensitive internal notes and public resolution summary
        $this->disputeService->updateClaimStatusByAdmin(
            $claim,
            'under_review',
            $this->adminUser->id,
            'Ops Admin',
            'CONFIDENTIAL: Carrier GPS indicates off-route detour. Possible pilferage.',
            'Operations is reviewing consignment transit telemetry.'
        );

        // 1. Buyer accesses claim -> admin_notes MUST NOT be present
        $this->actingAs($this->buyerUser1);
        $buyerRes = $this->getJson("/api/v2/buyer/claims/{$claim->id}");
        $buyerRes->assertStatus(200);
        $this->assertArrayNotHasKey('admin_notes', $buyerRes->json('data'));
        $this->assertEquals('Operations is reviewing consignment transit telemetry.', $buyerRes->json('data.resolution_summary'));

        // 2. Seller accesses claim -> admin_notes MUST NOT be present
        $this->actingAs($this->seller1);
        $sellerRes = $this->getJson("/api/v2/seller/claims/{$claim->id}");
        $sellerRes->assertStatus(200);
        $this->assertArrayNotHasKey('admin_notes', $sellerRes->json('data'));

        // 3. Admin accesses claim -> admin_notes IS present
        $this->actingAs($this->adminUser);
        $adminRes = $this->getJson("/api/v2/admin/claims/{$claim->id}");
        $adminRes->assertStatus(200);
        $this->assertEquals('CONFIDENTIAL: Carrier GPS indicates off-route detour. Possible pilferage.', $adminRes->json('data.admin_notes'));
    }

    /**
     * Scenario 15: Multi-seller parent order maintains independent seller fulfilments and claim statuses.
     */
    public function test_multi_seller_parent_order_maintains_independent_seller_fulfilments_and_claims(): void
    {
        // Lodge claim against Seller 1 fulfilment only
        $this->disputeService->submitClaim(
            $this->fulfilment1,
            'shortage',
            'Missing rolls from Seller 1.',
            $this->buyerUser1->id,
            'Apex Construction'
        );

        $this->fulfilment1->refresh();
        $this->fulfilment2->refresh();
        $this->fulfilment3->refresh();

        // Seller 1 fulfilment is disputed
        $this->assertEquals(SellerFulfilment::DISPUTE_SHORTAGE, $this->fulfilment1->dispute_status);

        // Seller 2 and Seller 3 fulfilments remain completely unaffected
        $this->assertEquals(SellerFulfilment::DISPUTE_NONE, $this->fulfilment2->dispute_status);
        $this->assertEquals(SellerFulfilment::DISPUTE_NONE, $this->fulfilment3->dispute_status);

        // Aggregate parent summary accurately distinguishes individual seller states
        $summary = $this->fulfilmentService->getParentOrderLogisticsSummary($this->parentOrder);
        $this->assertEquals(3, $summary['total_seller_orders']);
        $this->assertEquals(2, $summary['dispatched_count']);
        $this->assertEquals(SellerFulfilment::DISPUTE_SHORTAGE, $summary['sellers'][0]['buyer_acknowledgement']['dispute_status']);
        $this->assertEquals(SellerFulfilment::DISPUTE_NONE, $summary['sellers'][1]['buyer_acknowledgement']['dispute_status']);
    }

    /**
     * Scenario 16: Missing or unconfigured policy rules do not invent arbitrary deadlines or auto-reject.
     */
    public function test_missing_or_disabled_policy_window_does_not_invent_deadline_or_auto_reject(): void
    {
        // Explicitly set policy window disabled (default state)
        Config::set('marketplace_dispute.policy_window_enabled', false);
        Config::set('marketplace_dispute.policy_window_days', null);

        // Simulate an order dispatched 60 days ago
        $this->fulfilment1->update([
            'dispatched_at' => now()->subDays(60),
        ]);

        // Buyer lodges claim - should succeed because no arbitrary deadline was invented
        $claim = $this->disputeService->submitClaim(
            $this->fulfilment1,
            'damage',
            'Long-tail quality issue observed after unpacking stored consignment.',
            $this->buyerUser1->id,
            'Apex Construction'
        );

        $this->assertInstanceOf(FulfilmentClaim::class, $claim);
        $this->assertEquals(FulfilmentClaim::STATUS_SUBMITTED, $claim->claim_status);
    }

    /**
     * Scenario 17: Historical order, payment transaction, and dispatch records remain completely intact.
     */
    public function test_historical_order_payment_and_dispatch_records_remain_unmodified(): void
    {
        $originalGrandTotal = (float) $this->parentOrder->grandtotal;
        $originalNet = (float) $this->parentOrder->netamount;
        $originalPaymentStatus = $this->parentOrder->payment_status;
        $originalTxnCount = Transection::where('order_id', $this->parentOrder->id)->count();

        // Perform full claim lifecycle
        $claim = $this->disputeService->submitClaim(
            $this->fulfilment1,
            'damage',
            'Damage report verification.',
            $this->buyerUser1->id,
            'Apex Construction'
        );

        $this->disputeService->submitSellerResponse(
            $claim,
            'Investigating with factory line.',
            $this->seller1->id,
            'Jaipur Seller'
        );

        $this->disputeService->updateClaimStatusByAdmin(
            $claim,
            'resolved_settled',
            $this->adminUser->id,
            'Admin',
            'Approved for operational review.',
            'Resolution pending policy approval.'
        );

        // Verify parent and child orders are completely untouched
        $this->parentOrder->refresh();
        $this->assertEquals($originalGrandTotal, (float) $this->parentOrder->grandtotal);
        $this->assertEquals($originalNet, (float) $this->parentOrder->netamount);
        $this->assertEquals($originalPaymentStatus, $this->parentOrder->payment_status);
        $this->assertEquals($originalTxnCount, Transection::where('order_id', $this->parentOrder->id)->count());

        // Verify dispatch details on fulfilment1 remain completely unchanged
        $this->fulfilment1->refresh();
        $this->assertEquals('VRL Logistics Ltd', $this->fulfilment1->transporter_name);
        $this->assertEquals('LR-JAIPUR-101', $this->fulfilment1->lr_number);
        $this->assertEquals(10, $this->fulfilment1->package_count);
    }
}
