<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPackageSubscriptionTool extends Model
{
    protected $fillable = [
        'user_package_subscription_id',
        'tool_slug',
        'credits_per_cycle',
    ];

    protected function casts(): array
    {
        return [
            'credits_per_cycle' => 'integer',
        ];
    }

    public function userPackageSubscription(): BelongsTo
    {
        return $this->belongsTo(UserPackageSubscription::class);
    }
}
