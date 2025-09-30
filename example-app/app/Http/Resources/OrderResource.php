<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'order_id' => $this->OrderId,
            'customer_id' => $this->CustomerID,
            'customer_name' => $this->CustomerName,
            'email' => $this->Email,
            'phone' => $this->PhoneNo,
            'address' => $this->Address,
            'payment_method' => $this->PaymentMethod,
            'payment_status' => $this->PaymentStatus,
            'status' => $this->Status,
            'created_at' => $this->created_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'total' => (float) ($this->items?->sum('TotalPrice') ?? 0),
        ];
    }
}

