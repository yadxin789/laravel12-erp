<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogisticsShipment extends Model
{
    protected $fillable = [
        'tracking_no', 'carrier_key', 'carrier_name', 'status',
        'receiver_name', 'receiver_phone', 'receiver_address',
        'related_type', 'related_id', 'created_by',
        'notify_enabled', 'last_traced_at', 'signed_at',
    ];

    protected $casts = [
        'notify_enabled' => 'boolean',
        'last_traced_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    public const STATUS_CREATED = 'created';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_DELIVERING = 'delivering';
    public const STATUS_SIGNED = 'signed';
    public const STATUS_EXCEPTION = 'exception';
    public const STATUS_RETURNING = 'returning';
    public const STATUS_CLOSED = 'closed';

    public const STATUS_LABELS = [
        'created' => '已下单',
        'in_transit' => '运输中',
        'delivering' => '派送中',
        'signed' => '已签收',
        'exception' => '异常件',
        'returning' => '退回中',
        'closed' => '已完结',
    ];

    public const STATUS_BADGES = [
        'created' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
        'in_transit' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
        'delivering' => 'bg-info-subtle text-info-emphasis border-info-subtle',
        'signed' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'exception' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
        'returning' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        'closed' => 'bg-light text-secondary border',
    ];

    public function traces(): HasMany
    {
        return $this->hasMany(LogisticsTrace::class, 'shipment_id')->orderByDesc('traced_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'bg-light text-secondary border';
    }

    public function latestTrace(): ?LogisticsTrace
    {
        return $this->traces()->first();
    }
}
