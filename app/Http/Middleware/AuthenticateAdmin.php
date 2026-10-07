<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (! Auth::guard('myadmin')->check()) {
            return redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }
}
