<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Toda la lógica del carrito. Vive en la sesión de Laravel (Sprint 1) y guarda
 * solo `[id de producto => cantidad]`: los precios se leen siempre de la base
 * de datos, nunca de lo que envíe el navegador.
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(private readonly Session $session) {}

    /**
     * Agrega una unidad del producto (si ya estaba, su cantidad sube en 1; sin máximo).
     */
    public function add(Product $product): void
    {
        $items = $this->items();
        $items[$product->id] = ($items[$product->id] ?? 0) + 1;

        $this->session->put(self::SESSION_KEY, $items);
    }

    /**
     * Líneas del carrito en el orden en que se agregaron. Un producto que ya no
     * existe en la base de datos se descarta (y se quita de la sesión).
     *
     * @return Collection<int, array{product: Product, quantity: int, subtotal_cents: int}>
     */
    public function lines(): Collection
    {
        $items = $this->items();

        if ($items === []) {
            return collect();
        }

        $products = Product::whereIn('id', array_keys($items))->get()->keyBy('id');

        $lines = collect();
        $existing = [];

        foreach ($items as $id => $quantity) {
            $product = $products->get($id);

            // Producto que ya no existe: se descarta del carrito.
            if ($product === null) {
                continue;
            }

            $existing[$id] = $quantity;
            $lines->push([
                'product' => $product,
                'quantity' => $quantity,
                'subtotal_cents' => $product->price_cents * $quantity,
            ]);
        }

        if (count($existing) !== count($items)) {
            $this->store($existing);
        }

        return $lines;
    }

    /**
     * Total del carrito en centavos USD.
     */
    public function totalCents(): int
    {
        return (int) $this->lines()->sum('subtotal_cents');
    }

    public function isEmpty(): bool
    {
        return $this->lines()->isEmpty();
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /**
     * @return array<int, int>
     */
    private function items(): array
    {
        $items = $this->session->get(self::SESSION_KEY, []);

        return is_array($items) ? $items : [];
    }

    /**
     * @param  array<int, int>  $items
     */
    private function store(array $items): void
    {
        if ($items === []) {
            $this->clear();

            return;
        }

        $this->session->put(self::SESSION_KEY, $items);
    }
}
