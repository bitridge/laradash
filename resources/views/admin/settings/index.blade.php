@php
use App\Models\Setting;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <!-- App Settings Section -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Application Settings</h3>
                            
                            <!-- App Name -->
                            <div>
                                <x-input-label for="app_name" :value="__('Application Name')" />
                                <x-text-input id="app_name" name="app_name" type="text" class="mt-1 block w-full"
                                    :value="old('app_name', $settings['app']['app_name'] ?? config('app.name'))" required />
                                <x-input-error class="mt-2" :messages="$errors->get('app_name')" />
                            </div>

                            <!-- App Logo -->
                            <div>
                                <x-input-label for="app_logo" :value="__('Application Logo')" />
                                <div class="mt-2 flex items-center space-x-4">
                                    <img src="{{ Setting::getLogoUrl() }}" alt="Current Logo" class="h-12 w-auto">
                                    <input type="file" id="app_logo" name="app_logo" accept="image/*"
                                        class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100"/>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('app_logo')" />
                            </div>
                        </div>

                        <!-- Mail Settings Section -->
                        <div class="space-y-6 pt-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Mail Settings</h3>

                            <!-- Mail Driver Selection -->
                            <div>
                                <x-input-label for="mail_driver" :value="__('Mail Driver')" />
                                <select id="mail_driver" name="mail_driver" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    onchange="toggleMailSettings(this.value)">
                                    <option value="smtp" {{ ($settings['mail']['mail_driver'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP Server</option>
                                    <option value="local" {{ ($settings['mail']['mail_driver'] ?? '') == 'local' ? 'selected' : '' }}>Local Server</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">Select 'Local Server' to use localhost for development</p>
                            </div>
                            
                            <div id="smtp-settings" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Mail Host -->
                                <div>
                                    <x-input-label for="mail_host" :value="__('SMTP Host')" />
                                    <x-text-input id="mail_host" name="mail_host" type="text" class="mt-1 block w-full"
                                        :value="old('mail_host', $settings['mail']['mail_host'] ?? '')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_host')" />
                                </div>

                                <!-- Mail Port -->
                                <div>
                                    <x-input-label for="mail_port" :value="__('SMTP Port')" />
                                    <x-text-input id="mail_port" name="mail_port" type="number" class="mt-1 block w-full"
                                        :value="old('mail_port', $settings['mail']['mail_port'] ?? '')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_port')" />
                                </div>

                                <!-- Mail Username -->
                                <div>
                                    <x-input-label for="mail_username" :value="__('SMTP Username')" />
                                    <x-text-input id="mail_username" name="mail_username" type="text" class="mt-1 block w-full"
                                        :value="old('mail_username', $settings['mail']['mail_username'] ?? '')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_username')" />
                                </div>

                                <!-- Mail Password -->
                                <div>
                                    <x-input-label for="mail_password" :value="__('SMTP Password')" />
                                    <x-text-input id="mail_password" name="mail_password" type="password" class="mt-1 block w-full"
                                        :value="old('mail_password', $settings['mail']['mail_password'] ?? '')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_password')" />
                                </div>

                                <!-- Mail Encryption -->
                                <div>
                                    <x-input-label for="mail_encryption" :value="__('Encryption')" />
                                    <select id="mail_encryption" name="mail_encryption" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">None</option>
                                        <option value="tls" {{ ($settings['mail']['mail_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ ($settings['mail']['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_encryption')" />
                                </div>
                            </div>

                            <!-- From Address and Name (always visible) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Mail From Address -->
                                <div>
                                    <x-input-label for="mail_from_address" :value="__('From Address')" />
                                    <x-text-input id="mail_from_address" name="mail_from_address" type="email" class="mt-1 block w-full"
                                        :value="old('mail_from_address', $settings['mail']['mail_from_address'] ?? '')" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_from_address')" />
                                </div>

                                <!-- Mail From Name -->
                                <div>
                                    <x-input-label for="mail_from_name" :value="__('From Name')" />
                                    <x-text-input id="mail_from_name" name="mail_from_name" type="text" class="mt-1 block w-full"
                                        :value="old('mail_from_name', $settings['mail']['mail_from_name'] ?? '')" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_from_name')" />
                                </div>
                            </div>

                            <!-- Test Email Section -->
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-900">Test Email Configuration</h4>
                                <div class="mt-3 flex items-end gap-4">
                                    <div class="flex-grow">
                                        <x-input-label for="test_email" :value="__('Test Email Address')" />
                                        <x-text-input id="test_email" name="test_email" type="email" class="mt-1 block w-full" placeholder="Enter email to test" />
                                    </div>
                                    <x-secondary-button type="button" onclick="sendTestEmail()">
                                        Send Test Email
                                    </x-secondary-button>
                                </div>
                            </div>
                        </div>

                        <!-- Timezone Settings -->
                        <div class="space-y-6 pt-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Timezone Settings</h3>
                            
                            <div>
                                <x-input-label for="timezone" :value="__('Application Timezone')" />
                                <select id="timezone" name="timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}" {{ ($settings['timezone']['timezone'] ?? config('app.timezone')) == $tz ? 'selected' : '' }}>
                                            {{ $tz }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <x-secondary-button type="button" onclick="window.history.back()">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button>
                                {{ __('Save Settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleMailSettings(driver) {
            const smtpSettings = document.getElementById('smtp-settings');
            if (driver === 'smtp') {
                smtpSettings.style.display = 'grid';
            } else {
                smtpSettings.style.display = 'none';
            }
        }

        // Initialize the visibility state on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleMailSettings(document.getElementById('mail_driver').value);
        });

        function sendTestEmail() {
            const email = document.getElementById('test_email').value;
            if (!email) {
                alert('Please enter an email address');
                return;
            }

            // Send test email request
            fetch('{{ route('admin.settings.test-email') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ test_email: email })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert('Failed to send test email');
            });
        }
    </script>
    @endpush
</x-app-layout> 