<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use Illuminate\Support\Facades\Session;
use App\Models\Product;

#[LivewireLayout('layouts.customer')]
class ShoppingCart extends Component
{
    public array $items = [];

    public function mount()
    {
        $this->items = Session::get('cart', []);
    }

    public function increment($id)
    {
        $cart = Session::get('cart', []);
        if (isset($cart[$id])) {
            $product = Product::where('ProductID', (int)$id)->first();
            $available = (int)($product->StockQuantity ?? 0);
            $newQty = $cart[$id]['quantity'] + 1;
            if ($newQty > $available) {
                session()->flash('error', "Only {$available} in stock for {$product->Title}.");
                $newQty = $available;
            }
            $cart[$id]['quantity'] = max(1, $newQty);
            Session::put('cart', $cart);
            $this->items = $cart;
        }
    }

    public function decrement($id)
    {
        $cart = Session::get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = max(1, $cart[$id]['quantity'] - 1);
            Session::put('cart', $cart);
            $this->items = $cart;
        }
    }

    public function remove($id)
    {
        $cart = Session::get('cart', []);
        unset($cart[$id]);
        Session::put('cart', $cart);
        $this->items = $cart;
    }

    public function getSubtotalProperty()
    {
        return collect($this->items)->sum(fn($i) => $i['price'] * $i['quantity']);
    }

    public function render()
    {
        return view('livewire.customer.shopping-cart');
    }
}
