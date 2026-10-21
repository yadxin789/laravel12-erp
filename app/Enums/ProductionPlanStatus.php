<?php

namespace App\Enums;

enum ProductionPlanStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Converted = 'converted';
    case Closed = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Draft => '草稿',
            self::Approved => '已审批',
            self::Converted => '已转工单',
            self::Closed => '已关闭',
        };
    }
}
