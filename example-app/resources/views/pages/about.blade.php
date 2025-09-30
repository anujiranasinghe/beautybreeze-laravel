@extends('layouts.customer')

@section('content')
<div class="bg-[#D1BB91]">
    <!-- Hero Image -->
    <div class="relative w-full">
        <img src="{{ asset('images/Aboutpagehero.jpg') }}" 
            alt="BeautyBreeze" 
            class="w-full object-contain" />
    </div>

    <!-- Combined About Section -->
    <div class="py-20"> <!-- Changed from pb-0 to py-20 for equal top and bottom spacing -->
        <div class="max-w-6xl mx-auto px-4">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-12 shadow-lg">
                <!-- Story Section -->
                <div class="max-w-3xl mx-auto text-center mb-16">
                    <h2 class="text-4xl font-light text-[#8B4513] mb-8">Our Story</h2>
                    <p class="text-lg text-gray-700 leading-relaxed">
                        At BeautyBreeze, we believe that skincare should be both effective and uncomplicated. Founded with a passion for promoting healthy, radiant skin, we carefully curate products that combine the best of nature with scientific innovation. Each formula is thoughtfully designed to target real skin concerns without unnecessary additives, ensuring visible results you can trust.
                    </p>
                    <p class="text-lg text-gray-700 leading-relaxed mt-4">
                        We also place a strong emphasis on sustainability, from responsibly sourced ingredients to eco-friendly packaging that minimizes waste. Beyond products, BeautyBreeze is a community, we listen to feedback, evolve with our customers, and aim to make skincare a positive, empowering ritual.
                    </p>
                </div>

                <!-- Divider -->
                <div class="w-32 h-1 bg-[#8B4513]/20 mx-auto mb-16"></div>

                <!-- Quality Promise Section -->
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-light text-[#8B4513] mb-6">Our Quality Promise</h2>
                        <p class="text-gray-700 leading-relaxed mb-6">
                            Every BeautyBreeze product undergoes rigorous testing to meet our high standards. We believe in transparency and are committed to providing you with products that deliver real results.
                        </p>
                        <ul class="space-y-4">
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#8B4513]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Dermatologically tested</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#8B4513]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Cruelty-free formulations</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-[#8B4513]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Ethically sourced ingredients</span>
                            </li>
                        </ul>
                    </div>
                    <div class="text-center">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="bg-white/90 rounded-lg p-6">
                                <div class="text-3xl font-bold text-[#8B4513]">100%</div>
                                <div class="text-sm text-gray-600">Clean Ingredients</div>
                            </div>
                            <div class="bg-white/90 rounded-lg p-6">
                                <div class="text-3xl font-bold text-[#8B4513]">50+</div>
                                <div class="text-sm text-gray-600">Unique Products</div>
                            </div>
                            <div class="bg-white/90 rounded-lg p-6">
                                <div class="text-3xl font-bold text-[#8B4513]">10k+</div>
                                <div class="text-sm text-gray-600">Happy Customers</div>
                            </div>
                            <div class="bg-white/90 rounded-lg p-6">
                                <div class="text-3xl font-bold text-[#8B4513]">24/7</div>
                                <div class="text-sm text-gray-600">Customer Support</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

