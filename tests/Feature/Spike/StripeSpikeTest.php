<?php

namespace Tests\Feature\Spike;

use App\Services\Payments\StripeGateway;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class StripeSpikeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Se prueba el servidor (props de Inertia), no el front compilado.
        $this->withoutVite();
    }

    public function test_the_spike_page_works_without_login_and_shows_no_success_message()
    {
        $this->get(route('spike.stripe.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Spike/Stripe')
                ->where('amountCents', 1999)
                ->where('paid', false)
                ->where('message', null));
    }

    public function test_pay_button_creates_a_session_for_the_fixed_amount_and_redirects_to_stripe()
    {
        $this->mock(StripeGateway::class, function (MockInterface $mock) {
            $mock->shouldReceive('createCheckoutSession')
                ->once()
                ->withArgs(fn (int $amountCents, string $productName, string $successUrl, string $cancelUrl) => $amountCents === 1999
                    && str_contains($successUrl, 'session_id={CHECKOUT_SESSION_ID}')
                    && $cancelUrl === route('spike.stripe.cancel'))
                ->andReturn('https://checkout.stripe.com/c/pay/cs_test_fake');
        });

        $this->post(route('spike.stripe.checkout'))
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_fake');
    }

    public function test_the_browser_cannot_change_the_amount()
    {
        $this->mock(StripeGateway::class, function (MockInterface $mock) {
            $mock->shouldReceive('createCheckoutSession')
                ->once()
                ->withArgs(fn (int $amountCents) => $amountCents === 1999)
                ->andReturn('https://checkout.stripe.com/c/pay/cs_test_fake');
        });

        $this->post(route('spike.stripe.checkout'), ['amount' => 1, 'amountCents' => 1, 'price' => 1])
            ->assertRedirect();
    }

    public function test_a_paid_session_shows_the_exact_success_message()
    {
        $this->mock(StripeGateway::class, function (MockInterface $mock) {
            $mock->shouldReceive('isPaid')->with('cs_test_paid')->once()->andReturn(true);
        });

        $this->get(route('spike.stripe.success', ['session_id' => 'cs_test_paid']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('paid', true)
                ->where('message', 'Compra realizada exitosamente'));
    }

    public function test_an_unpaid_session_does_not_show_the_success_message()
    {
        $this->mock(StripeGateway::class, function (MockInterface $mock) {
            $mock->shouldReceive('isPaid')->with('cs_test_unpaid')->once()->andReturn(false);
        });

        $this->get(route('spike.stripe.success', ['session_id' => 'cs_test_unpaid']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('paid', false)
                ->where('message', null));
    }

    public function test_returning_without_a_session_id_does_not_show_the_success_message()
    {
        $this->mock(StripeGateway::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('isPaid');
        });

        $this->get(route('spike.stripe.success'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('paid', false)
                ->where('message', null));
    }

    public function test_cancelling_returns_to_the_spike_page_without_changes()
    {
        $this->get(route('spike.stripe.cancel'))
            ->assertRedirect(route('spike.stripe.show'));
    }

    public function test_no_stripe_key_is_hardcoded_in_the_repository()
    {
        // El patrón se arma por partes para que este archivo no coincida consigo mismo.
        $pattern = '/(sk|pk|rk)_'.'(test|live)_[A-Za-z0-9]+/';

        $files = Finder::create()
            ->files()
            ->in([base_path('app'), base_path('config'), base_path('routes'), base_path('resources'), base_path('tests'), base_path('database')])
            ->append([base_path('.env.example')]);

        foreach ($files as $file) {
            $this->assertDoesNotMatchRegularExpression(
                $pattern,
                $file->getContents(),
                "Posible clave de Stripe en {$file->getRelativePathname()}",
            );
        }
    }
}
