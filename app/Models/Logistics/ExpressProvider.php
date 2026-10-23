<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExpressProvider extends Model
{
    protected $fillable = [
        'type', 'key', 'name', 'code', 'api_base', 'app_key', 'app_secret',
        'monthly_quota', 'enabled', 'is_default', 'sort_order', 'note',
    ];

    protected $hidden = ['app_key', 'app_secret'];

    protected $casts = [
        'enabled' => 'boolean',
        'is_default' => 'boolean',
        'monthly_quota' => 'integer',
    ];

    public function scopeChannel(Builder $q): Builder
    {
        return $q->where('type', 'channel');
    }

    public function scopeCarrier(Builder $q): Builder
    {
        return $q->where('type', 'carrier');
    }

    /**
     * 查询通道目录（聚合服务商）
     * 照搬参考项目 AiSetting::providers() 的目录写法
     */
    public static function channels(): array
    {
        return [
            'kuaidi100' => [
                'name' => '快递100（推荐）',
                'api_base' => 'https://poll.kuaidi100.com',
                'price' => '免费版 1000 次/月 · 企业版按量计费',
                'free_quota' => '注册即送 1000 次免费查询',
                'best_for' => '覆盖国内 1000+ 快递公司，接入最简单',
                'docs' => 'https://api.kuaidi100.com/manager/page/myinfo/enterprise',
            ],
            'kuaidibird' => [
                'name' => '快递鸟',
                'api_base' => 'https://api.kdniao.com',
                'price' => '免费版 3000 单/月 · 电商版 ¥0.05/单',
                'free_quota' => '注册送 3000 单免费额度',
                'best_for' => '电商件量大、需要电子面单一体',
                'docs' => 'https://www.kdniao.com/UserCenter/v2/UserHome.aspx',
            ],
            'cainiao' => [
                'name' => '菜鸟物流开放平台',
                'api_base' => 'https://link.m.taobao.com',
                'price' => '按调用量计费，淘宝系免鉴权部分场景',
                'free_quota' => '淘系商家可申请免费额度',
                'best_for' => '淘宝/天猫店铺订单，电子面单打通',
                'docs' => 'https://open.cainiao.com/',
            ],
        ];
    }

    /**
     * 国内主流快递公司目录
     */
    public static function carriers(): array
    {
        return [
            'shunfeng' => ['name' => '顺丰速运', 'code' => 'shunfeng'],
            'zhongtong' => ['name' => '中通快递', 'code' => 'zhongtong'],
            'yuantong' => ['name' => '圆通速递', 'code' => 'yuantong'],
            'yunda' => ['name' => '韵达速递', 'code' => 'yunda'],
            'shentong' => ['name' => '申通快递', 'code' => 'shentong'],
            'jd' => ['name' => '京东物流', 'code' => 'jd'],
            'ems' => ['name' => 'EMS 邮政', 'code' => 'ems'],
            'jtexpress' => ['name' => '极兔速递', 'code' => 'jtexpress'],
            'deppon' => ['name' => '德邦快递', 'code' => 'debangkuaidi'],
            'zhaijisong' => ['name' => '宅急送', 'code' => 'zhaijisong'],
        ];
    }

    public function hasRealKey(): bool
    {
        return !empty($this->app_key) && !str_starts_with((string) $this->app_key, 'demo-');
    }

    public function maskedKey(): ?string
    {
        if (!$this->hasRealKey()) {
            return null;
        }
        $key = (string) $this->app_key;
        $len = mb_strlen($key);
        $head = mb_substr($key, 0, 4);
        $tail = $len > 8 ? mb_substr($key, -4) : '';

        return $head . str_repeat('*', max(6, $len - mb_strlen($head) - mb_strlen($tail))) . $tail;
    }

    public function markAsDefault(): void
    {
        static::query()->where('type', $this->type)->update(['is_default' => false]);
        $this->is_default = true;
        $this->save();
    }

    /**
     * 默认查询通道
     */
    public static function defaultChannel(): ?self
    {
        return static::channel()->where('enabled', true)->where('is_default', true)->first()
            ?: static::channel()->where('enabled', true)->first();
    }

    /**
     * 按目录补齐缺失行（幂等）
     */
    public static function syncFromCatalog(): void
    {
        $rows = static::query()->get()->keyBy('key');

        foreach (static::channels() as $key => $c) {
            if (!$rows->has($key)) {
                static::create([
                    'type' => 'channel',
                    'key' => $key,
                    'name' => $c['name'],
                    'api_base' => $c['api_base'],
                    'monthly_quota' => 1000,
                    'enabled' => false,
                ]);
            }
        }

        foreach (static::carriers() as $key => $c) {
            if (!$rows->has($key)) {
                static::create([
                    'type' => 'carrier',
                    'key' => $key,
                    'name' => $c['name'],
                    'code' => $c['code'],
                    'enabled' => true,
                ]);
            }
        }
    }
}
