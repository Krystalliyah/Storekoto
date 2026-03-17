<?php

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::where('is_approved', true)->get();

        foreach ($tenants as $tenant) {
            $tenant->run(function () {
                (new \Database\Seeders\Tenant\TenantMockDataSeeder())->run();
            });
        }
    }
}