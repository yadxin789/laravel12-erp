<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AiAttachment extends Model
{
    protected $fillable = [
        'conversation_id', 'message_id', 'user_id', 'type',
        'original_name', 'path', 'mime_type', 'size', 'text_extracted', 'extracted_text',
    ];

    protected $casts = [
        'text_extracted' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(AiMessage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getIsImageAttribute(): bool
    {
        return $this->type === 'image'
            || str_starts_with((string) $this->mime_type, 'image/');
    }

    public function absolutePath(): ?string
    {
        return Storage::disk('ai_chat')->path($this->path);
    }

    public function sizeHuman(): string
    {
        $bytes = (int) $this->size;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }
}
