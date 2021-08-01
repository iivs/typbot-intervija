<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // Create a random product name ranging from 5 to 50 symbols.
            'name' => $this->faker->text(rand(5, 50)),
            // Description is optional. So it is 50% chance for one to have a description
            'description' => $this->faker->boolean(50) ? $this->faker->sentence : null
        ];
    }
}
