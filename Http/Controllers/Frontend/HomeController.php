<?php

namespace Modules\DefaultTheme\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\InstalledModule;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    protected array $settings;

    public function __construct()
    {
        $theme = InstalledModule::where('slug', 'default-theme')->first();
        $this->settings = $theme?->settings ?? config('defaulttheme.settings', []);
    }

    /**
     * Display the homepage
     */
    public function index(): Response
    {
        return Inertia::render('DefaultTheme::Frontend/Home', [
            'settings' => $this->settings,
            // Add products, categories, sliders, etc. here
        ]);
    }
}
