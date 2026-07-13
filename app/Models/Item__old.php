<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        // Relations
        'user_id',
        'category_id',

        // Basic Info
        'title',
        'slug',
        'description',
        'key_features',

        // Pricing
        'price',
        'quantity',

        // Offers
        'allow_offers',

        // Type & Status
        'type',
        'status',
        'is_showcase',

        // Seller Metrics
        'rating',
        'reviews_count',
        'sold_count',

        // Shipping
        'shipping_from',
        'delivery_time',
        'return_policy',
        'free_shipping',

        // Specifications (JSON)
        'specifications',

        // Extra
        'location',
        'views',

        // Publish
        'published_at'
    ];

    protected $casts = [
        'specifications' => 'array',
        'free_shipping' => 'boolean',
        'allow_offers' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function images()
    {
        return $this->hasMany(ItemImage::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function offers()
    {
        return $this->hasMany(ItemOffer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function auction()
    {
        return $this->hasOne(Auction::class);
    }
    public function getFirstImageAttribute()
    {
        $image = $this->images()->first();

        return $image ? asset('storage/' . $image->image) : null;
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
    public function thumbnail()
    {
        return $this->hasOne(ItemImage::class)->orderBy('sort_order', 'asc');
    }
    protected $appends = ['is_wishlist'];
    
    public function getIsWishlistAttribute()
    {
        return isset($this->wishlists_count) && $this->wishlists_count > 0;
    }
    /**
     * Get all reports for this item.
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }
    /**
     * Get the boost record for this item
     */
    // public function boost()
    // {
    //     return $this->hasOne(BoostedListing::class)
    //         ->where('status', 'active')
    //         ->where('ends_at', '>', now());
    // }
    
    /**
     * Check if item is boosted
     */
    public function isBoosted()
    {
        return $this->boost()->exists();
    }
    
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    // public function activeSubscription()
    // {
    //     return $this->hasOne(Subscription::class)
    //         ->where('status', 'active')
    //         ->where('ends_at', '>=', now());
    // }
    
    // Boost relation (already hai, just improve)
    public function boost()
    {
        return $this->hasOne(BoostedListing::class)
            ->where('status', 'active')
            ->where('ends_at', '>', now());
    }
    
    // Seller active subscription
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class, 'user_id', 'user_id')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>', now());
            });
    }
    public function getIsBoostedAttribute()
    {
        return $this->relationLoaded('boost')
            ? $this->boost !== null
            : $this->boost()->exists();
    }
}