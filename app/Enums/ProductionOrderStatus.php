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
}
