<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Surat;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $rolePengusul = Auth::guard('pengusul')->check() ? Auth::guard('pengusul')->user()->id_role_pengusul : null;
            $staffRole = Auth::guard('staff')->check() ? Auth::guard('staff')->user()->role : null;

            $notifikasiSurat = collect(); // default empty

            // Cek apakah pengguna login sebagai pengusul
            if (Auth::guard('pengusul')->check()) {
                $user = Auth::guard('pengusul')->user();

                $notifikasiSurat = Surat::with(['jenisSurat', 'statusTerakhir.statusSurat'])
                    ->whereHas('statusTerakhir.statusSurat', function ($query) {
                        $query->whereIn('status_surat', ['Diterima', 'Ditolak']);
                    })
                    ->whereHas('pengusul', function ($query) use ($user) {
                        $query->where('pengusul.id_pengusul', $user->id_pengusul);
                    })
                    ->latest()
                    ->take(10)
                    ->get();
            }

            $view->with([
                'rolePengusul' => $rolePengusul,
                'staffRole' => $staffRole,
                'notifikasiSurat' => $notifikasiSurat,
            ]);
        });
    }
}
