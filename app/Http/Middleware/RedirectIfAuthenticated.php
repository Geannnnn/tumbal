<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guards = null)
    {
        foreach ((array) $guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect('/'); // Jangan arahkan ke login jika sudah login
            }
        }
        return $next($request);
    }
}
