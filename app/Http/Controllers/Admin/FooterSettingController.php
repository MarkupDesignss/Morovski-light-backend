<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use Illuminate\Http\Request;

class FooterSettingController extends Controller
{
    // Index – show current settings with edit button
    public function index()
    {
        $settings = FooterSetting::getSettings();
        return view('admin.footer.index', compact('settings'));
    }

    // Edit form
    public function edit()
    {
        $settings = FooterSetting::getSettings();
        return view('admin.footer.edit', compact('settings'));
    }

    // Update
    public function update(Request $request)
    {
        $settings = FooterSetting::getSettings();

        $data = $request->validate([
            'tagline' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'copyright_text' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'gst_in' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:500',
        ]);

        $settings->update($data);

        return redirect()->route('admin.footer.index')
            ->with('success', 'Footer settings updated successfully!');
    }
}
