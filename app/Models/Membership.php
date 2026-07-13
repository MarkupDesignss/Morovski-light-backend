<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $fillable = [
        'name',
        'name_de',
        'price',
        'duration_type',
        'duration_value',
        'features',
        'features_de',
        'discount',
        'is_popular',
        'status'
    ];

  protected $casts = [
        'features' => 'array',
        'features_de' => 'array',
        'is_popular' => 'boolean',
        'status' => 'boolean',
        'duration_value' => 'integer',
    ];
    
    public function getNameAttribute($value)
    {
        if (app()->getLocale() === 'de' && !empty($this->name_de)) {
            return $this->name_de;
        }
        return $value;
    }
    public function getFeaturesAttribute($value)
{
    if (app()->getLocale() === 'de' && !empty($this->features_de)) {
        return $this->features_de;
    }

    // handle string JSON case
    if (is_string($value)) {
        return json_decode($value, true);
    }

    return $value;
}
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function getFinalPriceAttribute()
    {
        $discountAmount = ($this->price * $this->discount) / 100;
        return max(0, $this->price - $discountAmount);
    }
    public function requests()
    {
        return $this->hasMany(GoldShowcaseRequest::class);
    }
}
