<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticatePengusulOrStaff
{
    public function handle($request, Closure $next)
    {
        if (
            Auth::guard('pengusul')->check() ||
            Auth::guard('staff')->check() ||
            Auth::guard('admin')->check() ||
            Auth::guard('kepala_sub')->check() ||
            Auth::guard('direktur')->check()
        ) {
            return $next($request);
        }

        return redirect()->route('login.form')->with('error', 'Silakan login terlebih dahulu.');
    }
}
