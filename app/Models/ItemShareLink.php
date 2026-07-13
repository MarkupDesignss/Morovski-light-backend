<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemShareLink extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->belongsToMany(
            Item::class,
            'item_share_link_items',
            'item_share_link_id',
            'item_id'
        );
    }

    public function clicks()
    {
        return $this->hasMany(
            ItemShareLinkClick::class,
            'item_share_link_id'
        );
    }
}
