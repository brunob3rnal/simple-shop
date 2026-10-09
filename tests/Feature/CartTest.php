<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Se prueba el servidor (props de Inertia), no el front compilado.
        $this->withoutVite();
    }

    private function product(int $id = 1, string $name = 'Camiseta básica', int $cents = 1999): Product
    {
        return Product::factory()->create(['id' => $id, 'name' => $name, 'price_cents' => $cents]);
    }

    private function add(Product $product): void
    {
        $this->post(route('cart.products.store', $product));
    }

    // Criterio 1: cada producto tiene un boton "Agregar al carrito".
    public function test_the_catalog_gives_every_product_the_add_to_cart_button()
    {
        $this->product();

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('addToCartLabel', 'Agregar al carrito'));

        $catalog = file_get_contents(resource_path('js/pages/Catalog.vue'));

        $this->assertStringContainsString('addToCartLabel', $catalog);
        $this->assertStringContainsString('v-for="product in products"', $catalog);
        // El boton publica en la ruta cart.products.store (generada por Wayfinder).
        $this->assertStringContainsString("@/routes/cart/products'", $catalog);
    }

    // Criterio 2: al pulsar "Agregar al carrito", el producto queda en el carrito.
    public function test_adding_a_product_puts_it_in_the_cart()
    {
        $product = $this->product();

        $this->post(route('cart.products.store', $product))
            ->assertRedirect(route('home'));

        $this->assertSame([$product->id => 1], session('cart'));
    }

    // Criterio 3: el carrito muestra productos (nombre, cantidad, precio unitario, subtotal) y el total.
    public function test_the_cart_page_shows_lines_with_unit_price_subtotal_and_total()
    {
        $shirt = $this->product(1, 'Camiseta básica', 1999);
        $mug = $this->product(2, 'Taza de cerámica', 950);

        $this->add($shirt);
        $this->add($shirt);
        $this->add($mug);

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cart')
                ->has('lines', 2)
                ->where('lines.0.name', 'Camiseta básica')
                ->where('lines.0.quantity', 2)
                ->where('lines.0.unit_price_label', '$19.99')
                ->where('lines.0.subtotal_label', '$39.98')
                ->where('lines.1.name', 'Taza de cerámica')
                ->where('lines.1.quantity', 1)
                ->where('lines.1.unit_price_label', '$9.50')
                ->where('lines.1.subtotal_label', '$9.50')
                ->where('total_cents', 4948)
                ->where('total_label', '$49.48'));
    }

    // Criterio 4: cualquier visitante puede agregar productos sin iniciar sesion.
    public function test_a_guest_can_add_products_without_logging_in()
    {
        $product = $this->product();

        $this->post(route('cart.products.store', $product))
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertSame([$product->id => 1], session('cart'));
    }

    // Criterio 5: al iniciar sesion, el carrito conserva sus productos.
    public function test_the_cart_is_kept_when_the_user_logs_in()
    {
        $product = $this->product();
        $user = User::factory()->create();

        $this->add($product);
        $this->add($product);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
        $this->assertSame([$product->id => 2], session('cart'));
        $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page
            ->where('lines.0.quantity', 2));
    }

    public function test_the_cart_is_kept_when_a_visitor_registers_and_is_logged_in()
    {
        $product = $this->product();

        $this->add($product);

        $this->post(route('register.store'), [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertSame([$product->id => 1], session('cart'));
    }

    // Criterio 6: al cerrar sesion, el carrito se vacia.
    public function test_the_cart_is_emptied_when_the_user_logs_out()
    {
        $product = $this->product();
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);
        $this->add($product);
        $this->assertNotNull(session('cart'));

        $this->post(route('logout'));

        $this->assertGuest();
        $this->assertNull(session('cart'));
        $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page
            ->has('lines', 0)
            ->where('total_cents', 0));
    }

    // Criterio 7: subtotales y total se calculan en el servidor con precios de la BD.
    public function test_subtotals_and_total_come_from_database_prices()
    {
        $product = $this->product(1, 'Camiseta básica', 1000);
        $this->add($product);
        $this->add($product);

        $product->update(['price_cents' => 1500]);

        $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page
            ->where('lines.0.subtotal_cents', 3000)
            ->where('lines.0.subtotal_label', '$30.00')
            ->where('total_cents', 3000)
            ->where('total_label', '$30.00'));
    }

    public function test_a_price_or_quantity_sent_by_the_browser_is_ignored()
    {
        $product = $this->product(1, 'Camiseta básica', 1999);

        $this->post(route('cart.products.store', $product), [
            'price' => 1,
            'price_cents' => 1,
            'quantity' => 99,
        ]);

        $this->assertSame([$product->id => 1], session('cart'));
        $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page
            ->where('lines.0.quantity', 1)
            ->where('total_cents', 1999));
    }

    // Criterio 8: agregar un producto repetido sube su cantidad en 1, sin maximo.
    public function test_adding_the_same_product_again_raises_its_quantity_by_one()
    {
        $product = $this->product();

        $this->add($product);
        $this->assertSame([$product->id => 1], session('cart'));

        $this->add($product);
        $this->assertSame([$product->id => 2], session('cart'));

        $this->add($product);
        $this->assertSame([$product->id => 3], session('cart'));
    }

    // Criterio 9: el carrito tiene su propia pagina en /carrito.
    public function test_the_cart_has_its_own_page_at_carrito()
    {
        $this->assertSame('/carrito', route('cart.show', absolute: false));

        $this->get('/carrito')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Cart'));
    }

    // Criterio 10: en la cabecera hay un enlace "Carrito" (sin contador) que lleva a /carrito.
    public function test_the_header_has_a_cart_link_without_a_counter()
    {
        $layout = file_get_contents(resource_path('js/layouts/StoreLayout.vue'));

        $this->assertStringContainsString('Carrito', $layout);
        $this->assertStringContainsString("@/routes/cart'", $layout);
        $this->assertStringContainsString('show()', $layout);
        // Sin contador: el layout no lee ninguna cantidad del carrito.
        $this->assertStringNotContainsString('cartCount', $layout);
        $this->assertStringNotContainsString('cart_count', $layout);
    }

    // Criterio 11: al agregar, el usuario se queda en el catalogo y ve el aviso exacto.
    public function test_adding_a_product_redirects_to_the_catalog_with_the_exact_message()
    {
        $product = $this->product();

        $this->post(route('cart.products.store', $product))
            ->assertRedirect(route('home'))
            ->assertInertiaFlash('toast', [
                'type' => 'success',
                'message' => 'Producto agregado al carrito',
            ]);
    }

    // Criterio 12: con el carrito vacio se muestra el texto exacto.
    public function test_an_empty_cart_shows_the_exact_message()
    {
        $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page
            ->has('lines', 0)
            ->where('total_cents', 0)
            ->where('emptyMessage', 'Tu carrito está vacío'));
    }

    public function test_a_cart_with_products_does_not_show_the_empty_message()
    {
        $this->add($this->product());

        $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page
            ->where('emptyMessage', null));
    }

    // Extras: producto inexistente y producto borrado de la base de datos.
    public function test_adding_an_unknown_product_returns_404_and_changes_nothing()
    {
        $this->post('/carrito/productos/999')->assertNotFound();

        $this->assertNull(session('cart'));
    }

    public function test_a_product_removed_from_the_database_disappears_from_the_cart()
    {
        $kept = $this->product(1, 'Camiseta básica', 1999);
        $gone = $this->product(2, 'Taza de cerámica', 950);

        $this->add($kept);
        $this->add($gone);
        $gone->delete();

        $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page
            ->has('lines', 1)
            ->where('lines.0.name', 'Camiseta básica')
            ->where('total_cents', 1999));
    }
}
