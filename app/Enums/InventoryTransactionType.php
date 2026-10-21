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
}
