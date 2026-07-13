<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransfer extends Model
{
    protected $table = 'warehouse_transfers';
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(
            WarehouseTransferItem::class,
            'warehouse_transfer_id'
        );
    }
    
    public function fromWarehouse()
    {
        return $this->belongsTo(
            Warehouse::class,
            'from_warehouse'
        );
    }
    
    public function toWarehouse()
    {
        return $this->belongsTo(
            Warehouse::class,
            'to_warehouse'
        );
    }
}
