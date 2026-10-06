<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin sign in - Khanal Liquor</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="card border-0 shadow-sm rounded-4 w-100" style="max-width: 440px;">
            <div class="card-body p-4 p-md-5">
                <p class="text-uppercase text-primary fw-semibold small mb-1">Store administration</p>
                <h1 class="h3 fw-bold mb-2">Admin sign in</h1>
                <p class="text-muted mb-4">Use your administrator account to continue.</p>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('admin.login.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email address</label>
                        <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" autocomplete="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input id="password" name="password" type="password" class="form-control" autocomplete="current-password" required>
                    </div>
                    <div class="form-check mb-3">
                        <input id="remember" name="remember" type="checkbox" class="form-check-input" value="1">
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>
                    <button class="btn btn-primary w-100 rounded-3" type="submit">Sign in</button>
                </form>
                <a href="{{ route('home') }}" class="d-block text-center small mt-4">Back to store</a>
            </div>
        </div>
    </main>
</body>
</html>
