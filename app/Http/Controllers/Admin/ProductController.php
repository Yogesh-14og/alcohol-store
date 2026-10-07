<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');
        $products = Product::with('category')
            ->withAvailableStock()
            ->when($search !== '', fn ($query) => $query->where(
                fn ($products) => $products
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('volume', 'like', '%'.$search.'%')
                    ->orWhereHas('category', fn ($categories) => $categories->where('name', 'like', '%'.$search.'%')),
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $product = new Product;
        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $this->validatedData($request);
        $validated['slug'] = $validated['slug'] ?: $this->generateSlug($validated['name']);
        $validated = $this->normalizeFlags($validated);
        $product = Product::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product created.',
                'product' => ['id' => $product->id],
            ], 201);
        }

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function stockAdjustments(Product $product): View
    {
        $adjustments = $product->stockAdjustments()
            ->with('admin')
            ->latest()
            ->paginate(20);

        return view('admin.products.stock-adjustments', compact('product', 'adjustments'));
    }

    public function storeStockAdjustment(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => ['required', Rule::in(['add', 'remove'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reason' => ['required', Rule::in(['restock', 'stock_count', 'damaged', 'expired', 'other'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($product, $validated): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $stockBefore = (int) $lockedProduct->stock_quantity;
            $quantity = (int) $validated['quantity'];
            $stockAfter = $validated['direction'] === 'add'
                ? $stockBefore + $quantity
                : $stockBefore - $quantity;

            if ($stockAfter > 4294967295) {
                throw ValidationException::withMessages([
                    'quantity' => 'The adjusted stock exceeds the supported stock limit.',
                ]);
            }

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock cannot be reduced below zero.',
                ]);
            }

            if ($validated['direction'] === 'remove') {
                $reservedQuantity = $lockedProduct->orderItems()
                    ->whereHas('order', fn ($orders) => $orders->whereIn('status', Order::STOCK_RESERVED_STATUSES))
                    ->sum('quantity');

                if ($stockAfter < $reservedQuantity) {
                    throw ValidationException::withMessages([
                        'quantity' => "Stock cannot be reduced below the {$reservedQuantity} units in open orders.",
                    ]);
                }
            }

            $lockedProduct->update(['stock_quantity' => $stockAfter]);

            StockAdjustment::create([
                'product_id' => $lockedProduct->id,
                'product_name' => $lockedProduct->name,
                'admin_id' => auth('myadmin')->id(),
                'direction' => $validated['direction'],
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('admin.products.stock-adjustments.index', $product)
            ->with('status', 'Stock adjusted and history recorded.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validatedData($request, $product);

        $validated['slug'] = $validated['slug'] ?: $this->generateSlug($validated['name'], $product);
        $validated = $this->normalizeFlags($validated);

        DB::transaction(function () use ($product, $validated): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $lockedProduct->update($validated);
        });

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product)],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'cost_price' => [
                $product === null ? 'required' : 'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],
            'stock_quantity' => [$product === null ? 'required' : 'prohibited', 'integer', 'min:0', 'max:4294967295'],
            'volume' => ['nullable', 'string', 'max:255'],
            'abv' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:2048'],
            'is_best_seller' => ['sometimes', 'boolean'],
            'is_new_arrival' => ['sometimes', 'boolean'],
            'is_hot_deal' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeFlags(array $validated): array
    {
        foreach (['is_best_seller', 'is_new_arrival', 'is_hot_deal'] as $flag) {
            $validated[$flag] = (bool) ($validated[$flag] ?? false);
        }

        return $validated;
    }

    private function generateSlug(string $name, ?Product $product = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($product !== null, fn ($query) => $query->where('id', '!=', $product->id))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
