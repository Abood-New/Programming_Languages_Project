<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'product_image'
    ];

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'product_store')->withPivot([
            'available_quantity',
            'price'
        ]);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}