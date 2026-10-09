<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Carga los productos del catálogo. Se puede ejecutar varias veces: los ids
     * son fijos, así que se actualizan en vez de duplicarse, y el orden del
     * catálogo (por id) coincide con el orden de esta lista.
     *
     * No usa factories ni Faker (solo existen en desarrollo): también corre en la demo.
     */
    public function run(): void
    {
        Product::upsert(
            [
                ['id' => 1, 'name' => 'Camiseta básica', 'price_cents' => 1999],
                ['id' => 2, 'name' => 'Taza de cerámica', 'price_cents' => 950],
                ['id' => 3, 'name' => 'Mochila urbana', 'price_cents' => 3900],
            ],
            uniqueBy: ['id'],
            update: ['name', 'price_cents'],
        );
    }
}
