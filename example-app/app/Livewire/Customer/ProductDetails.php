<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime;

#[LivewireLayout('layouts.customer')]
class ProductDetails extends Component
{
    public Product $product;
    public int $quantity = 1;

    public function mount($id)
    {
        $this->product = Product::findOrFail($id);
        $nowMs = new UTCDateTime(now()->getTimestamp() * 1000);
        $payload = [
            'action'     => 'view',
            'product_id' => $this->product->ProductID,
            'meta'       => ['title' => $this->product->Title],
            'user_id'    => Auth::id(),
            'created_at' => $nowMs,
        ];
        try {
            // Log customer views into Mongo (if available)
            DB::connection('mongodb')->selectCollection('product_views')->insertOne($payload);
        } catch (\Throwable $e) {
            Log::warning('Mongo view log insert failed', [
                'product_id' => $this->product->ProductID,
                'error' => $e->getMessage(),
            ]);
        } finally {
            // Always mirror to a dedicated file for demo/audit
            try {
                Log::channel('product_views')->info('product_view', [
                    'product_id' => $this->product->ProductID,
                    'title' => $this->product->Title,
                    'user_id' => Auth::id(),
                    'at' => now()->toIso8601String(),
                ]);
            } catch (\Throwable $e) {}
        }
    }

    public function addToCart()
    {
        // Strictly enforce stock limits
        $available = (int)($this->product->StockQuantity ?? 0);
        if ($available <= 0) {
            session()->flash('error', 'This product is out of stock');
            return;
        }
        $cart = Session::get('cart', []);
        $key = (string)$this->product->ProductID;
        $currentQty = $cart[$key]['quantity'] ?? 0;
        $requested = max(1, (int)$this->quantity);

        // If request exceeds available (considering existing cart qty), block and message
        if (($currentQty + $requested) > $available) {
            $remaining = max(0, $available - $currentQty);
            if ($remaining <= 0) {
                session()->flash('error', 'This product is out of stock');
            } else {
                session()->flash('error', "Only {$remaining} available. Please reduce quantity.");
            }
            return;
        }

        $cart[$key] = [
            'id' => $this->product->ProductID,
            'title' => $this->product->Title,
            'price' => $this->product->Price,
            'image' => $this->product->Image,
            'quantity' => $currentQty + $requested,
        ];
        Session::put('cart', $cart);
        $this->dispatch('cart-updated');
        session()->flash('message', 'Added to cart');
    }

    public function render()
    {
        return view('livewire.customer.product-details');
    }
}
