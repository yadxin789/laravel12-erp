<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $shipment;

    public function __construct($shipment)
    {
        $this->shipment = $shipment;
    }
}
