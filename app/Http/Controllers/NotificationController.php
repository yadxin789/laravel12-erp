<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * List all notifications for the current user.
     */
    public function index()
    {
        $notifications = $this->service->getForUser(auth()->id());
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read and redirect to its link.
     */
    public function read($id)
    {
        $notification = \App\Models\System\Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();

        if ($notification->link) {
            return redirect($notification->link);
        }

        return redirect()->route('notifications.index')->with('success', '已标记为已读');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        $this->service->markAllAsRead(auth()->id());
        return redirect()->route('notifications.index')->with('success', '全部标记为已读');
    }

    /**
     * Get unread count (for AJAX polling).
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => $this->service->unreadCount(auth()->id()),
        ]);
    }
}
