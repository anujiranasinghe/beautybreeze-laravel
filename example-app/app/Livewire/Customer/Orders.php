<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

#[LivewireLayout('layouts.customer')]
class Orders extends Component
{
    public function render()
    {
        $orders = collect();
        if (Auth::check()) {
            $orders = Order::with(['items.product'])
                ->where('Email', Auth::user()->email)
                ->orderByDesc('created_at')
                ->get();
        }
        return view('livewire.customer.orders', compact('orders'));
    }
}
