<?php

namespace Modules\AuditX\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class AuditXServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'AuditX';
    protected string $nameLower = 'auditx';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->name, 'config/config.php') => config_path($this->nameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(module_path($this->name, 'config/config.php'), $this->nameLower);
    }

    public function registerViews(): void
    {
        $sourcePath = module_path($this->name, 'resources/views');
        $this->loadViewsFrom([$sourcePath], $this->nameLower);
    }
}
