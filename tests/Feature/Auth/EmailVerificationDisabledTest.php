<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Tests\TestCase;

/**
 * Criterio 13 de specs/registro.md: en el Sprint 1 no se verifica el email.
 */
class EmailVerificationDisabledTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Se prueba el servidor (props de Inertia), no el front compilado.
        $this->withoutVite();
    }

    public function test_the_verification_routes_do_not_exist()
    {
        foreach (['verification.notice', 'verification.verify', 'verification.send'] as $name) {
            $this->assertFalse(Route::has($name), "La ruta {$name} no deberia existir.");
        }
    }

    public function test_no_route_depends_on_a_verified_email()
    {
        foreach (Route::getRoutes() as $route) {
            foreach ($route->gatherMiddleware() as $middleware) {
                if (! is_string($middleware)) {
                    continue;
                }

                $this->assertNotSame('verified', $middleware, "La ruta {$route->uri()} usa el middleware verified.");
                $this->assertFalse(
                    str_starts_with($middleware, EnsureEmailIsVerified::class),
                    "La ruta {$route->uri()} usa EnsureEmailIsVerified.",
                );
            }
        }
    }

    public function test_the_fortify_email_verification_feature_is_off()
    {
        $this->assertFalse(Features::enabled(Features::emailVerification()));
    }

    public function test_the_user_model_does_not_require_a_verified_email()
    {
        $this->assertNotInstanceOf(MustVerifyEmail::class, new User);
    }

    public function test_a_user_with_an_unverified_email_can_use_every_page()
    {
        $user = User::factory()->unverified()->create();
        $this->assertNull($user->email_verified_at);

        $this->actingAs($user);

        foreach (['/', '/carrito', '/dashboard', '/settings/profile', '/settings/appearance'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_changing_the_email_does_not_clear_anything_because_nothing_is_verified()
    {
        $user = User::factory()->create();
        $verifiedAt = $user->email_verified_at;

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'otro@example.com',
        ])->assertSessionHasNoErrors();

        $this->assertEquals($verifiedAt, $user->refresh()->email_verified_at);
    }
}
