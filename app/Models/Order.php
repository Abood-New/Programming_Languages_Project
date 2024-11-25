<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_date',
        'order_status',
        'total_price',
        'address',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
