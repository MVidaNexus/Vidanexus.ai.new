<?php

namespace App\Jobs;

use App\Models\PaymentEvent;
use App\Services\Billing\PaymentEventProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPaymentEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 120;

    public function __construct(public int $paymentEventId)
    {
        $this->onQueue('payments');
    }

    public function backoff(): array
    {
        return [10, 30, 60, 120];
    }

    public function tags(): array
    {
        return ['payment', 'payment_event:'.$this->paymentEventId];
    }

    public function handle(PaymentEventProcessingService $processor): void
    {
        $event = PaymentEvent::query()->find($this->paymentEventId);
        if (! $event) {
            return;
        }

        $processor->process($event);
    }

    public function failed(?Throwable $exception): void
    {
        $event = PaymentEvent::query()->find($this->paymentEventId);
        if ($event?->intent) {
            $event->intent->update([
                'state' => 'dead_lettered',
                'failure_reason' => $exception?->getMessage() ?? 'Payment job failed.',
            ]);
        }
    }
}
