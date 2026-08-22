<?php

namespace App\Services\Marketplace;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getOrCreateOpenCart(User $user): Cart
    {
        return Cart::firstOrCreate(
            [
                'user_id' => $user->id,
                'status' => Cart::STATUS_OPEN,
            ],
        );
    }

    /**
     * @throws ValidationException
     */
    public function addPackage(
        User $user,
        SubscriptionPackage $package,
        int $quantity,
        string $billingInterval,
    ): CartItem {
        if (! $package->is_active) {
            throw ValidationException::withMessages([
                'subscription_package_id' => ['This package is not available.'],
            ]);
        }

        if ($billingInterval === 'yearly' && $package->price_yearly === null && (float) $package->price_monthly <= 0) {
            throw ValidationException::withMessages([
                'billing_interval' => ['Yearly billing is not configured for this package.'],
            ]);
        }

        (new PackageCatalogValidator)->assertPackageToolsAreKnown($package);
        if ($package->tools()->count() === 0) {
            throw ValidationException::withMessages([
                'subscription_package_id' => ['This package has no tools configured.'],
            ]);
        }

        return DB::transaction(function () use ($user, $package, $quantity, $billingInterval) {
            $cart = Cart::where('user_id', $user->id)
                ->where('status', Cart::STATUS_OPEN)
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'status' => Cart::STATUS_OPEN,
                ]);
            }

            $unitPrice = $package->finalPriceForInterval($billingInterval);

            $item = CartItem::where('cart_id', $cart->id)
                ->where('subscription_package_id', $package->id)
                ->where('billing_interval', $billingInterval)
                ->first();

            if ($item) {
                $item->quantity = min(99, $item->quantity + $quantity);
                $item->unit_price_snapshot = $unitPrice;
                $item->currency_snapshot = $package->currency;
                $item->package_name_snapshot = $package->name;
                $item->save();
            } else {
                $item = CartItem::create([
                    'cart_id' => $cart->id,
                    'subscription_package_id' => $package->id,
                    'quantity' => min(99, $quantity),
                    'billing_interval' => $billingInterval,
                    'unit_price_snapshot' => $unitPrice,
                    'currency_snapshot' => $package->currency,
                    'package_name_snapshot' => $package->name,
                ]);
            }

            return $item->fresh(['subscriptionPackage.tools']);
        });
    }

    public function updateItemQuantity(User $user, CartItem $item, int $quantity): CartItem
    {
        $this->assertUserOwnsCartItem($user, $item);

        if ($quantity < 1) {
            $item->delete();

            return $item;
        }

        $item->quantity = min(99, $quantity);
        $item->save();

        return $item->fresh(['subscriptionPackage.tools']);
    }

    public function removeItem(User $user, CartItem $item): void
    {
        $this->assertUserOwnsCartItem($user, $item);
        $item->delete();
    }

    /**
     * @return array{subtotal: float, currency: string, items: \Illuminate\Support\Collection}
     */
    public function summarizeCart(Cart $cart): array
    {
        $cart->load('items.subscriptionPackage.tools');
        $currency = 'EGP';
        $subtotal = 0.0;

        foreach ($cart->items as $line) {
            $subtotal += $line->lineTotal();
            $currency = $line->currency_snapshot ?: $currency;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'currency' => $currency,
            'items' => $cart->items,
        ];
    }

    /**
     * @throws ValidationException
     */
    public function validateCheckout(User $user, Cart $cart): void
    {
        if ($cart->user_id !== $user->id) {
            throw ValidationException::withMessages(['cart' => ['Invalid cart.']]);
        }
        if ($cart->status !== Cart::STATUS_OPEN) {
            throw ValidationException::withMessages(['cart' => ['This cart cannot be checked out.']]);
        }
        if ($cart->items()->count() === 0) {
            throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
        }

        foreach ($cart->items as $item) {
            $pkg = $item->subscriptionPackage;
            if (! $pkg || ! $pkg->is_active) {
                throw ValidationException::withMessages([
                    'cart' => ["Package «{$item->package_name_snapshot}» is no longer available. Remove it and try again."],
                ]);
            }
            (new PackageCatalogValidator)->assertPackageToolsAreKnown($pkg);
            if ($pkg->tools()->count() === 0) {
                throw ValidationException::withMessages([
                    'cart' => ["Package «{$pkg->name}» has no tools. Remove it from your cart."],
                ]);
            }

            $freshUnit = $pkg->finalPriceForInterval($item->billing_interval);
            if ((float) $item->unit_price_snapshot !== (float) $freshUnit) {
                throw ValidationException::withMessages([
                    'cart' => ['Prices changed. Refresh your cart and review totals before paying.'],
                ]);
            }
        }
    }

    public function lockCartForPayment(Cart $cart): void
    {
        $cart->status = Cart::STATUS_LOCKED;
        $cart->save();
    }

    public function markCartConverted(Cart $cart): void
    {
        $cart->status = Cart::STATUS_CONVERTED;
        $cart->save();
    }

    private function assertUserOwnsCartItem(User $user, CartItem $item): void
    {
        $item->loadMissing('cart');
        if (! $item->cart || $item->cart->user_id !== $user->id || $item->cart->status !== Cart::STATUS_OPEN) {
            throw ValidationException::withMessages(['cart_item' => ['Invalid cart line.']]);
        }
    }
}
