<?php

use App\Http\Controllers\Spike\StripeSpikeController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Spike de Stripe (BRAVOBRAVO-15): producto fijo, sin login ni carrito. Desechable.
Route::prefix('spike/stripe')->name('spike.stripe.')->controller(StripeSpikeController::class)->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('checkout', 'checkout')->name('checkout');
    Route::get('success', 'success')->name('success');
    Route::get('cancel', 'cancel')->name('cancel');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
