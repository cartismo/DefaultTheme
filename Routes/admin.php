<?php

use Illuminate\Support\Facades\Route;
use Modules\DefaultTheme\Http\Controllers\Admin\SettingsController;

/*
|--------------------------------------------------------------------------
| DefaultTheme Admin Routes
|--------------------------------------------------------------------------
|
| Admin routes for theme settings and customization.
|
*/

Route::prefix('modules/themes/default-theme')->name('admin.themes.default.')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
