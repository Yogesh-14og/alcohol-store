<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseLedgerController extends Controller
{
    public function suppliers(): View
    {
        $suppliers = Supplier::with('purchases.payments')->orderBy('name')->get();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function index(): View
    {
        $suppliers = Supplier::with('purchases.payments')->orderBy('name')->get();

        return view('admin.purchase-ledger.index', compact('suppliers'));
    }

    public function createSupplier(): View
    {
        return view('admin.purchase-ledger.supplier-form');
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        $supplier = Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('status', 'Supplier added.');
    }

    public function showSupplier(Supplier $supplier): View
    {
        $supplier->load(['purchases.items', 'purchases.payments']);

        return view('admin.purchase-ledger.supplier-show', compact('supplier'));
    }

    public function createPurchase(Request $request): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $selectedSupplierId = $request->query('supplier_id');

        return view('admin.purchase-ledger.purchase-form', compact('suppliers', 'products', 'selectedSupplierId'));
    }

    public function storePurchase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'purchased_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit_cost' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ]);

        $purchase = DB::transaction(function () use ($validated): Purchase {
            $itemProductIds = collect($validated['items'])
                ->pluck('product_id')
                ->map(fn ($productId): int => (int) $productId)
                ->unique()
                ->sort()
                ->values();
            $products = Product::query()
                ->whereIn('id', $itemProductIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $totalCents = 0;

            foreach ($validated['items'] as $item) {
                $unitCostCents = (int) round((float) $item['unit_cost'] * 100);
                $quantity = (int) $item['quantity'];

                if ($unitCostCents > intdiv(999999999999, $quantity)) {
                    throw ValidationException::withMessages([
                        'items' => 'The purchase total exceeds the supported amount.',
                    ]);
                }

                $lineTotalCents = $unitCostCents * $quantity;

                if ($lineTotalCents > 999999999999 - $totalCents) {
                    throw ValidationException::withMessages([
                        'items' => 'The purchase total exceeds the supported amount.',
                    ]);
                }

                $totalCents += $lineTotalCents;
            }

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'invoice_number' => $validated['invoice_number'] ?? null,
                'purchased_at' => $validated['purchased_at'],
                'total_amount' => number_format($totalCents / 100, 2, '.', ''),
                'notes' => $validated['notes'] ?? null,
            ]);

            if (blank($purchase->invoice_number)) {
                $purchase->update([
                    'invoice_number' => sprintf(
                        'PUR-%s-%06d',
                        $purchase->purchased_at->format('Ymd'),
                        $purchase->id,
                    ),
                ]);
            }

            foreach ($validated['items'] as $item) {
                $product = $products->get((int) $item['product_id']);

                if ($product === null) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected product could not be found.',
                    ]);
                }

                $quantity = (int) $item['quantity'];

                if ($product->stock_quantity > 4294967295 - $quantity) {
                    throw ValidationException::withMessages([
                        'items' => 'The received quantity exceeds the supported stock limit for '.$product->name.'.',
                    ]);
                }

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_cost' => number_format((int) round((float) $item['unit_cost'] * 100) / 100, 2, '.', ''),
                    'quantity' => $item['quantity'],
                ]);

                $product->update([
                    'stock_quantity' => $product->stock_quantity + $quantity,
                    'cost_price' => $item['unit_cost'],
                ]);
            }

            return $purchase;
        });

        return redirect()->route('admin.purchase-ledger.suppliers.show', $purchase->supplier_id)
            ->with('status', 'Purchase recorded and stock updated.');
    }

    public function storeSupplierPayment(Request $request, Supplier $supplier, Purchase $purchase): RedirectResponse
    {
        abort_unless($purchase->supplier_id === $supplier->id, 404);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'method' => ['required', 'in:cash,bank_transfer,cheque,other'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($supplier, $purchase, $validated): void {
            $lockedPurchase = Purchase::query()->where('supplier_id', $supplier->id)
                ->lockForUpdate()->findOrFail($purchase->id);
            $paidCents = (int) round((float) $lockedPurchase->payments()->sum('amount') * 100);
            $totalCents = (int) round((float) $lockedPurchase->total_amount * 100);
            $paymentCents = (int) round((float) $validated['amount'] * 100);

            if ($paymentCents > $totalCents - $paidCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment cannot exceed the remaining supplier balance.',
                ]);
            }

            SupplierPayment::create([
                'purchase_id' => $lockedPurchase->id,
                'amount' => number_format($paymentCents / 100, 2, '.', ''),
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
                'paid_at' => now(),
            ]);
        });

        return redirect()->route('admin.purchase-ledger.suppliers.show', $supplier)
            ->with('status', 'Supplier payment recorded.');
    }
}
