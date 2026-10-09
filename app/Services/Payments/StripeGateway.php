<?php

namespace App\Services\Payments;

use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeGateway
{
    public function __construct(private readonly StripeClient $client) {}

    /**
     * Crea una sesión de Stripe Checkout (página alojada) y devuelve su URL.
     *
     * @param  int  $amountCents  Monto en centavos USD (1999 = 19.99 USD).
     */
    public function createCheckoutSession(
        int $amountCents,
        string $productName,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $session = $this->client->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $amountCents,
                    'product_data' => ['name' => $productName],
                ],
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return (string) $session->url;
    }

    /**
     * Pregunta a Stripe (no al navegador) si la sesión quedó pagada.
     */
    public function isPaid(string $sessionId): bool
    {
        try {
            $session = $this->client->checkout->sessions->retrieve($sessionId);
        } catch (ApiErrorException) {
            return false;
        }

        return $session->payment_status === 'paid';
    }
}
