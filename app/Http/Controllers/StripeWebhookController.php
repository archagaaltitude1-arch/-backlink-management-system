<?php

namespace App\Http\Controllers;

use App\Http\StripeCheckout;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeCheckout $checkout): Response
    {
        $secret = config('services.stripe.webhook_secret');
        abort_unless(is_string($secret) && $secret !== '', 503);

        $payload = $request->getContent();
        $fields = [];
        foreach (explode(',', (string) $request->header('Stripe-Signature')) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            $fields[$key][] = $value;
        }

        $timestamp = $fields['t'][0] ?? '';
        abort_unless(ctype_digit($timestamp) && abs(now()->timestamp - (int) $timestamp) <= 300, 400);

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        $valid = collect($fields['v1'] ?? [])->contains(fn (string $signature): bool => hash_equals($expected, $signature));
        abort_unless($valid, 400);

        $event = json_decode($payload, true);
        abort_unless(is_array($event) && is_string($event['type'] ?? null), 400);

        if (in_array($event['type'], ['checkout.session.completed', 'checkout.session.expired'], true)) {
            $session = $event['data']['object'] ?? null;
            abort_unless(is_array($session), 400);
            $checkout->handleSession($event['type'], $session);
        }

        return response()->noContent();
    }
}
