<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NotificationSetting;

class NotificationSettingController extends Controller
{
    /**
     * GET: Fetch user notification settings
     */
    public function index()
    {
        $user = Auth::user();

        $setting = NotificationSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'sales_notifications' => 1,
                'shipping_updates' => 1,
                'bid_notifications' => 1,
                'chat_notifications' => 1,
                'membership_notifications' => 1,
            ]
        );

        return response()->json([
            'status' => true,
            'data' => $setting
        ]);
    }

    /**
     * POST: Update notification settings
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'sales_notifications' => 'nullable|boolean',
            'shipping_updates' => 'nullable|boolean',
            'bid_notifications' => 'nullable|boolean',
            'chat_notifications' => 'nullable|boolean',
            'membership_notifications' => 'nullable|boolean',
        ]);

        $setting = NotificationSetting::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'sales_notifications',
                'shipping_updates',
                'bid_notifications',
                'chat_notifications',
                'membership_notifications'
            ])
        );

        return response()->json([
            'status' => true,
            'message' => 'Notification settings updated',
            'data' => $setting
        ]);
    }
    
    public function allSettings()
    {
        try {
            $user = Auth::user();
            $setting = NotificationSetting::where('user_id', $user->id)->get();

            return response()->json([
                'status' => true,
                'message' => "Notification settings fetched",
                'data' => $setting
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Failed to fetch notification settings",
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
