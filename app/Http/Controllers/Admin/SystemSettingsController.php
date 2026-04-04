<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\DB;

class SystemSettingsController extends Controller
{
    /**
     * Display the system settings management page.
     */
    public function index()
    {
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

        return view('admin.horizon.settings', compact('tools', 'settings', 'stats', 'walletBalance'));
    }

    public function update(Request $request)
    {
        $input = $request->except('_token');
        $tools = config('tools.all_tools', []);
        $envData = [];
        
        // Handle all input fields
        foreach ($input as $key => $value) {
            if ($key === 'packages' && is_array($value)) {
                // Ensure popular toggles are consistently boolean-like
                foreach(['lite', 'standard', 'pro', 'enterprise'] as $pkgKey) {
                    if (isset($value[$pkgKey])) {
                        $value[$pkgKey]['popular'] = isset($value[$pkgKey]['popular']) && $value[$pkgKey]['popular'] == '1';
                        unset($value[$pkgKey]['popular_hidden']);
                    }
                }
                Setting::set('marketplace_packages', json_encode($value), 'json', 'marketplace');
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

            Setting::set($key, $value, $type, $group);
        }

        // Persist .env changes if any
        if (!empty($envData)) {
            try {
                $this->updateEnv($envData);
            } catch (\Exception $e) {
                // Log or handle error if .env is not writable
            }
        }

        // Handle Checkboxes (ensure false is saved if missing)
        if (!$request->has('markdown_ai_enabled')) {
            Setting::set('markdown_ai_enabled', false, 'boolean', 'markdown_ai');
        }

        foreach ($tools as $tool) {
            $availKey = "tool_available_{$tool['slug']}";
            if (!$request->has($availKey)) {
                Setting::set($availKey, false, 'boolean', 'tool_availability');
            }
        }

        return back()->with('success', 'System settings matrix updated successfully.');
    }

    public function apiKeys()
    {
        $keys = [
            'OPENAI_API_KEY' => env('OPENAI_API_KEY'),
            'GEMINI_API_KEY' => env('GEMINI_API_KEY'),
            'OPENROUTER_API_KEY' => env('OPENROUTER_API_KEY'),
            'FAWATERK_API_KEY' => env('FAWATERK_API_KEY'),
            'FAWATERK_VENDOR_KEY' => env('FAWATERK_VENDOR_KEY'),
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
        $data = $request->only([
            'OPENAI_API_KEY', 
            'GEMINI_API_KEY', 
            'OPENROUTER_API_KEY',
            'FAWATERK_API_KEY',
            'FAWATERK_VENDOR_KEY'
        ]);
        
        try {
            $this->updateEnv($data);
            return back()->with('success', 'General API Keys updated successfully and persisted to .env');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update .env file: ' . $e->getMessage());
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
        
        if (!file_exists($path)) {
            throw new \Exception(".env file not found at " . $path);
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
        
        // Clear config cache if possible (requires artisan or clear cache manually)
        // In some shared hosting, we might just rely on the next request.
    }
}
