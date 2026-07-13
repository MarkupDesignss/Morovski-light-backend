<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'reference_type',
        'reference_id',
        'priority',
        'is_read',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'is_read' => 'boolean'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
