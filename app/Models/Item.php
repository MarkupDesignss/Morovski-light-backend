<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    // use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'specifications' => 'array',
    ];
    
        public function reviews()
    {
        return $this->hasMany(Review::class, 'item_id');
    }
    
    public function warehouseTransferItems()
    {
        return $this->hasMany(WarehouseTransferItem::class, 'item_id');
    }

    
    public function scopeVisibleForUser($query, $user)
    {
        if (!$user) {
            return $query->where('type', 'online');
        }
    
        $hasAccessRole = $user->roles()
            ->whereIn('roles.id', [1, 2, 3])
            ->exists();
    
        if (!$hasAccessRole) {
            return $query->where('type', 'online');
        }
    
        return $query;
    }

    public function images()
    {
        return $this->hasMany(ItemImage::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouseItems()
    {
        return $this->hasMany(WarehouseItem::class, 'item_id');
    }
    
    public function backInStockNotifications()
    {
        return $this->hasMany(BackInStockNotification::class);
    }
    
    public function warehouses()
    {
        return $this->belongsToMany(
            Warehouse::class,
            'warehouse_items',
            'item_id',
            'warehouse_id'
        )->withPivot('quantity', 'reserved_quantity', 'damaged_quantity');
    }

}
