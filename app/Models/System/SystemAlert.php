<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

/**
 * 系统告警
 *
 * 一条 fingerprint 对应一个「持续中的问题」，不因重复检测而重复发通知。
 */
class SystemAlert extends Model
{
    const STATUS_FIRING = 'firing';
    const STATUS_RESOLVED = 'resolved';

    const LEVEL_CRITICAL = 'critical';
    const LEVEL_WARNING = 'warning';
    const LEVEL_INFO = 'info';

    protected $fillable = [
        'fingerprint', 'source', 'level', 'title', 'message', 'context',
        'status', 'occurrences', 'notify_count',
        'first_triggered_at', 'last_triggered_at', 'last_notified_at', 'resolved_at',
    ];

    protected $casts = [
        'context' => 'array',
        'first_triggered_at' => 'datetime',
        'last_triggered_at' => 'datetime',
        'last_notified_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function isFiring(): bool
    {
        return $this->status === self::STATUS_FIRING;
    }

    public function levelLabel(): string
    {
        return match ($this->level) {
            self::LEVEL_CRITICAL => '严重',
            self::LEVEL_WARNING => '警告',
            default => '提示',
        };
    }
}
