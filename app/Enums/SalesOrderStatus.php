<?php

namespace App\Enums;

enum SalesOrderStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case PartiallyDelivered = 'partially_delivered';
    case FullyDelivered = 'fully_delivered';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
    case ChangePending = 'change_pending';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Submitted => '待审核',
            self::Approved => '已审核',
            self::PartiallyDelivered => '部分发货',
            self::FullyDelivered => '已发货',
            self::Closed => '已关闭',
            self::Cancelled => '已取消',
            self::ChangePending => '变更待审',
        };
    }

    /**
     * 供表单下拉使用
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
