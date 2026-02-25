<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $pageTitle = 'Notification Settings';
        $user = auth()->user();
        $types = config('notification_preferences.types', []);

        // Build current preferences: default true for any key not set
        $prefs = $user->notification_preferences ?? [];
        $preferences = [];
        foreach (array_keys($types) as $key) {
            $preferences[$key] = array_key_exists($key, $prefs) ? (bool) $prefs[$key] : true;
        }

        // Group by category for the view
        $grouped = [];
        foreach ($types as $key => $config) {
            $category = $config['category'] ?? 'Other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$key] = [
                'label' => $config['label'],
                'enabled' => $preferences[$key],
            ];
        }

        return view('Template::user.notification_settings', compact('pageTitle', 'grouped', 'preferences'));
    }

    public function update(Request $request)
    {
        $types = config('notification_preferences.types', []);
        $allowedKeys = array_keys($types);

        $input = $request->input('notifications', []);
        $prefs = [];
        foreach ($allowedKeys as $key) {
            $prefs[$key] = isset($input[$key]) && (bool) $input[$key];
        }

        $user = auth()->user();
        $user->notification_preferences = $prefs;
        $user->save();

        $notify[] = ['success', 'Notification settings updated successfully.'];
        return back()->withNotify($notify);
    }
}
