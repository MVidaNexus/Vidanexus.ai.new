<?php

namespace App\Repositories\Coupons;

use App\Models\Coupon;

class CouponRepository
{
    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }

    public function toggle(Coupon $coupon): Coupon
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);
        return $coupon->fresh();
    }
}
