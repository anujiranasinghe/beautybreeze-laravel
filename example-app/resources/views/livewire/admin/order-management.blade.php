<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-[#8B4513] mb-6 ">Order Management</h1>

    @if($bannerMessage)
        <div class="px-4 py-3 rounded {{ $bannerType==='success' ? 'bg-green-100 text-green-800' : ($bannerType==='error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
            {{ $bannerMessage }}
        </div>
    @endif

    <div class="space-y-4">
        <!--  Search Section -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-sm font-medium text-[#8B4513] mb-1">Search Orders</label>
                    <input type="text" wire:model.live="search" 
                        class="w-full border-gray-300 rounded-lg focus:ring-[#8B4513] focus:border-[#8B4513]" 
                        placeholder="Name, email, phone or Order ID">
                </div>
                <div class="w-48">
                    <label class="block text-sm font-medium text-[#8B4513] mb-1">Status</label>
                    <select wire:model.live="status" class="w-full border-gray-300 rounded-lg focus:ring-[#8B4513] focus:border-[#8B4513]">
                        <option value="">All Orders</option>
                        <option>Pending</option>
                        <option>Processing</option>
                        <option>Shipped</option>
                        <option>Delivered</option>
                        <option>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <!--  Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-[#4a2b1d] text-white">
                    <tr>
                        <th class="text-left px-3 py-2">Order</th>
                        <th class="text-left px-3 py-2">Customer</th>
                        <th class="text-left px-3 py-2">Items</th>
                        <th class="text-left px-3 py-2">Total</th>
                        <th class="text-left px-3 py-2">Status</th>
                        <th class="text-left px-3 py-2">Placed</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $o)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">#{{ $o['id'] }}</td>
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $o['customer'] }}</div>
                                <div class="text-gray-500">{{ $o['email'] }}</div>
                            </td>
                            <td class="px-3 py-2">{{ $o['items_count'] }}</td>
                            <td class="px-3 py-2">Rs {{ number_format($o['total_amount'], 2) }}</td>
                            <td class="px-3 py-2">{{ $o['status'] }}</td>
                            <td class="px-3 py-2">{{ $o['created_at'] }}</td>
                            <td class="px-3 py-2 text-right">
                                <button class="px-4 py-2 rounded-lg bg-[#654321] text-white hover:bg-[#8B4513] transition-colors" 
                                    wire:click="selectOrder({{ $o['id'] }})">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">No orders found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between">
            <div>Page {{ $pagination['current_page'] ?? 1 }} of {{ $pagination['last_page'] ?? 1 }}</div>
            <div class="space-x-2">
                <button class="px-3 py-1 border rounded" @disabled(($pagination['current_page'] ?? 1) <= 1) wire:click="gotoPage({{ max(1, ($pagination['current_page'] ?? 1) - 1) }})">Prev</button>
                <button class="px-3 py-1 border rounded" @disabled(($pagination['current_page'] ?? 1) >= ($pagination['last_page'] ?? 1)) wire:click="gotoPage({{ min(($pagination['last_page'] ?? 1), ($pagination['current_page'] ?? 1) + 1) }})">Next</button>
            </div>
        </div>
    </div>

    @if($selectedOrder)
        <!-- Modal overlay -->
        <div class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm" wire:click="closeDetails"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true" role="dialog">
            <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl border border-amber-100" wire:click.stop>
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b bg-amber-50/50">
                    <div>
                        <div class="text-sm text-[#8B4513]">Order Details</div>
                        <div class="text-xl font-semibold text-gray-800">#{{ $selectedOrder['id'] }}</div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 transition-colors" wire:click="closeDetails">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                    <!-- Customer Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-[#8B4513] font-medium mb-2">Customer Information</div>
                            <div><span class="text-gray-500">Name:</span> {{ $selectedOrder['customer'] }}</div>
                            <div><span class="text-gray-500">Email:</span> {{ $selectedOrder['email'] }}</div>
                            <div><span class="text-gray-500">Phone:</span> {{ $selectedOrder['phone'] }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-[#8B4513] font-medium mb-2">Delivery Address</div>
                            <div class="text-gray-700">{{ $selectedOrder['address'] }}</div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="bg-white rounded-lg border">
                        <div class="px-4 py-3 border-b bg-gray-50">
                            <div class="font-medium text-[#8B4513]">Order Items</div>
                        </div>
                        <div class="divide-y">
                            @foreach($selectedItems as $it)
                                <div class="flex items-center gap-4 p-4">
                                    @if($it['image_url'])
                                        <img src="{{ $it['image_url'] }}" class="w-16 h-16 object-cover rounded-lg border" />
                                    @else
                                        <div class="w-16 h-16 bg-gray-100 rounded-lg border"></div>
                                    @endif
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-800">{{ $it['product_name'] }}</div>
                                        <div class="text-sm text-gray-500">Qty: {{ $it['quantity'] }} × Rs {{ number_format($it['unit_price'], 2) }}</div>
                                    </div>
                                    <div class="font-medium text-[#8B4513]">
                                        Rs {{ number_format($it['total'], 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Status Update -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm font-medium text-[#8B4513] mb-3">Update Order Status</div>
                        <div class="flex flex-wrap gap-2">
                            <button class="px-4 py-2 rounded-lg bg-[#654321] text-white hover:bg-[#8B4513] transition-colors" 
                                wire:click="updateStatus('Shipped')">Mark as Shipped</button>
                            <button class="px-4 py-2 rounded-lg bg-[#654321] text-white hover:bg-[#8B4513] transition-colors" 
                                wire:click="updateStatus('Delivered')">Mark as Delivered</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
