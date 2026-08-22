<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function create(User $user): bool
    {
        return $user->can('manage_coupons') || $user->isAdmin();
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $this->create($user);
    }
}
