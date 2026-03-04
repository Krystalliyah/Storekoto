<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TenantRolesSeeder extends Seeder
{
    /**
     * Run the tenant roles seeder.
     */
    public function run(): void
    {
        // Make sure the "vendor" role exists in this tenant database
        if (! Role::where('name', 'vendor')->exists()) {
            Role::create(['name' => 'vendor']);
        }
    }
}
