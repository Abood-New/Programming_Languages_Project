<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_name',
        'product_image',
        'category_id',
        'description',
        'store_id',
        'available_quantity',
        'price'
    ];

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
