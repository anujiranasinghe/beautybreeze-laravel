<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;

class OrderItem extends Model
{
    protected $table = 'orderitems';
    protected $primaryKey = 'OrderItemId';

    protected $fillable = [
        'OrderId',
        'ProductId',
        'ProductName',
        'UnitPrice',
        'Quantity',
        'TotalPrice',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'OrderId', 'OrderId');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductId', 'ProductID');
    }
}
