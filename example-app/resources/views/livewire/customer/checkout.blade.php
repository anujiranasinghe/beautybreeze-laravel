<div class="min-h-screen bg-[#D1BB91]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-24">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Checkout</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Billing Details</h2>
                <form wire:submit.prevent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" wire:model.defer="name" class="mt-1 w-full rounded-md border-gray-300" />
                            @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" wire:model.defer="email" class="mt-1 w-full rounded-md border-gray-300" />
                            @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" wire:model.defer="phone" class="mt-1 w-full rounded-md border-gray-300" />
                            @error('phone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea wire:model.defer="address" rows="3" class="mt-1 w-full rounded-md border-gray-300"></textarea>
                            @error('address')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <h2 class="text-lg font-semibold mb-3">Payment</h2>
                        @if(empty(config('services.stripe.key')) || empty($clientSecret))
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                                Stripe keys not configured. You can still place a test order (no real charge).
                            </div>
                        @else
                            <div id="card-element" class="p-3 border rounded-md"></div>
                            <div id="card-errors" class="mt-2 text-sm text-red-600"></div>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button id="pay-button" class="px-4 py-2 bg-[#8B4513] text-white rounded-md hover:bg-[#6f3a0f]">Pay Now</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                <div class="space-y-3 max-h-80 overflow-auto">
                    @foreach($cart as $id => $item)
                        <div class="flex items-center gap-3">
                            <img src="{{ $item['image'] ? asset($item['image']) : asset('images/placeholder.png') }}" class="w-14 h-14 object-contain bg-gray-50 rounded" />
                            <div class="flex-1">
                                <div class="text-sm font-medium">{{ $item['title'] }}</div>
                                <div class="text-xs text-gray-600">Qty: {{ $item['quantity'] }}</div>
                            </div>
                            <div class="text-sm font-semibold">Rs {{ number_format($item['price'] * $item['quantity'],2) }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 border-t pt-3 flex justify-between text-sm">
                    <span>Subtotal</span>
                    <span>Rs {{ number_format($subtotal,2) }}</span>
                </div>
                <div class="mt-1 flex justify-between text-base font-semibold">
                    <span>Total</span>
                    <span>Rs {{ number_format($subtotal,2) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(!empty(config('services.stripe.key')) && !empty($clientSecret))
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stripe = Stripe(@json(config('services.stripe.key')));
            const elements = stripe.elements();
            const card = elements.create('card');
            card.mount('#card-element');
            const btn = document.getElementById('pay-button');
            const errors = document.getElementById('card-errors');

            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                btn.disabled = true;
                errors.textContent = '';
                const clientSecret = @json($clientSecret);
                const {paymentIntent, error} = await stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: card,
                        billing_details: { name: @json($name), email: @json($email) }
                    }
                });
                if (error) {
                    errors.textContent = error.message;
                    btn.disabled = false;
                } else if (paymentIntent && paymentIntent.status === 'succeeded') {
                    // Call Livewire method directly to finalize order
                    window.Livewire.find(@this.__instance.id).call('finalizeOrder', paymentIntent.id);
                }
            });
        });
    </script>
    @else
    <script>
        // Fallback: no stripe setup, directly finalize for demo/testing
        document.getElementById('pay-button')?.addEventListener('click', function(e){
            e.preventDefault();
            window.Livewire.find(@this.__instance.id).call('finalizeOrder', 'no-intent');
        });
    </script>
    @endif
</div>
