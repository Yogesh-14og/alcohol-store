@extends('layouts.app')

@section('title', 'Checkout - Khanal Liquor')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="mb-4">
        <p class="text-uppercase text-primary fw-semibold small mb-1">Almost there</p>
        <h1 class="fw-bold mb-1">Checkout</h1>
        <p class="text-muted mb-0">Add your delivery details and choose a payment method.</p>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf
        @if ($unavailableItems->isNotEmpty() || $errors->has('stock'))
            <div class="alert alert-danger rounded-3 mb-4" role="alert">
                @if ($errors->has('stock'))
                    {{ $errors->first('stock') }}
                @else
                    Some cart quantities are no longer available. Return to your cart and update them before placing the order.
                @endif
            </div>
        @endif
        <div class="row g-4">
            <div class="col-lg-7">
                <section class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Delivery details</h2>
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Full name</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', auth()->user()->name) }}"
                                autocomplete="name"
                                maxlength="255"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">Phone number</label>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}"
                                autocomplete="tel"
                                maxlength="30"
                                required
                            >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="delivery_address" class="form-label fw-semibold">Delivery address</label>
                            <textarea
                                id="delivery_address"
                                name="delivery_address"
                                class="form-control @error('delivery_address') is-invalid @enderror"
                                rows="3"
                                autocomplete="street-address"
                                maxlength="2000"
                                required
                            >{{ old('delivery_address') }}</textarea>
                            @error('delivery_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Payment method</h2>
                        <label class="d-flex align-items-center gap-3 border rounded-3 p-3 mb-3">
                            <input
                                class="form-check-input mt-0"
                                type="radio"
                                name="payment_method"
                                value="cod"
                                @checked(old('payment_method', 'cod') === 'cod')
                                required
                            >
                            <span>
                                <span class="fw-semibold d-block">Cash on delivery</span>
                                <span class="text-muted small">Pay the delivery person when your order arrives.</span>
                            </span>
                        </label>
                        <label class="d-flex align-items-center gap-3 border rounded-3 p-3 text-muted">
                            <input class="form-check-input mt-0" type="radio" disabled>
                            <span>
                                <span class="fw-semibold d-block">Fonepay</span>
                                <span class="small">Unavailable until the official merchant integration is configured.</span>
                            </span>
                        </label>
                        @error('payment_method')
                            <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </section>
            </div>

            <div class="col-lg-5">
                <section class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Order summary</h2>
                        <div class="vstack gap-3">
                            @foreach ($items as $item)
                                <div class="d-flex justify-content-between gap-3">
                                    <span>{{ $item->product->name }} <span class="text-muted">× {{ $item->quantity }}</span></span>
                                    <span class="text-nowrap">NPR {{ number_format((float) $item->product->price * $item->quantity, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between border-top pt-3 mt-3">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold">NPR {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <button class="btn btn-primary w-100 rounded-3 mt-4" type="submit" @disabled($unavailableItems->isNotEmpty())>Place order · Cash on delivery</button>
                        <a href="{{ route('cart.index') }}" class="btn btn-link w-100 mt-2">Back to cart</a>
                    </div>
                </section>
            </div>
        </div>
    </form>
    <div class="mt-4">
        <p class="text-muted small mb-0">By placing an order, you agree to our <a href="{{ route('terms') }}">Terms &amp; Conditions</a>.</p>
    </div>
</div>
@endsection
