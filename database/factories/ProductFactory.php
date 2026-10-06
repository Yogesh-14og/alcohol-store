<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'price' => fake()->randomFloat(2, 10, 500),
            'cost_price' => fake()->randomFloat(2, 5, 250),
            'stock_quantity' => fake()->numberBetween(5, 30),
            'volume' => fake()->randomElement(['500ml', '750ml', '1L', '1.5L']),
            'abv' => fake()->randomFloat(1, 0, 50),
            'image' => null,
            'is_best_seller' => fake()->boolean(20),
            'is_new_arrival' => fake()->boolean(20),
            'is_hot_deal' => fake()->boolean(20),
        ];
    }
}
