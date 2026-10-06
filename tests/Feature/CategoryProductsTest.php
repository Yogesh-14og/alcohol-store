<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class CategoryProductsTest extends TestCase
{
    public function test_category_page_shows_only_products_from_that_category(): void
    {
        $category = Category::factory()->create([
            'name' => 'Wine',
            'slug' => 'wine',
        ]);
        $otherCategory = Category::factory()->create();
        $categoryProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Local Red',
            'stock_quantity' => 5,
        ]);
        Product::factory()->create([
            'category_id' => $otherCategory->id,
            'name' => 'Other Category Drink',
        ]);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('Wine')
            ->assertSee($categoryProduct->name)
            ->assertDontSee('Other Category Drink')
            ->assertSee('5 in stock');
    }

    public function test_unknown_category_slug_returns_not_found(): void
    {
        $this->get('/category/not-a-category')
            ->assertNotFound();
    }

    public function test_category_without_slug_can_be_opened_by_its_id(): void
    {
        $category = Category::factory()->create([
            'name' => 'Category without slug',
            'slug' => '',
        ]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Fallback Category Product',
            'stock_quantity' => 2,
        ]);

        $this->get(route('categories.show', $category->slug ?: $category->id))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_category_products_can_be_filtered_by_alcohol_percentage(): void
    {
        $category = Category::factory()->create(['slug' => 'spirits']);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Filtered Spirit',
            'abv' => '40%',
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Weak Spirit',
            'abv' => '12%',
        ]);
        Product::factory()->create([
            'name' => 'Different Category Spirit',
            'abv' => '40%',
        ]);

        $this->get(route('categories.show', ['category' => $category->slug, 'min_abv' => 30]))
            ->assertOk()
            ->assertSee('Filtered Spirit')
            ->assertDontSee('Weak Spirit')
            ->assertDontSee('Different Category Spirit');
    }
}
