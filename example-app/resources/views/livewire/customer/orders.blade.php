

<div class="min-h-screen bg-[#D1BB91]" wire:poll.visible.10s>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-24"> <!-- Changed from pb-16 to pt-24 pb-24 -->
        <div class="bg-white/95 rounded-2xl shadow-lg p-8 mt-20">
            <h1 class="text-2xl font-bold text-[#8B4513] mb-6">My Orders</h1>
            
            @if($orders->isEmpty())
                <div class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <p class="text-gray-600">You haven't placed any orders yet.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($orders as $order)
                        <div class="bg-white rounded-xl border border-amber-100 overflow-hidden">
                            <!-- Order Header -->
                            <div class="bg-amber-50/50 px-6 py-4 flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-600">Order #{{ $order->OrderId }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->created_at->format('F d, Y - h:i A') }}</div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-sm font-semibold text-[#654321]">
                                        Rs {{ number_format($order->items->sum('TotalPrice'), 2) }}
                                    </div>
                                    @php($status = strtolower($order->Status ?? 'pending'))
                                    <span @class([
                                        'px-3 py-1 rounded-full text-xs font-medium',
                                        'bg-yellow-100 text-yellow-800' => $status === 'pending' || $status === '',
                                        'bg-blue-100 text-blue-800' => $status === 'processing' || $status === 'shipped',
                                        'bg-green-100 text-green-800' => $status === 'delivered',
                                    ])>
                                        {{ ucfirst($status) }}
                                    </span>
                                </div>
                            </div>

                           
                            <!-- Order Items -->
                            <div class="p-6">
                                <div class="space-y-3">
                                    @foreach($order->items as $item)
                                        <div class="flex items-center gap-4">
                                            <div class="w-16 h-16 bg-gray-50 rounded-lg flex items-center justify-center">
                                                <img src="{{ asset($item->product?->Image ?? 'images/placeholder.png') }}" 
                                                    alt="" class="max-h-12 max-w-12 object-contain" />
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-800">{{ $item->ProductName }}</div>
                                                <div class="text-sm text-gray-600">
                                                    Qty: {{ $item->Quantity }} Rs {{ number_format($item->UnitPrice, 2) }}
                                                </div>
                                            </div>
                                            <div class="font-medium text-[#654321]">
                                                Rs {{ number_format($item->TotalPrice, 2) }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-6 flex justify-end gap-3">
                                    <a href="{{ route('order.confirmation', ['id' => $order->OrderId]) }}" 
                                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-[#8B4513] text-[#8B4513] hover:bg-[#8B4513] hover:text-white transition-colors">
                                        View Receipt
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

