<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Order $order)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order confirmed',
            'message' => 'Your order #'.$this->order->order_number.' has been confirmed and is now being processed.',
            'order_number' => $this->order->order_number,
            'total' => round((float) $this->order->total, 2),
            'status' => $this->order->status,
            'url' => '/account/orders/'.$this->order->order_number,
        ];
    }
}