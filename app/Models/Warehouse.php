<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'address', 'city', 'state', 'pin_code',
        'country', 'contact_person', 'contact_phone', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'warehouse_items')
                    ->withPivot('quantity', 'reserved_quantity')
                    ->withTimestamps();
    }
}