<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTool extends Model
{
    protected $fillable = [
        'user_id',
        'tool_slug',
        'price_paid',
        'bonus_credits',
        'expires_at',
        'renews_at',
        'auto_renew',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'expires_at' => 'datetime',
            'renews_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    /**
     * Check if the tool subscription is currently active.
     */
    public function isActive(): bool
    {
        if (!$this->expires_at) return true; // Legacy support or permanent tools
        return $this->expires_at->isFuture();
    }

    /**
     * Check if the subscription is expiring soon (e.g., within 3 days).
     */
    public function isAboutToExpire(int $days = 3): bool
    {
        if (!$this->expires_at) return false;
        return $this->expires_at->isFuture() && $this->expires_at->diffInDays(now()) <= $days;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tool config data.
     */
    public function getToolConfig(): ?array
    {
        $allTools = config('tools.all_tools', []);
        return collect($allTools)->where('slug', $this->tool_slug)->first();
    }
}
