<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class SeoTest extends TestCase
{
    public function test_product_page_has_canonical_metadata_and_product_structured_data(): void
    {
        $product = Product::factory()->create([
            'name' => 'SEO Reserve',
            'slug' => 'seo-reserve',
            'stock_quantity' => 2,
            'volume' => '750ml',
            'abv' => '40%',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('<link rel="canonical" href="'.rtrim((string) config('app.url'), '/')
                .route('products.show', $product->slug, false).'">', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"availability":"https://schema.org/InStock"', false)
            ->assertSee($product->name);
    }

    public function test_product_without_a_slug_has_an_id_based_detail_url(): void
    {
        $product = Product::factory()->create([
            'slug' => '',
        ]);

        $this->get(route('products.show', $product->id))
            ->assertOk()
            ->assertSee(route('products.show', $product->id, false), false);
    }

    public function test_product_catalog_metadata_canonicalizes_abv_filters(): void
    {
        $this->get(route('products.index', ['min_abv' => 20, 'max_abv' => 40]))
            ->assertOk()
            ->assertSee('<meta name="robots" content="index,follow">', false)
            ->assertSee('<link rel="canonical" href="'.rtrim((string) config('app.url'), '/')
                .route('products.index', [], false).'">', false);
    }

    public function test_sitemap_includes_public_products_and_categories_with_products(): void
    {
        $category = Category::factory()->create([
            'slug' => 'seo-category',
        ]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'seo-product',
        ]);

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(rtrim((string) config('app.url'), '/').route('products.show', $product->slug, false), false)
            ->assertSee(rtrim((string) config('app.url'), '/').route('categories.show', $category->slug, false), false)
            ->assertSee(rtrim((string) config('app.url'), '/').route('products.index', [], false), false);
    }

    public function test_robots_endpoint_blocks_private_sections_and_advertises_sitemap(): void
    {
        $this->get(route('seo.robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /dashboard/')
            ->assertSee('Disallow: /checkout/')
            ->assertSee('Sitemap: '.rtrim((string) config('app.url'), '/').route('seo.sitemap', [], false));
    }

    public function test_login_page_is_not_indexable(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false);
    }
}
