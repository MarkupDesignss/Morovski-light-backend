<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function packedOrder()
    {
        return $this->hasOne(PackedOrder::class, 'order_item_id');
    }


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveries()
    {
        return $this->hasMany(OrderItemDelivery::class);
    }
    public function getImageAttribute($value)
    {
        return asset('storage/' . $value);
    }
    public function allocations()
    {
        return $this->hasMany(
            OrderItemAllocation::class,
            'order_item_id'
        );
    }
    public function images()
    {
        return $this->hasManyThrough(
            ItemImage::class,
            Item::class,
            'id',        // Foreign key on Item table
            'item_id',   // Foreign key on ItemImage table
            'item_id',   // Local key on OrderItem
            'id'         // Local key on Item
        );
    }
}
