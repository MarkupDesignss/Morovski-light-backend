<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $table = 'support_tickets';
    protected $guarded = [];
    public function images()
    {
        return $this->hasMany(SupportTicketImage::class, 'ticket_id');
    }
     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
