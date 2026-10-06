@extends('layouts.app')

@section('title', $category->name.' - Khanal Liquor')
@section('meta_description', 'Browse '.$category->name.' at Khanal Liquor. View product details, alcohol percentage and pricing.')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
            <div>
                <a href="{{ route('home') }}" class="text-decoration-none small">← Continue shopping</a>
                <p class="text-uppercase text-primary fw-semibold small mb-1 mt-3">Shop by category</p>
                <h1 class="storefront-page-title fw-bold mb-1">{{ $category->name }}</h1>
                <p class="text-muted mb-0">{{ $products->total() }} {{ Str::plural('product', $products->total()) }} in this category</p>
            </div>
            @if ($category->image)
                <img src="{{ asset($category->image) }}" alt="" class="rounded-3 border bg-light p-2" style="width: 88px; height: 88px; object-fit: contain;">
            @endif
        </div>

        <form action="{{ route('categories.show', $category->slug ?: $category->id) }}" method="GET" class="storefront-filter card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-3">Filter by alcohol percentage (ABV)</h2>
                <div class="row g-3 align-items-end">
                    <div class="col-sm-4 col-md-3">
                        <label for="min_abv" class="form-label">Minimum ABV (%)</label>
                        <input id="min_abv" name="min_abv" type="number" min="0" max="100" step="0.1" class="form-control" value="{{ request('min_abv') }}" placeholder="0">
                    </div>
                    <div class="col-sm-4 col-md-3">
                        <label for="max_abv" class="form-label">Maximum ABV (%)</label>
                        <input id="max_abv" name="max_abv" type="number" min="0" max="100" step="0.1" class="form-control" value="{{ request('max_abv') }}" placeholder="100">
                    </div>
                    <div class="col-sm-4 col-md-auto">
                        <button class="btn btn-primary" type="submit">Apply filter</button>
                        @if (request()->filled('min_abv') || request()->filled('max_abv'))
                            <a class="btn btn-outline-secondary" href="{{ route('categories.show', $category->slug ?: $category->id) }}">Clear</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        @if ($products->isEmpty())
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center p-5">
                    <i class="bi bi-box-seam display-5 text-muted"></i>
                    <h2 class="h5 fw-bold mt-3">No products in this category yet</h2>
                    <p class="text-muted mb-0">Please check back later or browse another category.</p>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach ($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <article class="product-card-new">
                            <div class="product-image">
                                <a href="{{ route('products.show', $product->slug ?: $product->id) }}" aria-label="View {{ $product->name }}">
                                    <img
                                        src="{{ $product->image ? asset($product->image) : 'https://placehold.co/120x180/f8f9fa/888?text=Bottle' }}"
                                        alt="{{ $product->name }}"
                                        class="product-img"
                                        onerror="this.src='https://placehold.co/120x180/f8f9fa/888?text=Bottle'"
                                    >
                                </a>
                                <span class="stock-badge {{ $product->available_stock < 1 ? 'stock-badge-out' : '' }}">
                                    {{ $product->available_stock > 0 ? $product->available_stock.' in stock' : 'Out of stock' }}
                                </span>
                                @if ($product->abv)
                                    <span class="product-abv-badge"><i class="bi bi-cup-straw me-1" aria-hidden="true"></i>{{ $product->abv }} ABV</span>
                                @endif
                            </div>
                            <div class="product-body">
                                <div>
                                    <small class="text-muted">{{ $category->name }}</small>
                                    <h2 class="product-name h6">
                                        <a class="text-reset text-decoration-none" href="{{ route('products.show', $product->slug ?: $product->id) }}">{{ $product->name }}</a>
                                    </h2>
                                    <p class="product-meta">{{ $product->volume ?: 'Bottle' }}</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <span class="product-price">NPR {{ number_format((float) $product->price) }}</span>
                                    @if ($product->available_stock < 1)
                                        <button class="btn btn-secondary btn-sm" type="button" disabled>Out of stock</button>
                                    @elseif (auth()->check())
                                        <form action="{{ route('cart.items.store', $product) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-dark btn-sm" type="submit">Add</button>
                                        </form>
                                    @else
                                        <a class="btn btn-dark btn-sm" href="{{ route('login') }}">Sign in to add</a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">{{ $products->links() }}</div>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
    .product-card-new {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .product-card-new:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }

    .product-image {
        background: #f8f9fa;
        height: 170px;
        overflow: hidden;
        position: relative;
    }

    .product-img {
        display: block;
        height: 100%;
        object-fit: contain;
        object-position: center;
        width: 100%;
    }

    .stock-badge {
        background: #dcfce7;
        border-radius: 9999px;
        color: #166534;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        position: absolute;
        right: 10px;
        top: 10px;
    }

    .stock-badge-out {
        background: #fee2e2;
        color: #991b1b;
    }

    .product-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        justify-content: space-between;
        padding: 0.875rem;
    }

    .product-name {
        font-weight: 600;
        margin: 0.25rem 0;
    }

    .product-meta {
        color: #6b7280;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }

    .product-price {
        color: #f59e0b;
        font-size: 1rem;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .product-image {
            height: 150px;
        }

        .product-body {
            padding: 0.75rem;
        }
    }
</style>
@endpush
