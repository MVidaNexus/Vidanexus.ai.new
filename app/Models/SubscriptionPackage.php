<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPackage extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'discount_percent',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'discount_percent' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function tools(): HasMany
    {
        return $this->hasMany(SubscriptionPackageTool::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserPackageSubscription::class);
    }

    /**
     * Final unit price for a billing interval after package discount.
     */
    public function finalPriceForInterval(string $billingInterval): float
    {
        $base = $billingInterval === 'yearly'
            ? (float) ($this->price_yearly ?? ((float) $this->price_monthly * 12))
            : (float) $this->price_monthly;

        $d = max(0, min(100, (int) $this->discount_percent));

        return round($base * (1 - $d / 100), 2);
    }
}
