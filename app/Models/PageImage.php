<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageImage extends Model
{
    protected $fillable = [
        'page_id',
        'image',
        'title',
        'sort_order',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        return asset('storage/' . $this->image);
    }
}