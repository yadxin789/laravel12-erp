<?php

namespace App\Enums;

/**
 * 检验单状态
 *
 * 与 InspectionType / QualityResult 一样挂在 QualityInspection 上做 enum cast，
 * 之前该字段是裸字符串（QualityService 里写 'pending' / 'completed'），
 * 与模块内其他枚举不一致且写错值不会被发现。
 */
enum QualityInspectionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '待检验',
            self::Completed => '已完成',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
