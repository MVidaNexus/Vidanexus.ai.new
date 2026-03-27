<?php

namespace App\Providers;

use App\Core\AI\AIManager;
use App\Core\AI\Providers\OpenAIProvider;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AIManager::class, function ($app) {
            $manager = new AIManager();
            
            // Register OpenAI Provider
            $manager->registerProvider(new OpenAIProvider(
                config('services.openai.key', env('OPENAI_API_KEY', ''))
            ));

            // Register Google Provider (Gemini)
            $manager->registerProvider(new \App\Core\AI\Providers\GoogleProvider(
                env('GEMINI_API_KEY', '')
            ));

            // Register OpenRouter Provider
            $manager->registerProvider(new \App\Core\AI\Providers\OpenRouterProvider(
                env('OPENROUTER_API_KEY', '')
            ));
            
            return $manager;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
