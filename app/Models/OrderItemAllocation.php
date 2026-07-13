<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemAllocation extends Model
{
    protected $fillable = [

        'order_id',

        'order_item_id',

        'warehouse_id',

        'sales_executive_id',

        'admin_id',
        'packed_qty',

        'allocated_qty',

        'dispatched_qty',

        'status',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function salesExecutive()
    {
        return $this->belongsTo(User::class, 'sales_executive_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN RELATION
    |--------------------------------------------------------------------------
    */

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}