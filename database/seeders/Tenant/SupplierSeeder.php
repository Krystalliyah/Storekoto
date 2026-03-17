<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $numberOfSuppliers = 5; 

        for ($i = 0; $i < $numberOfSuppliers; $i++) {

            Supplier::create([
                'name' => $faker->company(),
                'contact_email' => $faker->companyEmail(),
                'phone' => $faker->phoneNumber(),
                'metadata' => json_encode([
                    'address' => $faker->address(),
                    'rating' => $faker->randomFloat(1, 3, 5),
                ]),
            ]);

        }
    }
}