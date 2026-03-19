<?php

use App\Http\Controllers\Vendor\StoreSetupController;
<<<<<<< HEAD
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
=======
use App\Http\Controllers\Vendor\DashboardController;
>>>>>>> VendorUi-XX-CentralDomain
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CENTRAL DOMAIN VENDOR ROUTES ( vendor.php )
|--------------------------------------------------------------------------
| This file handles routes specifically on the CENTRAL domain (e.g., itinda.test)
| for vendor accounts BEFORE they access their tenant dashboard.
|
| IMPORTANT: Do NOT place product/order management routes here.
| Once a vendor store is approved, they must manage their store from their
| specific tenant subdomain. Those routes belong in routes/tenant.php.
|--------------------------------------------------------------------------
*/

// Store setup and dashboard (accessible while pending)
Route::middleware(['auth', 'verified', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
<<<<<<< HEAD
    Route::get('/dashboard', fn () => inertia('vendor/Dashboard'))->name('dashboard');

    Route::get('/profile', function (Request $request) {
        return inertia('vendor/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    })->name('profile');
=======
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', fn() => inertia('vendor/Profile'))->name('profile');
>>>>>>> VendorUi-XX-CentralDomain
    Route::post('/store/setup', [StoreSetupController::class, 'store'])->name('store.create');
});
