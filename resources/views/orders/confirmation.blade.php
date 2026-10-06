@extends('layouts.app')

@section('title', 'Order Confirmed - Khanal Liquor')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 900px;">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success mb-3" style="width: 72px; height: 72px;">
                <i class="bi bi-check-lg fs-1"></i>
            </div>
            <p class="text-uppercase text-success fw-semibold small mb-1">Thank you for your order</p>
            <h1 class="fw-bold mb-2">Order placed successfully</h1>
            <p class="text-muted mb-0">We have received your order and will contact you about delivery.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 pb-3 border-bottom">
                    <div>
                        <p class="text-muted small mb-1">Order number</p>
                        <p class="fw-bold fs-5 mb-0">{{ $order->order_number }}</p>
                    </div>
                    <div class="text-sm-end">
                        <p class="text-muted small mb-1">Placed on</p>
                        <p class="fw-semibold mb-0">{{ $order->created_at->format('M j, Y · g:i A') }}</p>
                    </div>
                    <div class="text-sm-end">
                        <p class="text-muted small mb-1">Order status</p>
                        <span class="badge text-bg-warning text-capitalize">{{ $order->status }}</span>
                    </div>
                </div>

                <h2 class="h5 fw-bold mt-4 mb-3">Items ordered</h2>
                <div class="vstack gap-3">
                    @foreach ($order->items as $item)
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <p class="fw-semibold mb-1">{{ $item->product_name }}</p>
                                <p class="text-muted small mb-0">NPR {{ number_format((float) $item->unit_price, 2) }} × {{ $item->quantity }}</p>
                            </div>
                            <p class="fw-semibold text-nowrap mb-0">NPR {{ number_format((float) $item->unit_price * $item->quantity, 2) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between border-top pt-3 mt-4">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold">NPR {{ number_format((float) $order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <section class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-primary"></i>Delivery details</h2>
                        <p class="fw-semibold mb-1">{{ $order->shipping_name }}</p>
                        <p class="text-muted mb-1">{{ $order->shipping_phone }}</p>
                        <p class="text-muted mb-0">{{ $order->shipping_address }}</p>
                    </div>
                </section>
            </div>
            <div class="col-md-6">
                <section class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3"><i class="bi bi-wallet2 me-2 text-primary"></i>Payment</h2>
                        <p class="fw-semibold mb-1">Cash on delivery</p>
                        <p class="text-muted mb-0">Payment is due when your order is delivered.</p>
                    </div>
                </section>
            </div>
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
            <a href="{{ route('dashboard.index') }}#orders" class="btn btn-primary rounded-3 px-4">View my orders</a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-3 px-4">Continue shopping</a>
        </div>
    </div>
</div>
@endsection
