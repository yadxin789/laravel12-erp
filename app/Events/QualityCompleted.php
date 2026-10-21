<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QualityCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inspection;

    public function __construct($inspection)
    {
        $this->inspection = $inspection;
    }
}
