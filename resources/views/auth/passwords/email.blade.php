@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center my-4">
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark">Forgot Password?</h3>
                    <p class="text-muted small">आफ्नो दर्ता गरिएको इमेल हाल्नुहोस्, हामी पासवर्ड रिसेट गर्ने लिङ्क पठाउनेछौँ।</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success small rounded-3 mb-3" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" 
                                   class="form-control bg-light border-start-0 rounded-end-3 @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="yourname@example.com" 
                                   required 
                                   autofocus>
                        </div>
                        @error('email')
                            <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 mt-2">
                        Send Reset Link
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <a href="{{ route('login') }}" class="text-decoration-none text-dark fw-medium small">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection