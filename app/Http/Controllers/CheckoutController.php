<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $items = $request->user()->cartItems()->with([
            'product' => fn ($query) => $query->withAvailableStock(),
        ])->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Add an item before checking out.');
        }

        $subtotal = $items->sum(
            fn (CartItem $item): int => (int) round((float) $item->product->price * 100) * $item->quantity,
        ) / 100;
        $unavailableItems = $items->filter(
            fn (CartItem $item): bool => $item->product->available_stock < $item->quantity,
        );

        return view('checkout.index', compact('items', 'subtotal', 'unavailableItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+(). -]+$/'],
            'delivery_address' => ['required', 'string', 'max:2000'],
            'payment_method' => ['required', 'in:cod'],
        ]);

        $order = DB::transaction(function () use ($request, $validated) {
            $items = $request->user()
                ->cartItems()
                ->with('product')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                return null;
            }

            $productIds = $items->pluck('product_id')->unique()->sort()->values();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $reservedQuantities = OrderItem::query()
                ->whereIn('product_id', $productIds)
                ->whereHas('order', fn ($orders) => $orders->whereIn('status', Order::STOCK_RESERVED_STATUSES))
                ->lockForUpdate()
                ->get()
                ->groupBy('product_id')
                ->map(fn ($reservedItems): int => $reservedItems->sum('quantity'));

            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                $availableStock = $product
                    ? max(0, $product->stock_quantity - (int) $reservedQuantities->get($item->product_id, 0))
                    : 0;

                if ($product === null || $availableStock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => ($product?->name ?? 'A product').' does not have enough stock for the quantity in your cart.',
                    ]);
                }
            }

            $totalInCents = $items->sum(
                fn (CartItem $item): int => (int) round((float) $item->product->price * 100) * $item->quantity,
            );

            $order = $request->user()->orders()->create([
                'order_number' => 'ORD-'.Str::upper(Str::random(10)),
                'total_amount' => number_format($totalInCents / 100, 2, '.', ''),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'cod',
                'shipping_name' => $validated['name'],
                'shipping_phone' => $validated['phone'],
                'shipping_address' => $validated['delivery_address'],
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'unit_price' => $item->product->price,
                    'unit_cost' => $item->product->cost_price,
                    'quantity' => $item->quantity,
                ]);
            }

            $request->user()->cartItems()->delete();

            return $order;
        });

        if ($order === null) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        return redirect()->route('orders.confirmation', $order->order_number);
    }
}
