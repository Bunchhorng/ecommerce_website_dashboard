<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewApprovedNotification extends Notification
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
            'title' => 'Review published',
            'message' => 'Your review of “'.$productName.'” has been approved and is now live.',
            'product_id' => $this->review->product_id,
            'product_slug' => $this->review->product?->slug,
            'url' => $this->review->product ? '/product/'.$this->review->product->slug : '/account/reviews',
        ];
    }
}