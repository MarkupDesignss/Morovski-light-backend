<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;


class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'image',
        'status',
        'moderated_by',
        'moderated_at',
        'resolution_notes',
        'send_updates',
        'is_anonymous',
        'terms_accepted',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'moderated_at' => 'datetime',
        'status' => 'string',
    ];
    protected $appends = [
        'image_url',
    ];

    /**
     * Get the user who reported this content.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the admin who moderated this report.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'moderated_by');
    }

    /**
     * Get the reported content (polymorphic).
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for pending reports only.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for reports by content type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('reportable_type', $type);
    }

    /**
     * Check if report is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if report is reviewed.
     */
    public function isReviewed(): bool
    {
        return $this->status === 'reviewed';
    }
    
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }

    /**
     * Check if report is dismissed.
     */
    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    /**
     * Check if action was taken.
     */
    public function actionTaken(): bool
    {
        return $this->status === 'action_taken';
    }

    /**
     * Mark report as reviewed.
     */
    public function markAsReviewed(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'reviewed',
            'moderated_by' => $adminId,
            'moderated_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Mark report as dismissed.
     */
    public function markAsDismissed(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'dismissed',
            'moderated_by' => $adminId,
            'moderated_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Mark report as action taken.
     */
    public function markAsActionTaken(int $adminId, string $notes): void
    {
        $this->update([
            'status' => 'action_taken',
            'moderated_by' => $adminId,
            'moderated_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }
}