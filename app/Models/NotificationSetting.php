<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'sales_notifications',
        'shipping_updates',
        'bid_notifications',
        'chat_notifications',
        'membership_notifications',
    ];

    protected $casts = [
        'sales_notifications' => 'boolean',
        'shipping_updates' => 'boolean',
        'bid_notifications' => 'boolean',
        'chat_notifications' => 'boolean',
        'membership_notifications' => 'boolean',
    ];

    /**
     * Relation with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}