<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $data = [
            'ai_mode' => Setting::getBool('ai_mode', env('AI_MODE', true)),
            'use_ailabtools' => Setting::getBool('use_ailabtools', env('USE_AILABTOOLS_API', true)),
            'use_gemini' => Setting::getBool('use_gemini', env('USE_GOOGLE_GEMINI_API', false)),
            'direct_api' => Setting::getBool('direct_api', env('DIRECT_API', false)),
        ];

        return view('admin.settings', compact('data'));
    }

    public function update(Request $request)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'ai_mode' => 'nullable|boolean',
            'use_ailabtools' => 'nullable|boolean',
            'use_gemini' => 'nullable|boolean',
            'direct_api' => 'nullable|boolean',
        ]);

        Setting::setBool('ai_mode', (bool) ($validated['ai_mode'] ?? false));
        Setting::setBool('use_ailabtools', (bool) ($validated['use_ailabtools'] ?? false));
        Setting::setBool('use_gemini', (bool) ($validated['use_gemini'] ?? false));
        Setting::setBool('direct_api', (bool) ($validated['direct_api'] ?? false));

        return back()->with('success', 'Settings updated successfully');
    }
}


