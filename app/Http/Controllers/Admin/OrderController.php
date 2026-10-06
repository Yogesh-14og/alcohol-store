<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');
        $orders = Order::with('user')
            ->when($search !== '', fn ($query) => $query->where(
                fn ($orders) => $orders
                    ->where('order_number', 'like', '%'.$search.'%')
                    ->orWhere('shipping_name', 'like', '%'.$search.'%')
                    ->orWhere('shipping_phone', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($users) => $users
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')),
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'search'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items', 'customerPayments']);
        $grossProfitCents = $order->status === 'delivered' ? $order->gross_profit_cents : null;

        return view('admin.orders.show', compact('order', 'grossProfitCents'));
    }

    public function invoice(Order $order): View
    {
        abort_unless(in_array($order->status, ['processing', 'shipped', 'delivered'], true), 404);

        $order->load(['user', 'items', 'customerPayments']);

        return view('admin.orders.invoice', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $wasDelivered = $lockedOrder->status === 'delivered';
            $willBeDelivered = $validated['status'] === 'delivered';

            if ($wasDelivered && ! $willBeDelivered && $lockedOrder->customerPayments()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'This delivered order has recorded payments and cannot be moved out of delivered status.',
                ]);
            }

            $validated['delivered_at'] = match (true) {
                ! $wasDelivered && $willBeDelivered => now(),
                $wasDelivered && ! $willBeDelivered => null,
                default => $lockedOrder->delivered_at,
            };
            $validated['payment_status'] = $willBeDelivered
                ? 'paid'
                : ($wasDelivered ? 'unpaid' : $lockedOrder->payment_status);

            if ($wasDelivered !== $willBeDelivered) {
                $items = $lockedOrder->items()->whereNotNull('product_id')->get();
                $productIds = $items->pluck('product_id')->unique()->sort()->values();
                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $product = $products->get($item->product_id);

                    if ($product === null) {
                        continue;
                    }

                    if ($willBeDelivered && $product->stock_quantity < $item->quantity) {
                        throw ValidationException::withMessages([
                            'status' => 'Not enough stock remains to mark '.$product->name.' as delivered.',
                        ]);
                    }

                    $product->update([
                        'stock_quantity' => $product->stock_quantity + ($willBeDelivered ? -$item->quantity : $item->quantity),
                    ]);
                }
            }

            $lockedOrder->update($validated);
        });

        $updatedOrder = $order->fresh();

        if (in_array($updatedOrder->status, ['processing', 'shipped', 'delivered'], true)) {
            return redirect()->route('admin.orders.invoice', $updatedOrder);
        }

        return redirect()->route('admin.orders.show', $updatedOrder)->with('status', 'Order updated.');
    }
}
