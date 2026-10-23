<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatClearState extends Model
{
    protected $fillable = ['user_id', 'chat_type', 'conversation_key', 'other_user_id', 'cleared_at'];

    protected $casts = [
        'cleared_at' => 'datetime',
    ];

    /**
     * 记录「我清空了某个会话」
     */
    public static function markCleared(int $userId, string $chatType, string $key, ?int $otherUserId = null): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'chat_type' => $chatType,
                'conversation_key' => $key,
            ],
            [
                'other_user_id' => $otherUserId,
                'cleared_at' => now(),
            ]
        );
    }

    public static function conversationKeyForPrivate(int $targetUserId): string
    {
        return 'user_' . $targetUserId;
    }

    public static function conversationKeyForGroup(): string
    {
        return 'group';
    }
}
