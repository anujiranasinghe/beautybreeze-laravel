<div class="min-h-screen bg-[#D1BB91]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-24">
        
        <!-- Streamlined Search Section -->
        <div class="max-w-2xl mx-auto mb-8">
            <div class="relative flex items-center">
                <input type="text" wire:model.debounce.300ms="search" 
                    placeholder="Search products..." 
                    class="w-full h-12 pl-12 pr-4 rounded-xl bg-white/95 border-transparent text-gray-800 placeholder-gray-500
                    focus:ring-2 focus:ring-[#8B4513] focus:border-[#8B4513] transition shadow-sm" />
                <svg class="w-5 h-5 text-[#8B4513] absolute left-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <button type="button" wire:click="openFilters" class="ml-2 p-3 bg-[#8B4513] text-white rounded-xl hover:bg-[#654321] transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </button>
            </div>
            @if(!empty($suggestions))
                <div class="relative">
                    <div class="absolute z-20 mt-2 w-full bg-white border rounded-lg shadow">
                        @foreach($suggestions as $s)
                            <a href="{{ route('product.details',['id'=>$s['ProductID']]) }}" class="block px-3 py-2 text-sm text-gray-800 hover:bg-amber-50">{{ $s['Title'] }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($showFilters)
                <div class="fixed inset-0 z-30 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/30" wire:click="closeFilters"></div>
                    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-md p-5">
                        <h3 class="text-sm font-semibold mb-3">Filters</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Category</label>
                                <select wire:model="category" class="w-full rounded-md border-gray-300 h-9 text-sm focus:ring-amber-400 focus:border-amber-400">
                                    <option value="">All</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->CategoryID }}">{{ $cat->CategoryName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Min price</label>
                                    <input type="number" step="0.01" wire:model.lazy="minPrice" class="w-full rounded-md border-gray-300 h-9 text-sm focus:ring-amber-400 focus:border-amber-400" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Max price</label>
                                    <input type="number" step="0.01" wire:model.lazy="maxPrice" class="w-full rounded-md border-gray-300 h-9 text-sm focus:ring-amber-400 focus:border-amber-400" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" wire:click="clearFilters" class="h-9 px-3 rounded-md border">Clear</button>
                            <button type="button" wire:click="applyFilters" class="h-9 px-3 rounded-md bg-amber-600 text-white hover:bg-amber-700">Apply</button>
                        </div>
                        <button type="button" wire:click="closeFilters" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">✕</button>
                    </div>
                </div>
            @endif
        </div>

        <section>
            @if($products->count() === 0)
                <p class="text-gray-500">No products found.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7 max-w-6xl mx-auto">
                    @foreach($products as $product)
                        <div class="bg-white rounded-xl overflow-hidden shadow-md border border-gray-100 hover:shadow-lg transition relative h-full flex flex-col">
                            <div class="w-full h-56 bg-white flex items-center justify-center p-3">
                                <img src="{{ $product->Image ? asset($product->Image) : asset('images/placeholder.png') }}" 
                                    alt="{{ $product->Title }}" class="max-h-full max-w-full object-contain" />
                                @if(($product->StockQuantity ?? 0) <= 0)
                                    <span class="absolute top-2 left-2 px-2 py-1 text-xs rounded bg-red-100 text-red-700">Out of stock</span>
                                @endif
                            </div>
                            <div class="p-5 flex-1 flex flex-col">
                                <h3 class="font-semibold text-gray-800 line-clamp-2">{{ $product->Title }}</h3>
                                <p class="mt-2 text-sm text-gray-600 line-clamp-2 min-h-[40px]">{{ $product->Description }}</p>
                                <div class="mt-3 text-[#654321] font-bold text-lg">Rs {{ number_format($product->Price,2) }}</div>
                                <div class="mt-auto pt-4">
                                    <a href="{{ route('product.details', ['id' => $product->ProductID]) }}" 
                                        class="w-full inline-block bg-[#8B4513] text-white text-center py-2 rounded-lg text-sm font-medium hover:bg-[#654321] transition">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $products->links() }}</div>
            @endif
        </section>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('filters', () => ({
            showFilters: false
        }))
    })
</script>
