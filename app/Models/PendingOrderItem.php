<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingOrderItem extends Model
{
    protected $table = 'pending_order_items';
    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
