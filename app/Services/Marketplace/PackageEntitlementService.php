<?php

namespace App\Services\Marketplace;

use App\Models\Cart;
use App\Models\User;
use App\Models\UserPackageSubscription;
use App\Models\UserPackageSubscriptionTool;
use App\Models\UserTool;
use Illuminate\Support\Facades\DB;

class PackageEntitlementService
{
    /**
     * After successful payment: create subscription rows, grant tool access and per-tool credits for the current period.
     * Does not charge payment — wire this from your gateway callback once funds are confirmed.
     *
     * @return list<UserPackageSubscription>
     */
    public function activatePaidCart(
        User $user,
        Cart $cart,
        ?string $externalPaymentRef = null,
    ): array {
        $cart->load(['items.subscriptionPackage.tools']);

        return DB::transaction(function () use ($user, $cart, $externalPaymentRef) {
            $created = [];
            foreach ($cart->items as $line) {
                $pkg = $line->subscriptionPackage;
                if (! $pkg) {
                    continue;
                }

                for ($i = 0; $i < $line->quantity; $i++) {
                    $periodEnd = $line->billing_interval === 'yearly'
                        ? now()->addYear()
                        : now()->addMonth();

                    $sub = UserPackageSubscription::create([
                        'user_id' => $user->id,
                        'subscription_package_id' => $pkg->id,
                        'status' => UserPackageSubscription::STATUS_ACTIVE,
                        'billing_interval' => $line->billing_interval,
                        'unit_price_paid' => $line->unit_price_snapshot,
                        'currency' => $line->currency_snapshot,
                        'current_period_start' => now(),
                        'current_period_end' => $periodEnd,
                        'external_payment_ref' => $externalPaymentRef,
                        'cart_id' => $cart->id,
                    ]);

                    foreach ($pkg->tools as $pt) {
                        UserPackageSubscriptionTool::create([
                            'user_package_subscription_id' => $sub->id,
                            'tool_slug' => $pt->tool_slug,
                            'credits_per_cycle' => $pt->credits_per_cycle,
                        ]);

                        $this->grantToolCreditsForPeriod($user, $pt->tool_slug, (int) $pt->credits_per_cycle, $periodEnd);
                    }

                    $created[] = $sub;
                }
            }

            return $created;
        });
    }

    /**
     * Add or extend UserTool access and merge bonus credits for this billing period.
     */
    public function grantToolCreditsForPeriod(User $user, string $toolSlug, int $credits, \DateTimeInterface $periodEnd): void
    {
        $userTool = UserTool::where('user_id', $user->id)->where('tool_slug', $toolSlug)->first();

        if ($userTool) {
            $userTool->bonus_credits += $credits;
            if ($userTool->expires_at === null || $userTool->expires_at->lt($periodEnd)) {
                $userTool->expires_at = $periodEnd;
                $userTool->renews_at = $periodEnd;
            }
            $userTool->save();
        } else {
            UserTool::create([
                'user_id' => $user->id,
                'tool_slug' => $toolSlug,
                'price_paid' => 0,
                'bonus_credits' => $credits,
                'expires_at' => $periodEnd,
                'renews_at' => $periodEnd,
                'auto_renew' => false,
            ]);
        }
    }

    /**
     * Apply the next cycle's credits for SaaS package renewals (scheduled job / gateway webhook).
     */
    public function renewSubscription(UserPackageSubscription $sub): void
    {
        if (! $sub->isActive()) {
            return;
        }

        $sub->load('tools', 'user');
        $user = $sub->user;
        if (! $user) {
            return;
        }

        $base = $sub->current_period_end ?? now();
        $nextEnd = $sub->billing_interval === 'yearly'
            ? $base->copy()->addYear()
            : $base->copy()->addMonth();

        DB::transaction(function () use ($sub, $user, $nextEnd) {
            foreach ($sub->tools as $t) {
                $this->grantToolCreditsForPeriod($user, $t->tool_slug, (int) $t->credits_per_cycle, $nextEnd);
            }
            $sub->current_period_start = $sub->current_period_end ?? now();
            $sub->current_period_end = $nextEnd;
            $sub->save();
        });
    }
}
