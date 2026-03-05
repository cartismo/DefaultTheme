<?php

/*
 * Copyright (c) LemonDev Ltd. (ЛемънДев ООД)
 * Email: info@lemondev.co
 * https://cartismo.com
 */

namespace Modules\DefaultTheme\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('DefaultTheme::Frontend/Home', [
            'settings' => config('defaulttheme.settings', []),
        ]);
    }
}