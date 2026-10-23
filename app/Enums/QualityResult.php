<?php

namespace App\Enums;

enum QualityResult: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Conditional = 'conditional';
    case Waived = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::Pass => '合格',
            self::Fail => '不合格',
            self::Conditional => '有条件放行',
            self::Waived => '豁免接收',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
