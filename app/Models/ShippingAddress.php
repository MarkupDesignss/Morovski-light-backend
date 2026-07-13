<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default'
    ];

    // 🔹 Address belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 One address can be used in multiple orders
    public function orders()
    {
        return $this->hasMany(Order::class, 'shipping_id');
    }
}
