<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Se prueba el servidor (props de Inertia), no el front compilado.
        $this->withoutVite();
    }

    // Criterio 1: hay 3 productos cargados con un seeder, cada uno con nombre y precio.
    public function test_the_seeder_loads_three_products_with_name_and_price()
    {
        $this->seed(ProductSeeder::class);

        $products = Product::orderBy('id')->get();

        $this->assertCount(3, $products);

        foreach ($products as $product) {
            $this->assertIsString($product->name);
            $this->assertNotSame('', $product->name);
            $this->assertIsInt($product->price_cents);
            $this->assertGreaterThan(0, $product->price_cents);
        }
    }

    // Criterio 2: los 3 productos se ven en una lista en el catalogo.
    public function test_the_catalog_lists_the_three_seeded_products()
    {
        $this->seed(ProductSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('products', 3)
                ->has('products.0', fn (Assert $product) => $product
                    ->hasAll(['id', 'name', 'price_cents', 'price_label'])));
    }

    // Criterio 3: el catalogo vive en / y reemplaza la pagina de bienvenida.
    public function test_the_catalog_lives_at_the_root_and_replaces_the_welcome_page()
    {
        $this->assertSame(url('/'), route('home'));

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Catalog'));

        $this->assertFileDoesNotExist(resource_path('js/pages/Welcome.vue'));
    }

    // Criterio 4: el catalogo se puede ver sin iniciar sesion.
    public function test_guests_can_see_the_catalog_without_logging_in()
    {
        $this->seed(ProductSeeder::class);

        $this->get('/')->assertOk();

        $this->assertGuest();
    }

    // Criterio 5: los precios se muestran en USD con el formato $19.99.
    public function test_prices_are_formatted_in_usd()
    {
        $this->assertSame('$19.99', (new Product(['price_cents' => 1999]))->priceLabel());
        $this->assertSame('$9.50', (new Product(['price_cents' => 950]))->priceLabel());
        $this->assertSame('$0.05', (new Product(['price_cents' => 5]))->priceLabel());
        $this->assertSame('$1,234.50', (new Product(['price_cents' => 123450]))->priceLabel());
    }

    public function test_the_catalog_sends_every_seeded_price_formatted_in_usd()
    {
        $this->seed(ProductSeeder::class);

        $this->get('/')->assertInertia(function (Assert $page) {
            foreach (Product::orderBy('id')->get() as $index => $product) {
                $page->where("products.$index.price_label", $product->priceLabel());
                $this->assertMatchesRegularExpression(
                    '/^\$\d{1,3}(,\d{3})*\.\d{2}$/',
                    $product->priceLabel(),
                );
            }
        });
    }

    // Criterio 6: el precio se guarda en la base de datos como entero en centavos.
    public function test_the_price_is_stored_as_an_integer_number_of_cents()
    {
        $column = collect(Schema::getColumns('products'))->firstWhere('name', 'price_cents');

        $this->assertNotNull($column, 'Falta la columna price_cents.');
        $this->assertContains($column['type_name'], ['integer', 'int']);

        $product = Product::factory()->create(['price_cents' => 1999]);

        $this->assertSame(1999, $product->fresh()->price_cents);
    }

    // Criterio 7: los productos se ordenan como en el seeder (por id).
    public function test_products_are_listed_by_id_not_by_name_or_creation_order()
    {
        Product::factory()->create(['id' => 3, 'name' => 'Mike']);
        Product::factory()->create(['id' => 1, 'name' => 'Zeta']);
        Product::factory()->create(['id' => 2, 'name' => 'Alfa']);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('products.0.id', 1)
            ->where('products.1.id', 2)
            ->where('products.2.id', 3)
            ->where('products.0.name', 'Zeta'));
    }

    // Criterio 8: ejecutar el seeder varias veces no duplica los productos.
    public function test_running_the_seeder_several_times_does_not_duplicate_products()
    {
        $this->seed(ProductSeeder::class);
        $this->seed(ProductSeeder::class);
        $this->seed(ProductSeeder::class);

        $this->assertSame(3, Product::count());
        $this->assertSame([1, 2, 3], Product::orderBy('id')->pluck('id')->all());
    }

    // Criterio 9: sin productos se muestra el texto exacto.
    public function test_it_shows_the_exact_message_when_there_are_no_products()
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('products', 0)
                ->where('emptyMessage', 'No hay productos disponibles'));
    }

    public function test_it_does_not_show_the_empty_message_when_there_are_products()
    {
        $this->seed(ProductSeeder::class);

        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('emptyMessage', null));
    }
}
