<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->ProductID ?? $this->id,
            'title' => $this->Title,
            'description' => $this->Description,
            'category_id' => $this->CategoryID,
            'price' => (float) $this->Price,
            'image' => $this->Image,
            'stock' => $this->StockQuantity ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
