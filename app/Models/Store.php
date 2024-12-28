<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'store_name',
        'store_image',
        'description',
        'owner_id'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function orders()
    {
        return $this->hasManyThrough(Order::class, OrderItem::class);
    }
}
