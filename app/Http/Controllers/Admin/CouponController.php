<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Repositories\Coupons\CouponRepository;
use App\Services\Logging\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected CouponRepository $couponRepository
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Coupon::class);

        $data = $request->validate([
            'code'             => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_\-]+$/i', 'unique:coupons,code'],
            'description'      => ['nullable', 'string', 'max:255'],
            'scope'            => ['required', 'string', 'in:all_tools,specific_tool'],
            'tool_slug'        => ['nullable', 'string', 'max:100', 'required_if:scope,specific_tool'],
            'credits'          => ['required', 'integer', 'min:1'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'expires_at'       => ['nullable', 'date', 'after:now'],
        ], [
            'code.regex'   => 'Code may only contain letters, numbers, underscores and dashes.',
            'code.unique'  => 'A coupon with this code already exists.',
            'expires_at.after' => 'Expiry date must be in the future.',
        ]);

        $data['code'] = strtoupper($data['code']);

        if (($data['scope'] ?? '') === 'all_tools') {
            $data['tool_slug'] = null;
        }

        $coupon = $this->couponRepository->create($data);
        $this->auditLogService->log(
            $request->user()?->id,
            'coupon.create',
            Coupon::class,
            $coupon->id,
            null,
            $coupon->toArray()
        );

        return back()
            ->with('coupon_success', 'Coupon "' . $data['code'] . '" created successfully.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->authorize('delete', $coupon);

        $oldValues = $coupon->toArray();
        $code = $coupon->code;
        $coupon->delete();
        $this->auditLogService->log(
            request()->user()?->id,
            'coupon.delete',
            Coupon::class,
            $oldValues['id'] ?? null,
            $oldValues,
            null
        );

        return back()
            ->with('coupon_success', 'Coupon "' . $code . '" has been deleted.');
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $this->authorize('update', $coupon);

        $oldValues = $coupon->toArray();
        $coupon = $this->couponRepository->toggle($coupon);
        $this->auditLogService->log(
            request()->user()?->id,
            'coupon.toggle',
            Coupon::class,
            $coupon->id,
            $oldValues,
            $coupon->fresh()->toArray()
        );
        $state = $coupon->is_active ? 'activated' : 'deactivated';

        return back()
            ->with('coupon_success', 'Coupon "' . $coupon->code . '" has been ' . $state . '.');
    }
}
