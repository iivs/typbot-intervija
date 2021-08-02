<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Support\Facades\Schema;

class ProductAttributesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate existing records from product_attributes table to start from scratch.
        Schema::disableForeignKeyConstraints();
        ProductAttribute::truncate();
        Schema::enableForeignKeyConstraints();

        $faker = \Faker\Factory::create();

        // Get all products and then generate attributes for them.
        $products = Product::all();

        foreach ($products as $product) {
            // A product has a 50% chance to have attributes. rand(0, 3) is not used. It would increase the chance.
            if ($faker->boolean(50)) {
                // Generate from 1 to 3 attributes.
                $attr_count = rand(1, 3);

                if ($attr_count > 0) {
                    $attributes = [];

                    for ($i = 0; $i <= $attr_count; $i++) {
                        $attributes[] = ProductAttribute::factory()->definition() + 
                           ['created_at' => $product->created_at 
                        ];
                    }

                    // Save product attributes.
                    $product->attributes()->createMany($attributes);
                }
            }
        }
    }
}
