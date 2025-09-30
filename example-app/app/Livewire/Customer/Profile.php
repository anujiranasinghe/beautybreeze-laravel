<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;

#[LivewireLayout('layouts.customer')]
class Profile extends Component
{
    public function render()
    {
        return view('livewire.customer.profile');
    }
}
