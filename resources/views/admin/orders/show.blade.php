@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><a href="{{ route('admin.orders.index') }}" class="text-decoration-none">← Orders</a><h1 class="h2 fw-bold mt-2">{{ $order->order_number }}</h1></div>
    @if (in_array($order->status, ['processing', 'shipped', 'delivered'], true))
        <a href="{{ route('admin.orders.invoice', $order) }}" class="btn btn-outline-primary" target="_blank" rel="noopener">
            <i class="bi bi-printer me-1"></i>Print invoice
        </a>
    @endif
</div>
<div class="row g-4">
    <div class="col-lg-7">
        <section class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3">Items</h2>
            @foreach ($order->items as $item)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <div>
                        <span class="fw-semibold">{{ $item->product_name }} × {{ $item->quantity }}</span>
                        <small class="d-block text-muted">
                            Cost: {{ $item->unit_cost !== null ? 'NPR '.number_format((float) $item->unit_cost, 2).' each' : 'Not recorded' }}
                        </small>
                    </div>
                    <span>NPR {{ number_format((float) $item->unit_price * $item->quantity, 2) }}</span>
                </div>
            @endforeach
            <div class="d-flex justify-content-between pt-3"><span class="fw-bold">Total</span><span class="fw-bold">NPR {{ number_format((float) $order->total_amount, 2) }}</span></div>
            @if ($order->status === 'delivered')
                <div class="d-flex justify-content-between border-top mt-3 pt-3">
                    <span>Received</span>
                    <span>NPR {{ number_format($order->received_amount_cents / 100, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Customer balance</span>
                    <span class="fw-bold">NPR {{ number_format($order->balance_due_cents / 100, 2) }}</span>
                </div>
                <a class="btn btn-sm btn-outline-primary mt-3" href="{{ route('admin.sales-ledger.index') }}">Open Bikri Khata</a>
            @endif
            @if ($order->status === 'delivered')
                <div class="d-flex justify-content-between border-top mt-3 pt-3">
                    <span class="fw-bold">Gross profit</span>
                    <span class="fw-bold">{{ $grossProfitCents !== null ? 'NPR '.number_format($grossProfitCents / 100, 2) : 'Unavailable: cost data missing' }}</span>
                </div>
                <p class="text-muted small mt-2 mb-0">Product revenue less recorded product cost; excludes delivery, tax, and operating expenses.</p>
            @endif
        </div></section>
        <section class="card border-0 shadow-sm rounded-4 mt-4"><div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3">Delivery details</h2>
            <p class="mb-1">{{ $order->shipping_name ?? $order->user?->name ?? 'No customer name' }}</p>
            <p class="mb-1">{{ $order->shipping_phone ?? 'No phone provided' }}</p>
            <p class="mb-0">{{ $order->shipping_address ?? 'No address provided' }}</p>
        </div></section>
    </div>
    <div class="col-lg-5">
        <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
            @csrf
            @method('PUT')
            <h2 class="h5 fw-bold mb-3">Update order</h2>
            <div class="mb-3">
                <label for="status" class="form-label fw-semibold">Fulfillment status</label>
                <select id="status" name="status" class="form-select" required>
                    @foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <p class="text-muted small">Marking an order delivered records the sale as paid and adds it to Bikri Khata.</p>
            <button class="btn btn-primary" type="submit">Save order</button>
        </div></form>
    </div>
</div>
@endsection
