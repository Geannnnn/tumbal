<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Surat;
use Carbon\Carbon;

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
        // Set Carbon locale to Indonesian
        Carbon::setLocale('id');
        
        View::composer('*', function ($view) {
            $rolePengusul = Auth::guard('pengusul')->check() ? Auth::guard('pengusul')->user()->id_role_pengusul : null;
            $staffRole = Auth::guard('staff')->check() ? Auth::guard('staff')->user()->role : null;

            $notifikasiSurat = collect(); // default

            if (Auth::guard('pengusul')->check()) {
                $user = Auth::guard('pengusul')->user();
                // Ambil notifikasi dari Laravel Notification
                $notifikasiSurat = $user->notifications()->latest()->limit(10)->get();
            }
            

            $view->with([
                'rolePengusul' => $rolePengusul,
                'staffRole' => $staffRole,
                'notifikasiSurat' => $notifikasiSurat,
            ]);
        });
    }
}
