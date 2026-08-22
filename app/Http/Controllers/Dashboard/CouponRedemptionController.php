<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\FinancialLedgerEntry;
use App\Models\Transaction;
use App\Models\UserTool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CouponRedemptionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'coupon_code' => ['required', 'string'],
        ]);

        $user = $request->user();
        $code = strtoupper(trim($request->input('coupon_code')));

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return back()
                ->withErrors(['coupon_code' => 'Invalid coupon code. Please check and try again.'])
                ->withInput();
        }

        [$valid, $reason] = $coupon->isValid($user);

        if (! $valid) {
            return back()
                ->withErrors(['coupon_code' => $reason])
                ->withInput();
        }

        if (($coupon->scope ?? 'all_tools') === 'specific_tool') {
            $userTool = UserTool::where('user_id', $user->id)
                ->where('tool_slug', $coupon->tool_slug)
                ->first();

            if (! $userTool) {
                return back()
                    ->withErrors(['coupon_code' => 'You must own this tool before applying this coupon.'])
                    ->withInput();
            }

            DB::transaction(function () use ($coupon, $user, $userTool) {
                CouponRedemption::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $user->id,
                    'credits_granted' => $coupon->credits,
                    'redeemed_at' => now(),
                ]);

                $coupon->increment('used_count');

                $userTool->bonus_credits = (int) $userTool->bonus_credits + (int) $coupon->credits;
                $userTool->allow_bonus_for_ai_usage = true;
                $userTool->save();

                FinancialLedgerEntry::create([
                    'user_id' => $user->id,
                    'event_type' => 'coupon_tool_bonus',
                    'wallet_delta' => 0,
                    'bonus_delta' => (int) $coupon->credits,
                    'tool_slug' => $coupon->tool_slug,
                    'reference' => 'COUPON_'.$coupon->id.'_USER_'.$user->id,
                    'meta' => ['code' => $coupon->code, 'scope' => 'specific_tool'],
                ]);
            });

            Log::info('Coupon redeemed (tool bonus)', [
                'user_id' => $user->id,
                'coupon' => $coupon->code,
                'tool_slug' => $coupon->tool_slug,
                'credits' => $coupon->credits,
            ]);

            return back()->with(
                'coupon_redeemed',
                '🎉 Coupon applied! '.number_format($coupon->credits).' bonus CRS were added to your tool pool (not the global wallet).'
            );
        }

        DB::transaction(function () use ($coupon, $user) {
            CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'credits_granted' => $coupon->credits,
                'redeemed_at' => now(),
            ]);

            $coupon->increment('used_count');

            $user->wallet->increment('balance_credits', $coupon->credits);

            Transaction::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $user->wallet->id,
                'type' => 'deposit',
                'amount' => $coupon->credits,
                'tool_name' => 'Coupon: '.$coupon->code,
                'idempotency_key' => 'COUPON_'.$coupon->id.'_USER_'.$user->id,
            ]);

            FinancialLedgerEntry::create([
                'user_id' => $user->id,
                'event_type' => 'coupon_wallet',
                'wallet_delta' => (int) $coupon->credits,
                'bonus_delta' => 0,
                'reference' => 'COUPON_'.$coupon->id.'_USER_'.$user->id,
                'meta' => ['code' => $coupon->code, 'scope' => 'all_tools'],
            ]);
        });

        Log::info('Coupon redeemed', [
            'user_id' => $user->id,
            'coupon' => $coupon->code,
            'credits' => $coupon->credits,
        ]);

        return back()->with(
            'coupon_redeemed',
            '🎉 Coupon applied! '.number_format($coupon->credits).' credits have been added to your wallet.'
        );
    }
}
