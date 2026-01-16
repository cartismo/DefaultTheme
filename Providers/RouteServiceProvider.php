<?php

namespace Modules\DefaultTheme\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\DefaultTheme\Http\Controllers';

    /**
     * Called before routes are registered.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapAdminRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "admin" routes for the module.
     */
    protected function mapAdminRoutes(): void
    {
        Route::middleware([
            'web',
            'auth:sanctum',
            config('jetstream.auth_session'),
            'verified',
        ])
            ->prefix('admin')
            ->group(module_path('DefaultTheme', '/Routes/admin.php'));
    }

    /**
     * Define the "web" routes for the module (frontend).
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path('DefaultTheme', '/Routes/web.php'));
    }
}
