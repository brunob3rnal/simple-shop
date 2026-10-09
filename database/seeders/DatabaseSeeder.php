<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ProductSeeder::class);

        // User::factory(10)->create();

        // Repetible: si el usuario ya existe no se vuelve a crear ni se modifica.
        // Sin factory (usa Faker, que solo existe en desarrollo) para que corra también en la demo.
        User::unguarded(fn () => User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        ));
    }
}
