<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Dashboard - {{ config('app.name', 'BeautyBreeze') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 w-64 bg-[#4a2b1d] text-white">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-4">
                    <span class="text-2xl font-bold text-[#D1BB91]">BeautyBreeze</span>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-2 py-4 space-y-1">
                    <a href="{{ route('admin.dashboard') }}" 
                        class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-[#8B4513] text-white' : 'text-gray-200 hover:bg-[#8B4513]/50' }}">
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.products') }}" 
                        class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.products') ? 'bg-[#8B4513] text-white' : 'text-gray-200 hover:bg-[#8B4513]/50' }}">
                        <span>Products</span>
                    </a>
                    <a href="{{ route('admin.orders') }}" 
                        class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.orders') ? 'bg-[#8B4513] text-white' : 'text-gray-200 hover:bg-[#8B4513]/50' }}">
                        <span>Orders</span>
                    </a>
                    <a href="{{ route('profile.show') }}" 
                        class="flex items-center px-4 py-2 rounded-lg text-gray-200 hover:bg-[#8B4513]/50">
                        <span>Profile</span>
                    </a>
                    
                </nav>

                <!-- Logout -->
                <div class="p-4 border-t border-[#8B4513]/30">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-left text-gray-200 hover:bg-[#8B4513]/50 rounded-lg">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="pl-64">
            <!-- Welcome Banner -->
            <div class="bg-white border-b">
                <div class="px-8 py-4">
                    <h2 class="text-xl text-[#8B4513]">
                        Welcome, <span class="font-semibold">{{ auth()->user()->name }}</span>
                    </h2>
                    <p class="text-sm text-gray-600">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>
            <main class="p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
