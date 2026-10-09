<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StoreHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Se prueba el servidor (props de Inertia), no el front compilado.
        $this->withoutVite();
    }

    private function layout(): string
    {
        return file_get_contents(resource_path('js/layouts/StoreLayout.vue'));
    }

    // Criterio 13: a la izquierda de la cabecera esta el nombre de la tienda, con enlace a /.
    public function test_the_store_name_comes_from_app_name_and_is_shared_with_every_store_page()
    {
        config(['app.name' => 'Mi Tienda']);

        foreach (['/', '/carrito'] as $path) {
            $this->get($path)->assertInertia(fn (Assert $page) => $page
                ->where('name', 'Mi Tienda'));
        }
    }

    public function test_the_store_name_is_shared_for_logged_in_users_too()
    {
        config(['app.name' => 'Mi Tienda']);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('name', 'Mi Tienda'));
    }

    public function test_the_example_environment_names_the_store_mi_tienda()
    {
        $this->assertStringContainsString(
            'APP_NAME="Mi Tienda"',
            file_get_contents(base_path('.env.example')),
        );
    }

    public function test_the_header_shows_the_store_name_as_a_link_to_the_home_page()
    {
        $layout = $this->layout();

        // Muestra la prop compartida (APP_NAME), no un texto escrito a mano.
        $this->assertStringContainsString('{{ $page.props.name }}', $layout);
        // Enlaza a la ruta `home` (/) generada por Wayfinder.
        $this->assertMatchesRegularExpression("/import \{[^}]*\bhome\b[^}]*\} from '@\/routes';/", $layout);
        $this->assertMatchesRegularExpression('/<Link\s+:href="home\(\)"[^>]*>\s*\{\{ \$page\.props\.name \}\}\s*<\/Link>/s', $layout);
        $this->assertSame('/', route('home', absolute: false));
    }

    public function test_the_store_name_is_on_the_left_of_the_header_before_the_navigation()
    {
        $layout = $this->layout();

        $header = substr($layout, strpos($layout, '<header'), strpos($layout, '</header>') - strpos($layout, '<header'));

        $this->assertStringContainsString('justify-between', $header);
        $this->assertLessThan(strpos($header, '<nav'), strpos($header, '$page.props.name'));
        $this->assertLessThan(strpos($header, 'Carrito'), strpos($header, '$page.props.name'));
    }

    public function test_the_store_name_does_not_depend_on_being_logged_in()
    {
        $layout = $this->layout();

        // El enlace esta fuera de los bloques v-if/v-else de sesion: lo ven todos.
        $this->assertLessThan(
            strpos($layout, 'v-if="$page.props.auth.user"'),
            strpos($layout, '$page.props.name'),
        );
        $this->assertStringNotContainsString('<template v-else>'.PHP_EOL.'                    <Link :href="home()"', $layout);
    }
}
