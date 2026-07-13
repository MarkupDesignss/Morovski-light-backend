<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';
    protected $fillable = [
        'title',
        'slug',
        'title_de',
        'heading_de',
        'content_de',
        'content',
        'heading',
        'meta_title',
        'meta_description',
        'is_active',
        'main_button',
        'sub_buttons',
    ];

    protected $casts = [
        'main_button' => 'array',
        'sub_buttons' => 'array',
    ];

    public function getTitleAttribute($value)
    {
        if (app()->getLocale() === 'de' && !empty($this->title_de)) {
            return $this->title_de;
        }
        return $value;
    }
    public function getHeadingAttribute($value)
    {
        if (app()->getLocale() === 'de' && !empty($this->heading_de)) {
            return $this->heading_de;
        }
        return $value;
    }
    public function getContentAttribute($value)
    {
        if (app()->getLocale() === 'de' && !empty($this->content_de)) {
            return $this->content_de;
        }
        return $value;
    }

    public function getTitleTranslatedAttribute()
    {
        return (app()->getLocale() === 'de' && !empty($this->title_de))
            ? $this->title_de
            : $this->title;
    }

    public function getHeadingTranslatedAttribute()
    {
        return (app()->getLocale() === 'de' && !empty($this->heading_de))
            ? $this->heading_de
            : $this->heading;
    }

    public function getContentTranslatedAttribute()
    {
        return (app()->getLocale() === 'de' && !empty($this->content_de))
            ? $this->content_de
            : $this->content;
    }

    public function images()
    {
        return $this->hasMany(PageImage::class)->orderBy('sort_order');
    }
}
