<?php

namespace App\Support;

use App\Models\System\AuditLog;

class AuditLogger
{
    public static function log(string $action, string $module, array $data = [])
    {
        AuditLog::create([
            'action' => $action,
            'module' => $module,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $data['record_id'] ?? null,
            'record_type' => $data['record_type'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'remark' => $data['remark'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function create(string $module, $record)
    {
        self::log('create', $module, [
            'record_id' => $record->id,
            'record_type' => get_class($record),
            'new_values' => $record->toArray(),
        ]);
    }

    public static function update(string $module, $record, array $oldValues)
    {
        self::log('update', $module, [
            'record_id' => $record->id,
            'record_type' => get_class($record),
            'old_values' => $oldValues,
            'new_values' => $record->toArray(),
        ]);
    }

    public static function delete(string $module, $record)
    {
        self::log('delete', $module, [
            'record_id' => $record->id,
            'record_type' => get_class($record),
            'old_values' => $record->toArray(),
        ]);
    }

    public static function approve(string $module, $record)
    {
        self::log('approve', $module, [
            'record_id' => $record->id,
            'record_type' => get_class($record),
            'remark' => '审核通过',
        ]);
    }

    public static function reject(string $module, $record, string $reason = '')
    {
        self::log('reject', $module, [
            'record_id' => $record->id,
            'record_type' => get_class($record),
            'remark' => $reason,
        ]);
    }
}
