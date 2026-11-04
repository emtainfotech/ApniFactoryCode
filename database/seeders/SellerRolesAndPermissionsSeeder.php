<?php

namespace Database\Seeders;

use App\Models\SellerPermission;
use App\Models\SellerRole;
use Illuminate\Database\Seeder;

class SellerRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // --- Permissions ---
        $permissions = [
            // Product Management
            ['module' => 'products', 'action' => 'view',            'name' => 'products.view',            'label' => 'View Products'],
            ['module' => 'products', 'action' => 'create_listing',   'name' => 'products.create_listing',  'label' => 'Create Seller Listings'],
            ['module' => 'products', 'action' => 'edit',             'name' => 'products.edit',            'label' => 'Edit Product Information'],
            ['module' => 'products', 'action' => 'manage_sku',       'name' => 'products.manage_sku',      'label' => 'Manage Seller SKUs'],
            ['module' => 'products', 'action' => 'toggle_listing',   'name' => 'products.toggle_listing',  'label' => 'Activate/Deactivate Listings'],
            // Pricing
            ['module' => 'pricing',  'action' => 'view',             'name' => 'pricing.view',             'label' => 'View Seller Prices'],
            ['module' => 'pricing',  'action' => 'update',           'name' => 'pricing.update',           'label' => 'Update Seller Prices'],
            ['module' => 'pricing',  'action' => 'view_history',     'name' => 'pricing.view_history',     'label' => 'View Price History'],
            // Inventory
            ['module' => 'inventory','action' => 'view',             'name' => 'inventory.view',           'label' => 'View Stock'],
            ['module' => 'inventory','action' => 'update',           'name' => 'inventory.update',         'label' => 'Update Stock'],
            ['module' => 'inventory','action' => 'adjust',           'name' => 'inventory.adjust',         'label' => 'Adjust Stock'],
            ['module' => 'inventory','action' => 'view_history',     'name' => 'inventory.view_history',   'label' => 'View Inventory History'],
            // Excel Operations
            ['module' => 'excel',    'action' => 'export',           'name' => 'excel.export',             'label' => 'Export Excel'],
            ['module' => 'excel',    'action' => 'templates',        'name' => 'excel.templates',          'label' => 'Download Excel Templates'],
            ['module' => 'excel',    'action' => 'upload',           'name' => 'excel.upload',             'label' => 'Upload Excel Files'],
            ['module' => 'excel',    'action' => 'validate',         'name' => 'excel.validate',           'label' => 'Validate Excel Uploads'],
            ['module' => 'excel',    'action' => 'commit',           'name' => 'excel.commit',             'label' => 'Commit Excel Imports'],
            ['module' => 'excel',    'action' => 'view_history',     'name' => 'excel.view_history',       'label' => 'View Import History'],
            // Orders
            ['module' => 'orders',   'action' => 'view',             'name' => 'orders.view',              'label' => 'View Orders'],
            ['module' => 'orders',   'action' => 'accept_reject',    'name' => 'orders.accept_reject',     'label' => 'Accept / Reject Orders'],
            ['module' => 'orders',   'action' => 'update_status',    'name' => 'orders.update_status',     'label' => 'Update Order Status'],
            ['module' => 'orders',   'action' => 'view_details',     'name' => 'orders.view_details',      'label' => 'View Order Details'],
            // Dispatch
            ['module' => 'dispatch', 'action' => 'manage_packing',   'name' => 'dispatch.manage_packing',  'label' => 'Manage Packing'],
            ['module' => 'dispatch', 'action' => 'update_details',   'name' => 'dispatch.update_details',  'label' => 'Update Dispatch Details'],
            ['module' => 'dispatch', 'action' => 'upload_lr',        'name' => 'dispatch.upload_lr',       'label' => 'Upload LR / Bilty'],
            ['module' => 'dispatch', 'action' => 'update_transporter','name' => 'dispatch.update_transporter','label' => 'Update Transporter'],
            ['module' => 'dispatch', 'action' => 'view_history',     'name' => 'dispatch.view_history',    'label' => 'View Dispatch History'],
            // Staff Management
            ['module' => 'staff',    'action' => 'view',             'name' => 'staff.view',               'label' => 'View Staff Members'],
            ['module' => 'staff',    'action' => 'invite',           'name' => 'staff.invite',             'label' => 'Invite Staff Members'],
            ['module' => 'staff',    'action' => 'assign_role',      'name' => 'staff.assign_role',        'label' => 'Assign Staff Roles'],
            ['module' => 'staff',    'action' => 'manage_permissions','name' => 'staff.manage_permissions','label' => 'Manage Staff Permissions'],
            ['module' => 'staff',    'action' => 'deactivate',       'name' => 'staff.deactivate',         'label' => 'Deactivate Staff Members'],
        ];

        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['name']] = SellerPermission::firstOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        // --- Roles ---
        $allPermissions = array_keys($permissionModels);

        $roles = [
            [
                'name' => 'seller_owner',
                'label' => 'Seller Owner',
                'description' => 'Full access to all seller features.',
                'is_system' => true,
                'permissions' => $allPermissions,
            ],
            [
                'name' => 'seller_admin',
                'label' => 'Seller Administrator',
                'description' => 'Full access except cannot manage staff roles above their level.',
                'is_system' => true,
                'permissions' => $allPermissions,
            ],
            [
                'name' => 'product_manager',
                'label' => 'Product Manager',
                'description' => 'Manages products, listings and SKUs.',
                'is_system' => true,
                'permissions' => [
                    'products.view', 'products.create_listing', 'products.edit',
                    'products.manage_sku', 'products.toggle_listing',
                    'pricing.view', 'inventory.view',
                    'excel.export', 'excel.templates', 'excel.upload', 'excel.validate',
                    'excel.commit', 'excel.view_history',
                ],
            ],
            [
                'name' => 'pricing_manager',
                'label' => 'Pricing Manager',
                'description' => 'Manages seller pricing and price history.',
                'is_system' => true,
                'permissions' => [
                    'products.view', 'pricing.view', 'pricing.update', 'pricing.view_history',
                    'excel.export', 'excel.templates',
                ],
            ],
            [
                'name' => 'inventory_manager',
                'label' => 'Inventory Manager',
                'description' => 'Manages stock and inventory adjustments.',
                'is_system' => true,
                'permissions' => [
                    'products.view', 'inventory.view', 'inventory.update',
                    'inventory.adjust', 'inventory.view_history',
                    'excel.export', 'excel.templates', 'excel.upload',
                    'excel.validate', 'excel.commit', 'excel.view_history',
                ],
            ],
            [
                'name' => 'order_manager',
                'label' => 'Order Manager',
                'description' => 'Manages orders and their statuses.',
                'is_system' => true,
                'permissions' => [
                    'orders.view', 'orders.accept_reject', 'orders.update_status',
                    'orders.view_details', 'inventory.view',
                ],
            ],
            [
                'name' => 'dispatch_manager',
                'label' => 'Dispatch Manager',
                'description' => 'Manages dispatch operations.',
                'is_system' => true,
                'permissions' => [
                    'orders.view', 'orders.view_details',
                    'dispatch.manage_packing', 'dispatch.update_details',
                    'dispatch.upload_lr', 'dispatch.update_transporter',
                    'dispatch.view_history',
                ],
            ],
            [
                'name' => 'readonly_staff',
                'label' => 'Read-only Staff',
                'description' => 'Can only view data, no modifications.',
                'is_system' => true,
                'permissions' => [
                    'products.view', 'pricing.view', 'inventory.view',
                    'orders.view', 'orders.view_details', 'dispatch.view_history',
                    'staff.view', 'excel.export',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permNames = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = SellerRole::firstOrCreate(['name' => $roleData['name']], $roleData);

            $permIds = collect($permNames)->map(fn($n) => $permissionModels[$n]->id)->toArray();
            $role->permissions()->syncWithoutDetaching($permIds);
        }

        if ($this->command) {
            $this->command->info('Seller roles and permissions seeded successfully.');
        }
    }
}

