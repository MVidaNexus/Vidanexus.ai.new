<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPackageTool extends Model
{
    protected $fillable = [
        'subscription_package_id',
        'tool_slug',
        'credits_per_cycle',
    ];

    protected function casts(): array
    {
        return [
            'credits_per_cycle' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'subscription_package_id');
    }
}
