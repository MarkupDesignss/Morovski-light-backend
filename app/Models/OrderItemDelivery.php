<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemDelivery extends Model
{
    protected $table = 'order_item_deliveries';
    protected $guarded = [];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
