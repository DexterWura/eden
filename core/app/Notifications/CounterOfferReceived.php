<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CounterOfferReceived extends Notification
{
    use Queueable;

    public $offer;
    public $listing;

    /**
     * Create a new notification instance.
     */
    public function __construct($offer, $listing)
    {
        $this->offer = $offer;
        $this->listing = $listing;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Counter Offer Received',
            'message' => 'Seller countered your offer with ' . showAmount($this->offer->counter_amount) . ' for listing: ' . $this->listing->title,
            'click_url' => route('user.offer.index'),
            'type' => 'counter_offer',
        ];
    }
}

