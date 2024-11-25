<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemOrder extends Model
{
    protected $table = 'item_order';
    protected $fillable = [
        'order_id',
        'product_store_id',
        'quantity',
        'price'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function productStore()
    {
        return $this->belongsTo(ProductStore::class);
    }
}

