@extends('layouts.app')

@section('title', 'Sign In - Khanal Liquor')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark mb-1">Welcome Back</h3>
                        <p class="text-muted small">Sign in to continue shopping</p>
                    </div>

                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf

                        <!-- Email Input -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold small">Email Address</label>
                            <input type="email" name="email" id="email"
                                class="form-control rounded-3 @error('email') is-invalid @enderror"
                                placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label fw-semibold small mb-0">Password</label>
                                <a href="{{ route('password.request') }}" class="text-decoration-none small text-muted">Forgot?</a>
                            </div>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    class="form-control rounded-start-3 @error('password') is-invalid @enderror"
                                    placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary rounded-end-3 border-start-0"
                                    type="button" id="togglePassword"
                                    style="border-color: #dee2e6;"
                                    onclick="
                                        const input = document.getElementById('password');
                                        const icon  = document.getElementById('eyeIcon');
                                        if (input.type === 'password') {
                                            input.type = 'text';
                                            icon.classList.replace('bi-eye', 'bi-eye-slash');
                                        } else {
                                            input.type = 'password';
                                            icon.classList.replace('bi-eye-slash', 'bi-eye');
                                        }
                                    ">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-4 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label small text-muted" for="remember">Remember me</label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold rounded-3 mb-3">
                            Sign In
                        </button>
                    </form>

                    <div class="text-center mt-3 border-top pt-3">
                        <p class="small text-muted mb-0">Don't have an account?
                            <a href="{{ route('register') }}" class="fw-bold text-dark text-decoration-none">Create One</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection