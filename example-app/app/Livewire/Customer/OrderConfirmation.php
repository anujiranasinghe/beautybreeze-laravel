<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use App\Models\Order;

#[LivewireLayout('layouts.customer')]
class OrderConfirmation extends Component
{
    public Order $order;

    public function mount($id)
    {
        $this->order = Order::with('items')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.customer.order-confirmation');
    }
}

