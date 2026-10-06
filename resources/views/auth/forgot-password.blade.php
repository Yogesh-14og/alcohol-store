@extends('layouts.app')

@section('title', 'Forgot Password - Khanal Liquor')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">

                    {{-- Icon + Heading --}}
                    <div class="text-center mb-4">
                        <div class="forgot-icon-wrap mx-auto mb-3">
                            <i class="bi bi-lock fs-3 text-dark"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Forgot Password?</h3>
                        <p class="text-muted small mb-0">
                            Enter your email and we'll send you a reset link.
                        </p>
                    </div>

                    {{-- Success Message --}}
                    @if (session('status'))
                        <div class="alert alert-success d-flex align-items-center gap-2 small rounded-3 mb-3" role="alert">
                            <i class="bi bi-check-circle-fill text-success fs-6"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold small">Email Address</label>
                            <input type="email"
                                name="email"
                                id="email"
                                class="form-control rounded-3 @error('email') is-invalid @enderror"
                                placeholder="name@example.com"
                                value="{{ old('email') }}"
                                required
                                autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold rounded-3 mb-3">
                            Send Reset Link
                        </button>
                    </form>

                    {{-- Back to Login --}}
                    <div class="text-center border-top pt-3">
                        <a href="{{ route('login') }}" class="text-decoration-none small text-muted fw-medium">
                            <i class="bi bi-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.forgot-icon-wrap {
    width: 56px;
    height: 56px;
    background: #f3f4f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush