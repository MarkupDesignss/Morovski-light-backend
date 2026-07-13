<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the admin
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $perPage = $request->get('per_page', 20);
        $status = $request->get('status');
        $priority = $request->get('priority');
        $type = $request->get('type');
        $dateFilter = $request->get('date_filter');

        $query = AdminNotification::where('admin_id', $admin->id);

        // Apply filters
        if ($status === 'unread') {
            $query->where('is_read', false);
        } elseif ($status === 'read') {
            $query->where('is_read', true);
        }

        if ($priority && in_array($priority, ['high', 'medium', 'low'])) {
            $query->where('priority', $priority);
        }

        if ($type && in_array($type, ['new_order', 'new_user', 'report', 'ticket'])) {
            $query->where('type', $type);
        }

        if ($dateFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('created_at', Carbon::yesterday());
        } elseif ($dateFilter === 'earlier') {
            $query->whereDate('created_at', '<', Carbon::yesterday());
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Get unread notifications (AJAX)
     */
    public function getUnread(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $limit = $request->get('limit', 5);

        $notifications = AdminNotification::where('admin_id', $admin->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $unreadCount = AdminNotification::where('admin_id', $admin->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread count (AJAX)
     */
    public function getUnreadCount(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $count = AdminNotification::where('admin_id', $admin->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Mark as read (single notification)
     */
    public function markAsRead($id)
    {
        $admin = Auth::guard('admin')->user();

        $notification = AdminNotification::where('id', $id)
            ->where('admin_id', $admin->id)
            ->first();

        if ($notification) {
            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'notification' => $notification
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Mark as unread (single notification)
     */
    public function markAsUnread($id)
    {
        $admin = Auth::guard('admin')->user();

        $notification = AdminNotification::where('id', $id)
            ->where('admin_id', $admin->id)
            ->first();

        if ($notification) {
            $notification->markAsUnread();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as unread',
                'notification' => $notification
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        $admin = Auth::guard('admin')->user();

        AdminNotification::where('admin_id', $admin->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'unread_count' => 0
        ]);
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        $admin = Auth::guard('admin')->user();

        $notification = AdminNotification::where('id', $id)
            ->where('admin_id', $admin->id)
            ->first();

        if ($notification) {
            $notification->delete();
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll()
    {
        $admin = Auth::guard('admin')->user();

        AdminNotification::where('admin_id', $admin->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted successfully'
        ]);
    }

    /**
     * Show single notification detail (with redirect if reference exists)
     */
    public function show($id)
    {
        $admin = Auth::guard('admin')->user();

        $notification = AdminNotification::where('id', $id)
            ->where('admin_id', $admin->id)
            ->first();

        if (!$notification) {
            return redirect()->route('admin.notifications.index')
                ->with('error', 'Notification not found');
        }

        // Mark as read if not already
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        // Redirect based on reference type if exists
        if ($notification->reference_type && $notification->reference_id) {
            return $this->redirectToReference($notification);
        }

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Redirect to the related page based on reference
     */
    protected function redirectToReference(AdminNotification $notification)
    {
        $routes = [
            'order' => 'admin.orders.show',
            'user' => 'admin.users.show',
            'report' => 'admin.reports.show',
            'ticket' => 'admin.support-tickets.show',
            'contact' => 'admin.contact_requests.show',
        ];

        $referenceType = $notification->reference_type;
        $referenceId = $notification->reference_id;

        if (isset($routes[$referenceType])) {
            return redirect()->route($routes[$referenceType], $referenceId);
        }

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Download notification as PDF
     */
}
