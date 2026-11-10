<?php

use App\Http\Middleware\CanonicalHost;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ShareSiteMode;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(CanonicalHost::class);
        $middleware->web(append: [
            SetLocale::class,
            ShareSiteMode::class,
        ]);
        $middleware->alias([
            'admin'  => \App\Http\Middleware\IsAdmin::class,
            'seller' => \App\Http\Middleware\IsSeller::class,
            'shop'   => \App\Http\Middleware\EnsureShopOpen::class,
        ]);
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);
        $middleware->encryptCookies(except: ['hot_admin_theme']);
        $middleware->redirectGuestsTo(fn ($request) => $request->is('cuenta*', 'checkout*', 'favoritos*')
            ? route('account.login') : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
