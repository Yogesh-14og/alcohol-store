<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    public function test_all_products_page_displays_the_product_catalog(): void
    {
        $product = Product::factory()->create([
            'name' => 'Catalog Reserve',
            'stock_quantity' => 5,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('All Products')
            ->assertSee($product->name)
            ->assertSee($product->abv.' ABV')
            ->assertSee('5 in stock');
    }

    public function test_new_arrivals_page_only_shows_new_arrival_products(): void
    {
        $newArrival = Product::factory()->create([
            'name' => 'Just Arrived',
            'is_new_arrival' => true,
            'stock_quantity' => 4,
        ]);
        Product::factory()->create([
            'name' => 'Older Product',
            'is_new_arrival' => false,
        ]);

        $this->get(route('products.new-arrivals'))
            ->assertOk()
            ->assertSee('New Arrivals')
            ->assertSee($newArrival->name)
            ->assertDontSee('Older Product');
    }

    public function test_all_products_can_be_filtered_by_alcohol_percentage_range(): void
    {
        Product::factory()->create([
            'name' => 'Low ABV Drink',
            'abv' => '12.5%',
        ]);
        Product::factory()->create([
            'name' => 'In Range Drink',
            'abv' => '40%',
        ]);
        Product::factory()->create([
            'name' => 'High ABV Drink',
            'abv' => '55%',
        ]);
        Product::factory()->create([
            'name' => 'ABV Not Set',
            'abv' => null,
        ]);

        $this->get(route('products.index', ['min_abv' => 20, 'max_abv' => 50]))
            ->assertOk()
            ->assertSee('In Range Drink')
            ->assertDontSee('Low ABV Drink')
            ->assertDontSee('High ABV Drink')
            ->assertDontSee('ABV Not Set')
            ->assertSee('Filter by alcohol percentage (ABV)');
    }

    public function test_new_arrivals_can_be_filtered_by_alcohol_percentage_range(): void
    {
        Product::factory()->create([
            'name' => 'New In Range',
            'is_new_arrival' => true,
            'abv' => '35%',
        ]);
        Product::factory()->create([
            'name' => 'New Out of Range',
            'is_new_arrival' => true,
            'abv' => '15%',
        ]);
        Product::factory()->create([
            'name' => 'Old In Range',
            'is_new_arrival' => false,
            'abv' => '35%',
        ]);

        $this->get(route('products.new-arrivals', ['min_abv' => 20, 'max_abv' => 50]))
            ->assertOk()
            ->assertSee('New In Range')
            ->assertDontSee('New Out of Range')
            ->assertDontSee('Old In Range');
    }

    public function test_alcohol_percentage_filter_rejects_a_minimum_above_the_maximum(): void
    {
        $this->get(route('products.index', ['min_abv' => 50, 'max_abv' => 20]))
            ->assertSessionHasErrors('min_abv');
    }
}
