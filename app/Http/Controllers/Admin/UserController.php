<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of all users for admin management.
     */
    public function index()
    {
        $users = User::with('wallet')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Update a user's wallet balance.
     */
    public function updateBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $newBalance = $request->amount;
        $oldBalance = $user->wallet->balance_credits ?? 0;
        
        $difference = $newBalance - $oldBalance;

        $user->wallet->update(['balance_credits' => $newBalance]);

        if ($difference != 0) {
            // Record transaction
            DB::table('transactions')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'wallet_id' => $user->wallet->id,
                'amount' => abs($difference),
                'type' => $difference > 0 ? 'deposit' : 'withdrawal',
                'tool_name' => 'Admin Direct Adjustment',
                'idempotency_key' => 'admin-adj-' . time() . '-' . $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', "Updated {$user->name}'s balance to {$newBalance} CRS.");
    }

    /**
     * Update user's subscription tier.
     */
    public function updateTier(Request $request, User $user)
    {
        $request->validate([
            'tier' => 'required|in:beginner,starter,growth,pro,ultimate,agency',
            'daily_limit' => 'nullable|integer|min:0',
        ]);

        // If manual shift to starter, or any shift, we cancel active DB subscriptions to avoid logic conflicts
        \App\Models\Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $settings = $user->settings ?? [];
        if ($request->has('daily_limit') && $request->daily_limit !== null) {
            $settings['daily_limit'] = (int)$request->daily_limit;
        } else {
            unset($settings['daily_limit']);
        }

        $user->update([
            'subscription_tier' => $request->tier,
            'settings' => $settings
        ]);

        // Reset balance based on new tier (Additive Logic)
        $dbCredits = Setting::get("plan_credits_{$request->tier}");
        
        if ($dbCredits !== null && is_numeric($dbCredits)) {
            $tierCredits = (int)$dbCredits;
        } else {
            $tierCredits = match($request->tier) {
                'beginner' => 4,
                'starter' => 300,
                'growth' => 1750,
                'pro' => 5500,
                'ultimate' => 20000,
                'agency' => 50000,
                default => 0,
            };
        }

        if ($user->wallet) {
            $newBalance = ($user->wallet->balance_credits ?? 0) + $tierCredits;
            $user->wallet->update(['balance_credits' => $newBalance]);
        }

        return back()->with('success', "Updated {$user->name} to {$request->tier} tier and added {$tierCredits} CRS (Total: {$newBalance} CRS).");
    }

    /**
     * Update user's manual tool overrides (Marketplace Ownership).
     */
    public function updateTools(Request $request, User $user)
    {
        $toolStates = $request->input('tools', []); // [slug => "1" or "0"]
        
        foreach ($toolStates as $slug => $value) {
            if ($value === "1") {
                // Ensure tool is UNLOCKED (Owned)
                \App\Models\UserTool::updateOrCreate(
                    ['user_id' => $user->id, 'tool_slug' => $slug],
                    ['price_paid' => 0, 'bonus_credits' => 0] // Admin unlock = free
                );
            } elseif ($value === "0") {
                // LOCK the tool (Revoke ownership)
                \App\Models\UserTool::where('user_id', $user->id)
                    ->where('tool_slug', $slug)
                    ->delete();
            }
        }

        return back()->with('success', "Updated marketplace access permissions for {$user->name}.");
    }

    /**
     * Update user password manually by admin.
     */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', "Password for {$user->name} has been updated successfully.");
    }

    /**
     * Update user email manually by admin.
     */
    public function updateEmail(Request $request, User $user)
    {
        $request->validate([
            'new_email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'email' => $request->new_email
        ]);

        return back()->with('success', "Email for {$user->name} has been updated successfully.");
    }

    /**
     * Start impersonating a user.
     */
    public function impersonate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', "You are already logged in as this user.");
        }

        session(['impersonate_admin_id' => auth()->id()]);
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('dashboard')->with('success', "You are now impersonating {$user->name}.");
    }

    /**
     * Stop impersonating and return to admin.
     */
    public function stopImpersonating()
    {
        $adminId = session('impersonate_admin_id');
        
        if (!$adminId) {
            return redirect()->route('home');
        }

        $admin = User::find($adminId);
        
        if ($admin) {
            \Illuminate\Support\Facades\Auth::login($admin);
            session()->forget('impersonate_admin_id');
            return redirect()->route('admin.users.index')->with('success', "Returned to Admin session.");
        }

        return redirect()->route('login');
    }

    /**
     * Delete a user.
     */
    public function delete(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', "You cannot delete yourself!");
        }

        $user->delete();

        return back()->with('success', "User deleted successfully.");
    }

    public function toggleVerification()
    {
        $current = (bool) \App\Models\Setting::get('global_email_verification', true);
        \App\Models\Setting::set('global_email_verification', !$current, 'boolean', 'security');
        
        $status = !$current ? 'ENABLED' : 'DISABLED';
        return back()->with('success', "Email Verification has been $status globally.");
    }
}
