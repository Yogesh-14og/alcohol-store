<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductCatalogController extends Controller
{
    public function index(Request $request): View
    {
        [$minimumAbv, $maximumAbv] = $this->validatedAbvRange($request);
        $products = Product::with('category')
            ->withAvailableStock()
            ->withinAbvRange($minimumAbv, $maximumAbv)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'title' => 'All Products',
            'description' => 'Browse our full selection of drinks.',
            'products' => $products,
            'minimumAbv' => $minimumAbv,
            'maximumAbv' => $maximumAbv,
            'catalogRoute' => 'products.index',
        ]);
    }

    public function newArrivals(Request $request): View
    {
        [$minimumAbv, $maximumAbv] = $this->validatedAbvRange($request);
        $products = Product::with('category')
            ->withAvailableStock()
            ->where('is_new_arrival', true)
            ->withinAbvRange($minimumAbv, $maximumAbv)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'title' => 'New Arrivals',
            'description' => 'Explore the latest additions to our collection.',
            'products' => $products,
            'minimumAbv' => $minimumAbv,
            'maximumAbv' => $maximumAbv,
            'catalogRoute' => 'products.new-arrivals',
        ]);
    }

    public function show(string $product): View
    {
        $query = Product::query()
            ->with('category')
            ->withAvailableStock()
            ->where('slug', $product);

        if (ctype_digit($product)) {
            $query->orWhereKey($product);
        }

        $product = $query->firstOrFail();
        $productDetails = collect([
            $product->category?->name,
            $product->volume,
            $product->abv ? $product->abv.' ABV' : null,
        ])->filter()->implode(', ');
        $metaDescription = Str::limit(
            $product->name.($productDetails !== '' ? ' - '.$productDetails : '')
                .'. View product details and current price at Khanal Liquor.',
            160,
        );

        return view('products.show', [
            'product' => $product,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => rtrim((string) config('app.url'), '/')
                .route('products.show', $product->slug ?: $product->id, false),
        ]);
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function validatedAbvRange(Request $request): array
    {
        $validated = $request->validate([
            'min_abv' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_abv' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $minimumAbv = isset($validated['min_abv']) ? (float) $validated['min_abv'] : null;
        $maximumAbv = isset($validated['max_abv']) ? (float) $validated['max_abv'] : null;

        if ($minimumAbv !== null && $maximumAbv !== null && $minimumAbv > $maximumAbv) {
            throw ValidationException::withMessages([
                'min_abv' => 'The minimum alcohol percentage must not exceed the maximum.',
            ]);
        }

        return [$minimumAbv, $maximumAbv];
    }
}
