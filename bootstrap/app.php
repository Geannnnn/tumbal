<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticatePengusulOrStaff;
use App\Http\Middleware\CheckUserPrivileges;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,  // Menambahkan alias role
            'multi-auth' => AuthenticatePengusulOrStaff::class,
            'check-privileges' => CheckUserPrivileges::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        $exceptions->renderable(function (QueryException $e) {
            $errorCode = $e->getCode();
            if (
                $errorCode == 1142 ||
                str_contains($e->getMessage(), 'access violation') ||
                str_contains($e->getMessage(), 'access') ||
                str_contains($e->getMessage(), 'privilege') ||
                str_contains($e->getMessage(), 'denied')
            ) {
                if (request()->is('access-revoked.html')) {
                    return response()->file(public_path('access-revoked.html'));
                }
                return redirect('/access-revoked.html');
            }
        });
    })->create();
