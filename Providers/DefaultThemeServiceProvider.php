<?php

namespace Modules\DefaultTheme\Providers;

use Illuminate\Support\ServiceProvider;

class DefaultThemeServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'DefaultTheme';
    protected string $moduleNameLower = 'defaulttheme';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerAssets();
        $this->registerInertiaComponents();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register and publish assets.
     */
    protected function registerAssets(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Resources/assets/images') => public_path('modules/' . $this->moduleNameLower . '/images'),
        ], ['assets', $this->moduleNameLower . '-module-assets']);
    }

    /**
     * Register Inertia page components from module.
     */
    protected function registerInertiaComponents(): void
    {
        // This allows Inertia::render('DefaultTheme::Admin/Settings') to work
        // The actual Vue components are loaded via Vite config
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
