<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'order_item_id' => $this->OrderItemId ?? $this->OrderItemId,
            'product_id' => $this->ProductId,
            'product_name' => $this->ProductName,
            'unit_price' => (float) $this->UnitPrice,
            'quantity' => (int) $this->Quantity,
            'total_price' => (float) $this->TotalPrice,
        ];
    }
}

