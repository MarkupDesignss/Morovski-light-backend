<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplacementRequest extends Model
{
    protected $table = 'replacement_requests';
    protected $guarded = [];


    protected $casts = [
        'images' => 'array',
        'items' => 'array',
        'created_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'received_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
     public function getImagesAttribute($value)
    {
        $images = json_decode($value, true) ?? [];

        return array_map(function ($image) {

            // already full url
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }

            return asset('storage/' . ltrim($image, '/'));

        }, $images);
    }
}
