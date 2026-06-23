<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\LandingCms;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function edit()
    {
        return view('admin.landing-cms.settings.edit', [
            'settings' => SiteSetting::query()->orderBy('group')->orderBy('key')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['settings' => ['array'], 'settings.*' => ['nullable', 'string', 'max:3000']]);

        foreach ($data['settings'] ?? [] as $id => $value) {
            SiteSetting::whereKey($id)->update(['value' => $value]);
        }

        return back()->with('status', 'تم حفظ إعدادات الموقع الإلكتروني.');
    }
}
