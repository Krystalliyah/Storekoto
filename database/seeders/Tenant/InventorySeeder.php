<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Product;
use App\Models\InventoryMovement;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $products = Product::all();

        foreach ($products as $product) {

            $quantity = $faker->numberBetween(20, 200);

            InventoryMovement::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'type' => 'stock_in',
                'reference' => 'initial_seed',
                'notes' => 'Initial inventory',
            ]);

            $product->update([
                'stock' => $quantity
            ]);
        }
    }
}