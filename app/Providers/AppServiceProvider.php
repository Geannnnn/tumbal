<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
    
            $view->with('rolePengusul', $rolePengusul)->with('staffRole', $staffRole);
        });
    }
}
