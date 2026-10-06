@extends('layouts.admin')

@section('title', $supplier->name.' khata')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <a href="{{ route('admin.purchase-ledger.index') }}" class="text-decoration-none">← Kharid Khata</a>
        <h1 class="h2 fw-bold mt-2 mb-1">{{ $supplier->name }}</h1>
        <p class="text-muted mb-0">{{ $supplier->phone ?: 'No phone' }}{{ $supplier->email ? ' · '.$supplier->email : '' }}</p>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.purchase-ledger.purchases.create', ['supplier_id' => $supplier->id]) }}">Record purchase</a>
</div>

@php($totalDueCents = $supplier->purchases->sum(fn ($purchase) => $purchase->balance_due_cents))
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <p class="text-muted small mb-1">Outstanding supplier balance</p>
        <p class="h3 fw-bold mb-0">NPR {{ number_format($totalDueCents / 100, 2) }}</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">Date / invoice</th><th>Product</th><th>Purchase total</th><th>Paid</th><th>Due</th><th>Record payment</th></tr></thead>
            <tbody>
                @forelse ($supplier->purchases as $purchase)
                    <tr>
                        <td class="ps-4">{{ $purchase->purchased_at->format('M j, Y') }}<br><small class="text-muted">{{ $purchase->invoice_number ?: 'No invoice number' }}</small></td>
                        <td>@foreach ($purchase->items as $item)<div>{{ $item->product_name }} × {{ $item->quantity }}</div>@endforeach</td>
                        <td>NPR {{ number_format((float) $purchase->total_amount, 2) }}</td>
                        <td>
                            NPR {{ number_format($purchase->paid_amount_cents / 100, 2) }}
                            @foreach ($purchase->payments as $payment)
                                <small class="d-block text-muted">{{ $payment->paid_at->format('M j, Y') }} · NPR {{ number_format((float) $payment->amount, 2) }} · {{ str_replace('_', ' ', ucfirst($payment->method)) }}</small>
                            @endforeach
                        </td>
                        <td class="fw-semibold">NPR {{ number_format($purchase->balance_due_cents / 100, 2) }}</td>
                        <td>
                            @if ($purchase->balance_due_cents > 0)
                                <form action="{{ route('admin.purchase-ledger.payments.store', [$supplier, $purchase]) }}" method="POST" class="d-flex flex-wrap gap-2">
                                    @csrf
                                    <input aria-label="Payment amount in NPR" name="amount" type="number" min="0.01" max="{{ number_format($purchase->balance_due_cents / 100, 2, '.', '') }}" step="0.01" class="form-control form-control-sm" placeholder="NPR" required>
                                    <select aria-label="Payment method" name="method" class="form-select form-select-sm" required>
                                        <option value="cash">Cash</option><option value="bank_transfer">Bank</option><option value="cheque">Cheque</option><option value="other">Other</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Pay</button>
                                </form>
                            @else
                                <span class="badge text-bg-success">Settled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">No purchases recorded for this supplier.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
