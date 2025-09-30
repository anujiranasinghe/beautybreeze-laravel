<div class="space-y-6">
    <h1 class="text-2xl font-semibold">Products</h1>

    @if(!empty($bannerMessage))
        <div class="rounded-lg px-4 py-3 {{ $bannerType === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : ($bannerType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
            {{ $bannerMessage }}
        </div>
@endif

    @if($mode === 'menu')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold mb-2">View & Edit Products</h3>
                <p class="text-sm text-gray-600 mb-4">Browse all products, edit details, and delete items.</p>
                <div class="flex items-center justify-start">
                    <button class="px-4 py-2 rounded bg-amber-700 text-white" wire:click="openList">Open List</button>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Add New Product</h3>
                <p class="text-sm text-gray-600 mb-4">Create a new product with images and details.</p>
                <button class="px-4 py-2 rounded bg-amber-700 text-white" wire:click="openCreate">Add Product</button>
            </div>
        </div>
    @endif

    @if($mode === 'list')
        <div class="bg-white rounded-lg shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 relative">
                <div class="relative">
                    <input type="text" wire:model.debounce.300ms="search" class="border rounded-lg px-3 py-2 w-full" placeholder="Search title...">
                    @if(!empty($suggestions))
                        <div class="absolute z-20 mt-1 w-full bg-white border rounded-lg shadow">
                            @foreach($suggestions as $s)
                                <button type="button" class="block w-full text-left px-3 py-2 text-sm hover:bg-amber-50" wire:click="selectSuggestion('{{ addslashes($s) }}')">{{ $s }}</button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <input type="number" step="0.01" wire:model.debounce.300ms="minPrice" class="border rounded-lg px-3 py-2" placeholder="Min price">
                <input type="number" step="0.01" wire:model.debounce.300ms="maxPrice" class="border rounded-lg px-3 py-2" placeholder="Max price">
                <div class="flex items-center justify-end gap-2">
                    <button wire:click="loadProducts" class="px-3 py-2 border rounded-lg">Refresh</button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow divide-y">
            <div class="grid grid-cols-12 gap-3 px-4 py-2 font-semibold text-sm text-gray-600">
                <div class="col-span-5">Title</div>
                <div class="col-span-2">Price</div>
                <div class="col-span-2">Stock</div>
                <div class="col-span-3 text-right">Actions</div>
            </div>
            @forelse($products as $p)
                <div class="grid grid-cols-12 gap-3 px-4 py-3 items-center">
                    <div class="col-span-5 flex items-center gap-3">
                        @php($src = $p['image_url'] ?? (!empty($p['image']) ? asset($p['image']) : asset('images/placeholder.svg')))
                        <img src="{{ $src }}" class="w-12 h-12 object-cover rounded" alt="" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}'">
                        <div class="text-sm text-gray-800 line-clamp-2">{{ $p['title'] }}</div>
                    </div>
                    <div class="col-span-2 text-sm">Rs {{ number_format($p['price'] ?? 0, 2) }}</div>
                    <div class="col-span-2 text-sm">{{ $p['stock'] ?? '-' }}</div>
                    <div class="col-span-3 flex items-center justify-end gap-2">
                        <button wire:click="openEdit({{ $p['id'] }})" class="px-3 py-1.5 text-sm rounded border">Edit</button>
                        <button wire:click="confirmDelete({{ $p['id'] }})" class="px-3 py-1.5 text-sm rounded bg-red-600 text-white">Delete</button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-gray-500">No products found.</div>
            @endforelse
        </div>

        @if(($pagination['last_page'] ?? 1) > 1)
            <div class="flex items-center justify-end gap-2">
                <button class="px-3 py-1.5 border rounded" @disabled(($pagination['current_page'] ?? 1) <= 1) wire:click="goToPage({{ ($pagination['current_page'] ?? 1) - 1 }})">Prev</button>
                <div class="text-sm">Page {{ $pagination['current_page'] ?? 1 }} / {{ $pagination['last_page'] ?? 1 }}</div>
                <button class="px-3 py-1.5 border rounded" @disabled(($pagination['current_page'] ?? 1) >= ($pagination['last_page'] ?? 1)) wire:click="goToPage({{ ($pagination['current_page'] ?? 1) + 1 }})">Next</button>
            </div>
        @endif
    @endif

    <!-- Blocked Delete Modal -->
    @if($showBlockedDelete)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" wire:click="$set('showBlockedDelete', false)"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-md p-6 z-50">
            <h3 class="text-lg font-semibold mb-2 text-red-700">Cannot Delete Product</h3>
            <p class="text-sm text-gray-700">{{ $blockedDeleteMessage }}</p>
            <div class="mt-6 flex justify-end">
                <button class="px-4 py-2 rounded bg-amber-700 text-white" wire:click="$set('showBlockedDelete', false)">OK</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Create Modal -->
    @if($showCreate)
    <div class="fixed inset-0 z-40 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/30" wire:click="$set('showCreate', false)"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl p-6 z-50">
            <h3 class="text-lg font-semibold mb-4">Create Product</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" wire:model="Title" class="border rounded-lg px-3 py-2" placeholder="Title">
                <input type="number" step="0.01" wire:model="Price" class="border rounded-lg px-3 py-2" placeholder="Price">
                <input type="number" wire:model="CategoryID" class="border rounded-lg px-3 py-2" placeholder="Category ID">
                <input type="number" wire:model="StockQuantity" class="border rounded-lg px-3 py-2" placeholder="Stock Quantity">
                <input type="text" wire:model="Image" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Image URL (optional)">
                <textarea wire:model="Description" class="border rounded-lg px-3 py-2 md:col-span-2" rows="3" placeholder="Description"></textarea>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-600 mb-1">Upload Image (optional)</label>
                    <input type="file" wire:model="imageUpload" class="block w-full">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button class="px-4 py-2 rounded border" wire:click="$set('showCreate', false); $set('mode','menu')">Cancel</button>
                <button class="px-4 py-2 rounded bg-amber-700 text-white" wire:click="createProduct" wire:loading.attr="disabled">Create</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Modal -->
    @if($showEdit)
    <div class="fixed inset-0 z-40 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/30" wire:click="$set('showEdit', false)"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl p-6 z-50">
            <h3 class="text-lg font-semibold mb-4">Edit Product</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" wire:model="Title" class="border rounded-lg px-3 py-2" placeholder="Title">
                <input type="number" step="0.01" wire:model="Price" class="border rounded-lg px-3 py-2" placeholder="Price">
                <input type="number" wire:model="CategoryID" class="border rounded-lg px-3 py-2" placeholder="Category ID">
                <input type="number" wire:model="StockQuantity" class="border rounded-lg px-3 py-2" placeholder="Stock Quantity">
                <input type="text" wire:model="Image" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Image URL (optional)">
                <textarea wire:model="Description" class="border rounded-lg px-3 py-2 md:col-span-2" rows="3" placeholder="Description"></textarea>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-600 mb-1">Replace Image (optional)</label>
                    <input type="file" wire:model="imageUpload" class="block w-full">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button class="px-4 py-2 rounded border" wire:click="$set('showEdit', false); $set('mode','menu')">Cancel</button>
                <button class="px-4 py-2 rounded bg-amber-700 text-white" wire:click="updateProduct" wire:loading.attr="disabled">Save</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirm -->
    @if($showDelete)
    <div class="fixed inset-0 z-40 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/30" wire:click="$set('showDelete', false)"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-md p-6 z-50">
            <h3 class="text-lg font-semibold mb-2">Delete Product</h3>
            <p class="text-sm text-gray-600">Are you sure you want to delete this product?</p>
            <div class="mt-6 flex justify-end gap-2">
                <button class="px-4 py-2 rounded border" wire:click="$set('showDelete', false)">Cancel</button>
                <button class="px-4 py-2 rounded bg-red-600 text-white" wire:click="deleteProduct" wire:loading.attr="disabled">Delete</button>
            </div>
        </div>
    </div>
    @endif
</div>
