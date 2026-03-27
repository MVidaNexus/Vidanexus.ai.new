<?php

namespace Modules\TrendingSearchMonitor\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\TrendingSearchMonitor\Providers\RouteServiceProvider;

class TrendingSearchServiceProvider extends ServiceProvider
{
    protected string $name = 'TrendingSearchMonitor';
    protected string $nameLower = 'trendingsearchmonitor';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerViews();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $sourcePath = module_path($this->name, 'resources/views');
        $this->loadViewsFrom($sourcePath, $this->nameLower);
    }
}
