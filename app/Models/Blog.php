<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $table = 'blogs';
    protected $fillable = [
        'category_id',
        'slug',
        'heading',
        'heading_de',
        'entries',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'entries' => 'array',
    ];

    public function images()
    {
        return $this->hasMany(BlogImage::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getHeadingAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'de' && $this->heading_de) {
            return $this->heading_de;
        }
        return $value;
    }

    public function getHeadingTranslatedAttribute()
    {
        return (app()->getLocale() === 'de' && $this->heading_de)
            ? $this->heading_de
            : $this->heading;
    }
    public function getEntriesTranslatedAttribute()
    {
        $locale = app()->getLocale();

        return collect($this->entries)->map(function ($entry) use ($locale) {
            return [
                'title' => ($locale === 'de' && !empty($entry['title_de']))
                    ? $entry['title_de']
                    : $entry['title'],

                'description' => ($locale === 'de' && !empty($entry['description_de']))
                    ? $entry['description_de']
                    : $entry['description'],
            ];
        });
    }
    public function getEntriesAttribute($value)
    {
        $entries = json_decode($value, true);
        $locale = app()->getLocale();

        return collect($entries)->map(function ($entry) use ($locale) {

            return [
                'title' => ($locale === 'de' && !empty($entry['title_de']))
                    ? $entry['title_de']
                    : $entry['title'],

                'description' => ($locale === 'de' && !empty($entry['description_de']))
                    ? $entry['description_de']
                    : $entry['description'],

                // optional: agar tum original bhi rakhna chaho
                'title_de' => $entry['title_de'] ?? null,
                'description_de' => $entry['description_de'] ?? null,
            ];
        });
    }
}
