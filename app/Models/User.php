<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'subscription_tier', // Legacy — kept for backward compatibility
        'phone',
        'country',
        'settings',
    ];

    /**
     * Check if user has completed their profile (phone is required).
     */
    public function hasCompletedProfile(): bool
    {
        return !empty($this->phone) && !empty($this->country);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ──────────────────────────────────────────────
    // MARKETPLACE: Tool Ownership
    // ──────────────────────────────────────────────

    /**
     * Get all tools owned by this user.
     */
    public function ownedTools(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserTool::class);
    }

    /**
     * Check if user owns a specific tool and it hasn't expired.
     */
    public function ownsTool(string $slug): bool
    {
        if ($this->isAdmin()) return true;
        
        $userTool = $this->ownedTools()->where('tool_slug', $slug)->first();
        
        if (!$userTool) return false;
        
        return $userTool->isActive();
    }

    /**
     * Get accessible tools for this user (owned + available).
     */
    public function getAccessibleTools(): array
    {
        $allTools = config('tools.all_tools', []);

        if ($this->isAdmin()) {
            return $allTools;
        }

        $ownedSlugs = $this->ownedTools()->pluck('tool_slug')->toArray();

        return array_filter($allTools, function ($tool) use ($ownedSlugs) {
            return in_array($tool['slug'], $ownedSlugs);
        });
    }

    /**
     * Check if user can use a specific tool (owns it + has enough credits).
     */
    public function canUseTool(string $slug): bool
    {
        if ($this->isAdmin()) return true;

        // Must own the tool first
        if (!$this->ownsTool($slug)) return false;

        $costPerAction = $this->getToolCreditCost($slug);

        // Free tools (cost = 0) are always usable once owned
        if ($costPerAction <= 0) return true;

        // 1. Check Tool-Specific Bonus Credits first
        $userTool = $this->ownedTools()->where('tool_slug', $slug)->first();
        if ($userTool && $userTool->bonus_credits >= $costPerAction) {
            return true;
        }

        // 2. Fallback to Global Wallet Credits
        $wallet = $this->wallet;
        return $wallet && $wallet->balance_credits >= $costPerAction;
    }

    /**
     * Get the credit cost for a specific tool action.
     */
    public function getToolCreditCost(string $slug): int
    {
        $toolConfig = collect(config('tools.all_tools', []))->where('slug', $slug)->first();
        return (int) Setting::get("tool_credit_cost_{$slug}", $toolConfig['credit_cost_per_action'] ?? 1);
    }

    /**
     * Deduct credits for a tool action.
     * Priority: Tool-Specific Bonus -> Global Wallet
     */
    public function deductToolCredits(string $slug): bool
    {
        $cost = $this->getToolCreditCost($slug);
        if ($cost <= 0) return true;

        // 1. Try Tool-Specific Bonus Credits first
        $userTool = $this->ownedTools()->where('tool_slug', $slug)->first();
        if ($userTool && $userTool->bonus_credits >= $cost) {
            $userTool->bonus_credits -= $cost;
            $userTool->save();
            return true;
        }

        // 2. Fallback to Global Wallet
        if (!$this->wallet || $this->wallet->balance_credits < $cost) {
            return false;
        }

        $this->wallet->balance_credits -= $cost;
        $this->wallet->save();

        return true;
    }

    // ──────────────────────────────────────────────
    // LEGACY: Kept for backward compatibility
    // ──────────────────────────────────────────────

    /**
     * Get the current plan name (Legacy bridge).
     */
    public function currentPlan(): string
    {
        return ucfirst($this->subscription_tier ?? 'Marketplace');
    }

    /**
     * Get the current status (Owned/Unlocked).
     */
    public function currentStatus(string $slug): string
    {
        return $this->ownsTool($slug) ? 'Unlocked' : 'Locked';
    }

    /**
     * Check if user can use a tool today (Marketplace bridge).
     */
    public function canUseToolToday(string $slug): bool
    {
        return $this->canUseTool($slug);
    }

    /**
     * Get the available limit (Marketplace bridge).
     * Returns the total points available (Bonus + Wallet) for that tool.
     */
    public function getDailyToolLimit(string $slug = null): int
    {
        if ($this->isAdmin()) return 999999;
        if (!$slug) return 0;
        
        $bonus = $this->ownedTools()->where('tool_slug', $slug)->first()?->bonus_credits ?? 0;
        $wallet = $this->wallet->balance_credits ?? 0;
        
        return (int) ($bonus + $wallet);
    }

    /**
     * Get a user-friendly message for when a tool limit is reached (legacy bridge).
     */
    public function getLimitReachedMessage(string $toolName, string $slug): string
    {
        if (!$this->ownsTool($slug)) {
            $price = (int)Setting::get("tool_unlock_price_{$slug}", 99);
            return "يجب الاشتراك في أداة $toolName لتتمكن من استخدامها. سعر الاشتراك الشهري: " . number_format($price) . " ج.م";
        }
        
        return "رصيدك غير كافٍ لاستخدام أداة $toolName. يرجى شحن محفظتك.";
    }

    // ──────────────────────────────────────────────
    // RELATIONSHIPS
    // ──────────────────────────────────────────────

    /**
     * Get the wallet associated with the user.
     */
    public function wallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get subscriptions for the user (legacy).
     */
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the active subscription (legacy).
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();
    }

    /**
     * Get invoices for the user.
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get AI usage records for the user.
     */
    public function aiUsages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AiUsage::class);
    }

    /**
     * Get usage count for a specific tool today.
     */
    public function getToolUsageToday(string $slug): int
    {
        return $this->aiUsages()
            ->where('tool', $slug)
            ->where('status', 'success')
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
        ];
    }
}
