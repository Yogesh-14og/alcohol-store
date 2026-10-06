<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $siteUrl = rtrim((string) config('app.url'), '/');
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /dashboard/',
            'Disallow: /cart/',
            'Disallow: /checkout/',
            'Disallow: /orders/',
            'Sitemap: '.$siteUrl.route('seo.sitemap', [], false),
        ]);

        return response($content."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        $siteUrl = rtrim((string) config('app.url'), '/');
        $makeUrl = fn (string $routeName, array $parameters = []): string => $siteUrl
            .route($routeName, $parameters, false);
        $urls = collect([
            $makeUrl('home'),
            $makeUrl('products.index'),
            $makeUrl('products.new-arrivals'),
            $makeUrl('terms'),
        ])
            ->concat(Category::query()
                ->whereHas('products')
                ->get(['id', 'slug'])
                ->map(fn (Category $category): string => $makeUrl('categories.show', [
                    'category' => $category->slug ?: $category->id,
                ])))
            ->concat(Product::query()
                ->get(['id', 'slug'])
                ->map(fn (Product $product): string => $makeUrl('products.show', [
                    'product' => $product->slug ?: $product->id,
                ])));

        return response()->view('seo.sitemap', compact('urls'), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
