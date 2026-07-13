<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{

    protected $table = 'invoice_items';
    protected $guarded = [];
    
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
