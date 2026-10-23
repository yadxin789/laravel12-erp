<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiConversation extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'title', 'is_pinned', 'model_id', 'message_count', 'last_message_at'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id')->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AiAttachment::class, 'conversation_id');
    }

    /**
     * 追加一条消息并维护计数与时间
     */
    public function addMessage(string $role, ?string $content, ?array $meta = null): AiMessage
    {
        $message = $this->messages()->create([
            'role' => $role,
            'content' => $content,
            'meta' => $meta,
        ]);

        $this->increment('message_count');
        $this->forceFill(['last_message_at' => now()])->save();

        return $message;
    }

    /**
     * 首条用户消息自动生成标题
     */
    public function autoTitle(?string $content): void
    {
        if ($this->title !== '新会话' || empty($content)) {
            return;
        }
        $title = trim(preg_replace('/\s+/', ' ', strip_tags((string) $content)));
        $this->title = mb_substr($title, 0, 40) ?: '新会话';
        $this->save();
    }
}
