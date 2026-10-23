<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'receiver_id', 'is_private', 'content', 'file_path', 'file_name',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'receiver_id');
    }

    public function scopeGroup(Builder $q): Builder
    {
        return $q->where('is_private', false);
    }

    public function scopePrivate(Builder $q): Builder
    {
        return $q->where('is_private', true);
    }

    /**
     * 两个人之间的私聊记录（双向）
     */
    public function scopeBetween(Builder $q, int $u1, int $u2): Builder
    {
        return $q->private()->where(function ($q) use ($u1, $u2) {
            $q->where(fn ($s) => $s->where('user_id', $u1)->where('receiver_id', $u2))
                ->orWhere(fn ($s) => $s->where('user_id', $u2)->where('receiver_id', $u1));
        });
    }

    /**
     * 应用「清空我的记录」标记：只影响本人视图
     */
    public function scopeAfterClear(Builder $q, int $userId, string $conversationKey): Builder
    {
        $clearedAt = ChatClearState::query()
            ->where('user_id', $userId)
            ->where('conversation_key', $conversationKey)
            ->value('cleared_at');

        return $clearedAt ? $q->where('created_at', '>', $clearedAt) : $q;
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    public function isImage(): bool
    {
        return $this->hasFile()
            && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', (string) $this->file_name);
    }

    /**
     * 统一的前台消息结构（供 Blade / JSON 复用）
     */
    public function toFrontend(int $viewerId): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name ?? '未知用户',
            'avatar_url' => $this->user?->avatar_url,
            'is_my_message' => $this->user_id === $viewerId,
            'file_url' => $this->hasFile() ? route('app.chat.file', $this->id) : null,
            'file_name' => $this->file_name,
            'is_image' => $this->isImage(),
            'created_at' => $this->created_at?->format('H:i'),
            'full_time' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
