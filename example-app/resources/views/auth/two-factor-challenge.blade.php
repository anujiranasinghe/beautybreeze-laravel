<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication - BeautyBreeze</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[#D1BB91] flex items-center justify-center p-6" x-data="{ recovery: false }">
    <div class="w-full max-w-md">
        <!-- Card with BeautyBreeze branding -->
        <div class="bg-white/95 rounded-2xl shadow-xl p-8">
            <!-- Centered Logo/Brand -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-[#8B4513]">BeautyBreeze</h1>
                <p class="mt-2 text-sm text-gray-600">Two-Factor Authentication</p>
            </div>

            <!-- Rest of the form -->
            <div class="space-y-6">
                <div class="text-sm text-gray-600" x-show="! recovery">
                    Please enter your authentication code to continue.
                </div>

                <div class="text-sm text-gray-600" x-cloak x-show="recovery">
                    Please enter your recovery code to continue.
                </div>

                <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-6">
                    @csrf

                    <div x-show="! recovery">
                        <label class="block text-sm font-medium text-gray-700">Authentication Code</label>
                        <input type="text" name="code" class="mt-1 w-full rounded-lg border-gray-300 focus:border-[#8B4513] focus:ring-[#8B4513]" autofocus />
                    </div>

                    <div x-cloak x-show="recovery">
                        <label class="block text-sm font-medium text-gray-700">Recovery Code</label>
                        <input type="text" name="recovery_code" class="mt-1 w-full rounded-lg border-gray-300 focus:border-[#8B4513] focus:ring-[#8B4513]" />
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="button" class="text-sm text-[#8B4513] hover:underline"
                            x-show="! recovery"
                            x-on:click="recovery = true">
                            Use recovery code
                        </button>
                        <button type="button" class="text-sm text-[#8B4513] hover:underline"
                            x-cloak x-show="recovery"
                            x-on:click="recovery = false">
                            Use authentication code
                        </button>
                        <button type="submit" class="bg-[#8B4513] text-white px-4 py-2 rounded-lg hover:bg-[#654321]">
                            Verify
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>


