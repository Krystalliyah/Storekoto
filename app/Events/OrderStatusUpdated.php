<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $status;
    public $orderNumber;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->status = $order->status;
        $this->orderNumber = $order->order_number;
    }

    public function broadcastOn(): array
    {
        $customerId = $this->getCustomerIdFromCentral();
        
        return [
            new PrivateChannel('customer.orders.' . $customerId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    public function broadcastWith(): array
    {
        $statusMap = [
            'pending' => 'pending',
            'confirmed' => 'pending',
            'preparing' => 'preparing',
            'ready' => 'ready_for_pickup',
            'completed' => 'picked_up',
            'cancelled' => 'cancelled',
        ];

        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $statusMap[$this->order->status] ?? $this->order->status,
            'updated_at' => $this->order->updated_at->toISOString(),
        ];
    }

    private function getCustomerIdFromCentral(): ?int
    {
        try {
            $customerOrder = \App\Models\CustomerOrder::on('central')
                ->where('tenant_id', tenant('id'))
                ->where('order_id', $this->order->id)
                ->first();
            
            return $customerOrder?->customer_id;
        } catch (\Exception $e) {
            return null;
        }
    }
}