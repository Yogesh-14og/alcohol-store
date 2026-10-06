@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <p class="text-uppercase text-primary fw-semibold small mb-1">Store overview</p>
        <h1 class="h2 fw-bold mb-0">Admin dashboard</h1>
    </div>
    <span class="text-muted">Signed in as {{ auth('admin')->user()->name }}</span>
</div>

<div class="row g-3 mb-4">
    @foreach ([
        'Products' => ['products', 'bi-box-seam'],
        'Categories' => ['categories', 'bi-tags'],
        'Orders' => ['orders', 'bi-receipt'],
        'Customers' => ['users', 'bi-people'],
        'Pending orders' => ['pending_orders', 'bi-hourglass-split'],
    ] as $label => [$key, $icon])
        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <i class="bi {{ $icon }} text-primary fs-4"></i>
                    <p class="text-muted small mt-2 mb-1">{{ $label }}</p>
                    <p class="h3 fw-bold mb-0">{{ number_format($counts[$key]) }}</p>
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                <p class="text-muted small mt-2 mb-1">Lifetime gross profit on delivered orders</p>
                <p class="h3 fw-bold mb-0">NPR {{ number_format($grossProfitCents / 100, 2) }}</p>
            </div>
        </div>
    </div>
</div>
@if ($ordersMissingCostData > 0)
    <div class="alert alert-warning" role="status">
        Gross profit excludes {{ $ordersMissingCostData }} delivered {{ \Illuminate\Support\Str::plural('order', $ordersMissingCostData) }} with missing product cost records. Costs are not available for older orders created before cost tracking.
    </div>
@endif

@if ($deliveredOrdersMissingDate > 0)
    <div class="alert alert-warning" role="status">
        {{ number_format($deliveredOrdersMissingDate) }} delivered {{ \Illuminate\Support\Str::plural('order', $deliveredOrdersMissingDate) }} {{ $deliveredOrdersMissingDate === 1 ? 'has' : 'have' }} no delivery date yet, so {{ $deliveredOrdersMissingDate === 1 ? 'it is' : 'they are' }} excluded from date-based sales reports. New deliveries will be dated automatically.
    </div>
@endif

<section class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-3">Sales and gross profit</h2>
        <p class="text-muted small">Delivered orders grouped by delivery date. Gross profit is shown only for orders with complete cost records.</p>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Period</th><th>Sales</th><th>Gross profit</th><th>Missing cost data</th></tr></thead>
                <tbody>
                    @foreach ($salesPeriods as $period => $summary)
                        <tr>
                            <th scope="row">{{ $period }}</th>
                            <td>NPR {{ number_format($summary['sales_cents'] / 100, 2) }}</td>
                            <td>NPR {{ number_format($summary['gross_profit_cents'] / 100, 2) }}</td>
                            <td>{{ $summary['orders_missing_cost'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h3 class="h6 fw-bold mt-4 mb-3">Daily breakdown — last 7 days</h3>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Day</th><th>Sales</th><th>Gross profit</th><th>Missing cost data</th></tr></thead>
                <tbody>
                    @foreach ($dailySales as $day)
                        <tr>
                            <th scope="row">{{ $day['label'] }}</th>
                            <td>NPR {{ number_format($day['sales_cents'] / 100, 2) }}</td>
                            <td>NPR {{ number_format($day['gross_profit_cents'] / 100, 2) }}</td>
                            <td>{{ $day['orders_missing_cost'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Stock levels</h2>
                <p class="text-muted small mb-0">Based on available stock after units reserved for open orders.</p>
            </div>
            <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label for="low_stock_threshold" class="form-label small mb-1">Low stock at or below</label>
                    <input id="low_stock_threshold" name="low_stock_threshold" type="number" min="0" max="1000000" class="form-control form-control-sm" value="{{ $lowStockThreshold }}" required>
                </div>
                <div class="col-auto">
                    <label for="high_stock_threshold" class="form-label small mb-1">High stock at or above</label>
                    <input id="high_stock_threshold" name="high_stock_threshold" type="number" min="1" max="1000000" class="form-control form-control-sm" value="{{ $highStockThreshold }}" required>
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-outline-primary" type="submit">Apply</button></div>
            </form>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <h3 class="h6 fw-bold">Low stock <span class="badge text-bg-warning">{{ $lowStockProducts->count() }}</span></h3>
                @if ($lowStockProducts->isEmpty())
                    <p class="text-muted small mb-0">No products at or below {{ $lowStockThreshold }} available units.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Product</th><th>Available</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($lowStockProducts as $product)
                                    <tr><td>{{ $product->name }}</td><td>{{ $product->available_stock }}</td><td><a href="{{ route('admin.products.edit', $product) }}">Edit</a></td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="col-lg-6">
                <h3 class="h6 fw-bold">High stock <span class="badge text-bg-success">{{ $highStockProducts->count() }}</span></h3>
                @if ($highStockProducts->isEmpty())
                    <p class="text-muted small mb-0">No products at or above {{ $highStockThreshold }} available units.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Product</th><th>Available</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($highStockProducts as $product)
                                    <tr><td>{{ $product->name }}</td><td>{{ $product->available_stock }}</td><td><a href="{{ route('admin.products.edit', $product) }}">Edit</a></td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 fw-bold mb-0">Recent orders</h2>
            <a href="{{ route('admin.orders.index') }}">All orders</a>
        </div>
        @if ($recentOrders->isEmpty())
            <p class="text-muted mb-0">No orders yet.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td class="fw-semibold">{{ $order->order_number }}</td>
                                <td>{{ $order->shipping_name ?? $order->user?->name ?? 'Guest' }}</td>
                                <td>{{ $order->created_at->format('M j, Y') }}</td>
                                <td>NPR {{ number_format((float) $order->total_amount, 2) }}</td>
                                <td><span class="badge text-bg-secondary text-capitalize">{{ $order->status }}</span></td>
                                <td><a href="{{ route('admin.orders.show', $order) }}">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
@endsection
