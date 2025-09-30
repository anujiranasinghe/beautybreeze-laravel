<div class="min-h-screen flex flex-col items-center pt-2 sm:pt-0">
    <div class="w-full sm:max-w-md mt-2 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <!-- Logo -->
        <div class="mb-4 text-center">
            <h2 class="text-2xl font-bold text-[#8B4513]">BeautyBreeze</h2>
        </div>

        @if (session('status'))
            <div class="mb-4 text-sm font-medium text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit="login">
            <div>
                <label class="block font-medium text-sm text-gray-700">
                    Email
                </label>
                <input wire:model="email" type="email" class="block mt-1 w-full border-gray-300 focus:border-[#8B4513] focus:ring-[#8B4513] rounded-md shadow-sm" required autofocus autocomplete="username" />
                @error('email')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-4">
                <label class="block font-medium text-sm text-gray-700">
                    Password
                </label>
                <input wire:model="password" type="password" class="block mt-1 w-full border-gray-300 focus:border-[#8B4513] focus:ring-[#8B4513] rounded-md shadow-sm" required autocomplete="current-password" />
                @error('password')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-4 flex items-center justify-between">
                <label class="flex items-center">
                    <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-[#8B4513] focus:ring-[#8B4513] shadow-sm" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-sm text-[#8B4513] hover:underline" href="{{ route('password.request') }}">
                        Forgot your password?
                    </a>
                @endif
            </div>

            <div class="mt-4">
                <button type="submit" class="w-full py-2 bg-[#8B4513] text-white rounded-lg hover:bg-[#654321] transition-colors">
                    Log in
                </button>
            </div>

            <div class="mt-4 text-center text-sm text-gray-600">
                Don't have an account? 
                <a href="{{ route('register') }}" class="text-[#8B4513] hover:underline">Sign up</a>
            </div>
        </form>
    </div>
</div>
