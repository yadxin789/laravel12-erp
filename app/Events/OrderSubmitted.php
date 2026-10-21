<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $orderType;

    public function __construct($order, string $orderType = 'sales')
    {
        $this->order = $order;
        $this->orderType = $orderType;
    }
}
