<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-admin-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Admin Panel') - Khanal Liquor</title>
    <script>
        try {
            document.documentElement.dataset.adminTheme = localStorage.getItem('admin-theme') === 'dark' ? 'dark' : 'light';
        } catch {
            document.documentElement.dataset.adminTheme = 'light';
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="admin-layout bg-light">
    @if (session('status'))
        <div class="admin-status-toast-container position-fixed top-0 end-0 p-3">
            <div id="adminStatusToast" class="alert alert-success alert-dismissible fade show shadow mb-0" role="status" aria-live="polite">
                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>{{ session('status') }}
                <button type="button" class="btn-close" id="dismissAdminStatusToast" aria-label="Close"></button>
            </div>
        </div>
        <script>
            (() => {
                const toast = document.getElementById('adminStatusToast');
                const dismissButton = document.getElementById('dismissAdminStatusToast');
                const dismissToast = () => {
                    toast.classList.remove('show');
                    window.setTimeout(() => toast.remove(), 150);
                };

                dismissButton.addEventListener('click', dismissToast);
                window.setTimeout(dismissToast, 5000);
            })();
        </script>
    @endif
    <nav class="admin-navbar navbar bg-dark navbar-dark shadow-sm">
        <div class="container-fluid px-3 gap-3">
            <button class="admin-menu-toggle btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminNavigation" aria-controls="adminNavigation" aria-label="Open admin navigation">
                <i class="bi bi-list fs-5" aria-hidden="true"></i>
                
            </button>
            <a class="navbar-brand fw-bold mb-0" href="{{ route('admin.dashboard') }}">Khanal Liquor <span class="badge text-bg-warning">Admin</span></a>
            <div class="dropdown ms-auto">
                <button class="admin-profile-toggle btn btn-outline-light dropdown-toggle" type="button" id="adminProfileMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-sm-1" aria-hidden="true"></i>
                    <span>{{ auth('admin')->user()->name }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="adminProfileMenu">
                    <div class="px-3 py-2">
                        <div class="fw-semibold">{{ auth('admin')->user()->name }}</div>
                        <small class="text-muted">{{ auth('admin')->user()->email }}</small>
                    </div>
                    <hr class="dropdown-divider">
                    <button class="dropdown-item" type="button" data-admin-theme-toggle>
                        <i class="bi bi-moon-stars me-2" aria-hidden="true" data-admin-theme-icon></i>
                        <span data-admin-theme-label>Dark theme</span>
                    </button>
                    <hr class="dropdown-divider">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="admin-navigation offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="adminNavigation" aria-labelledby="adminNavigationTitle">
        <div class="offcanvas-header border-bottom border-secondary">
            <h2 class="offcanvas-title h5 fw-bold mb-0" id="adminNavigationTitle">Admin menu</h2>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <nav class="nav nav-pills flex-column gap-1" aria-label="Admin navigation">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="nav-link" href="{{ route('admin.products.index') }}">Products</a>
                <a class="nav-link" href="{{ route('admin.categories.index') }}">Categories</a>
                <a class="nav-link" href="{{ route('admin.orders.index') }}">Orders</a>
                <a class="nav-link" href="{{ route('admin.purchase-ledger.index') }}">Kharid Khata</a>
                <a class="nav-link" href="{{ route('admin.sales-ledger.index') }}">Bikri Khata</a>
                <a class="nav-link" href="{{ route('admin.users.index') }}">Customers</a>
                <a class="nav-link" href="{{ route('admin.suppliers.index') }}">Suppliers</a>
                <a class="nav-link" href="{{ route('home') }}" target="_blank" rel="noopener">View store</a>
            </nav>
        </div>
    </div>

    <main class="container py-4 py-lg-5">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
