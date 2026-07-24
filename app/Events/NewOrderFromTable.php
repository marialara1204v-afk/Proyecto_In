<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderFromTable implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('waiter-notifications'),
            new Channel("table.{$this->order->table_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-order-from-table';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'     => $this->order->id,
            'table_id'     => $this->order->table_id,
            'table_number' => $this->order->table->number,
            'total'        => (float) $this->order->total,
            'items_count'  => $this->order->items->count(),
        ];
    }
}
