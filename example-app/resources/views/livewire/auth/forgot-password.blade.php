<div class="min-h-screen flex flex-col items-center pt-2 sm:pt-0">
    <div class="w-full sm:max-w-md mt-2 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4 text-center">
            <h2 class="text-2xl font-bold text-[#8B4513]">Forgot Password</h2>
            <p class="text-sm text-gray-600 mt-1">Enter your email to receive a reset link</p>
        </div>

        @if (session('status'))
            <div class="mb-4 text-sm font-medium text-green-600 text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" wire:submit="sendPasswordResetLink">
            @csrf
            <div>
                <label class="block font-medium text-sm text-gray-700">Email Address</label>
                <input wire:model="email" type="email" class="block mt-1 w-full border-gray-300 focus:border-[#8B4513] focus:ring-[#8B4513] rounded-md shadow-sm" required autofocus placeholder="email@example.com" />
                @error('email')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-4">
                <button type="submit" class="w-full py-2 bg-[#8B4513] text-white rounded-lg hover:bg-[#654321] transition-colors">
                    Email password reset link
                </button>
            </div>

            <div class="mt-4 text-center text-sm text-gray-600">
                Or, return to
                <a href="{{ route('login') }}" class="text-[#8B4513] hover:underline">Log in</a>
            </div>
        </form>
    </div>
</div>
