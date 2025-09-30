<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-0 pb-8 space-y-16">
    <!-- Hero Slider -->
    <section class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen overflow-hidden">
        <div id="hero-slides" class="relative h-screen"><!-- hero Video  -->
            <video
                class="hero-slide absolute inset-0 w-full h-full object-cover opacity-100 transition-opacity duration-700 ease-in-out"
                src="{{ asset('images/hero/hero-video.mp4') }}"
                poster="{{ asset('images/hero/hero-1.jpg') }}"
                autoplay muted loop playsinline preload="auto"
            ></video>
        </div>
        
    </section>
    <!-- Product Range Categories -->
    <section class="py-8 bg-stone-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row items-start">
                <!-- Title Section -->
                <div class="lg:w-1/3 mb-8 lg:mb-0 flex flex-col justify-start lg:justify-center -mt-10 lg:pl-15">
                    <h2 class="text-5xl font-light text-stone-900 leading-tight">Our<br>Product<br>Range</h2>
                    <p class="text-lg text-stone-600 mt-4">FOR EVERY SKIN TYPE</p>
                </div>
                <!-- Categories Grid -->
                <div class="lg:w-2/3 grid grid-cols-1 md:grid-cols-3 gap-8 -mt-30 md:ml-[-40px] lg:ml-[-60px]">
                    <!-- Serums -->
                    <div class="text-center">
                        <a href="{{ route('products', ['category' => 1]) }}" class="block group">
                            <img 
                                src="{{ asset('images/categories/Serum-home.svg') }}" 
                                alt="Serums"
                                class="w-full h-auto mb-4 transition-transform duration-300 group-hover:scale-105"
                            >
                            <h3 class="text-xl font-medium text-stone-900 mt-[-50px] flex justify-center items-center">Serums</h3>
                        </a>
                    </div>

                    <!-- Cleansers -->
                    <div class="text-center">
                        <a href="{{ route('products', ['category' => 2]) }}" class="block group">
                            <img 
                                src="{{ asset('images/categories/Cleanser-home.svg') }}" 
                                alt="Cleansers"
                                class="w-full h-auto mb-4 transition-transform duration-300 group-hover:scale-105"
                            >
                            <h3 class="text-xl font-medium text-stone-900  mt-[-50px] flex justify-center items-center">Cleansers</h3>
                        </a>
                    </div>

                    <!-- Moisturizers -->
                    <div class="text-center mt-16">
                        <a href="{{ route('products', ['category' => 3]) }}" class="block group">
                            <img 
                                src="{{ asset('images/categories/moisturrizer-home.svg') }}" 
                                alt="Moisturizers"
                                class="w-full h-auto mb-4 transition-transform duration-300 group-hover:scale-105"
                            >
                            <h3 class="text-xl font-medium text-stone-900 mt-[-115px] flex justify-center items-center">Moisturizers</h3>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Suitable For All Section -->
    <section class="relative bg-[#4A2B1D] overflow-hidden w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] -mt-8 pb-0" style="height: 550px;">
        <div class="container mx-auto">
            <div class="flex flex-col lg:flex-row items-center py-16">
                <!-- Image Side -->
                <div class="lg:w-1/2 -mt-16">
                    <div class="w-full h-[550px] overflow-hidden ">
                        <img 
                            src="{{ asset('images/pages/Home/SuitableForAll.svg') }}" 
                            alt="Different skin types"
                            class="w-full h-full object-cover object-top"
                        >
                    </div>
                </div>
                <!-- Text Content Side -->
                <div class="lg:w-1/2 px-8 lg:pl-16 text-white flex flex-col justify-center h-full -mt-10">
                    <h3 class="uppercase text-sm tracking-wider mb-4">SUITS EVERY SKIN TYPE</h3><br>
                    <h2 class="text-[#dc9572] text-4xl font-light mb-6">Why Our Products<br>Suits For Everyone?</h2><br>
                    <p class="text-white/80">
                        Our products suit all skin types because we only carry trusted brands that create solutions for everyone. 
                        Whether your skin is dry, oily, sensitive, or combination, you'll find the perfect match in our collection.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Trending Products Section -->
    <section class="py-12 -mt-32">
        <div class="container mx-auto px-4">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-4xl font-light text-stone-900">Trending Products</h2>
                <p class="text-lg text-stone-600 mt-2">Our most popular picks this season</p>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @forelse($trendingProducts as $p)
                    <div class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden h-full flex flex-col">
                        <a href="{{ route('product.details', ['id' => $p->ProductID]) }}" class="block">
                            <div class="relative bg-white p-6 aspect-square">
                                <img 
                                    src="{{ $p->Image ? asset($p->Image) : asset('images/placeholder.png') }}" 
                                    alt="{{ $p->Title }}" 
                                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" 
                                />
                            </div>
                        </a>
                        <div class="p-6 pt-4 border-t border-gray-100 flex-1 flex flex-col">
                            <h3 class="font-medium text-lg text-[#8B4513] whitespace-pre-line text-left min-h-[48px]">{{ $p->Title }}</h3>
                            <div class="mt-3 text-[#654321] font-bold">Rs {{ number_format($p->Price, 2) }}</div>
                            <div class="mt-auto pt-4">
                                <a href="{{ route('product.details', ['id' => $p->ProductID]) }}" class="w-full inline-block bg-[#8B4513] text-white text-center py-2 rounded-lg text-sm font-medium hover:bg-[#654321] transition">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-4 text-center text-stone-600">Trending products will appear here as orders come in.</div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Community -->
    <section class="py-20 -mt-24">
        <div class="container">
            <h2 class="text-3xl font-bold text-center text-amber-900 mb-8">Join Our Community</h2>
            <p class="text-center text-stone-600 mb-6">Subscribe to our newsletter for the latest updates and exclusive offers.</p>
           <form class="flex justify-center">
               <input type="email" placeholder="Enter your email" class="border border-stone-300 rounded-l-md px-4 py-2 w-1/3" required>
               <button type="submit" class="bg-amber-800 text-white rounded-r-md px-4 py-2 hover:bg-amber-900 transition">Subscribe</button>
           </form>
       </div>
   </section>

    <script>
    // Lightweight slider: auto-rotate hero slides (videos supported)
    (function() {
        const container = document.getElementById('hero-slides');
        if (!container) return;
        const slides = Array.from(container.querySelectorAll('.hero-slide'));
        const dots = Array.from(document.querySelectorAll('.hero-dot'));
        let i = 0;

        function show(idx) {
            slides.forEach((s, n) => s.style.opacity = (n === idx ? '1' : '0'));
            dots.forEach((d, n) => d.setAttribute('aria-selected', n === idx ? 'true' : 'false'));
            // Pause non-active videos and play the active one
            slides.forEach((s, n) => {
                if (s.tagName === 'VIDEO') {
                    try {
                        if (n === idx) {
                            s.play();
                        } else {
                            s.pause();
                            s.currentTime = 0;
                        }
                    } catch (e) {}
                }
            });
            i = idx;
        }

        dots.forEach((d, n) => d.addEventListener('click', () => show(n)));

        // Initialize first slide state
        if (slides.length > 0) {
            show(0);
        }

        // Auto-rotate only when multiple slides exist
        if (slides.length > 1) {
            setInterval(() => {
                show((i + 1) % slides.length);
            }, 5000);
        }
    })();
    </script>

    @section('footer')
        <x-layouts.footer />
    @endsection

</div>

