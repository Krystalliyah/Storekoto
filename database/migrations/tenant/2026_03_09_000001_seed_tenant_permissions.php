<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view-revenue',
            'view-expenses',
            'view-analytics',
            'edit-prices',
            'manage-staff',
            'manage-store-settings',
            'manage-inventory',
            'manage-products',
            'manage-orders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $vendorAdmin = Role::firstOrCreate(['name' => 'vendor-admin']);
        $vendorAdmin->syncPermissions($permissions);

        $staff = Role::firstOrCreate(['name' => 'staff']);
        // Default staff might only have limited permissions
        $staff->syncPermissions([
            'manage-inventory',
            'manage-products',
            'manage-orders',
        ]);

        // Legacy: The project currently uses 'vendor' role for the owner.
        // Let's ensure 'vendor' role also has all permissions.
        $vendor = Role::firstOrCreate(['name' => 'vendor']);
        $vendor->syncPermissions($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not really needed for seeding migration but good practice
    }
};
