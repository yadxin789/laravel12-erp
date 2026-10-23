<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case PurchaseReceive = 'purchase_receive';
    case PurchaseReturn = 'purchase_return';
    case ProductionIssue = 'production_issue';
    case ProductionReturn = 'production_return';
    case ProductionReceipt = 'production_receipt';
    case SalesIssue = 'sales_issue';
    case SalesReturn = 'sales_return';
    case Transfer = 'transfer';
    case Adjust = 'adjust';
    case Freeze = 'freeze';
    case Unfreeze = 'unfreeze';
    case Stocktake = 'stocktake';
    case Scrap = 'scrap';

    public function label(): string
    {
        return match ($this) {
            self::PurchaseReceive => '采购收货',
            self::PurchaseReturn => '采购退货',
            self::ProductionIssue => '生产领料',
            self::ProductionReturn => '生产退料',
            self::ProductionReceipt => '成品入库',
            self::SalesIssue => '销售发货',
            self::SalesReturn => '销售退货',
            self::Transfer => '库存调拨',
            self::Adjust => '库存调整',
            self::Freeze => '库存冻结',
            self::Unfreeze => '库存解冻',
            self::Stocktake => '库存盘点',
            self::Scrap => '报废出库',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
