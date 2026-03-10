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

        // 1. Remove the 'view-revenue' permission from everywhere
        $permission = Permission::where('name', 'view-revenue')->first();
        if ($permission) {
            $permission->delete();
        }

        // 2. Ensure sensitive permissions are NOT assigned to staff role by default
        $staff = Role::where('name', 'staff')->first();
        if ($staff) {
            $staff->revokePermissionTo(['manage-staff', 'manage-store-settings']);
        }
        
        // 3. Ensure 'vendor' (owner) role has all remaining permissions
        $vendor = Role::where('name', 'vendor')->first();
        if ($vendor) {
            $allPermissions = Permission::all();
            $vendor->syncPermissions($allPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
