<div class="min-h-screen bg-[#D1BB91]">
    <div class="max-w-5xl mx-auto px-4 pt-24 pb-24">
        <div class="bg-white/95 rounded-2xl shadow-lg p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="w-full h-[400px] flex items-center justify-center">
                        <img src="{{ $product->Image ? asset($product->Image) : asset('images/placeholder.png') }}" 
                            alt="{{ $product->Title }}" 
                            class="max-w-full max-h-full object-contain" />
                    </div>
                </div>
                
                <div class="flex flex-col"
                     x-data="{
                        qty: @entangle('quantity'),
                        stock: {{ (int)($product->StockQuantity ?? 0) }},
                        err: '',
                        inc() {
                            if (this.stock <= 0) return;
                            if (this.qty < this.stock) {
                                this.qty++
                                this.err=''
                            } else {
                                this.err = `Only ${this.stock} available. Please reduce quantity.`
                            }
                        },
                        dec() {
                            if (this.qty > 1) { this.qty--; this.err=''; }
                        }
                     }"
                     x-init="$watch('qty', v => { if (v > stock) { err = `Only ${stock} available. Please reduce quantity.` } else { err = '' } })"
                >
                    <h1 class="text-2xl font-bold text-gray-800">{{ $product->Title }}</h1>
                    <div class="mt-4 text-[#654321] text-2xl font-bold flex items-center gap-3">
                        Rs {{ number_format($product->Price,2) }}
                        @if(($product->StockQuantity ?? 0) <= 0)
                            <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Out of stock</span>
                        @endif
                    </div>
                    
                    <div class="mt-2">
                        @if (session()->has('error'))
                            <div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
                        @endif
                        <template x-if="err">
                            <div class="mt-2 p-3 bg-red-50 text-red-700 rounded-lg text-sm" x-text="err"></div>
                        </template>
                    </div>

                    <div class="mt-6 prose prose-sm text-gray-600">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Product Description</h3>
                        <p>{{ $product->Description }}</p>
                    </div>

                    <div class="mt-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <div class="flex items-center gap-4">
                            <div class="relative w-32">
                                <button type="button" @click="dec()"
                                    class="absolute left-0 top-0 h-full px-3 text-gray-600 hover:text-[#8B4513]">
                                    -
                                </button>
                                <input type="number" min="1" x-model.number="qty"
                                    class="w-full rounded-lg border-gray-300 text-center focus:ring-[#8B4513] focus:border-[#8B4513]" />
                                <button type="button" @click="inc()"
                                    class="absolute right-0 top-0 h-full px-3 text-gray-600 hover:text-[#8B4513]">
                                    +
                                </button>
                            </div>
                            <button wire:click="addToCart"
                                :disabled="stock <= 0 || qty > stock || qty < 1"
                                class="flex-1 py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center gap-2"
                                :class="(stock <= 0 || qty > stock || qty < 1) ? 'bg-gray-300 text-gray-600 cursor-not-allowed' : 'bg-[#8B4513] text-white hover:bg-[#654321]'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span x-text="(stock <= 0) ? 'Out of stock' : 'Add to Cart'"></span>
                            </button>
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="mt-4 p-3 bg-green-50 text-green-700 rounded-lg">
                            {{ session('message') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
