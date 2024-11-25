<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'name',
        'store_image'
    ];
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_store')->withPivot([
            'available_quantity',
            'price'
        ]);
    }
}
