<!-- Top Announcement Bar -->
<div class="bg-light border-bottom py-1 text-sm text-secondary">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
             Cash on Delivery inside <strong>Kathmandu Valley</strong>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="#" class="text-decoration-none text-secondary">Track Order</a>
            <span>|</span>
            <span>NPR (Rs.)</span>
        </div>
    </div>
</div>

<!-- Main Navigation Bar -->
<header class="py-3 bg-white border-bottom">
    <div class="container d-flex align-items-center justify-content-between gap-4">
        <a href="{{ url('/') }}" class="navbar-brand fw-bold fs-2 text-dark m-0">
            KHANAL LIQUOR
        </a>

        <!-- Search Bar -->
        <div class="flex-grow-1 mx-lg-4" style="max-width: 650px;">
            <form action="#" method="GET">
                <div class="input-group bg-light rounded-pill p-1 border">
                    <input type="text" class="form-control bg-transparent border-0 shadow-none ps-3" placeholder="Search drinks...">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Right Icons -->
        <div class="d-flex align-items-center gap-3">
            @guest
                <a href="{{ route('login') }}" class="text-dark text-decoration-none fw-semibold">Sign In</a>
            @else
                <div class="dropdown">
                    <a href="#" class="text-dark text-decoration-none fw-semibold dropdown-toggle" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userMenu">
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
            <a href="#" class="btn btn-light rounded-pill px-3 border">Cart</a>
        </div>
    </div>
</header>
    <!-- Category Navigation Bar -->
<nav class="bg-white border-bottom py-2 shadow-sm d-none d-md-block">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
                @php
                $categories = \App\Models\Category::all(); 
                @endphp
            
            <div class="dropdown">
                <button class="btn btn-primary rounded-3 px-3 py-2 d-flex align-items-center gap-2 fw-semibold dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-list fs-5"></i> All Categories
                </button>
                <ul class="dropdown-menu shadow border-0 mt-2" aria-labelledby="categoryDropdown">
                    @forelse($categories as $category)
                        <li><a class="dropdown-item py-2" href="{{ url('/category/' . $category->slug ?? $category->id) }}">{{ $category->name }}</a></li>
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
            <div class="d-flex align-items-center gap-4 ms-2 fw-medium text-nowrap" style="font-size: 0.95rem;">
                <a href="#" class="text-decoration-none text-secondary">Whiskey</a>
                <a href="#" class="text-decoration-none text-secondary">Beer</a>
                <a href="#" class="text-decoration-none text-secondary">Wine</a>
                <a href="#" class="text-decoration-none text-secondary">Vodka</a>
                <a href="#" class="text-decoration-none text-secondary">Rum</a>
                <a href="" class="text-decoration-none text-secondary dropdown-btn" onclick="toggleDropdown()">More<span class="arrow">&#x25BC;</span></a>
            </div>
        </div>

        <!-- Right Side Badges -->
        <div class="d-flex align-items-center gap-3">
            <a href="#" class="text-decoration-none text-secondary fw-medium" style="font-size: 0.95rem;">
                New Arrivals
            </a>
            <a href="#" class="text-decoration-none text-secondary fw-medium" style="font-size: 0.95rem;">All Products</a>
        </div>
    </div>
</nav>
