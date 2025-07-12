<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPrivileges
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah ada user yang login di salah satu guard
        $guards = ['pengusul', 'staff', 'admin', 'kepala_sub','direktur'];
        $user = null;
        $currentGuard = null;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $currentGuard = $guard;
                break;
            }
        }

        // Jika tidak ada user yang login, lanjutkan (biasanya untuk halaman login)
        if (!$user) {
            return $next($request);
        }
        
        try {
            // Coba melakukan query sederhana untuk mengecek akses database
            $result = DB::select('SELECT 1 as test');
            
            // Jika berhasil, cek apakah hak akses baru saja dikembalikan
            if (!empty($result)) {
                if (Session::has('access_revoked')) {
                    Session::put('access_restored', true);
                    Session::put('restored_at', now());
                    Session::forget('access_revoked');
                    Session::forget('revoked_at');
                    
                    // Redirect ke halaman asal jika ada, lalu hapus dari session
                    $lastUrl = Session::pull('last_url_before_revoke');
                    if ($lastUrl) {
                        return redirect($lastUrl);
                    }
                    // Jika tidak ada, redirect ke halaman akses dikembalikan
                    return redirect()->route('access.restored');
                }
                
                return $next($request);
            }
            
        } catch (QueryException $e) {
            // Tangkap error database (privilege dicabut)
            $errorCode = $e->getCode();
            
            // Error 1142 = SELECT command denied (hak akses dicabut)
            if ($errorCode == 1142 || str_contains($e->getMessage(), 'access violation')) {
                // Simpan URL asal sebelum redirect
                if (!$request->is('access-revoked')) {
                    Session::put('last_url_before_revoke', $request->fullUrl());
                }
                $this->handleAccessRevoked($guards);
                return redirect()->route('access.revoked');
            }
            
            // Error database lainnya, tetap anggap sebagai pencabutan hak akses
            if (!$request->is('access-revoked')) {
                Session::put('last_url_before_revoke', $request->fullUrl());
            }
            $this->handleAccessRevoked($guards);
            return redirect()->route('access.revoked');
            
        } catch (\Exception $e) {
            // Error lainnya yang mungkin terkait database
            if (str_contains($e->getMessage(), 'access') || 
                str_contains($e->getMessage(), 'privilege') ||
                str_contains($e->getMessage(), 'denied')) {
                if (!$request->is('access-revoked')) {
                    Session::put('last_url_before_revoke', $request->fullUrl());
                }
                $this->handleAccessRevoked($guards);
                return redirect()->route('access.revoked');
            }
            
            // Error lain, lanjutkan normal
            return $next($request);
        }

        return $next($request);
    }

    private function handleAccessRevoked($guards): void
    {
        // Simpan informasi pencabutan hak akses
        Session::put('access_revoked', true);
        Session::put('revoked_at', now());
        
        // Logout dari semua guard
        foreach ($guards as $guard) {
            Auth::guard($guard)->logout();
        }
    }
} 