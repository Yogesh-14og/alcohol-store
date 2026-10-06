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
                <small class="text-muted">{{ $product->category?->name ?? 'Liquor' }}</small>
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
