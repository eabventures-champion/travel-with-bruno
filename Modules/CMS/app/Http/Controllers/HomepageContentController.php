<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CMS\Models\HomepageSetting;

class HomepageContentController extends Controller
{
    public function index()
    {
        $settings = HomepageSetting::pluck('value', 'key')->toArray();
        return view('cms::content.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', 'venture_1_image', 'venture_2_image', 'venture_3_image', 'venture_4_image']);

        // Explicitly handle visibility toggles
        $visibilityKeys = ['show_ventures', 'show_destinations', 'show_scheduled_tours', 'show_fleet', 'show_transfers'];
        foreach ($visibilityKeys as $vKey) {
            $data[$vKey] = $request->has($vKey) ? '1' : '0';
        }

        // Handle text data
        foreach ($data as $key => $value) {
            HomepageSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Handle Image 1
        if ($request->hasFile('venture_1_image')) {
            $path = $request->file('venture_1_image')->store('homepage/content', 'public');
            HomepageSetting::updateOrCreate(
                ['key' => 'venture_1_image'],
                ['value' => $path]
            );
        }

        // Handle Image 2
        if ($request->hasFile('venture_2_image')) {
            $path = $request->file('venture_2_image')->store('homepage/content', 'public');
            HomepageSetting::updateOrCreate(
                ['key' => 'venture_2_image'],
                ['value' => $path]
            );
        }

        // Handle Image 3
        if ($request->hasFile('venture_3_image')) {
            $path = $request->file('venture_3_image')->store('homepage/content', 'public');
            HomepageSetting::updateOrCreate(
                ['key' => 'venture_3_image'],
                ['value' => $path]
            );
        }

        // Handle Image 4
        if ($request->hasFile('venture_4_image')) {
            $path = $request->file('venture_4_image')->store('homepage/content', 'public');
            HomepageSetting::updateOrCreate(
                ['key' => 'venture_4_image'],
                ['value' => $path]
            );
        }

        return redirect()->back()->with('success', 'Homepage content updated successfully.');
    }
}
