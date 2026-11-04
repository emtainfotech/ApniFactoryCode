<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SellerPermission;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SellerStaffPermission;
use App\Models\User;
use App\Services\SellerPermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2_1_SellerPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $sellerA;
    protected Company $companyA;
    protected User $sellerB;
    protected Company $companyB;
    protected User $staffUser;
    protected SellerRole $roleProductManager;
    protected SellerRole $roleReadonly;
    protected SellerRole $roleOwner;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\SellerRolesAndPermissionsSeeder::class);

        $this->roleOwner          = SellerRole::where('name', 'seller_owner')->first();
        $this->roleProductManager = SellerRole::where('name', 'product_manager')->first();
        $this->roleReadonly       = SellerRole::where('name', 'readonly_staff')->first();

        // Seller A (owner)
        $this->sellerA  = User::factory()->create(['email' => 'sellerA@test.com']);
        $this->companyA = Company::factory()->create(['user_id' => $this->sellerA->id]);

        // Seller B (owner, different company)
        $this->sellerB  = User::factory()->create(['email' => 'sellerB@test.com']);
        $this->companyB = Company::factory()->create(['user_id' => $this->sellerB->id]);

        // A staff user (not an owner)
        $this->staffUser = User::factory()->create(['email' => 'staff@test.com']);
    }

    // ===========================
    // 1. ROLE & PERMISSION SEEDER
    // ===========================

    /** @test */
    public function it_seeds_all_required_roles()
    {
        $expected = [
            'seller_owner', 'seller_admin', 'product_manager',
            'pricing_manager', 'inventory_manager', 'order_manager',
            'dispatch_manager', 'readonly_staff',
        ];
        foreach ($expected as $roleName) {
            $this->assertDatabaseHas('seller_roles', ['name' => $roleName]);
        }
    }

    /** @test */
    public function it_seeds_all_required_permissions()
    {
        $expected = [
            'products.view', 'products.create_listing', 'products.edit',
            'products.manage_sku', 'products.toggle_listing',
            'pricing.view', 'pricing.update', 'pricing.view_history',
            'inventory.view', 'inventory.update', 'inventory.adjust', 'inventory.view_history',
            'orders.view', 'orders.accept_reject', 'orders.update_status', 'orders.view_details',
            'dispatch.manage_packing', 'dispatch.update_details', 'dispatch.upload_lr',
            'dispatch.update_transporter', 'dispatch.view_history',
            'staff.view', 'staff.invite', 'staff.assign_role', 'staff.manage_permissions', 'staff.deactivate',
            'excel.export', 'excel.templates', 'excel.upload', 'excel.validate', 'excel.commit', 'excel.view_history',
        ];
        foreach ($expected as $perm) {
            $this->assertDatabaseHas('seller_permissions', ['name' => $perm]);
        }
    }

    // ===========================
    // 2. SELLER OWNER PERMISSIONS
    // ===========================

    /** @test */
    public function seller_owner_has_all_permissions()
    {
        $allPerms = SellerPermission::pluck('name');
        foreach ($allPerms as $perm) {
            $this->assertTrue(
                $this->sellerA->hasSellerPermission($perm),
                "Owner should have permission: {$perm}"
            );
        }
    }

    // ===========================
    // 3. STAFF PERMISSIONS
    // ===========================

    /** @test */
    public function staff_with_product_manager_role_has_correct_permissions()
    {
        SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleProductManager->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        $this->assertTrue($this->staffUser->hasSellerPermission('products.view'));
        $this->assertTrue($this->staffUser->hasSellerPermission('products.create_listing'));
        $this->assertFalse($this->staffUser->hasSellerPermission('orders.accept_reject'));
        $this->assertFalse($this->staffUser->hasSellerPermission('pricing.update'));
    }

    /** @test */
    public function readonly_staff_cannot_modify_anything()
    {
        SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleReadonly->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        $this->assertTrue($this->staffUser->hasSellerPermission('products.view'));
        $this->assertFalse($this->staffUser->hasSellerPermission('products.create_listing'));
        $this->assertFalse($this->staffUser->hasSellerPermission('pricing.update'));
        $this->assertFalse($this->staffUser->hasSellerPermission('inventory.adjust'));
        $this->assertFalse($this->staffUser->hasSellerPermission('orders.accept_reject'));
    }

    /** @test */
    public function deactivated_staff_has_no_permissions()
    {
        SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleProductManager->id,
            'is_active'      => false,
            'deactivated_at' => now(),
        ]);

        $allPerms = SellerPermission::pluck('name');
        foreach ($allPerms as $perm) {
            $this->assertFalse(
                $this->staffUser->hasSellerPermission($perm),
                "Deactivated staff should not have permission: {$perm}"
            );
        }
    }

    /** @test */
    public function permission_override_grant_overrides_role_deny()
    {
        $staff = SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleReadonly->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        // Readonly cannot create listings by default
        $this->assertFalse($this->staffUser->hasSellerPermission('products.create_listing'));

        // Grant individual override
        $perm = SellerPermission::where('name', 'products.create_listing')->first();
        SellerStaffPermission::create([
            'seller_staff_id'      => $staff->id,
            'seller_permission_id' => $perm->id,
            'granted'              => true,
        ]);

        $freshStaff = $this->staffUser->fresh();
        $this->assertTrue($freshStaff->hasSellerPermission('products.create_listing'));
    }

    /** @test */
    public function permission_override_deny_overrides_role_grant()
    {
        $staff = SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleProductManager->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        // Product manager can view products
        $this->assertTrue($this->staffUser->hasSellerPermission('products.view'));

        // Deny individual override
        $perm = SellerPermission::where('name', 'products.view')->first();
        SellerStaffPermission::create([
            'seller_staff_id'      => $staff->id,
            'seller_permission_id' => $perm->id,
            'granted'              => false,
        ]);

        $freshStaff = $this->staffUser->fresh();
        $this->assertFalse($freshStaff->hasSellerPermission('products.view'));
    }

    // ===========================
    // 4. IDOR / CROSS-SELLER ISOLATION
    // ===========================

    /** @test */
    public function seller_b_cannot_access_seller_a_staff_via_api()
    {
        $staffA = SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleReadonly->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        $tokenB = $this->sellerB->createToken('test')->plainTextToken;
        $response = $this->withToken($tokenB)
            ->getJson("/api/v2/seller/staff/{$staffA->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function seller_b_cannot_list_seller_a_staff()
    {
        SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleReadonly->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        $tokenB = $this->sellerB->createToken('test')->plainTextToken;
        $response = $this->withToken($tokenB)->getJson('/api/v2/seller/staff');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    /** @test */
    public function seller_b_cannot_deactivate_seller_a_staff()
    {
        $staffA = SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleReadonly->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        $tokenB = $this->sellerB->createToken('test')->plainTextToken;
        $response = $this->withToken($tokenB)
            ->deleteJson("/api/v2/seller/staff/{$staffA->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('seller_staff', ['id' => $staffA->id, 'is_active' => true]);
    }

    // ===========================
    // 5. PRIVILEGE ESCALATION
    // ===========================

    /** @test */
    public function staff_cannot_invite_staff_without_permission()
    {
        SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleReadonly->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        $token = $this->staffUser->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson('/api/v2/seller/staff/invite', [
            'name'  => 'New Staff',
            'email' => 'newstaff@test.com',
            'role'  => 'readonly_staff',
        ]);

        $response->assertStatus(422);
    }

    // ===========================
    // 6. SERVICE LAYER TESTS
    // ===========================

    /** @test */
    public function service_resolves_company_for_owner()
    {
        $service = app(SellerPermissionService::class);
        $company = $service->resolveCompany($this->sellerA);
        $this->assertNotNull($company);
        $this->assertEquals($this->companyA->id, $company->id);
    }

    /** @test */
    public function service_resolves_company_for_staff()
    {
        SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleReadonly->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        $service = app(SellerPermissionService::class);
        $company = $service->resolveCompany($this->staffUser);
        $this->assertNotNull($company);
        $this->assertEquals($this->companyA->id, $company->id);
    }

    /** @test */
    public function service_invites_staff_and_logs_audit()
    {
        $service = app(SellerPermissionService::class);
        $staff = $service->inviteStaff($this->sellerA, $this->companyA->id, [
            'name'     => 'Test Staff',
            'email'    => 'newtest@example.com',
            'role'     => 'product_manager',
            'password' => 'password123',
        ]);

        $this->assertInstanceOf(SellerStaff::class, $staff);
        $this->assertDatabaseHas('seller_staff', [
            'company_id' => $this->companyA->id,
            'is_active'  => true,
        ]);
        $this->assertDatabaseHas('seller_permission_audit_logs', [
            'company_id' => $this->companyA->id,
            'action'     => 'staff.invited',
        ]);
    }

    /** @test */
    public function service_deactivate_staff_logs_audit()
    {
        $staff = SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffUser->id,
            'seller_role_id' => $this->roleReadonly->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        $service = app(SellerPermissionService::class);
        $service->deactivateStaff($this->sellerA, $staff->id);

        $this->assertDatabaseHas('seller_staff', ['id' => $staff->id, 'is_active' => false]);
        $this->assertDatabaseHas('seller_permission_audit_logs', [
            'action' => 'staff.deactivated',
        ]);
    }

    // ===========================
    // 7. API ENDPOINT TESTS
    // ===========================

    /** @test */
    public function api_permission_matrix_returns_correct_structure()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/v2/seller/permissions/matrix');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => ['my_permissions', 'roles'],
            ]);
    }

    /** @test */
    public function api_roles_endpoint_lists_all_roles()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/v2/seller/roles');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(8, count($data));
    }

    /** @test */
    public function unauthenticated_user_cannot_access_staff_api()
    {
        $response = $this->getJson('/api/v2/seller/staff');
        $response->assertStatus(401);
    }

    // ===========================
    // 8. WEB ROUTE TESTS
    // ===========================

    /** @test */
    public function seller_owner_can_access_staff_management_page()
    {
        $response = $this->actingAs($this->sellerA)->get('/seller/staff');
        $response->assertOk();
    }

    /** @test */
    public function unauthenticated_user_redirected_from_staff_page()
    {
        $response = $this->get('/seller/staff');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function existing_seller_login_route_still_works()
    {
        $response = $this->get('/seller/login');
        $this->assertNotEquals(500, $response->getStatusCode());
    }
}

