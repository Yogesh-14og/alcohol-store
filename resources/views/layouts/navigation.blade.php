<!-- Top Announcement Bar -->
<div class="storefront-announcement py-1 text-sm">
    <div class="container storefront-announcement-inner d-flex justify-content-between align-items-center">
        <div>
             Cash on Delivery inside <strong>Kathmandu Valley</strong>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ auth()->check() ? route('dashboard.index').'#orders' : route('login') }}" class="text-decoration-none text-secondary">Track Order</a>
            <span>|</span>
            <span>NPR (Rs.)</span>
        </div>
    </div>
</div>

<!-- Main Navigation Bar -->
<header class="storefront-main-header py-3 bg-white">
    <div class="container storefront-header-inner d-flex align-items-center justify-content-between gap-4">
        <a href="{{ url('/') }}" class="navbar-brand fw-bold fs-2 text-dark m-0">
            <span class="storefront-brand">
                <i class="bi bi-cup-straw text-primary" aria-hidden="true"></i>
                <span>
                    KHANAL LIQUOR
                    <small class="storefront-brand-tagline">Your neighbourhood bottle shop</small>
                </span>
            </span>
        </a>

        <!-- Search Bar -->
        <div class="storefront-search mx-lg-4">
            <form action="#" method="GET">
                <div class="input-group bg-light rounded-pill p-1 border">
                    <input type="text" class="form-control bg-transparent border-0 shadow-none ps-3" placeholder="Search drinks...">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Right Icons -->
        <div class="storefront-header-actions d-flex align-items-center gap-3">
            @guest
                <a href="{{ route('login') }}" class="text-dark text-decoration-none fw-semibold">Sign In</a>
            @else
                <div class="dropdown">
                    <a href="#" class="text-dark text-decoration-none fw-semibold dropdown-toggle" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userMenu">
                        <li><a class="dropdown-item" href="{{ route('dashboard.index') }}">My Dashboard</a></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            @endguest
            <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="btn btn-light rounded-pill px-3 border">
                Cart
                @auth
                    <span class="badge text-bg-primary rounded-pill">{{ auth()->user()->cartItems()->sum('quantity') }}</span>
                @endauth
            </a>
        </div>
    </div>
</header>
    <!-- Category Navigation Bar -->
<nav class="storefront-category-nav bg-white py-2 shadow-sm">
    <div class="container storefront-category-nav-inner d-flex align-items-center justify-content-between">
        <div class="storefront-category-nav-groups d-flex align-items-center gap-3">
                @php
                $categories = \App\Models\Category::all(); 
                @endphp
            
            <div class="dropdown">
                <button class="btn btn-primary rounded-3 px-3 py-2 d-flex align-items-center gap-2 fw-semibold dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-list fs-5"></i> All Categories
                </button>
                <ul class="dropdown-menu shadow border-0 mt-2" aria-labelledby="categoryDropdown">
                    @forelse($categories as $category)
                        <li><a class="dropdown-item py-2" href="{{ route('categories.show', $category->slug ?: $category->id) }}">{{ $category->name }}</a></li>
                        @empty
                        <li><a class="dropdown-item py-2" href="#">Whiskey</a></li>
                        <li><a class="dropdown-item py-2" href="#">Beer</a></li>
                        <li><a class="dropdown-item py-2" href="#">Wine</a></li>
                        <li><a class="dropdown-item py-2" href="#">Vodka</a></li>
                        <li><a class="dropdown-item py-2" href="#">Rum</a></li>
                    @endforelse
                    
                </ul>
            </div>

            <!-- Category Links -->
            <div class="storefront-category-links d-flex align-items-center gap-4 ms-2 fw-medium text-nowrap">
                @foreach ($categories->take(5) as $category)
                    <a href="{{ route('categories.show', $category->slug ?: $category->id) }}" class="text-decoration-none text-secondary">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>

        <!-- Right Side Badges -->
        <div class="storefront-catalog-links d-flex align-items-center gap-3">
            <a href="{{ route('products.new-arrivals') }}" class="text-decoration-none text-secondary fw-medium">
                New Arrivals
            </a>
            <a href="{{ route('products.index') }}" class="text-decoration-none text-secondary fw-medium">All Products</a>
        </div>
    </div>
</nav>
