<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $pengusul = Auth::guard('pengusul')->user();
        $staff = Auth::guard('staff')->user();

        if (!$pengusul && !$staff) {
            return redirect('/login')->with('error', 'Silakan login dulu.');
        }

        if ($role === 'pengusul' && $pengusul && in_array($pengusul->id_role_pengusul, [1, 2])) {
            return $next($request);
        }

        if ($role === 'staff' && $staff && in_array($staff->role, ['Tata Usaha', 'Staff Umum'])) {
            return $next($request);
        }

        return abort(403, 'Akses ditolak. Anda tidak memiliki izin.');
    }
}
