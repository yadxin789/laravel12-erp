<?php

namespace App\Enums;

enum InventoryQualityStatus: string
{
    case Pending = 'pending';
    case Qualified = 'qualified';
    case Rejected = 'rejected';
    case Frozen = 'frozen';
    case Conditional = 'conditional';
}
