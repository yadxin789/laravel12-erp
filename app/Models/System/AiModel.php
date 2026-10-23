<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModel extends Model
{
    protected $fillable = [
        'provider_id', 'model_id', 'name', 'is_vision', 'enabled', 'is_default', 'sort_order',
    ];

    protected $casts = [
        'is_vision' => 'boolean',
        'enabled' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class);
    }

    /**
     * 用户可选（供应商已启用且模型开放）
     */
    public function scopeUsable(Builder $q): Builder
    {
        return $q->where('enabled', true)
            ->whereHas('provider', fn ($p) => $p->where('enabled', true));
    }

    public function scopeVision(Builder $q): Builder
    {
        return $q->where('is_vision', true);
    }

    public function markAsDefault(): void
    {
        static::query()->update(['is_default' => false]);
        $this->is_default = true;
        $this->save();
        $this->provider?->syncToLegacySetting();
    }

    /**
     * 默认可用模型
     */
    public static function usableDefault(): ?self
    {
        return static::usable()->where('is_default', true)->first()
            ?: static::usable()->orderBy('sort_order')->first();
    }

    public function label(): string
    {
        return $this->provider?->name
            ? $this->name . '（' . $this->provider->name . '）'
            : $this->name;
    }
}
