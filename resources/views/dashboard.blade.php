@extends('layouts.app')

@section('title', 'My Dashboard - Khanal Liquor')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="mb-4">
        <p class="text-uppercase text-primary fw-semibold small mb-1">Your account</p>
        <h1 class="fw-bold mb-1">Welcome, {{ auth()->user()->name }}</h1>
        <p class="text-muted mb-0">Manage your orders and account preferences.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success rounded-3" role="status">{{ session('status') }}</div>
    @endif
    @if (session('password_status'))
        <div class="alert alert-success rounded-3" role="status">{{ session('password_status') }}</div>
    @endif

    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="list-group list-group-flush rounded-4">
                    <a class="list-group-item list-group-item-action py-3" href="#orders">
                        <i class="bi bi-box-seam me-2"></i>Order status
                    </a>
                    <a class="list-group-item list-group-item-action py-3" href="#appearance">
                        <i class="bi bi-palette me-2"></i>Appearance
                    </a>
                    <a class="list-group-item list-group-item-action py-3" href="#security">
                        <i class="bi bi-shield-lock me-2"></i>Password &amp; security
                    </a>
                </div>
            </div>
        </aside>

        <div class="col-lg-9">
            <section id="orders" class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="h4 fw-bold mb-1">Your orders</h2>
                            <p class="text-muted mb-0">Check the latest status of your purchases.</p>
                        </div>
                        <i class="bi bi-box-seam fs-3 text-primary"></i>
                    </div>

                    @if ($orders->isEmpty())
                        <div class="text-center bg-light rounded-3 p-4">
                            <i class="bi bi-bag fs-2 text-muted"></i>
                            <p class="fw-semibold mt-2 mb-1">No orders yet</p>
                            <p class="text-muted small mb-0">Your order status will appear here after you place an order.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Order</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Payment</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        @php
                                            $orderStatusClass = match (strtolower($order->status)) {
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                                'shipped' => 'primary',
                                                'processing' => 'info',
                                                default => 'warning',
                                            };
                                        @endphp
                                        <tr>
                                            <th scope="row">{{ $order->order_number }}</th>
                                            <td>{{ $order->created_at->format('M j, Y') }}</td>
                                            <td>NPR {{ number_format((float) $order->total_amount, 2) }}</td>
                                            <td class="text-capitalize">{{ $order->effective_payment_status }}</td>
                                            <td><span class="badge text-bg-{{ $orderStatusClass }} text-capitalize">{{ $order->status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $orders->links() }}</div>
                    @endif
                </div>
            </section>

            <section id="appearance" class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="h4 fw-bold mb-1">Appearance</h2>
                            <p class="text-muted mb-0">Your choice is saved to your account and follows you across devices.</p>
                        </div>
                        <i class="bi bi-palette fs-3 text-primary"></i>
                    </div>

                    <form action="{{ route('dashboard.theme.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            @foreach (['light' => ['Light', 'bi-sun'], 'dark' => ['Dark', 'bi-moon-stars']] as $theme => [$label, $icon])
                                <div class="col-sm-6">
                                    <label class="theme-choice d-flex align-items-center gap-3 border rounded-3 p-3 h-100">
                                        <input class="form-check-input mt-0" type="radio" name="theme" value="{{ $theme }}" @checked(auth()->user()->theme === $theme)>
                                        <i class="bi {{ $icon }} fs-4"></i>
                                        <span class="fw-semibold">{{ $label }} theme</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('theme')
                            <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
                        @enderror
                        <button class="btn btn-primary rounded-3 mt-3" type="submit">Save theme</button>
                    </form>
                </div>
            </section>

            <section id="security" class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="h4 fw-bold mb-1">Change password</h2>
                            <p class="text-muted mb-0">Confirm your current password before choosing a new one.</p>
                        </div>
                        <i class="bi bi-shield-lock fs-3 text-primary"></i>
                    </div>

                    <form action="{{ route('dashboard.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">Current password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold">New password</label>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirm new password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                            </div>
                        </div>
                        <button class="btn btn-primary rounded-3" type="submit">Update password</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .theme-choice {
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .theme-choice:has(input:checked) {
        border-color: var(--bs-primary) !important;
        box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.12);
    }
</style>
@endpush
