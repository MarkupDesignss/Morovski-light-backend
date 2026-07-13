<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamagedItem extends Model
{
    protected $table = 'damaged_items';
    protected $guarded = [];
    
        public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    
    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
