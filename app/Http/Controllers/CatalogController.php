<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    private const EMPTY_MESSAGE = 'No hay productos disponibles';

    public function __invoke(): Response
    {
        $products = Product::orderBy('id')
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'price_cents' => $product->price_cents,
                'price_label' => $product->priceLabel(),
            ]);

        return Inertia::render('Catalog', [
            'products' => $products,
            'emptyMessage' => $products->isEmpty() ? self::EMPTY_MESSAGE : null,
        ]);
    }
}
