@extends('layouts.app')

@section('title', $product->name.' - Khanal Liquor')
@section('meta_description', $metaDescription)
@section('canonical_url', $canonicalUrl)
@section('og_type', 'product')
@if ($product->image)
    @section('og_image', asset($product->image))
@endif

@push('seo')
    @php
        $productStructuredData = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $metaDescription,
            'image' => $product->image ? asset($product->image) : null,
            'sku' => $product->slug ?: (string) $product->id,
            'category' => $product->category?->name,
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => 'NPR',
                'price' => number_format((float) $product->price, 2, '.', ''),
                'availability' => $product->available_stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    @endphp
    <script type="application/ld+json">{!! json_encode($productStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
<section class="py-4 py-lg-5">
    <div class="container">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                @if ($product->category)
                    <li class="breadcrumb-item">
                        <a href="{{ route('categories.show', $product->category->slug ?: $product->category->id) }}">{{ $product->category->name }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        <article class="row g-4 g-lg-5 align-items-start">
            <div class="col-md-6">
                <div class="rounded-4 bg-light border p-3 p-lg-5 text-center">
                    <img
                        src="{{ $product->image ? asset($product->image) : 'https://placehold.co/480x600/f8f9fa/888?text=Bottle' }}"
                        alt="{{ $product->name }}"
                        class="img-fluid"
                        style="max-height: 520px; object-fit: contain;"
                    >
                </div>
            </div>
            <div class="col-md-6">
                @if ($product->category)
                    <a class="text-uppercase text-primary fw-semibold small text-decoration-none" href="{{ route('categories.show', $product->category->slug ?: $product->category->id) }}">
                        {{ $product->category->name }}
                    </a>
                @endif
                <h1 class="storefront-page-title fw-bold mt-2">{{ $product->name }}</h1>

                <p class="h3 fw-bold text-warning-emphasis mb-3">NPR {{ number_format((float) $product->price, 2) }}</p>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    @if ($product->volume)
                        <span class="badge text-bg-light border">{{ $product->volume }}</span>
                    @endif
                    @if ($product->abv)
                        <span class="badge text-bg-light border">{{ $product->abv }} ABV</span>
                    @endif
                    <span class="badge {{ $product->available_stock > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">
                        {{ $product->available_stock > 0 ? 'In stock' : 'Out of stock' }}
                    </span>
                </div>

                @if ($product->available_stock < 1)
                    <button class="btn btn-secondary btn-lg" type="button" disabled>Out of stock</button>
                @elseif (auth()->check())
                    <form action="{{ route('cart.items.store', $product) }}" method="POST">
                        @csrf
                        <button class="btn btn-dark btn-lg" type="submit">Add to cart</button>
                    </form>
                @else
                    <a class="btn btn-dark btn-lg" href="{{ route('login') }}">Sign in to add</a>
                @endif

                <section class="mt-5">
                    <h2 class="h5 fw-bold">Product details</h2>
                    <dl class="row mb-0">
                        @if ($product->category)
                            <dt class="col-sm-4">Category</dt>
                            <dd class="col-sm-8">{{ $product->category->name }}</dd>
                        @endif
                        @if ($product->volume)
                            <dt class="col-sm-4">Volume</dt>
                            <dd class="col-sm-8">{{ $product->volume }}</dd>
                        @endif
                        @if ($product->abv)
                            <dt class="col-sm-4">Alcohol percentage</dt>
                            <dd class="col-sm-8">{{ $product->abv }} ABV</dd>
                        @endif
                        <dt class="col-sm-4">Availability</dt>
                        <dd class="col-sm-8">{{ $product->available_stock > 0 ? 'In stock' : 'Out of stock' }}</dd>
                    </dl>
                </section>
            </div>
        </article>
    </div>
</section>
@endsection
