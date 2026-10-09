<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    private const ADDED_MESSAGE = 'Producto agregado al carrito';

    private const EMPTY_MESSAGE = 'Tu carrito está vacío';

    public function show(CartService $cart): Response
    {
        $lines = $cart->lines();
        $total = $cart->totalCents();

        return Inertia::render('Cart', [
            'lines' => $lines->map(fn (array $line): array => [
                'id' => $line['product']->id,
                'name' => $line['product']->name,
                'quantity' => $line['quantity'],
                'unit_price_label' => $line['product']->priceLabel(),
                'subtotal_cents' => $line['subtotal_cents'],
                'subtotal_label' => Money::usd($line['subtotal_cents']),
            ])->all(),
            'total_cents' => $total,
            'total_label' => Money::usd($total),
            'emptyMessage' => $lines->isEmpty() ? self::EMPTY_MESSAGE : null,
        ]);
    }

    public function store(Product $product, CartService $cart): RedirectResponse
    {
        // Solo cuenta el producto de la URL: no se lee ningún precio ni cantidad del navegador.
        $cart->add($product);

        Inertia::flash('toast', ['type' => 'success', 'message' => self::ADDED_MESSAGE]);

        return redirect()->route('home');
    }
}
