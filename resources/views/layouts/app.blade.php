<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ auth()->user()?->theme ?? 'light' }}">

<head>
    @php
        $privateRoute = request()->routeIs(
            'dashboard.*',
            'cart.*',
            'checkout.*',
            'orders.confirmation',
            'login',
            'register',
            'password.*',
            'verification.*',
        );
        $siteUrl = rtrim((string) config('app.url'), '/');
        $canonicalQuery = request()->only('page');
        $canonicalPath = '/'.ltrim(request()->path(), '/');
        $canonicalUrl = $siteUrl.$canonicalPath.($canonicalQuery ? '?'.http_build_query($canonicalQuery) : '');
        $pageTitle = trim($__env->yieldContent('title')) ?: 'Khanal Liquor';
        $pageDescription = trim($__env->yieldContent('meta_description'))
            ?: 'Browse the Khanal Liquor online drinks collection and view product details and prices.';
        $socialImage = trim($__env->yieldContent('og_image'));
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="@yield('meta_robots', $privateRoute ? 'noindex,nofollow' : 'index,follow')">
    <link rel="canonical" href="@yield('canonical_url', $canonicalUrl)">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Khanal Liquor">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="@yield('canonical_url', $canonicalUrl)">
    @if ($socialImage !== '')
        <meta property="og:image" content="{{ $socialImage }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $socialImage }}">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('seo')
    @stack('styles')

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app" class="storefront-shell">
        @include('layouts.navigation')

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>

</html>