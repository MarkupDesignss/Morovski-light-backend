<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'name',
        'name_de',
        'image',
        'slug',
        'parent_id',
        'is_active',
        'sort_order'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }
    public function items()
    {
        return $this->hasMany(Item::class);
    }
    
    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
    
        return asset('storage/' . $value);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function getNameAttribute($value)
    {
        if (app()->getLocale() === 'de' && !empty($this->name_de)) {
            return $this->name_de;
        }
        return $value;
    }

    public function getNameTranslatedAttribute()
    {
        return (app()->getLocale() === 'de' && !empty($this->name_de))
            ? $this->name_de
            : $this->name;
    }
}
