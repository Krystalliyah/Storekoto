<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $categories = Category::all();

        $numberOfProductsPerCategory = 10;

        foreach ($categories as $category) {

            for ($i = 0; $i < $numberOfProductsPerCategory; $i++) {

                Product::create([
                    'sku' => strtoupper(Str::random(8)),
                    'name' => ucfirst($faker->words(3, true)),
                    'description' => $faker->sentence(),
                    'category_id' => $category->id,
                    'price' => $faker->randomFloat(2, 10, 500),
                    'stock' => $faker->numberBetween(10, 100),
                    'image_path' => null,
                    'is_active' => true,
                ]);

            }
        }
    }
}