<?php

namespace App\Repositories\Payments;

use App\Models\PaymentEvent;
use App\Models\PaymentIntent;

class PaymentIntentRepository
{
    public function createOrGet(array $attributes, array $values): PaymentIntent
    {
        return PaymentIntent::query()->firstOrCreate($attributes, $values);
    }

    public function findByOrderRef(string $orderRef): ?PaymentIntent
    {
        return PaymentIntent::query()->where('provider_order_ref', $orderRef)->first();
    }

    public function recordEvent(PaymentIntent $intent, array $data): PaymentEvent
    {
        return PaymentEvent::query()->firstOrCreate(
            ['provider_event_id' => $data['provider_event_id']],
            [
                'payment_intent_id' => $intent->id,
                'provider' => $data['provider'] ?? 'fawaterk',
                'provider_order_ref' => $data['provider_order_ref'] ?? null,
                'provider_status' => $data['provider_status'],
                'signature_valid' => (bool) ($data['signature_valid'] ?? false),
                'payload' => $data['payload'] ?? null,
            ]
        );
    }
}
