<?php

namespace App\Enums;

enum ProductionOrderStatus: string
{
    case Draft = 'draft';
    case Released = 'released';
    case MaterialShortage = 'material_shortage';
    case Ready = 'ready';
    case InProgress = 'in_progress';
    case Paused = 'paused';
    case Completed = 'completed';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
    case Rework = 'rework';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Released => '已下达',
            self::MaterialShortage => '物料短缺',
            self::Ready => '待开工',
            self::InProgress => '生产中',
            self::Paused => '已暂停',
            self::Completed => '已完工',
            self::Closed => '已关闭',
            self::Cancelled => '已取消',
            self::Rework => '返工中',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
