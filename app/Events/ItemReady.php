<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderId;
    public $tableNumber;
    public $itemName;

    public function __construct($orderId, $tableNumber, $itemName)
    {
        $this->orderId = $orderId;
        $this->tableNumber = $tableNumber;
        $this->itemName = $itemName;
    }

    public function broadcastOn()
    {
        return new Channel('waiter-notifications');
    }

    public function broadcastAs()
    {
        return 'item-ready';
    }
}
