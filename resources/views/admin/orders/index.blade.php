@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="mb-4"><p class="text-uppercase text-primary fw-semibold small mb-1">Store operations</p><h1 class="h2 fw-bold mb-0">Orders</h1></div>
<form action="{{ route('admin.orders.index') }}" method="GET" class="card border-0 shadow-sm rounded-4 mb-4" role="search" data-live-search>
    <div class="card-body">
        <label for="orderSearch" class="form-label fw-semibold">Search orders</label>
        <input id="orderSearch" name="search" type="search" class="form-control mb-3" value="{{ $search }}" placeholder="Order number, customer name, email, or phone" maxlength="100" autocomplete="off">
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Search</button>
            @if ($search !== '')
                <a class="btn btn-outline-secondary" href="{{ route('admin.orders.index') }}">Clear</a>
            @endif
        </div>
    </div>
</form>
<div class="card border-0 shadow-sm rounded-4"><div class="card-body p-0"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th class="ps-4">Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $order->order_number }}</td>
                    <td>{{ $order->shipping_name ?? $order->user?->name ?? 'Guest' }}</td>
                    <td>{{ $order->created_at->format('M j, Y') }}</td>
                    <td>NPR {{ number_format((float) $order->total_amount, 2) }}</td>
                    <td class="text-capitalize">{{ $order->payment_method ?? '—' }} / {{ $order->effective_payment_status }}</td>
                    <td><span class="badge text-bg-secondary text-capitalize">{{ $order->status }}</span></td>
                    <td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.orders.show', $order) }}">Manage</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">{{ $search !== '' ? 'No orders match your search.' : 'No orders yet.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div></div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
