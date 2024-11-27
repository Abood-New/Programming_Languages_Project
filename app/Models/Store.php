<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'name',
        'store_image'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_stores')->withPivot([
            'available_quantity',
            'price'
        ]);
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
