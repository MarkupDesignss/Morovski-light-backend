<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackedOrder extends Model
{
    protected $fillable = [
        'order_id',
        'number_of_boxes',
        'total_weight',
        'packed_by',
        'packed_at',
    ];

    protected $casts = [
        'packed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'packed_by');
    }
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
    
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}