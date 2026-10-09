<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Spike\StripeSpikeController;
use Illuminate\Support\Facades\Route;

Route::get('/', CatalogController::class)->name('home');

// Carrito (BRAVOBRAVO-14): en la sesion, sin iniciar sesion.
Route::get('carrito', [CartController::class, 'show'])->name('cart.show');
Route::post('carrito/productos/{product}', [CartController::class, 'store'])->name('cart.products.store');

// Spike de Stripe (BRAVOBRAVO-15): producto fijo, sin login ni carrito. Desechable.
Route::prefix('spike/stripe')->name('spike.stripe.')->controller(StripeSpikeController::class)->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('checkout', 'checkout')->name('checkout');
    Route::get('success', 'success')->name('success');
    Route::get('cancel', 'cancel')->name('cancel');
});

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
