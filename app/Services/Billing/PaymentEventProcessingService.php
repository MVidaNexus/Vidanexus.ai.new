<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\PaymentEvent;
use App\Models\PaymentIntent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentEventProcessingService
{
    public function __construct(
        protected PaymentCatalogService $catalog,
        protected PaymentFulfillmentService $fulfillment
    ) {}

    public function process(PaymentEvent $event): void
    {
        $intent = $event->intent;
        if (! $intent || ! $event->signature_valid) {
            return;
        }

        DB::transaction(function () use ($intent, $event): void {
            $intent->refresh();
            if (! in_array($intent->state, ['pending', 'processing'], true)) {
                return;
            }

            $intent->update([
                'state' => 'processing',
                'last_event_at' => now(),
            ]);

            if ($event->provider_status !== 'success') {
                $intent->update([
                    'state' => 'failed',
                    'failure_reason' => 'Provider status: '.$event->provider_status,
                ]);
                return;
            }

            $user = User::query()->find($intent->user_id);
            if (! $user) {
                $intent->update([
                    'state' => 'failed',
                    'failure_reason' => 'User not found for payment intent.',
                ]);
                return;
            }

            if ($intent->payment_type === 'tool') {
                $this->fulfillment->finalizeToolPurchase($user, $intent->payment_target_id, $intent->provider_order_ref);
            } else {
                $packages = $this->catalog->getPackages();
                $package = $packages[$intent->payment_target_id] ?? null;
                if (! $package) {
                    $intent->update(['state' => 'failed', 'failure_reason' => 'Package not found.']);
                    return;
                }
                $this->fulfillment->finalizeCreditPurchase($user, $intent->payment_target_id, $intent->provider_order_ref, $package);
            }

            $alreadyPaid = Invoice::query()
                ->where('user_id', $user->id)
                ->where('description', 'LIKE', '%'.$intent->provider_order_ref.'%')
                ->exists();

            $intent->update([
                'state' => $alreadyPaid ? 'completed' : 'failed',
                'failure_reason' => $alreadyPaid ? null : 'Fulfillment did not persist invoice.',
                'last_event_at' => now(),
            ]);
        });
    }
}
