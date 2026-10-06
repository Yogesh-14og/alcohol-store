<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_product_can_be_created_with_factory(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Product::class, $product);
        $this->assertNotNull($product->id);
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->slug);
        $this->assertNotNull($product->price);
        $this->assertNotNull($product->category_id);
    }

    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_fillable_attributes(): void
    {
        $fillable = (new Product)->getFillable();

        $this->assertContains('category_id', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('price', $fillable);
        $this->assertContains('volume', $fillable);
        $this->assertContains('abv', $fillable);
        $this->assertContains('image', $fillable);
        $this->assertContains('is_best_seller', $fillable);
        $this->assertContains('is_new_arrival', $fillable);
        $this->assertContains('is_hot_deal', $fillable);
    }

    public function test_product_can_be_best_seller(): void
    {
        $product = Product::factory()->create(['is_best_seller' => true]);

        $this->assertTrue($product->is_best_seller);
    }

    public function test_product_can_be_new_arrival(): void
    {
        $product = Product::factory()->create(['is_new_arrival' => true]);

        $this->assertTrue($product->is_new_arrival);
    }

    public function test_product_can_be_hot_deal(): void
    {
        $product = Product::factory()->create(['is_hot_deal' => true]);

        $this->assertTrue($product->is_hot_deal);
    }
}
