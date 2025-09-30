<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BeautyBreeze') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-stone-50 text-stone-800">

    {{-- ===== Header (contains optional Hero and the overlay Nav) ===== --}}
    <header class="relative">
        {{-- NAV: always absolute + transparent overlay to match original styling --}}
        <nav class="absolute top-0 inset-x-0 z-20 bg-transparent border-b border-transparent">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <a href="{{ route('home') }}" class="text-2xl font-bold text-amber-800">
                            BeautyBreeze
                        </a>

                        <!-- Primary Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('home') }}" class="inline-flex items-center px-1 pt-1 text-white hover:text-stone-200">Home</a>
                            <a href="{{ route('about') }}" class="inline-flex items-center px-1 pt-1 text-white hover:text-stone-200">About Us</a>
                            <a href="{{ route('products') }}" class="inline-flex items-center px-1 pt-1 text-white hover:text-stone-200">Products</a>
                        </div>
                    </div>

                    <!-- Cart & Account -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <a href="{{ route('cart') }}" class="text-white hover:text-stone-200 px-3 py-2">
                            <!-- Cart icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0
                                         a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                        @auth
                            @auth
                                @php($hasOrders = \App\Models\Order::where('Email', auth()->user()->email)->exists())
                                @if($hasOrders)
                                    <a href="{{ route('orders') }}" class="text-white hover:text-stone-200 px-3 py-2">Orders</a>
                                @endif
                                <a href="{{ route('profile.show') }}" class="text-white hover:text-stone-200 px-3 py-2">Profile</a>
                            @endauth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="text-white hover:text-stone-200 px-3 py-2"
                                    title="Hi {{ auth()->user()->name }}, click to logout"
                                    aria-label="Hi {{ auth()->user()->name }}, click to logout"
                                >Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-white hover:text-stone-200 px-3 py-2">Login</a>
                            <a href="{{ route('register') }}" class="text-white hover:text-stone-200 px-3 py-2">Sign Up</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        {{-- HERO: only renders if the page defines @section('hero') --}}
        @hasSection('hero')
            <section class="relative">
                {{-- Optional dark gradient to improve link contrast over bright video frames --}}
                <div class="pointer-events-none absolute inset-0 z-10 bg-gradient-to-b from-black/40 via-black/20 to-transparent"></div>

                @yield('hero')
            </section>
        @endif
    </header>

    {{-- ===== Main Content ===== --}}
    <main class="{{ !Route::is('home') ? 'bg-[#D1BB91]' : '' }}">
        {{-- Support both slot-based and section-based pages --}}
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- ===== Footer (global across customer pages) ===== --}}
    <footer class="bg-[#4a2b1d] text-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div>
                    <div class="text-2xl font-bold text-[#D1BB91]">BeautyBreeze</div>
                    <p class="mt-3 text-stone-300 text-sm">
                        Your trusted destination for premium skincare products. We believe in beauty that's more than skin deep.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <div class="font-semibold text-[#D1BB91]">Quick Links</div>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('about') }}" class="hover:underline">About Us</a></li>
                        <li><a href="{{ route('products') }}" class="hover:underline">Our Products</a></li>
                        <li><a href="{{ route('bundle-offers') }}" class="hover:underline">Special Offers</a></li>
                        <li><a href="#" class="hover:underline">FAQs</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <div class="font-semibold text-[#D1BB91]">Customer Service</div>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="#" class="hover:underline">Contact Us</a></li>
                        <li><a href="#" class="hover:underline">Shipping Policy</a></li>
                        <li><a href="#" class="hover:underline">Returns &amp; Exchanges</a></li>
                        <li><a href="#" class="hover:underline">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <div class="font-semibold text-[#D1BB91]">Contact Us</div>
                    <ul class="mt-3 space-y-2 text-sm text-stone-300">
                        <li>123 Beauty Street, Skincare City, 12345</li>
                        <li><a href="mailto:support@beautybreeze.com" class="hover:underline text-stone-200">support@beautybreeze.com</a></li>
                        <li><a href="tel:+11234567890" class="hover:underline text-stone-200">(123) 456-7890</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="border-t border-[#3d2318]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col md:flex-row items-center justify-between gap-3 text-sm text-stone-400">
                <div>© {{ now()->year }} BeautyBreeze. All rights reserved.</div>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-stone-200">Terms</a>
                    <a href="#" class="hover:text-stone-200">Privacy</a>
                    <a href="#" class="hover:text-stone-200">Cookies</a>
                </div>
            </div>
        </div>
    </footer>
    @livewireScripts
</body>
</html>





