<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_db_seed_can_run_several_times_without_failing_or_duplicating()
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::where('email', 'test@example.com')->count());
        $this->assertSame(1, User::count());
        $this->assertSame(3, Product::count());
    }

    public function test_db_seed_also_loads_the_catalog_products()
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame([1, 2, 3], Product::orderBy('id')->pluck('id')->all());
    }

    public function test_the_seeded_test_user_is_verified_and_can_log_in()
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertSame('Test User', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_running_db_seed_again_does_not_overwrite_an_existing_test_user()
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $user->forceFill(['name' => 'Nombre cambiado'])->save();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame('Nombre cambiado', $user->fresh()->name);
    }
}
