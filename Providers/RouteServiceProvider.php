<?php

/*
 * Copyright (c) LemonDev Ltd. (ЛемънДев ООД)
 * Email: info@lemondev.co
 * https://cartismo.com
 */

namespace Modules\DefaultTheme\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapAdminRoutes();
        $this->mapWebRoutes();
    }

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

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path('DefaultTheme', '/Routes/web.php'));
    }
}