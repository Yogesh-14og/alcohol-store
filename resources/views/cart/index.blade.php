@extends('layouts.app')

@section('title', 'Your Cart - Khanal Liquor')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="mb-4">
        <p class="text-uppercase text-primary fw-semibold small mb-1">Your shopping</p>
        <h1 class="fw-bold mb-1">Your cart</h1>
        <p class="text-muted mb-0">Your cart is saved to your account.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success rounded-3" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->has('cart'))
        <div class="alert alert-danger rounded-3" role="alert">{{ $errors->first('cart') }}</div>
    @endif

    @if ($items->isEmpty())
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center p-5">
                <i class="bi bi-cart3 display-4 text-muted"></i>
                <h2 class="h4 fw-bold mt-3">Your cart is empty</h2>
                <p class="text-muted">Browse our drinks and add something you like.</p>
                <a href="{{ route('home') }}" class="btn btn-primary rounded-3 px-4">Continue shopping</a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-0">
                        @foreach ($items as $item)
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 p-3 p-md-4 {{ $loop->last ? '' : 'border-bottom' }}">
                                <img
                                    src="{{ $item->product->image ? asset($item->product->image) : 'https://placehold.co/100x100/f8f9fa/888?text=Drink' }}"
                                    alt="{{ $item->product->name }}"
                                    class="rounded-3 border object-fit-contain bg-light p-2"
                                    width="96"
                                    height="96"
                                >
                                <div class="flex-grow-1">
                                    <h2 class="h6 fw-bold mb-1">{{ $item->product->name }}</h2>
                                    @if ($item->product->volume)
                                        <p class="text-muted small mb-1">{{ $item->product->volume }}</p>
                                    @endif
                                    <p class="fw-semibold mb-0">NPR {{ number_format((float) $item->product->price, 2) }}</p>
                                    @if ($item->quantity > $item->product->available_stock)
                                        <p class="text-danger small mb-0">Only {{ $item->product->available_stock }} available</p>
                                    @else
                                        <p class="text-muted small mb-0">{{ $item->product->available_stock }} available</p>
                                    @endif
                                </div>
                                <form action="{{ route('cart.items.update', $item->id) }}" method="POST" class="d-flex align-items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <label class="form-label small mb-0" for="quantity-{{ $item->id }}">Qty</label>
                                    <input
                                        id="quantity-{{ $item->id }}"
                                        class="form-control form-control-sm"
                                        type="number"
                                        name="quantity"
                                        value="{{ $item->quantity }}"
                                        min="1"
                                        max="99"
                                        required
                                        style="width: 76px;"
                                    >
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Update</button>
                                </form>
                                <div class="text-sm-end">
                                    <p class="fw-bold mb-1">NPR {{ number_format((float) $item->product->price * $item->quantity, 2) }}</p>
                                    <form action="{{ route('cart.items.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-sm text-danger text-decoration-none p-0">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('quantity')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Order summary</h2>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span>NPR {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-3 mt-3">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold">NPR {{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if ($items->contains(fn ($item) => $item->quantity > $item->product->available_stock))
                            <button class="btn btn-secondary w-100 rounded-3 mt-4" type="button" disabled>Update quantities to continue</button>
                        @else
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 rounded-3 mt-4">Proceed to checkout</a>
                        @endif
                        <a href="{{ route('home') }}" class="btn btn-link w-100 mt-2">Continue shopping</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
