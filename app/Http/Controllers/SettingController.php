<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'settings' => AppSetting::allSettings(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:80'],
            'app_tagline' => ['required', 'string', 'max:40'],
            'theme_primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        foreach (['app_name', 'app_tagline', 'theme_primary', 'theme_accent', 'theme_background'] as $key) {
            AppSetting::put($key, $data[$key]);
        }

        if ($request->hasFile('logo')) {
            $directory = public_path('uploads/settings');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $file = $request->file('logo');
            $fileName = 'logo-'.Str::random(10).'.'.$file->getClientOriginalExtension();
            $file->move($directory, $fileName);

            AppSetting::put('logo_path', 'uploads/settings/'.$fileName);
        }

        return back()->with('status', 'Pengaturan aplikasi berhasil diperbarui.');
    }
}
