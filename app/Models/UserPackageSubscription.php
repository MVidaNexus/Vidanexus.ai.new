<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPackageSubscription extends Model
{
    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_PAST_DUE = 'past_due';

    protected $fillable = [
        'user_id',
        'subscription_package_id',
        'status',
        'billing_interval',
        'unit_price_paid',
        'currency',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'external_payment_ref',
        'cart_id',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_paid' => 'decimal:2',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionPackage(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function tools(): HasMany
    {
        return $this->hasMany(UserPackageSubscriptionTool::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ACTIVE)
            ->where(function (Builder $inner) {
                $inner->whereNull('current_period_end')
                    ->orWhere('current_period_end', '>', now());
            });
    }

    public function isActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }
        if ($this->current_period_end === null) {
            return true;
        }

        return $this->current_period_end->isFuture();
    }
}
