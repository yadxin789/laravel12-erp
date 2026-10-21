<?php

namespace App\Services;

use App\Models\System\Notification;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     */
    public function send(int $userId, string $title, string $message, string $type = 'info', ?string $link = null, ?array $data = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'data'    => $data,
        ]);
    }

    /**
     * Send a notification to all users with a specific role.
     */
    public function sendToRole(string $roleName, string $title, string $message, string $type = 'info', ?string $link = null, ?array $data = null): void
    {
        $users = \App\Models\User::role($roleName)->get();
        foreach ($users as $user) {
            $this->send($user->id, $title, $message, $type, $link, $data);
        }
    }

    /**
     * Send a notification to all users with a specific permission.
     */
    public function sendToPermission(string $permission, string $title, string $message, string $type = 'info', ?string $link = null, ?array $data = null): void
    {
        $users = \App\Models\User::permission($permission)->get();
        foreach ($users as $user) {
            $this->send($user->id, $title, $message, $type, $link, $data);
        }
    }

    /**
     * Get unread notification count for a user.
     */
    public function unreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)->whereNull('read_at')->count();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get paginated notifications for a user.
     */
    public function getForUser(int $userId, int $perPage = 20)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
