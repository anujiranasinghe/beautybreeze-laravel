<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'OrderId';

    protected $fillable = [
        'CustomerID',
        'CustomerName',
        'PhoneNo',
        'Address',
        'Email',
        'PaymentMethod',
        'PaymentStatus',
        'Status',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'OrderId', 'OrderId');
    }
}
