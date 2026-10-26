<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiOcrResult extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    protected $table = 'ai_ocr_results';

    protected $fillable = [
        'user_id', 'disk', 'path', 'original_name', 'status',
        'result', 'raw_response', 'error_message', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'result' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_SUCCESS, self::STATUS_FAILED], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => '排队中',
            self::STATUS_PROCESSING => '识别中',
            self::STATUS_SUCCESS => '已完成',
            self::STATUS_FAILED => '失败',
            default => $this->status,
        };
    }
}
