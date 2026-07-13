<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransferItem extends Model
{
    protected $table = 'warehouse_transfer_items';

    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    
    public function warehouseTransfer()
    {
        return $this->belongsTo(WarehouseTransfer::class);
    }

    public function transfer()
    {
        return $this->belongsTo(
            WarehouseTransfer::class,
            'warehouse_transfer_id'
        );
    }
}
