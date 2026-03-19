<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user (roles will be assigned separately)
        $admin = User::firstOrCreate(
            ['email' => 'admin@itinda.test'],
            [
                'name' => 'Admin User',
                'phone' => '09123456789',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => Carbon::now(),
            ]
        );

        if ($admin->email_verified_at === null) {
            $admin->forceFill([
                'email_verified_at' => Carbon::now(),
            ])->save();
        }

        // Try to assign admin role if roles table exists
        try {
            $admin->assignRole('admin');
            $this->command->info('✓ Admin user created with role');
        } catch (\Exception $e) {
            $this->command->warn('⚠ Admin user created but role not assigned. Run: php artisan db:seed --class=RolesSeeder first');
        }

        $this->command->info('Login: admin@itinda.test / Password123!');
    }
}
