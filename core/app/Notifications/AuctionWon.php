<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionWon extends Notification
{
    use Queueable;

    public $listing;
    public $winningBid;
    public $escrowNumber;
    public $escrowId;

    /**
     * Create a new notification instance.
     */
    public function __construct($listing, $winningBid, $escrowNumber, $escrowId = null)
    {
        $this->listing = $listing;
        $this->winningBid = $winningBid;
        $this->escrowNumber = $escrowNumber;
        $this->escrowId = $escrowId ?? $listing->escrow_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Congratulations! You Won the Auction')
            ->greeting('Congratulations ' . $notifiable->username . '!')
            ->line('You have won the auction for: ' . $this->listing->title)
            ->line('Winning Bid: ' . $this->winningBid)
            ->line('Escrow Number: ' . $this->escrowNumber)
            ->action('View Escrow', route('user.escrow.details', $this->escrowId))
            ->line('An escrow has been automatically opened for this transaction. Please proceed to complete the payment.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Auction Won!',
            'message' => 'Congratulations! You won the auction for "' . $this->listing->title . '" with a bid of ' . $this->winningBid . '. An escrow has been automatically opened.',
            'click_url' => route('user.escrow.details', $this->escrowId),
            'type' => 'auction_won',
            'listing_id' => $this->listing->id,
            'escrow_id' => $this->escrowId,
            'escrow_number' => $this->escrowNumber,
        ];
    }
}

