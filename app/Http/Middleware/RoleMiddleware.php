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
        $admin = Auth::guard('admin')->user();
        $kepalaSub = Auth::guard('kepala_sub')->user();
        $direktur = Auth::guard('direktur')->user();

        // Cek jika belum login di semua guard
        if (!$pengusul && !$staff && !$admin && !$kepalaSub && !$direktur) {
            return redirect('/login')->with('error', 'Silakan login dulu.');
        }

        // Role Pengusul
        if ($role === 'pengusul' && $pengusul && in_array($pengusul->id_role_pengusul, [1, 2])) {
            return $next($request);
        }

        // Role Staff
        if ($role === 'staff' && $staff && in_array($staff->role, ['Tata Usaha', 'Staff Umum'])) {
            return $next($request);
        }

        // Role Admin
        if ($role === 'admin' && $admin) {
            return $next($request);
        }

        // Role Kepala Sub
        if ($role === 'kepala_sub' && $kepalaSub) {
            return $next($request);
        }

        // Role Direktur
        if ($role === 'direktur' && $direktur) {
            return $next($request);
        }

        // Kalau tidak sesuai role
        return abort(403, 'Akses ditolak. Anda tidak memiliki izin.');
    }
}
