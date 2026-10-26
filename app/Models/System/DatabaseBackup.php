<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class DatabaseBackup extends Model
{
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'connection', 'database', 'disk', 'filename', 'path',
        'size_bytes', 'status', 'compressed', 'duration_ms',
        'error_message', 'created_by', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'compressed' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function sizeForHumans(): string
    {
        $bytes = (float) $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
