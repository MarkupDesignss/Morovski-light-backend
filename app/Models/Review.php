<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Review extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'reviewer_id',
        'user_id',
        'order_id',
        'item_id',
        'rating',
        'comment',
        'comment_de',
        'edited_at',
        'is_edited',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rating' => 'integer',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];
    
    
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    /**
     * Get the user who received this review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who left this review.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the order associated with this review.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if review can be edited (within 24 hours).
     */
    public function canBeEdited(): bool
    {
        if ($this->edited_at) {
            return false;
        }
        
        return Carbon::parse($this->created_at)->diffInHours(now()) < 24;
    }

    /**
     * Scope for reviews of a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for reviews with specific rating.
     */
    public function scopeWithRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope for recent reviews.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    /**
     * Get all reports for this review.
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    // Accessor for dynamic language switch
    public function getCommentAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale === 'de' && $this->comment_de) {
            return $this->comment_de;
        }
        return $value;
    }
}