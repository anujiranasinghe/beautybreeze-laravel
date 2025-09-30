<div class="min-h-screen bg-[#D1BB91]">
    <div class="max-w-6xl mx-auto px-4 pt-24 pb-24">
        <div class="bg-white/95 rounded-2xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Your Shopping Cart</h1>
            
            @if (session()->has('error'))
                <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg">{{ session('error') }}</div>
            @endif

            @if (empty($items))
                <div class="text-gray-500 text-center py-8">Your cart is empty.</div>
            @else
                <div class="space-y-4">
                    @foreach($items as $id => $item)
                        <div class="flex items-center gap-4 bg-white rounded-xl p-4 shadow-sm border border-amber-100">
                            <img src="{{ $item['image'] ? asset($item['image']) : asset('images/placeholder.png') }}" 
                                alt="{{ $item['title'] }}" 
                                class="w-20 h-20 object-contain rounded-lg" />
                            <div class="flex-1">
                                <div class="font-medium text-gray-800">{{ $item['title'] }}</div>
                                <div class="text-sm text-[#654321]">Rs {{ number_format($item['price'],2) }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="decrement('{{ $id }}')" 
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50">
                                    -
                                </button>
                                <div class="w-12 text-center font-medium">{{ $item['quantity'] }}</div>
                                <button wire:click="increment('{{ $id }}')" 
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50">
                                    +
                                </button>
                            </div>
                            <div class="w-32 text-right font-semibold text-[#654321]">
                                Rs {{ number_format($item['price'] * $item['quantity'],2) }}
                            </div>
                            <button wire:click="remove('{{ $id }}')" 
                                class="ml-3 text-red-600 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6">
                    <div class="text-xl text-gray-800">
                        Subtotal: <span class="font-bold text-[#654321]">Rs {{ number_format($this->subtotal,2) }}</span>
                    </div>
                    <a href="{{ route('checkout') }}" 
                        class="px-6 py-3 bg-[#8B4513] text-white rounded-lg hover:bg-[#654321] transition duration-200 flex items-center gap-2">
                        Proceed to Checkout
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
