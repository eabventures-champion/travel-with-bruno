<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::pluck('value', 'key')->toArray();
        return view('admin::settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token');

        // Handle File Uploads
        if ($request->hasFile('site_logo')) {
            $logoPath = $request->file('site_logo')->store('settings', 'public');
            SystemSetting::updateOrCreate(['key' => 'site_logo'], ['value' => $logoPath]);
        }

        if ($request->hasFile('site_favicon')) {
            $faviconPath = $request->file('site_favicon')->store('settings', 'public');
            SystemSetting::updateOrCreate(['key' => 'site_favicon'], ['value' => $faviconPath]);
        }

        // Handle other settings
        foreach ($data as $key => $value) {
            // Skip files already handled
            if ($key === 'site_logo' || $key === 'site_favicon') {
                continue;
            }

            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'System settings updated successfully.');
    }
}
