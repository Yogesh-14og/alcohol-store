<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerPayment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesLedgerController extends Controller
{
    public function index(): View
    {
        $customers = User::query()
            ->whereHas('orders', fn ($orders) => $orders->where('status', 'delivered'))
            ->with(['orders' => fn ($orders) => $orders
                ->where('status', 'delivered')
                ->with('customerPayments')
                ->orderByDesc('delivered_at')])
            ->orderBy('name')
            ->get();
        $unlinkedOrders = Order::query()
            ->whereNull('user_id')
            ->where('status', 'delivered')
            ->with('customerPayments')
            ->orderByDesc('delivered_at')
            ->get();

        return view('admin.sales-ledger.index', compact('customers', 'unlinkedOrders'));
    }

    public function storePayment(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'method' => ['required', 'in:cash,bank_transfer,cheque,other'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status !== 'delivered') {
                throw ValidationException::withMessages([
                    'amount' => 'Payments can only be recorded for delivered orders.',
                ]);
            }

            $receivedCents = $lockedOrder->received_amount_cents;
            $totalCents = (int) round((float) $lockedOrder->total_amount * 100);
            $paymentCents = (int) round((float) $validated['amount'] * 100);

            if ($paymentCents > $totalCents - $receivedCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment cannot exceed the remaining customer balance.',
                ]);
            }

            CustomerPayment::create([
                'order_id' => $lockedOrder->id,
                'amount' => number_format($paymentCents / 100, 2, '.', ''),
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
                'received_at' => now(),
            ]);

            $lockedOrder->update([
                'payment_status' => $paymentCents + $receivedCents === $totalCents ? 'paid' : 'unpaid',
            ]);
        });

        return redirect()->route('admin.sales-ledger.index')->with('status', 'Customer payment recorded.');
    }
}
