@extends('layouts.admin')

@section('title', 'Bikri Khata')

@section('content')
<div class="mb-4">
    <p class="text-uppercase text-primary fw-semibold small mb-1">Sales ledger</p>
    <h1 class="h2 fw-bold mb-1">Bikri Khata</h1>
    <p class="text-muted mb-0">An order marked delivered is counted as sold and paid automatically.</p>
</div>
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">Customer</th><th>Sales</th><th>Received</th><th>Balance due</th><th>Delivered orders</th></tr></thead>
            <tbody>
                @forelse ($customers as $customer)
                    @php
                        $salesCents = $customer->orders->sum(fn ($order) => (int) round((float) $order->total_amount * 100));
                        $receivedCents = $customer->orders->sum(fn ($order) => $order->received_amount_cents);
                        $dueCents = $customer->orders->sum(fn ($order) => $order->balance_due_cents);
                    @endphp
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $customer->name }}<small class="d-block text-muted">{{ $customer->email }}</small></td>
                        <td>NPR {{ number_format($salesCents / 100, 2) }}</td>
                        <td>NPR {{ number_format($receivedCents / 100, 2) }}</td>
                        <td class="fw-semibold">NPR {{ number_format($dueCents / 100, 2) }}</td>
                        <td>{{ $customer->orders->count() }}</td>
                    </tr>
                    @foreach ($customer->orders as $order)
                        <tr class="table-light">
                            <td colspan="5" class="px-4">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0" data-sales-history-table>
                                        <thead>
                                            <tr>
                                                <th data-sales-history-column="order">Order</th>
                                                <th data-sales-history-column="date">Delivery date</th>
                                                <th data-sales-history-column="sale">Sale</th>
                                                <th data-sales-history-column="received">Received</th>
                                                <th data-sales-history-column="due">Amount due</th>
                                                <th data-sales-history-column="payments">Payment history</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td data-sales-history-column="order">{{ $order->order_number }}</td>
                                                <td data-sales-history-column="date">{{ $order->delivered_at?->format('M j, Y') ?? 'Not recorded' }}</td>
                                                <td data-sales-history-column="sale">NPR {{ number_format((float) $order->total_amount, 2) }}</td>
                                                <td data-sales-history-column="received">NPR {{ number_format($order->received_amount_cents / 100, 2) }}</td>
                                                <td data-sales-history-column="due">NPR {{ number_format($order->balance_due_cents / 100, 2) }}</td>
                                                <td data-sales-history-column="payments">
                                                    @forelse ($order->customerPayments as $payment)
                                                        <small class="d-block text-muted">{{ $payment->received_at->format('M j, Y') }} · NPR {{ number_format((float) $payment->amount, 2) }} · {{ str_replace('_', ' ', ucfirst($payment->method)) }}</small>
                                                    @empty
                                                        <span class="text-muted">Collected on delivery</span>
                                                    @endforelse
                                                </td>
                                                <td>
                                                    @if ($order->balance_due_cents > 0)
                                                        <form action="{{ route('admin.sales-ledger.payments.store', $order) }}" method="POST" class="admin-sales-payment-form d-flex flex-wrap gap-2">
                                                            @csrf
                                                            <input aria-label="Payment amount in NPR" name="amount" type="number" min="0.01" max="{{ number_format($order->balance_due_cents / 100, 2, '.', '') }}" step="0.01" class="form-control form-control-sm" placeholder="NPR" required>
                                                            <select aria-label="Payment method" name="method" class="form-select form-select-sm" required>
                                                                <option value="cash">Cash</option><option value="bank_transfer">Bank</option><option value="cheque">Cheque</option><option value="other">Other</option>
                                                            </select>
                                                            <button class="btn btn-sm btn-outline-primary" type="submit">Receive</button>
                                                        </form>
                                                    @else
                                                        <span class="badge text-bg-success">Settled</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">No delivered customer orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($unlinkedOrders->isNotEmpty())
    <section class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold">Orders without a linked customer</h2>
            <p class="text-muted small">These delivered orders are tracked separately because they have no customer account.</p>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" data-sales-history-table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th data-sales-history-column="order">Order</th>
                            <th data-sales-history-column="date">Delivery date</th>
                            <th data-sales-history-column="sale">Sale</th>
                            <th data-sales-history-column="received">Received</th>
                            <th data-sales-history-column="due">Amount due</th>
                            <th data-sales-history-column="payments">Payment history</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unlinkedOrders as $order)
                            <tr>
                                <td>{{ $order->shipping_name ?: 'Customer' }}</td>
                                <td data-sales-history-column="order">{{ $order->order_number }}</td>
                                <td data-sales-history-column="date">{{ $order->delivered_at?->format('M j, Y') ?? 'Not recorded' }}</td>
                                <td data-sales-history-column="sale">NPR {{ number_format((float) $order->total_amount, 2) }}</td>
                                <td data-sales-history-column="received">NPR {{ number_format($order->received_amount_cents / 100, 2) }}</td>
                                <td data-sales-history-column="due">NPR {{ number_format($order->balance_due_cents / 100, 2) }}</td>
                                <td data-sales-history-column="payments">
                                    @forelse ($order->customerPayments as $payment)
                                        <small class="d-block text-muted">{{ $payment->received_at->format('M j, Y') }} · NPR {{ number_format((float) $payment->amount, 2) }} · {{ str_replace('_', ' ', ucfirst($payment->method)) }}</small>
                                    @empty
                                        <span class="text-muted">Collected on delivery</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($order->balance_due_cents > 0)
                                        <form action="{{ route('admin.sales-ledger.payments.store', $order) }}" method="POST" class="admin-sales-payment-form d-flex flex-wrap gap-2">
                                            @csrf
                                            <input aria-label="Payment amount in NPR" name="amount" type="number" min="0.01" max="{{ number_format($order->balance_due_cents / 100, 2, '.', '') }}" step="0.01" class="form-control form-control-sm" placeholder="NPR" required>
                                            <select aria-label="Payment method" name="method" class="form-select form-select-sm" required>
                                                <option value="cash">Cash</option><option value="bank_transfer">Bank</option><option value="cheque">Cheque</option><option value="other">Other</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-primary" type="submit">Receive</button>
                                        </form>
                                    @else
                                        <span class="badge text-bg-success">Settled</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endif
@endsection
