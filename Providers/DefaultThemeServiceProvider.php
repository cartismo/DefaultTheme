<?php

/*
 * Copyright (c) LemonDev Ltd. (ЛемънДев ООД)
 * Email: info@lemondev.co
 * https://cartismo.com
 */

namespace Modules\DefaultTheme\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class DefaultThemeServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'DefaultTheme';
    protected string $moduleNameLower = 'defaulttheme';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerAssets();
        $this->shareThemeTranslations();
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower,
        );
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(
            array_merge($this->getPublishableViewPaths(), [$sourcePath]),
            $this->moduleNameLower,
        );
    }

    protected function registerTranslations(): void
    {
        $langPath = module_path($this->moduleName, 'Resources/lang');

        $this->loadTranslationsFrom($langPath, $this->moduleNameLower);

        $this->publishes([
            $langPath => $this->app->langPath('modules/' . $this->moduleNameLower),
        ], ['lang', $this->moduleNameLower . '-module-lang']);
    }

    protected function registerAssets(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Resources/assets/images') => public_path('modules/' . $this->moduleNameLower . '/images'),
        ], ['assets', $this->moduleNameLower . '-module-assets']);
    }

    /**
     * Share theme translations with all Inertia responses.
     */
    protected function shareThemeTranslations(): void
    {
        Inertia::share('themeTranslations', fn () => trans($this->moduleNameLower . '::theme'));
    }

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