<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBidReceived extends Notification
{
    use Queueable;

    public $listing;
    public $bidAmount;
    public $bidder;

    /**
     * Create a new notification instance.
     */
    public function __construct($listing, $bidAmount, $bidder)
    {
        $this->listing = $listing;
        $this->bidAmount = $bidAmount;
        $this->bidder = $bidder;
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
            'title' => 'New Bid Received',
            'message' => $this->bidder . ' placed a bid of ' . $this->bidAmount . ' on your listing: ' . $this->listing->title,
            'click_url' => route('marketplace.listing.show', $this->listing->slug),
            'type' => 'bid',
        ];
    }
}

