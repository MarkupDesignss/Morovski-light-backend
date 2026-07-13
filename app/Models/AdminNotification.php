<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';

    protected $fillable = [
        'admin_id',
        'type',
        'title',
        'message',
        'reference_type',
        'reference_id',
        'priority',
        'is_read',
        'extra_data',
        'read_at'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    /**
     * Relation with Admin
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => Carbon::now()
        ]);
        return $this;
    }

    /**
     * Mark as unread
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
        return $this;
    }

    /**
     * Get unread count
     */
    public static function getUnreadCount($adminId)
    {
        return self::where('admin_id', $adminId)
                   ->where('is_read', false)
                   ->count();
    }

    /**
     * Get unread notifications
     */
    public static function getUnread($adminId, $limit = 5)
    {
        return self::where('admin_id', $adminId)
                   ->where('is_read', false)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }
}
