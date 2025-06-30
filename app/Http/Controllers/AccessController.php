<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccessController extends Controller
{
    public function checkAccess(Request $request)
    {
        try {
            $user = null;
            $hasAccess = false;
            $guard = null;

            // Cek user yang sedang login
            if (Auth::guard('pengusul')->check()) {
                $user = Auth::guard('pengusul')->user();
                $guard = 'pengusul';
                $hasAccess = $this->checkPengusulAccess($user);
            } elseif (Auth::guard('admin')->check()) {
                $user = Auth::guard('admin')->user();
                $guard = 'admin';
                $hasAccess = $this->checkAdminAccess($user);
            } elseif (Auth::guard('staff')->check()) {
                $user = Auth::guard('staff')->user();
                $guard = 'staff';
                $hasAccess = $this->checkStaffAccess($user);
            } elseif (Auth::guard('kepala_sub')->check()) {
                $user = Auth::guard('kepala_sub')->user();
                $guard = 'kepala_sub';
                $hasAccess = $this->checkKepalaSubAccess($user);
            } else {
                // User belum login, return success untuk menghindari redirect loop
                return response()->json([
                    'has_access' => true,
                    'user_id' => null,
                    'guard' => null,
                    'authenticated' => false,
                    'timestamp' => now()
                ]);
            }

            return response()->json([
                'has_access' => $hasAccess,
                'user_id' => $user ? $user->id : null,
                'guard' => $guard,
                'authenticated' => true,
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking access: ' . $e->getMessage());
            return response()->json([
                'has_access' => false,
                'error' => 'Error checking access',
                'authenticated' => false
            ], 500);
        }
    }

    private function checkPengusulAccess($user)
    {
        try {
            // Cek apakah user masih ada di database
            $exists = DB::table('pengusul')
                ->where('id_pengusul', $user->id_pengusul)
                ->exists();

            if (!$exists) {
                return false;
            }

            // Cek apakah user memiliki role yang valid
            $hasRole = DB::table('role_pengusul')
                ->where('id_role_pengusul', $user->id_role_pengusul)
                ->exists();

            return $hasRole;

        } catch (\Exception $e) {
            Log::error('Error checking pengusul access: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek akses admin
     */
    private function checkAdminAccess($user)
    {
        try {
            return DB::table('admin')
                ->where('id_admin', $user->id_admin)
                ->exists();
        } catch (\Exception $e) {
            Log::error('Error checking admin access: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek akses staff
     */
    private function checkStaffAccess($user)
    {
        try {
            return DB::table('staff')
                ->where('id_staff', $user->id_staff)
                ->exists();
        } catch (\Exception $e) {
            Log::error('Error checking staff access: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek akses kepala sub
     */
    private function checkKepalaSubAccess($user)
    {
        try {
            return DB::table('kepala_sub')
                ->where('id_kepala_sub', $user->id_kepala_sub)
                ->exists();
        } catch (\Exception $e) {
            Log::error('Error checking kepala sub access: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Halaman akses dicabut
     */
    public function accessRevoked()
    {
        return view('auth.access-revoked');
    }

    /**
     * Halaman akses dikembalikan
     */
    public function accessRestored()
    {
        return view('auth.access-restored');
    }

}
