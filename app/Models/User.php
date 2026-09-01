<?php

namespace App\Models;

use App\Notifications\QueuedResetPassword as QueuedResetPasswordNotification;
use App\Notifications\QueuedVerifyEmail as QueuedVerifyEmailNotification;
use App\Services\Credits\ToolCreditConsumptionService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'subscription_tier', // Legacy — kept for backward compatibility
        'phone',
        'country',
        'settings',
        'oauth_provider',
        'oauth_provider_id',
        'avatar_url',
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
        return $this->role === 'admin'
            || $this->hasRole('admin')
            || $this->hasRole('super_admin');
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
        if ($this->isAdmin()) {
            return true;
        }

        $userTool = $this->relationLoaded('ownedTools')
            ? $this->ownedTools->firstWhere('tool_slug', $slug)
            : $this->ownedTools()->where('tool_slug', $slug)->first();

        if (! $userTool) {
            return false;
        }

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
        $packageSlugs = $this->packageSubscriptions()
            ->active()
            ->with('tools')
            ->get()
            ->pluck('tools')
            ->flatten()
            ->pluck('tool_slug')
            ->unique()
            ->values()
            ->all();
        $allowed = array_unique(array_merge($ownedSlugs, $packageSlugs));

        return array_values(array_filter($allTools, function ($tool) use ($allowed) {
            return in_array($tool['slug'], $allowed, true);
        }));
    }

    /**
     * Check if user can use a specific tool (owns it + has enough credits).
     */
    public function canUseTool(string $slug, ?int $explicitCost = null): bool
    {
        return app(ToolCreditConsumptionService::class)->canUse($this, $slug, $explicitCost);
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
     * Deduct credits for a tool action (wallet first, then per-tool bonus when allowed).
     */
    public function deductToolCredits(string $slug, ?int $explicitCost = null): bool
    {
        return app(ToolCreditConsumptionService::class)->deduct($this, $slug, $explicitCost);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->logAuthMailDispatch('verify_email');
        $this->notifyNow(new QueuedVerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->logAuthMailDispatch('password_reset');
        $this->notifyNow(new QueuedResetPasswordNotification($token));
    }

    /**
     * Debug tap for every auth-related email dispatch (verify, reset, etc.).
     * Writes to the `mail` log channel so all auth sends share one audit trail.
     */
    protected function logAuthMailDispatch(string $event): void
    {
        $request = request();
        $caller = app()->runningInConsole()
            ? 'console'
            : ($request?->method().' '.($request?->fullUrl() ?? 'n/a'));

        Log::channel('mail')->info('auth_mail.dispatch', [
            'event' => $event,
            'user_id' => $this->getKey(),
            'email' => $this->email,
            'verified' => (bool) $this->email_verified_at,
            'mailer' => config('mail.default'),
            'from' => config('mail.from.address'),
            'queue_connection' => config('queue.default'),
            'queue_name' => 'emails',
            'caller' => $caller,
        ]);
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
     * Returns CRS usable for AI on this tool (wallet + bonus only when bonus may be consumed for usage).
     */
    public function getDailyToolLimit(string $slug = null): int
    {
        if ($this->isAdmin()) {
            return 999999;
        }
        if (! $slug) {
            return 0;
        }

        $ut = $this->ownedTools()->where('tool_slug', $slug)->first();
        $bonus = 0;
        if ($ut && ($ut->allow_bonus_for_ai_usage ?? true)) {
            $bonus = (int) ($ut->bonus_credits ?? 0);
        }

        $wallet = (int) ($this->wallet->balance_credits ?? 0);

        return $wallet + $bonus;
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
     * External OAuth identities linked to this user (Google, GitHub, Microsoft, …).
     */
    public function socialAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get subscriptions for the user (legacy).
     */
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * SaaS bundles: a user may hold multiple active package subscriptions at once.
     */
    public function packageSubscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserPackageSubscription::class);
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
