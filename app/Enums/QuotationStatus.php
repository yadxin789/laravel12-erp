<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Won = 'won';
    case Lost = 'lost';
    case Expired = 'expired';
    case Converted = 'converted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Submitted => '待审核',
            self::Won => '已成交',
            self::Lost => '未成交',
            self::Expired => '已过期',
            self::Converted => '已转订单',
            self::Cancelled => '已取消',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
