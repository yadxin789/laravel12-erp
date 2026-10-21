<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $itemId;
    public $warehouseId;
    public $changeType;
    public $quantity;
    public $transactionNo;

    public function __construct(int $itemId, int $warehouseId, string $changeType, float $quantity, string $transactionNo)
    {
        $this->itemId = $itemId;
        $this->warehouseId = $warehouseId;
        $this->changeType = $changeType;
        $this->quantity = $quantity;
        $this->transactionNo = $transactionNo;
    }
}
