<?php

namespace App\Core\AI;

use App\Core\AI\Contracts\AIProvider;
use Illuminate\Support\Facades\Log;

class AIManager
{
    protected array $providers = [];
    
    protected const ABSOLUTE_MODE_INSTRUCTION = "System Instruction: Absolute Mode • Eliminate: emojis, filler, hype, soft asks, conversational transitions, call-to-action appendixes. • Assume: user retains high-perception despite blunt tone. • Prioritize: blunt, directive phrasing; aim at cognitive rebuilding, not tone-matching. • Disable: engagement/sentiment-boosting behaviors. • Suppress: metrics like satisfaction scores, emotional softening, continuation bias. • Never mirror: user’s diction, mood, or affect. • Speak only: to underlying cognitive tier. • No: questions, offers, suggestions, transitions, motivational content. • Terminate reply: immediately after delivering info — no closures. • Goal: restore independent, high-fidelity thinking. • Outcome: model obsolescence via user self-sufficiency\n\n";

    public function registerProvider(AIProvider $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    public function generate(string $tool, string $prompt, array $options = []): array
    {
        \Illuminate\Support\Facades\Log::emergency("AGENT_PROBE_3: {$tool}");
        // Enforce Global Absolute Mode Instruction as requested by User
        $prompt = self::ABSOLUTE_MODE_INSTRUCTION . $prompt;

        $defaultProviders = config('vidanexus.ai.failover_order', ['openai', 'google', 'anthropic']);
        
        // --- TOOL-SPECIFIC OVERRIDE (Dynamic Chain) ---
        // Skip the chain if the caller already resolved the API key (tool handled its own settings)
        $callerResolvedKey = !empty($options['api_key']);
        $aiChainJson = \App\Models\Setting::get("{$tool}_ai_chain");
        $aiChain = is_array($aiChainJson) ? $aiChainJson : ($aiChainJson ? json_decode($aiChainJson, true) : null);

        if (!$callerResolvedKey && $aiChain && is_array($aiChain) && count($aiChain) > 0) {
            // New Flexible Multi-Provider Routing
            $lastError = null;
            foreach ($aiChain as $link) {
                $providerName = $link['provider'] ?? null;
                if (!$providerName) continue;
                $provider = $this->providers[$providerName] ?? null;
                if (!$provider) continue;

                try {
                    $startTime = microtime(true);
                    
                    $currentOptions = $options;
                    $overriddenModel = !empty($link['model']) ? $link['model'] : ($options['model'] ?? null);
                    $currentOptions['model'] = $this->normalizeModelForProvider($providerName, $overriddenModel);
                    
                    if (!empty($link['api_key'])) {
                        $currentOptions['api_key'] = $link['api_key'];
                    } else {
                        $currentOptions['api_key'] = $this->resolveApiKeyFromSettings($providerName);
                    }

                    Log::info("AI: Tool '{$tool}' calling '{$providerName}/{$currentOptions['model']}' with key ending in " . substr($currentOptions['api_key'] ?? 'NONE', -4));

                    $response = $provider->generate($prompt, $currentOptions);
                    $latency = (int) ((microtime(true) - $startTime) * 1000);

                    $this->logUsage($tool, $providerName, $currentOptions['model'] ?? 'default', $response, $latency);

                    return $response;
                } catch (\Exception $e) {
                    $lastError = $e;
                    Log::warning("AI Failover (Chain): Provider [{$providerName}] failed. Error: " . $e->getMessage());
                }
            }
            // Chain exhausted — fall through to global fallback instead of hard-failing
            Log::warning("AI Chain: All tool-specific providers for '{$tool}' failed. Falling through to global providers.");
        }

        // --- GLOBAL FALLBACK BEHAVIOR ---

        // Use requested provider if specified and registered
        if (isset($options['provider'])) {
            if (isset($this->providers[$options['provider']])) {
                array_unshift($defaultProviders, $options['provider']);
            }
            $defaultProviders = array_unique($defaultProviders);
        }

        $lastError = null;
        $primaryError = null;
        $attemptCount = 0;

        foreach ($defaultProviders as $providerName) {
            $provider = $this->providers[$providerName] ?? null;
            if (!$provider) continue;

            $attemptCount++;

            try {
                $startTime = microtime(true);
                
                $currentOptions = $options;
                $currentOptions['model'] = $this->normalizeModelForProvider($providerName, $options['model'] ?? null);

                // Fetch "General Key" from settings if not set in environment or specific options
                if (empty($currentOptions['api_key'])) {
                    $currentOptions['api_key'] = $this->resolveApiKeyFromSettings($providerName);
                }

                $response = $provider->generate($prompt, $currentOptions);
                $latency = (int) ((microtime(true) - $startTime) * 1000);

                $this->logUsage($tool, $providerName, $currentOptions['model'] ?? 'default', $response, $latency);

                return $response;
            } catch (\Exception $e) {
                $lastError = $e;
                if ($attemptCount === 1) {
                    $primaryError = $e;
                }
                Log::warning("AI Failover: Provider [{$providerName}] failed. Error: " . $e->getMessage());
            }
        }

        $finalMsg = "All AI Providers failed.";
        if ($primaryError) {
            $primaryProvider = $options['provider'] ?? ($defaultProviders[0] ?? 'primary');
            $finalMsg .= " [{$primaryProvider} Error]: " . $primaryError->getMessage();
        }
        if ($lastError && $lastError->getMessage() !== ($primaryError ? $primaryError->getMessage() : '')) {
            $finalMsg .= " (Fallback also failed: " . $lastError->getMessage() . ")";
        }

        throw new \Exception($finalMsg);
    }

    /**
     * Convenient wrapper that returns just the text response.
     * Often used by older controllers expecting (prompt, tool, options).
     */
    public function generateResponse(string $prompt, string $tool, array $options = []): string
    {
        $response = $this->generate($tool, $prompt, $options);
        return $response['text'] ?? '';
    }

    protected function normalizeModelForProvider(string $provider, ?string $model): string
    {
        if (!$model) {
            return $this->getDefaultModelForProvider($provider);
        }

        // Handle OpenRouter-style names (e.g. google/gemini-2.0-flash-001)
        if (str_contains($model, '/')) {
            if ($provider === 'openrouter') {
                return $model;
            }

            [$prefix, $name] = explode('/', $model, 2);
            
            if ($provider === 'google' && ($prefix === 'google' || str_contains($name, 'gemini'))) {
                // Return version without 001 if possible or just the core name
                return str_replace('-001', '', $name); 
            }

            if ($provider === 'openai' && ($prefix === 'openai' || str_contains($name, 'gpt'))) {
                return $name;
            }

            if ($provider === 'anthropic' && ($prefix === 'anthropic' || str_contains($name, 'claude'))) {
                return $name;
            }
        }

        // Handle cross-provider model leakage
        $modelLower = strtolower($model);
        
        if ($provider === 'google' && (str_contains($modelLower, 'gpt') || str_contains($modelLower, 'claude'))) {
            return $this->getDefaultModelForProvider($provider);
        }
        if ($provider === 'openai' && (str_contains($modelLower, 'gemini') || str_contains($modelLower, 'claude'))) {
            return $this->getDefaultModelForProvider($provider);
        }
        if ($provider === 'anthropic' && (str_contains($modelLower, 'gpt') || str_contains($modelLower, 'gemini'))) {
            return $this->getDefaultModelForProvider($provider);
        }

        return $model;
    }

    protected function resolveApiKeyFromSettings(string $providerName): ?string
    {
        $settingKey = match ($providerName) {
            'openrouter' => 'openrouter_api_key',
            'google' => 'gemini_api_key',
            'openai' => 'openai_api_key',
            'anthropic' => 'anthropic_api_key',
            default => null
        };

        if ($settingKey) {
            $key = trim(\App\Models\Setting::get($settingKey) ?? '');
            
            // Fallback for Gemini specifically if 'gemini_api_key' wasn't the right one
            if (!$key && $providerName === 'google') {
                $key = trim(\App\Models\Setting::get('google_api_key') ?? '');
            }

            // Fallback to Environment variables if not in Database, 
            // OR if the database key is suspiciously short (placeholder)
            if (empty($key) || strlen($key) < 5) {
                $envKey = strtoupper($settingKey);
                $key = trim(env($envKey) ?? '');
                
                // Extra check for OPENROUTER key specifically in env
                if (!$key && $providerName === 'openrouter') {
                    $key = trim(env('OPEN_ROUTER_API_KEY') ?? '');
                }
            }
            
            if ($key) {
                Log::info("AI: Resolved key for [{$providerName}] (Length: " . strlen($key) . ")");
            }
            
            return !empty($key) ? $key : null;
        }

        return null;
    }

    protected function getDefaultModelForProvider(string $provider): string
    {
        return match ($provider) {
            'google' => 'gemini-2.0-flash',
            'openai' => 'gpt-4o-mini',
            'anthropic' => 'claude-3-haiku-20240307',
            'openrouter' => 'google/gemini-2.0-flash-001',
            default => 'default'
        };
    }

    protected function logUsage(string $tool, string $provider, string $model, array $response, int $latency): void
    {
        // This will eventually insert into ai_usages table
        Log::info("AI Usage: Tool={$tool}, Provider={$provider}, Model={$model}, Input={$response['input_tokens']}, Output={$response['output_tokens']}, Latency={$latency}ms");
    }
}
