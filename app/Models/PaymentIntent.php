<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentIntent extends Model
{
    protected $fillable = [
        'user_id',
        'idempotency_key',
        'provider',
        'provider_order_ref',
        'payment_type',
        'payment_target_id',
        'amount_egp',
        'state',
        'last_event_at',
        'failure_reason',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'last_event_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }
}
