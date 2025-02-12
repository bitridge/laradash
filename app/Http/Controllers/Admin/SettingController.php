<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = [
            'app' => Setting::getGroup('app'),
            'mail' => Setting::getGroup('mail'),
            'timezone' => Setting::getGroup('timezone'),
        ];

        $timezones = DateTimeZone::listIdentifiers();

        return view('admin.settings.index', compact('settings', 'timezones'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|max:2048',
            'mail_driver' => 'required|in:smtp,local',
            'mail_host' => 'required_if:mail_driver,smtp|nullable|string',
            'mail_port' => 'required_if:mail_driver,smtp|nullable|numeric',
            'mail_username' => 'required_if:mail_driver,smtp|nullable|string',
            'mail_password' => 'required_if:mail_driver,smtp|nullable|string',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
            'timezone' => 'required|string|in:' . implode(',', DateTimeZone::listIdentifiers()),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            \Log::info('Updating settings with data:', $request->all());

            // Handle logo upload
            if ($request->hasFile('app_logo')) {
                $path = $request->file('app_logo')->store('public/logos');
                Setting::set('app_logo', $path, 'app');
                \Log::info('Logo uploaded:', ['path' => $path]);
            }

            // App settings
            Setting::set('app_name', $request->app_name, 'app');
            \Log::info('App name updated:', ['name' => $request->app_name]);

            // Mail settings
            Setting::set('mail_driver', $request->mail_driver, 'mail');
            
            if ($request->mail_driver === 'smtp') {
                $mailSettings = [
                    'mail_host' => $request->mail_host,
                    'mail_port' => $request->mail_port,
                    'mail_username' => $request->mail_username,
                    'mail_password' => $request->mail_password,
                    'mail_encryption' => $request->mail_encryption,
                ];
            } else {
                // Local mail server settings
                $mailSettings = [
                    'mail_host' => 'localhost',
                    'mail_port' => '1025',
                    'mail_username' => null,
                    'mail_password' => null,
                    'mail_encryption' => null,
                ];
            }

            // Common mail settings
            $mailSettings['mail_from_address'] = $request->mail_from_address;
            $mailSettings['mail_from_name'] = $request->mail_from_name;

            foreach ($mailSettings as $key => $value) {
                Setting::set($key, $value, 'mail');
            }
            \Log::info('Mail settings updated:', $mailSettings);

            // Timezone settings
            Setting::set('timezone', $request->timezone, 'timezone');
            \Log::info('Timezone updated:', ['timezone' => $request->timezone]);

            // Clear all settings cache
            Setting::clearCache();
            
            // Clear configuration cache
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return back()->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Error updating settings:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Send a test email.
     */
    public function testEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid email address.',
            ], 422);
        }

        try {
            $mailConfig = [
                'transport' => 'smtp',
                'host' => Setting::get('mail_host'),
                'port' => Setting::get('mail_port'),
                'encryption' => Setting::get('mail_encryption'),
                'username' => Setting::get('mail_username'),
                'password' => Setting::get('mail_password'),
                'from' => [
                    'address' => Setting::get('mail_from_address'),
                    'name' => Setting::get('mail_from_name'),
                ],
            ];

            config(['mail.mailers.smtp' => $mailConfig]);

            Mail::raw('This is a test email from your application.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('Test Email');
            });

            return response()->json([
                'message' => 'Test email sent successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }
} 