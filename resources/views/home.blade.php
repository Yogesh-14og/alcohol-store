@extends('layouts.app')

@section('title', 'Khanal Liquor - Alcohol Delivery in Kathmandu')
@section('meta_description', 'Browse Khanal Liquor’s online selection of whiskey, beer, wine, vodka and rum. View product details, prices and availability.')
@section('og_image', asset('images/hero-banner.jpg'))

@section('content')

{{-- Hero Section --}}
<section class="hero-outer">
    <div class="container">

        {{-- Inner Banner Box (place your banner image/content here) --}}
        <div class="hero-banner-box">

            <!-- Left: Text Content -->
            <div class="hero-text">
                <h1 class="hero-title">
                    Premium Drinks<br>
                    <span class="text-warning-custom">Delivered Fast.</span>
                </h1>
                <p class="hero-subtitle">
                    From local favorites to imported classics. 
                    Whiskey, Beer, Wine, Vodka, Rum — all at your doorstep.
                </p>
                <div class="hero-buttons mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-warning btn-lg px-4 me-2 fw-bold text-dark border-0">
                        Shop All Drinks →
                    </a>
                    <a href="#offers" class="btn btn-outline-light btn-lg px-4 fw-medium border-2">
                        Special Deals
                    </a>
                </div>
            </div>

        </div>
        {{-- End Inner Banner Box --}}

    </div>
</section>

{{-- Shop by Category (Horizontal scroll) --}}
<section class="storefront-category-section py-4 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="storefront-section-title fw-bold mb-0">
                Shop by Category 
                <span class="text-muted fs-6">({{ $categories->count() }})</span>
            </h3>
            <a href="/categories" class="text-decoration-none">View All →</a>
        </div>
        
        <div class="category-scroll">
            @foreach($categories as $cat)
                <a href="{{ route('categories.show', $cat->slug ?: $cat->id) }}" class="category-card">
                    <div class="category-icon-wrapper">
                        <img src="{{ asset($cat->image) }}" 
                             alt="{{ $cat->name }}" 
                             class="category-img"
                             onerror="this.src='https://placehold.co/50x50/e5e7eb/333?text=Img'">
                    </div>
                    <div class="category-info">
                        <h6 class="mb-0 fw-bold">{{ $cat->name }}</h6>
                        <small class="text-muted">{{ $cat->products_count }} items</small>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Best Sellers --}}
<section class="py-4 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h3 class="storefront-section-title fw-bold mb-0">Best Sellers</h3>
            <a href="{{ route('products.index') }}" class="text-decoration-none">View All →</a>
        </div>
        
        <div class="row g-4">
            @foreach($bestSellers as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card-new">
                        <div class="product-image">
                            <img src="{{ asset($product->image) }}" 
                                 alt="{{ $product->name }}" 
                                 class="product-img"
                                 onerror="this.src='https://placehold.co/120x180/f8f9fa/888?text=Bottle'">
                            <span class="stock-badge {{ $product->available_stock < 1 ? 'stock-badge-out' : '' }}">
                                {{ $product->available_stock > 0 ? $product->available_stock.' in stock' : 'Out of stock' }}
                            </span>
                            @if ($product->abv)
                                <span class="product-abv-badge"><i class="bi bi-cup-straw me-1" aria-hidden="true"></i>{{ $product->abv }} ABV</span>
                            @endif
                        </div>
                        <div class="product-body">
                            <small class="text-muted">{{ $product->category->name ?? 'Liquor' }}</small>
                            <h6 class="product-name"><a class="text-reset text-decoration-none" href="{{ route('products.show', $product->slug ?: $product->id) }}">{{ $product->name }}</a></h6>
                            <p class="product-meta">{{ $product->volume ?: 'Bottle' }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price">NPR {{ number_format($product->price) }}</span>
                                @if ($product->available_stock < 1)
                                    <button class="btn btn-secondary btn-sm" type="button" disabled>Out of stock</button>
                                @elseif (auth()->check())
                                    <form action="{{ route('cart.items.store', $product) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-dark btn-sm" type="submit">Add</button>
                                    </form>
                                @else
                                    <a class="btn btn-dark btn-sm" href="{{ route('login') }}">Sign in to add</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- New Arrivals --}}
<section class="py-4 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h3 class="storefront-section-title fw-bold mb-0">New Arrivals</h3>
            <a href="{{ route('products.new-arrivals') }}" class="text-decoration-none">View All →</a>
        </div>
        
        <div class="row g-4">
            @foreach($newArrivals as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card-new">
                        <div class="product-image">
                            <img src="{{ asset($product->image) }}" 
                                 alt="{{ $product->name }}" 
                                 class="product-img"
                                 onerror="this.src='https://placehold.co/120x180/f8f9fa/888?text=Bottle'">
                            <span class="stock-badge {{ $product->available_stock < 1 ? 'stock-badge-out' : '' }}">
                                {{ $product->available_stock > 0 ? $product->available_stock.' in stock' : 'Out of stock' }}
                            </span>
                            @if ($product->abv)
                                <span class="product-abv-badge"><i class="bi bi-cup-straw me-1" aria-hidden="true"></i>{{ $product->abv }} ABV</span>
                            @endif
                        </div>
                        <div class="product-body">
                            <small class="text-muted">{{ $product->category->name ?? 'Liquor' }}</small>
                            <h6 class="product-name"><a class="text-reset text-decoration-none" href="{{ route('products.show', $product->slug ?: $product->id) }}">{{ $product->name }}</a></h6>
                            <p class="product-meta">{{ $product->volume ?: 'Bottle' }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price">NPR {{ number_format($product->price) }}</span>
                                @if ($product->available_stock < 1)
                                    <button class="btn btn-secondary btn-sm" type="button" disabled>Out of stock</button>
                                @elseif (auth()->check())
                                    <form action="{{ route('cart.items.store', $product) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-dark btn-sm" type="submit">Add</button>
                                    </form>
                                @else
                                    <a class="btn btn-dark btn-sm" href="{{ route('login') }}">Sign in to add</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Hot Deals --}}
<section class="py-4 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h3 class="storefront-section-title fw-bold mb-0">Hot Deals</h3>
            <a href="{{ route('products.index') }}" class="text-decoration-none">View All →</a>
        </div>
        
        <div class="row g-4">
            @foreach($hotDeals as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card-new">
                        <div class="product-image">
                            <img src="{{ asset($product->image) }}" 
                                 alt="{{ $product->name }}" 
                                 class="product-img"
                                 onerror="this.src='https://placehold.co/120x180/f8f9fa/888?text=Bottle'">
                            <span class="stock-badge {{ $product->available_stock < 1 ? 'stock-badge-out' : '' }}">
                                {{ $product->available_stock > 0 ? $product->available_stock.' in stock' : 'Out of stock' }}
                            </span>
                            @if ($product->abv)
                                <span class="product-abv-badge"><i class="bi bi-cup-straw me-1" aria-hidden="true"></i>{{ $product->abv }} ABV</span>
                            @endif
                        </div>
                        <div class="product-body">
                            <small class="text-muted">{{ $product->category->name ?? 'Liquor' }}</small>
                            <h6 class="product-name"><a class="text-reset text-decoration-none" href="{{ route('products.show', $product->slug ?: $product->id) }}">{{ $product->name }}</a></h6>
                            <p class="product-meta">{{ $product->volume ?: 'Bottle' }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price">NPR {{ number_format($product->price) }}</span>
                                @if ($product->available_stock < 1)
                                    <button class="btn btn-secondary btn-sm" type="button" disabled>Out of stock</button>
                                @elseif (auth()->check())
                                    <form action="{{ route('cart.items.store', $product) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-dark btn-sm" type="submit">Add</button>
                                    </form>
                                @else
                                    <a class="btn btn-dark btn-sm" href="{{ route('login') }}">Sign in to add</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
{{-- Brand Showcase --}}
<section class="py-5">
    <div class="container">
        <h3 class="storefront-section-title storefront-section-title-centered fw-bold text-center mb-4">Trusted Brands</h3>
        <div class="row g-3 text-center">
            @php
                $brands = [
                    'Arna', 'Tuborg', 'Old Durbar', 'Ruslan', 
                    'Khukri', 'Gorkha', "Jack Daniel's", 'Chivas',
                    'Absolut', 'Bacardi', 'Jameson', 'Heineken'
                ];
            @endphp
            
            @foreach($brands as $brand)
                <div class="col-4 col-md-2">
                    <div class="brand-card">
                        <strong>{{ $brand }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
<section class="py-5 bg-light">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="row g-4 align-items-center">
                
                <!-- Left Column: Khanal Liquor Info & Contact -->
                <div class="col-lg-7 col-md-6 text-start">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold mb-3">
                        About Our Store
                    </span>
                    <h2 class="fw-bold mb-3 text-dark">Khanal Liquor</h2>
                    <p class="text-muted mb-4 fs-6 lh-lg">
                        We bring you the finest selection of alcoholic beverages, ensuring quality, authenticity, and convenience. Enjoy fast delivery, exclusive deals, and a seamless shopping experience right at your doorstep.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="tel:97000000000" class="btn btn-dark px-4 py-2 fw-medium rounded-3">
                            📞 97000000000
                        </a>
                        <a href="mailto:info@khanalliquor.com" class="btn btn-outline-dark px-4 py-2 fw-medium rounded-3">
                            ✉️ info@khanalliquor.com
                        </a>
                    </div>
                </div>

                <!-- Right Column: Quick Links & Categories -->
                <div class="col-lg-5 col-md-6">
                    <div class="bg-light p-4 rounded-4 border">
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="/about" class="text-decoration-none text-dark d-block p-2 rounded bg-white border-sm hover-shadow text-center fw-medium">
                                    About Us
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('products.index') }}" class="text-decoration-none text-dark d-block p-2 rounded bg-white border-sm text-center fw-medium">
                                    All Products
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="/contact" class="text-decoration-none text-dark d-block p-2 rounded bg-white border-sm text-center fw-medium">
                                    Contact Us
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="storefront-footer py-4">
    <div class="container text-center">
        <p>@2024 Khanal Liquor. All rights reserved.</p>
    </div>
@endsection

@push('styles')
<style>
/* Hero – Outer Section (full page width) */
.hero-outer {
    background: #ffffff;
    padding: 1.25rem 0;
    border-bottom: 1px solid #e5e7eb;
}

/* Hero – Inner Banner Box (smaller, sits inside .container) */
.hero-banner-box {
    position: relative;
    background: #1a1a1a;
    border: none;
    border-radius: 12px;
    padding: 2.5rem 2.5rem;
    min-height: 280px;
    display: flex;
    align-items: center;
    overflow: hidden;
    cursor: pointer;
}

/* Background image lives on ::before so only IT zooms on hover */
.hero-banner-box::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(to right, rgba(0,0,0,0.65) 45%, rgba(0,0,0,0.15) 100%),
        url('/images/hero-banner.jpg');
    background-size: cover;
    background-position: center;
    border-radius: 12px;
    transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                filter 0.7s ease;
    will-change: transform;
    z-index: 0;
}

/* On hover: zoom in slightly and shift position — Ken Burns effect */
.hero-banner-box:hover::before {
    transform: scale(1.07) translateX(-8px);
    filter: brightness(0.9);
}

/* Keep text content above the ::before pseudo-element */
.hero-banner-box > * {
    position: relative;
    z-index: 1;
}

.hero-text {
    flex: 1;
    color: #ffffff;
    max-width: 620px;
    text-align: left;
}
.hero-title {
    font-size: 2.6rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 0.75rem;
    color: #ffffff;
}
.text-warning-custom {
    color: #f59e0b;
}
.hero-subtitle {
    font-size: 1rem;
    color: #e5e7eb;
    margin-bottom: 1.5rem;
    max-width: 520px;
}
.hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
}

.category-scroll {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: 1rem;
    scrollbar-width: thin;
}
.category-scroll::-webkit-scrollbar {
    height: 6px;
}
.category-scroll::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}
.category-card {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s;
    min-width: 100px;
}
.category-card:hover {
    border-color: #1a1a1a;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.category-icon-wrapper {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 6px;
}
.category-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* Product Card */
.product-card-new {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.product-card-new:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
}
.product-image {
    background: #f8f9fa;
    position: relative;
    height: 170px;
    overflow: hidden;
}
.product-img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
}
.stock-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dcfce7;
    color: #166534;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}
.stock-badge-out {
    background: #fee2e2;
    color: #991b1b;
}
.product-body {
    padding: 0.875rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.product-name {
    font-weight: 600;
    margin: 0.25rem 0;
}
.product-meta {
    font-size: 0.85rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
}
.product-price {
    font-weight: 700;
    color: #f59e0b;
    font-size: 1rem;
}

/* Brand Card */
.brand-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 1rem;
    transition: all 0.3s;
}
.brand-card:hover {
    border-color: #1a1a1a;
    transform: translateY(-2px);
}

/* Mobile */
@media (max-width: 768px) {
    .product-image {
        height: 150px;
    }
    .product-body {
        padding: 0.75rem;
    }
    .hero-inner {
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem 0;
        min-height: 220px;
    }
    .hero-title {
        font-size: 2rem;
    }
    .hero-buttons {
        flex-wrap: wrap;
        gap: 0.75rem;
    }
}
.w-bold mb-3 {
flex: 1;
text-align: center;
display: flex;
justify-content: center;
}
</style>
@endpush