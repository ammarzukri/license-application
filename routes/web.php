<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\LicenseApplicationController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// License Application Routes
Route::get('/license/apply', [LicenseApplicationController::class, 'create'])
    ->middleware(['auth'])
    ->name('license.apply');

Route::get('/license/status', [LicenseApplicationController::class, 'status'])
    ->middleware(['auth'])
    ->name('license.status');

Route::post('/license/apply', [LicenseApplicationController::class, 'store'])
    ->middleware(['auth'])
    ->name('license.apply.store');

Route::get('/license/payment/start', [LicenseApplicationController::class, 'startPayment'])
    ->middleware(['auth'])
    ->name('license.payment.start');

Route::get('/license/payment/return', [LicenseApplicationController::class, 'handlePaymentReturn'])
    ->middleware(['auth'])
    ->name('license.payment.return');

Route::post('/license/payment/callback', [LicenseApplicationController::class, 'handlePaymentCallback'])
    ->name('license.payment.callback');

// Admin License Application Routes
Route::get('/admin/license-applications', [LicenseApplicationController::class, 'adminIndex'])
    ->middleware(['auth'])
    ->name('admin.license.index');

Route::get('/admin/license-applications/{application}', [LicenseApplicationController::class, 'adminShow'])
    ->middleware(['auth'])
    ->name('admin.license.show');

Route::post('/admin/license-applications/{application}/approve', [LicenseApplicationController::class, 'approve'])
    ->middleware(['auth'])
    ->name('admin.license.approve');

Route::post('/admin/license-applications/{application}/reject', [LicenseApplicationController::class, 'reject'])
    ->middleware(['auth'])
    ->name('admin.license.reject');

Route::get('/admin/license-documents/{document}', [LicenseApplicationController::class, 'adminDocument'])
    ->middleware(['auth'])
    ->name('admin.license.document');

require __DIR__.'/settings.php';
