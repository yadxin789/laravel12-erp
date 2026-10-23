<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    public const PLATFORMS = [
        'taobao' => '淘宝',
        'tmall' => '天猫',
        'jd' => '京东',
        'pdd' => '拼多多',
        'douyin' => '抖音',
        'amazon' => '亚马逊',
    ];

    public const STATUS = [
        'pending' => '待处理',
        'synced' => '已同步',
        'shipped' => '已发货',
        'completed' => '已完成',
        'cancelled' => '已取消',
    ];

    protected $fillable = [
        'platform', 'platform_order_no', 'buyer_name', 'buyer_phone',
        'shipping_address', 'total_amount', 'status', 'items', 'synced_at',
    ];

    protected $casts = [
        'items' => 'array',
        'synced_at' => 'datetime',
        'total_amount' => 'decimal:4',
    ];

    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * 订单明细行数
     */
    public function getItemsCountAttribute(): int
    {
        return is_array($this->items) ? count($this->items) : 0;
    }
}
