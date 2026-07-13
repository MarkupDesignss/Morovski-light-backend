<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackInStockNotification extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'is_notified',
        'notified_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
