<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Setting;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard — Marketplace edition.
     */
    public function index()
    {
        $user = Auth::user();
        $walletBalance = $user->wallet ? $user->wallet->balance_credits : 0;

        $invoices = $user->invoices()->latest()->take(10)->get();

        // Get owned tool slugs
        $ownedSlugs = $user->isAdmin()
            ? collect(config('tools.all_tools', []))->pluck('slug')->toArray()
            : $user->ownedTools()->pluck('tool_slug')->toArray();

        $todayUsage = \DB::table('ai_usages')
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->select('tool', \DB::raw('count(*) as cnt'))
            ->groupBy('tool')
            ->pluck('cnt', 'tool');

        $allToolsData = config('tools.all_tools', []);

        $tools = collect($allToolsData)->map(function ($tool) use ($user, $ownedSlugs, $todayUsage) {
            $isAvailable = (bool) Setting::get("tool_available_{$tool['slug']}", false);
            $isOwned = in_array($tool['slug'], $ownedSlugs);

            if ($user->isAdmin()) {
                $tool['accessible'] = true;
                $tool['is_available'] = true;
                $tool['is_owned'] = true;
            } else {
                $tool['is_available'] = $isAvailable;
                $tool['is_owned'] = $isOwned;
                $tool['accessible'] = $isOwned && $isAvailable;
            }

            // Marketplace pricing (admin-overridable)
            $tool['unlock_price'] = (int) Setting::get("tool_unlock_price_{$tool['slug']}", $tool['unlock_price'] ?? 99);
            $tool['credit_cost'] = (int) Setting::get("tool_credit_cost_{$tool['slug']}", $tool['credit_cost_per_action'] ?? 1);
            $tool['bonus_credits'] = (int) Setting::get("tool_bonus_credits_{$tool['slug']}", $tool['initial_bonus_credits'] ?? 10);
            $tool['required_label'] = ucfirst($tool['required_tier'] ?? 'Marketplace');
            $tool['used_today'] = $todayUsage[$tool['slug']] ?? 0;

            return $tool;
        })->sortByDesc('accessible')->values()->toArray();

        $ownedCount = collect($tools)->where('is_owned', true)->count();

        return view('dashboard', [
            'user' => $user,
            'walletBalance' => $walletBalance,
            'tools' => $tools,
            'currentPlan' => $user->currentPlan(),
            'invoices' => $invoices,
            'ownedCount' => $ownedCount,
            'accessibleCount' => $ownedCount, // For backward compatibility in view
            'totalTools' => count($tools),
        ]);
    }

    /**
     * Update user settings.
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'country' => 'required|string|max:100',
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->filled('password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return redirect('/dashboard#settings')->withErrors(['current_password' => 'The current password you entered is incorrect.']);
            }
            $user->password = Hash::make($request->password);
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->country = $request->country;
        $user->save();

        return redirect('/dashboard#settings')->with('success', 'Account settings updated successfully.');
    }
}
