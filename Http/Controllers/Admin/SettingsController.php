<?php

namespace Modules\DefaultTheme\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HasMultiStoreModuleSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

        // Get available sliders if the SimpleSliderGeneral module is installed
        $sliders = [];
        if (Schema::hasTable('sliders')) {
            $sliders = \DB::table('sliders')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'location']);
        }

        $data['sliders'] = $sliders;
        $data['theme'] = $data['module']; // For backward compatibility

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
