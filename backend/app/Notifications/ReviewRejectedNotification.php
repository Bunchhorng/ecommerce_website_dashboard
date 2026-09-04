<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Review $review)
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
        $productName = $this->review->product?->name ?? 'product';

        return [
            'title' => 'Review not approved',
            'message' => 'Your review of “'.$productName.'” was not approved. Please review our guidelines and try again.',
            'product_id' => $this->review->product_id,
            'product_slug' => $this->review->product?->slug,
            'url' => $this->review->product ? '/product/'.$this->review->product->slug : '/account/reviews',
        ];
    }
}