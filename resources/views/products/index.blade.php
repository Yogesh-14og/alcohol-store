@extends('layouts.app')

@section('title', $title.' - Khanal Liquor')
@section('meta_description', $description.' Browse product details, availability and prices at Khanal Liquor.')

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="mb-4">
            <a href="{{ route('home') }}" class="text-decoration-none small">← Back to home</a>
            <p class="text-uppercase text-primary fw-semibold small mt-3 mb-1">Shop</p>
            <h1 class="storefront-page-title fw-bold mb-1">{{ $title }}</h1>
            <p class="text-muted mb-0">{{ $description }} <span class="ms-1">({{ $products->total() }} {{ Str::plural('product', $products->total()) }})</span></p>
        </div>

        <form action="{{ route($catalogRoute) }}" method="GET" class="storefront-filter card border-0 shadow-sm rounded-4 mb-4">
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
                            <a class="btn btn-outline-secondary" href="{{ route($catalogRoute) }}">Clear</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        @if ($products->isEmpty())
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center p-5">
                    <i class="bi bi-box-seam display-5 text-muted"></i>
                    <h2 class="h5 fw-bold mt-3">No products to show</h2>
                    <p class="text-muted mb-0">
                        @if ($title === 'New Arrivals')
                            Check back soon for new additions.
                        @else
                            Products will appear here when they are available.
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach ($products as $product)
                    @include('products.partials.card', ['product' => $product])
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
        background: #fff;
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
