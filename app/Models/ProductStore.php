<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStore extends Model
{
    protected $fillable = [
        'product_id',
        'store_id',
        'available_quantity',
        'price'
    ];

    public function orderItems()
    {
        return $this->hasMany(ItemOrder::class);
    }
}
