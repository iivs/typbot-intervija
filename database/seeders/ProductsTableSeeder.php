<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate existing records from products table to start from scratch.
        Schema::disableForeignKeyConstraints();
        Product::truncate();
        Schema::enableForeignKeyConstraints();

        $faker = \Faker\Factory::create();

        // Create a few products.
        foreach (range(1, 10) as $i) {
            Product::create([
                // Create a random product name ranging from 5 to 50 symbols.
                'name' => $faker->text(rand(5, 50)),
                // Description is optional. So it is 50% chance for one to have a description
                'description' => $faker->boolean(50) ? $faker->sentence : null
            ]);
        }
    }
}
