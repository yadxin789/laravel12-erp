<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiMessage extends Model
{
    protected $fillable = ['conversation_id', 'role', 'content', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AiAttachment::class, 'message_id');
    }
}
