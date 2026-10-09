<?php

namespace App\Http\Controllers\Spike;

use App\Http\Controllers\Controller;
use App\Services\Payments\StripeGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class StripeSpikeController extends Controller
{
    private const PRODUCT_NAME = 'Producto de prueba';

    private const AMOUNT_CENTS = 1999;

    private const SUCCESS_MESSAGE = 'Compra realizada exitosamente';

    public function show(): Response
    {
        return $this->page(paid: false);
    }

    public function checkout(StripeGateway $stripe): SymfonyResponse
    {
        // El monto sale siempre del servidor; el navegador no envía ningún precio.
        $url = $stripe->createCheckoutSession(
            amountCents: self::AMOUNT_CENTS,
            productName: self::PRODUCT_NAME,
            // Stripe sustituye el marcador {CHECKOUT_SESSION_ID}; no debe ir codificado.
            successUrl: route('spike.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
            cancelUrl: route('spike.stripe.cancel'),
        );

        return Inertia::location($url);
    }

    public function success(Request $request, StripeGateway $stripe): Response
    {
        $sessionId = $request->query('session_id');

        $paid = is_string($sessionId)
            && $sessionId !== ''
            && $stripe->isPaid($sessionId);

        return $this->page($paid);
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('spike.stripe.show');
    }

    private function page(bool $paid): Response
    {
        return Inertia::render('Spike/Stripe', [
            'productName' => self::PRODUCT_NAME,
            'amountCents' => self::AMOUNT_CENTS,
            'paid' => $paid,
            'message' => $paid ? self::SUCCESS_MESSAGE : null,
        ]);
    }
}
