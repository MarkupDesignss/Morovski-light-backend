<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAdvantage extends Model
{
    protected $table = 'platform_advantages';

    protected $fillable = [
        'step_number',
        'title',
        'title_de',
        'description',
        'description_de',
        'heading',
        'heading_de',
        'subheading',
        'subheading_de',
    ];

    public function getTitleAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->attributes['title_de'])) {
            return $this->attributes['title_de'];
        }

        return $this->attributes['title'] ?? null;
    }

    public function getDescriptionAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->attributes['description_de'])) {
            return $this->attributes['description_de'];
        }

        return $this->attributes['description'] ?? null;
    }

    public function getHeadingAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->attributes['heading_de'])) {
            return $this->attributes['heading_de'];
        }

        return $this->attributes['heading'] ?? null;
    }

    public function getSubheadingAttribute()
    {
        if (app()->getLocale() === 'de' && !empty($this->attributes['subheading_de'])) {
            return $this->attributes['subheading_de'];
        }

        return $this->attributes['subheading'] ?? null;
    }

    public function getTitleTranslatedAttribute()
    {
        return (app()->getLocale() === 'de' && !empty($this->title_de))
            ? $this->title_de
            : $this->title;
    }

    public function getDescriptionTranslatedAttribute()
    {
        return (app()->getLocale() === 'de' && !empty($this->description_de))
            ? $this->description_de
            : $this->description;
    }

    public function getHeadingTranslatedAttribute()
    {
        return (app()->getLocale() === 'de' && !empty($this->heading_de))
            ? $this->heading_de
            : $this->heading;
    }

    public function getSubheadingTranslatedAttribute()
    {
        return (app()->getLocale() === 'de' && !empty($this->subheading_de))
            ? $this->subheading_de
            : $this->subheading;
    }
}
