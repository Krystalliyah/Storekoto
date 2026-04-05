<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectBasedOnRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            RedirectBasedOnRole::class,
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->alias([
            'signed' => \App\Http\Middleware\ValidateFlexibleSignature::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'vendor.store_access' => \App\Http\Middleware\EnsureVendorHasStoreAccess::class,
            'vendor.is_approved' => \App\Http\Middleware\EnsureVendorIsApproved::class,
            'vendor.is_admin' => \App\Http\Middleware\EnsureVendorIsAdmin::class,
            'consume-tenant-auth-token' => \App\Http\Middleware\ConsumeTenantAuthToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
