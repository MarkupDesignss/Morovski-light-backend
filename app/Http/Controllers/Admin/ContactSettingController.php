<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContactSettingController extends Controller
{
    public function index()
    {
        $settings = ContactSetting::getSettings();
        return view('admin.contact-settings.index', compact('settings'));
    }

    public function edit()
    {
        $settings = ContactSetting::getSettings();
        return view('admin.contact-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            // Email section
            'email_title' => 'nullable|string|max:255',
            'email_description' => 'nullable|string',
            
            // Call Us section
            'call_us_title' => 'nullable|string|max:255',
            'call_us_description' => 'nullable|string',
            
            // Business Hours section
            'business_hours_title' => 'nullable|string|max:255',
            'business_hours_description' => 'nullable|string',
            
            // Visit Us section
            'visit_us_title' => 'nullable|string|max:255',
            'visit_us_description' => 'nullable|string',
        ]);

        // Update or create each setting type
        $types = [
            'email' => ['title' => $data['email_title'] ?? '', 'description' => $data['email_description'] ?? ''],
            'call_us' => ['title' => $data['call_us_title'] ?? '', 'description' => $data['call_us_description'] ?? ''],
            'business_hours' => ['title' => $data['business_hours_title'] ?? '', 'description' => $data['business_hours_description'] ?? ''],
            'visit_us' => ['title' => $data['visit_us_title'] ?? '', 'description' => $data['visit_us_description'] ?? ''],
        ];

        foreach ($types as $type => $fields) {
            ContactSetting::updateOrCreate(
                ['type' => $type],
                [
                    'title' => $fields['title'],
                    'short_description' => $fields['description'],
                ]
            );
        }

        return redirect()->route('admin.contact-settings.index')
            ->with('success', 'Contact settings updated successfully!');
    }
}