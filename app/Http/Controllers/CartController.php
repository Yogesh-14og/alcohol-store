<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $items = $request->user()->cartItems()->with([
            'product' => fn ($query) => $query->withAvailableStock(),
        ])->get();
        $subtotal = $items->sum(fn (CartItem $item): float => (float) $item->product->price * $item->quantity);

        return view('cart.index', compact('items', 'subtotal'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $cartItem = $request->user()->cartItems()->firstOrNew([
            'product_id' => $product->id,
        ]);

        $product = Product::query()->withAvailableStock()->findOrFail($product->id);

        if ($product->available_stock < 1) {
            return back()->withErrors([
                'cart' => 'This product is currently out of stock.',
            ]);
        }

        $nextQuantity = $cartItem->exists ? $cartItem->quantity + 1 : 1;

        if ($nextQuantity > 99) {
            return back()->withErrors([
                'cart' => 'You can add up to 99 of each product.',
            ]);
        }

        if ($nextQuantity > $product->available_stock) {
            return back()->withErrors([
                'cart' => 'Only '.$product->available_stock.' units of '.$product->name.' are currently available.',
            ]);
        }

        $cartItem->quantity = $nextQuantity;
        $cartItem->save();

        return back()->with('status', 'Added '.$product->name.' to your cart.');
    }

    public function update(Request $request, int $cartItem): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $ownedCartItem = $request->user()->cartItems()->with([
            'product' => fn ($query) => $query->withAvailableStock(),
        ])->findOrFail($cartItem);

        if ($validated['quantity'] > $ownedCartItem->product->available_stock) {
            return back()
                ->withErrors(['quantity' => 'Only '.$ownedCartItem->product->available_stock.' units are currently available.'])
                ->withInput();
        }

        $ownedCartItem->update($validated);

        return redirect()->route('cart.index')->with('status', 'Your cart has been updated.');
    }

    public function destroy(Request $request, int $cartItem): RedirectResponse
    {
        $request->user()->cartItems()->findOrFail($cartItem)->delete();

        return redirect()->route('cart.index')->with('status', 'The item was removed from your cart.');
    }
}
