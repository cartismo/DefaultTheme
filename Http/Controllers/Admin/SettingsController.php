<?php

/*
 * Copyright (c) LemonDev Ltd. (ЛемънДев ООД)
 * Email: info@lemondev.co
 * https://cartismo.com
 */

namespace Modules\DefaultTheme\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Traits\HasMultiStoreModuleSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    use HasMultiStoreModuleSettings;

    protected function getModuleSlug(): string
    {
        return 'default-theme';
    }

    protected function getDefaultSettings(): array
    {
        return config('defaulttheme.settings', []);
    }

    public function index(): Response
    {
        $data = $this->getMultiStoreData();

        $data['sliders'] = class_exists(Slider::class)
            ? Slider::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug', 'location'])
            : [];

        return Inertia::render('DefaultTheme::Admin/Settings', $data);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'is_enabled' => 'boolean',
        ]);

        return $this->saveStoreSettings($request);
    }
}