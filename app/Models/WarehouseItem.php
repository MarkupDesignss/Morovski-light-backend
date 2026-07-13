<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class WarehouseItem extends Model
{
    use HasFactory;

    protected $table = 'warehouse_items'; // Note: your table name is `warehouse_items` (plural)

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'quantity',
        'reserved_quantity',
        'updated_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];

    /**
     * Get the warehouse that owns this stock record.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the item that owns this stock record.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}