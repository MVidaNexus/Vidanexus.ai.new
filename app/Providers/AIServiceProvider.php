<?php

namespace App\Providers;

use App\Core\AI\AIManager;
use App\Core\AI\Providers\GoogleProvider;
use App\Core\AI\Providers\OpenAIProvider;
use App\Core\AI\Providers\OpenRouterProvider;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AIManager::class, function () {
            $manager = new AIManager();

            $manager->registerProvider(new OpenAIProvider($this->resolveKey('openai_api_key', 'OPENAI_API_KEY')));
            $manager->registerProvider(new GoogleProvider($this->resolveKey('gemini_api_key', 'GEMINI_API_KEY')));
            $manager->registerProvider(new OpenRouterProvider($this->resolveKey('openrouter_api_key', 'OPENROUTER_API_KEY')));

            return $manager;
        });
    }

    public function boot(): void
    {
        //
    }

    /**
     * Resolve the API key for a provider, preferring the database-backed
     * Setting over the environment variable. We swallow exceptions so that
     * a missing settings table at boot (e.g. fresh install) doesn't take
     * down the application.
     */
    protected function resolveKey(string $settingKey, string $envKey): string
    {
        try {
            $value = Setting::get($settingKey);
        } catch (\Throwable) {
            $value = null;
        }

        $value = is_string($value) ? trim($value) : '';
        if ($value !== '') {
            return $value;
        }

        return (string) (env($envKey, '') ?? '');
    }
}
