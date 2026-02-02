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

require __DIR__.'/settings.php';
