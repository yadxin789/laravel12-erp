<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case PartiallyReceived = 'partially_received';
    case FullyReceived = 'fully_received';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Submitted => '待审核',
            self::Approved => '已审核',
            self::PartiallyReceived => '部分收货',
            self::FullyReceived => '已收货',
            self::Closed => '已关闭',
            self::Cancelled => '已取消',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
