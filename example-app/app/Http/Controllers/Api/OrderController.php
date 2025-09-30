<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $email = $user?->email;
        $orders = Order::with(['items'])
            ->when($email, fn($q) => $q->where('Email', $email))
            ->orderByDesc('OrderId')
            ->paginate(10);

        return OrderResource::collection($orders);
    }
}
