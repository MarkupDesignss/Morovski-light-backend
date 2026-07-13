<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCharge extends Model
{
    protected $table = 'shipping_charges';
    protected $fillable = [
        'type',
        'min_value',
        'max_value',
        'charge',
        'status'
    ];
}
