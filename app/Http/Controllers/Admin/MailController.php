<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MailController extends Controller
{
    public function index(SettingService $setting): View
    {
        return view('admin.mail.index', [
            'mail_driver' => $setting->get('mail_driver') ?? 'default',
            'mail_host' => $setting->get('mail_host'),
            'mail_port' => $setting->get('mail_port', '587'),
            'mail_username' => $setting->get('mail_username'),
            'mail_password' => $setting->get('mail_password'),
            'mail_encryption' => $setting->get('mail_encryption'),
            'mail_from_address' => $setting->get('mail_from_address'),
            'mail_from_name' => $setting->get('mail_from_name'),
        ]);
    }

    public function update(Request $request, SettingService $setting): RedirectResponse
    {
        $validated = $request->validate([
            'mail_driver' => 'required|in:default,smtp,php',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
        ]);

        $setting->set('mail_driver', $validated['mail_driver'] === 'default' ? '' : $validated['mail_driver']);
        $setting->set('mail_from_address', $validated['mail_from_address']);
        $setting->set('mail_from_name', $validated['mail_from_name']);
        $setting->set('mail_host', $validated['mail_host'] ?? '');
        $setting->set('mail_port', (string) ($validated['mail_port'] ?? ''));
        $setting->set('mail_username', $validated['mail_username'] ?? '');
        if (isset($validated['mail_password']) && $validated['mail_password'] !== '') {
            $setting->set('mail_password', $validated['mail_password']);
        }
        $setting->set('mail_encryption', $validated['mail_encryption'] ?? '');

        return redirect()->route('admin.mail.index')->with('success', 'Mail settings saved.');
    }
}
