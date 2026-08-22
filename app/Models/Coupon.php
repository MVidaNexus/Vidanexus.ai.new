<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'scope',
        'tool_slug',
        'credits',
        'max_uses',
        'used_count',
        'is_active',
        'assigned_user_id',
        'expires_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    // ─── Business Logic ───────────────────────────────────────────

    /**
     * Check if this coupon can be redeemed by the given user.
     *
     * @return array{0: bool, 1: string|null}
     */
    public function isValid(User $user): array
    {
        if (!$this->is_active) {
            return [false, 'This coupon is currently inactive.'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return [false, 'This coupon has expired.'];
        }

        if ($this->assigned_user_id && $this->assigned_user_id !== $user->id) {
            return [false, 'This coupon is not assigned to your account.'];
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return [false, 'This coupon has reached its maximum number of uses.'];
        }

        if ($this->redemptions()->where('user_id', $user->id)->exists()) {
            return [false, 'You have already redeemed this coupon.'];
        }

        if (($this->scope ?? 'all_tools') === 'specific_tool') {
            if (! $this->tool_slug) {
                return [false, 'This coupon is misconfigured (missing tool).'];
            }
            $slugs = collect(config('tools.all_tools', []))->pluck('slug')->filter()->all();
            if (! in_array($this->tool_slug, $slugs, true)) {
                return [false, 'This coupon references an invalid tool.'];
            }
        }

        return [true, null];
    }

    /**
     * Compute a human-readable status considering expiry and exhaustion.
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Expired';
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return 'Exhausted';
        }
        return 'Active';
    }
}
