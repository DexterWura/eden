<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CounterOfferAccepted extends Notification
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
        $finalAmount = $this->offer->counter_amount > 0 ? $this->offer->counter_amount : $this->offer->amount;
        
        return [
            'title' => 'Counter Offer Accepted',
            'message' => $this->offer->buyer->username . ' accepted your counter offer of ' . showAmount($finalAmount) . ' for listing: ' . $this->listing->title . '. Escrow has been created.',
            'click_url' => $this->offer->escrow_id ? route('user.escrow.details', $this->offer->escrow_id) : route('user.offer.received'),
            'type' => 'counter_offer_accepted',
        ];
    }
}

