<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends Model
{
    protected $fillable = [
        'key', 'name', 'base_url', 'api_key', 'enabled', 'is_default', 'note',
    ];

    protected $hidden = ['api_key'];

    protected $casts = [
        'enabled' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function models(): HasMany
    {
        return $this->hasMany(AiModel::class, 'provider_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 该供应商预设目录（来自 AiSetting::providers()）
     */
    public function catalog(): array
    {
        return AiSetting::providers()[$this->key] ?? [];
    }

    public function hasRealApiKey(): bool
    {
        return !empty($this->api_key) && !str_starts_with((string) $this->api_key, 'sk-xxxx');
    }

    /**
     * 脱敏后的 Key，用于页面展示
     */
    public function maskedApiKey(): ?string
    {
        if (!$this->hasRealApiKey()) {
            return null;
        }
        $key = (string) $this->api_key;
        $len = mb_strlen($key);
        $head = mb_substr($key, 0, 6);
        $tail = $len > 10 ? mb_substr($key, -4) : '';

        return $head . str_repeat('*', max(8, $len - mb_strlen($head) - mb_strlen($tail))) . $tail;
    }

    public function markAsDefault(): void
    {
        static::query()->update(['is_default' => false]);
        $this->is_default = true;
        $this->save();
    }

    /**
     * 把当前默认供应商/模型同步回单行的 ai_settings（兼容旧调用点）
     */
    public function syncToLegacySetting(): void
    {
        $setting = AiSetting::current();
        $data = [
            'provider' => $this->key,
            'base_url' => $this->base_url,
        ];
        if ($this->hasRealApiKey()) {
            $data['api_key'] = $this->api_key;
        }

        $defaultModel = $this->models()->where('is_default', true)->first()
            ?: $this->models()->where('enabled', true)->first();
        if ($defaultModel) {
            $data['model'] = $defaultModel->model_id;
        }
        if ($defaultModel && $defaultModel->is_vision) {
            $data['vision_model'] = $defaultModel->model_id;
        }
        $setting->update($data);
    }

    /**
     * 按目录补齐缺失的供应商行（幂等）
     */
    public static function syncFromCatalog(): void
    {
        $rows = static::query()->get()->keyBy('key');
        foreach (AiSetting::providers() as $key => $p) {
            if (!$rows->has($key)) {
                static::create([
                    'key' => $key,
                    'name' => $p['name'],
                    'base_url' => $p['base_url'] ?? null,
                    'enabled' => false,
                ]);
            }
        }
    }

    /**
     * 当前生效的供应商（默认优先，其次第一个已启用的）
     */
    public static function active(): ?self
    {
        return static::where('is_default', true)->where('enabled', true)->first()
            ?: static::where('enabled', true)->where(fn ($q) => $q->whereNotNull('api_key'))->first();
    }
}
