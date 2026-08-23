<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;

class SystemSettingsController extends Controller
{
    /**
     * Display the system settings management page.
     */
    public function index(?string $tab = null)
    {
        $allowedTabs = [
            'availability',
            'welcome',
            'credit-system',
            'trial',
            'coupons',
            'packages',
            'smtp',
            'scripts',
            'infrastructure',
            'ledger',
            'command',
            'markdown',
            'countries',
        ];
        $activeTab = in_array($tab, $allowedTabs, true) ? $tab : 'availability';

        $tools = config('tools.all_tools', []);
        $settings = Setting::getAllSettings();

        // System Stats for Infrastructure & Ledger Tabs
        $stats = [
            'total_users' => User::count(),
            'total_credits' => Wallet::sum('balance_credits'),
            'active_modules' => count(Module::allEnabled()),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'latest_transactions' => DB::table('transactions')
                ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
                ->join('users', 'wallets.user_id', '=', 'users.id')
                ->select('transactions.*', 'users.name as user_name', 'users.email as user_email')
                ->latest('transactions.created_at')
                ->take(15)
                ->get(),
        ];

        $walletBalance = auth()->user()->wallet->balance_credits ?? 0;

        $coupons = Coupon::with('assignedUser')
            ->withCount('redemptions')
            ->latest()
            ->get();

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.horizon.settings', compact('tools', 'settings', 'stats', 'walletBalance', 'coupons', 'users', 'activeTab', 'allowedTabs'));
    }

    public function update(Request $request, ?string $tab = null)
    {
        try {
            $allowedTabs = [
                'availability',
                'welcome',
                'credit-system',
                'trial',
                'coupons',
                'packages',
                'smtp',
                'scripts',
                'infrastructure',
                'ledger',
                'command',
                'markdown',
                'countries',
            ];
            $activeTab = in_array($tab, $allowedTabs, true) ? $tab : 'availability';
            $input = $request->except('_token');

            // Transparently decode any base64 encoded input fields (to bypass aggressive WAF/ModSecurity on shared hosting)
            foreach ($input as $k => $v) {
                if (is_string($v) && str_starts_with($k, '_b64_')) {
                    $origKey = substr($k, 5);
                    $decoded = base64_decode($v);
                    if ($decoded !== false) {
                        $input[$origKey] = $decoded;
                        $request->merge([$origKey => $decoded]);
                    }
                }
            }

            $tools = config('tools.all_tools', []);
            $envData = [];

            // ─── Country Registry ─────────────────────────────────────
            // The "Countries" tab writes a master multiline list and a
            // per-country visibility array. Saved under reserved keys so
            // tools never collide with their own per-tool country lists.
            if ($activeTab === 'countries') {
                $registryText = (string) $request->input('global_country_registry', '');
                Setting::set('global_country_registry', $registryText, 'textarea', 'country_registry');

                $visible = $request->input('global_country_visibility', []);
                if (! is_array($visible)) {
                    $visible = [];
                }
                $visible = array_values(array_filter(array_map(
                    fn ($c) => \App\Support\CountryRegistry::normalizeCode((string) $c),
                    $visible
                )));
                Setting::set('global_country_visibility', json_encode($visible), 'json', 'country_registry');

                // Forget the per-key cache wrapper as well so the next
                // page load re-reads from the database.
                \Illuminate\Support\Facades\Cache::forget('setting_global_country_registry');
                \Illuminate\Support\Facades\Cache::forget('setting_global_country_visibility');
                \App\Support\CountryRegistry::clearGlobalCache();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Country registry updated. Hidden countries will disappear from every tool on the next request.',
                    ]);
                }

                return redirect()->route('admin.horizon.settings.tab', ['tab' => 'countries'])
                    ->with('success', 'Country registry updated. Hidden countries will disappear from every tool on the next request.');
            }

            // Handle all input fields
            foreach ($input as $key => $value) {
                if ($key === 'packages' && is_array($value)) {
                    // Ensure popular toggles are consistently boolean-like
                    foreach (['lite', 'standard', 'pro', 'enterprise'] as $pkgKey) {
                        if (isset($value[$pkgKey])) {
                            $value[$pkgKey]['popular'] = isset($value[$pkgKey]['popular']) && $value[$pkgKey]['popular'] == '1';
                            unset($value[$pkgKey]['popular_hidden']);
                        }
                    }
                    Setting::set('marketplace_packages', json_encode($value), 'json', 'marketplace');

                    continue;
                }

                // Skip array values that aren't packages (safety check)
                if (is_array($value)) {
                    continue;
                }

                $type = 'integer';
                $group = 'general';

                if (str_starts_with($key, 'plan_credits_')) {
                    $group = 'plan_credits';
                } elseif (str_starts_with($key, 'plan_active_')) {
                    $group = 'plan_activation';
                    $type = 'boolean';
                    $value = ($value === 'on' || $value == 1);
                } elseif (str_starts_with($key, 'plan_tool_access_')) {
                    $group = 'tool_access';
                    $type = 'boolean';
                    $value = ($value === 'on' || $value == 1);
                } elseif (str_starts_with($key, 'plan_tool_limit_')) {
                    $group = 'tool_limits';
                } elseif (str_starts_with($key, 'tool_available_')) {
                    $group = 'tool_availability';
                    $type = 'boolean';
                    $value = ($value === 'on' || $value == 1);
                } elseif (str_starts_with($key, 'tool_unlock_price_')) {
                    $group = 'marketplace_pricing';
                    $type = 'integer';
                } elseif (str_starts_with($key, 'tool_credit_cost_')) {
                    $group = 'tool_usage_pricing';
                    $type = 'integer';
                } elseif (str_starts_with($key, 'tool_bonus_credits_')) {
                    $group = 'marketplace_bonuses';
                    $type = 'integer';
                } elseif (str_ends_with($key, '_provider')) {
                    $group = 'tool_ai_config';
                    $type = 'text';
                } elseif (str_ends_with($key, '_model')) {
                    $group = 'tool_ai_config';
                    $type = 'text';
                } elseif (str_starts_with($key, 'trial_tool_')) {
                    $group = 'trial_package';
                    $type = 'boolean';
                    $value = ($value === 'on' || $value == 1);
                } elseif (str_starts_with($key, 'markdown_ai_')) {
                    $group = 'markdown_ai';
                    $type = str_contains($key, '_enabled') ? 'boolean' : (str_contains($key, '_ttl') ? 'integer' : 'text');
                    if ($type === 'boolean') {
                        $value = ($value === 'on' || $value == 1);
                    }
                } elseif ($key === 'footer_script') {
                    $group = 'scripts';
                    $type = 'text';
                }

                // Handle SMTP / .env keys separately
                if (str_starts_with($key, 'MAIL_')) {
                    $envData[$key] = $value;

                    continue;
                }

                if (
                    str_starts_with($key, 'plan_credits_') ||
                    str_starts_with($key, 'tool_credit_cost_') ||
                    str_starts_with($key, 'tool_bonus_credits_')
                ) {
                    $value = max(0, (int) $value);
                }

                Setting::set($key, $value, $type, $group);

                // Reverse-mirror: when the canonical per-tool cost is saved,
                // also keep the legacy per-tool admin page key in lockstep so
                // both views display the same number. The per-tool admin form
                // writes the opposite direction in HorizonController.
                if (str_starts_with($key, 'tool_credit_cost_')) {
                    $slug = substr($key, strlen('tool_credit_cost_'));
                    $legacyKey = "{$slug}_sync_credits";
                    Setting::set($legacyKey, max(0, (int) $value), 'number', 'tool_settings');
                }
            }

            // Persist .env changes if any
            if (! empty($envData)) {
                try {
                    $this->updateEnv($envData);
                } catch (\Exception $e) {
                    \Log::warning('Failed to update .env: '.$e->getMessage());
                }
            }

            // Handle Checkboxes per tab (ensure unchecked boxes are saved as false without affecting other tabs)
            if ($activeTab === 'markdown') {
                Setting::set('markdown_ai_enabled', $request->has('markdown_ai_enabled'), 'boolean', 'markdown_ai');
            }

            foreach ($tools as $tool) {
                $slug = $tool['slug'] ?? null;
                if (! $slug) {
                    continue;
                }

                if ($activeTab === 'availability') {
                    $availKey = "tool_available_{$slug}";
                    Setting::set($availKey, $request->has($availKey), 'boolean', 'tool_availability');
                }

                if ($activeTab === 'trial') {
                    $trialKey = "trial_tool_{$slug}";
                    Setting::set($trialKey, $request->has($trialKey), 'boolean', 'trial_package');
                }

                // Guarantee marketplace keys exist for every tool if not already present
                $unlockKey = "tool_unlock_price_{$slug}";
                $creditKey = "tool_credit_cost_{$slug}";
                $bonusKey = "tool_bonus_credits_{$slug}";

                if (Setting::get($unlockKey, null) === null) {
                    Setting::set($unlockKey, max(0, (int) ($tool['unlock_price'] ?? 99)), 'integer', 'marketplace_pricing');
                }
                if (Setting::get($creditKey, null) === null) {
                    Setting::set($creditKey, max(0, (int) ($tool['credit_cost_per_action'] ?? 1)), 'integer', 'tool_usage_pricing');
                }
                if (Setting::get($bonusKey, null) === null) {
                    Setting::set($bonusKey, max(0, (int) ($tool['initial_bonus_credits'] ?? 10)), 'integer', 'marketplace_bonuses');
                }
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'System settings matrix updated successfully.',
                ]);
            }

            return redirect()->route('admin.horizon.settings.tab', ['tab' => $activeTab])
                ->with('success', 'System settings matrix updated successfully.');

        } catch (\Throwable $e) {
            \Log::error('SystemSettings update CRASHED: '.$e->getMessage()."\n".$e->getTraceAsString());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error saving settings: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.horizon.settings.tab', ['tab' => $activeTab ?? 'availability'])
                ->with('error', 'Error saving settings: '.$e->getMessage());
        }
    }

    public function apiKeys()
    {
        $keys = [
            'OPENAI_API_KEY' => env('OPENAI_API_KEY'),
            'GEMINI_API_KEY' => env('GEMINI_API_KEY'),
            'OPENROUTER_API_KEY' => env('OPENROUTER_API_KEY'),
            'FAWATERK_API_KEY' => env('FAWATERK_API_KEY'),
            'FAWATERK_VENDOR_KEY' => env('FAWATERK_VENDOR_KEY'),
            'FAWATERK_SANDBOX_MODE' => config('services.fawaterk.sandbox_mode'),
        ];

        // Also get tool routing for the second tab
        $allSettings = Setting::all();
        $toolConfig = [];
        $tools = config('tools.all_tools', []);

        foreach ($allSettings as $setting) {
            if (preg_match('/^([a-z0-9-]+)_(provider|model|api_key|is_active|ai_chain)$/', $setting->key, $matches)) {
                $toolSlug = $matches[1];
                $prop = $matches[2];
                $toolConfig[$toolSlug][$prop] = ($prop === 'ai_chain' && is_string($setting->value)) ? json_decode($setting->value, true) : $setting->value;
            } elseif (str_starts_with($setting->key, 'tool_available_')) {
                $toolSlug = str_replace('tool_available_', '', $setting->key);
                $toolConfig[$toolSlug]['is_active'] = $setting->value;
            }
        }

        return view('admin.horizon.api-keys', compact('keys', 'toolConfig', 'tools'));
    }

    public function updateApiKeys(Request $request)
    {
        $request->validate([
            'FAWATERK_SANDBOX_MODE' => 'required|in:auto,sandbox,live',
        ]);

        $data = $request->only([
            'OPENAI_API_KEY',
            'GEMINI_API_KEY',
            'OPENROUTER_API_KEY',
            'FAWATERK_API_KEY',
            'FAWATERK_VENDOR_KEY',
        ]);
        $data['FAWATERK_SANDBOX'] = match ($request->input('FAWATERK_SANDBOX_MODE')) {
            'auto' => 'auto',
            'sandbox' => 'true',
            'live' => 'false',
        };

        try {
            $this->updateEnv($data);

            return back()->with('success', 'General API Keys updated successfully and persisted to .env');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update .env file: '.$e->getMessage());
        }
    }

    public function apiReference()
    {
        // 1. Get .env Keys (Global)
        $envKeys = [
            'OPENAI_API_KEY' => env('OPENAI_API_KEY'),
            'GEMINI_API_KEY' => env('GEMINI_API_KEY'),
            'OPENROUTER_API_KEY' => env('OPENROUTER_API_KEY'),
            'FAWATERK_API_KEY' => env('FAWATERK_API_KEY'),
            'FAWATERK_VENDOR_KEY' => env('FAWATERK_VENDOR_KEY'),
            'FAWATERK_SANDBOX' => config('services.fawaterk.sandbox_mode')
                .' → '.(config('services.fawaterk.sandbox') ? 'staging' : 'live')
                .' (APP_ENV='.config('app.env').')',
        ];

        // 2. Scrape Database Settings
        $allSettings = Setting::all();
        $databaseKeys = [];
        $toolConfig = [];

        foreach ($allSettings as $setting) {
            $key = $setting->key;
            $value = $setting->value;

            // Group tool-specific config (provider, model, etc)
            if (preg_match('/^([a-z0-9-]+)_(provider|model|api_key|is_active|ai_chain)$/', $key, $matches)) {
                $toolSlug = $matches[1];
                $prop = $matches[2];
                $toolConfig[$toolSlug][$prop] = ($prop === 'ai_chain' && is_string($value)) ? json_decode($value, true) : $value;
            } elseif (str_starts_with($key, 'tool_available_')) {
                $toolSlug = str_replace('tool_available_', '', $key);
                $toolConfig[$toolSlug]['is_active'] = $value;
            }
            // Generic API/Token/Secret keys
            elseif (str_contains(strtolower($key), 'key') ||
                str_contains(strtolower($key), 'token') ||
                str_contains(strtolower($key), 'secret') ||
                str_contains(strtolower($key), 'credential')) {
                $databaseKeys[$key] = $value;
            }
        }

        $tools = config('tools.all_tools', []);

        return view('admin.horizon.api-reference', compact('envKeys', 'databaseKeys', 'toolConfig', 'tools'));
    }

    protected function updateEnv(array $data)
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
            throw new \Exception('.env file not found at '.$path);
        }

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
            // Secure value (remove any newlines or dangerous characters)
            $value = str_replace(["\n", "\r"], '', $value);

            // Check if key exists
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                // Append if not exists
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($path, $content);

        try {
            Artisan::call('config:clear');
        } catch (\Throwable) {
            // Best-effort so .env edits still persist if artisan is unavailable.
        }
    }
}
