@extends('layouts.app')

@section('title', 'Create Account - Khanal Liquor')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark mb-1">Create an Account</h3>
                    <p class="text-muted small">Join us for fast alcohol delivery in Kathmandu</p>
                </div>

                <form action="{{ route('register.submit') }}" method="POST">
                    @csrf

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control rounded-3 @error('name') is-invalid @enderror" placeholder="Yogesh Khatri" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Input -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control rounded-3 @error('email') is-invalid @enderror" placeholder="name@example.com" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold small">Password</label>
                        <input type="password" name="password" id="password" class="form-control rounded-3 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold small">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control rounded-3" placeholder="••••••••" required>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-dark w-100 py-2 fw-bold rounded-3 mb-3">
                        Register Account
                    </button>
                </form>

                <div class="text-center mt-3 border-top pt-3">
                    <p class="small text-muted mb-0">Already have an account? 
                        <a href="{{ route('login') }}" class="fw-bold text-dark text-decoration-none">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection