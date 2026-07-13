<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderMenu extends Model
{
    protected $table = 'header_menus';
    protected $fillable = ['type', 'title', 'title_de', 'logo','favicon', 'sort_order', 'status'];

    public function getTitleAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->attributes['title_de'])) {
            return $this->attributes['title_de'];
        }

        return $this->attributes['title'] ?? null;
    }

    public function getTitleTranslatedAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->title_de)) {
            return $this->title_de;
        }

        return $this->title;
    }
}
