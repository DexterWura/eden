<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionEndedSold extends Notification
{
    use Queueable;

    public $listing;
    public $finalPrice;
    public $winner;
    public $escrowNumber;
    public $escrowId;

    /**
     * Create a new notification instance.
     */
    public function __construct($listing, $finalPrice, $winner, $escrowNumber = null, $escrowId = null)
    {
        $this->listing = $listing;
        $this->finalPrice = $finalPrice;
        $this->winner = $winner;
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
        $message = (new MailMessage)
            ->subject('Your Auction Has Ended - Item Sold!')
            ->greeting('Hello ' . $notifiable->username . '!')
            ->line('Your auction for "' . $this->listing->title . '" has ended successfully.')
            ->line('Final Price: ' . $this->finalPrice)
            ->line('Winner: ' . $this->winner);

        if ($this->escrowNumber && $this->escrowId) {
            $message->line('Escrow Number: ' . $this->escrowNumber)
                ->action('View Escrow', route('user.escrow.details', $this->escrowId))
                ->line('An escrow has been automatically opened. Please wait for the buyer to complete payment.');
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'title' => 'Auction Ended - Item Sold!',
            'message' => 'Your auction for "' . $this->listing->title . '" has ended. Final price: ' . $this->finalPrice . '. Winner: ' . $this->winner . '.',
            'type' => 'auction_ended_sold',
            'listing_id' => $this->listing->id,
        ];

        if ($this->escrowId) {
            $data['click_url'] = route('user.escrow.details', $this->escrowId);
            $data['escrow_id'] = $this->escrowId;
            $data['escrow_number'] = $this->escrowNumber;
            $data['message'] .= ' An escrow has been automatically opened.';
        }

        return $data;
    }
}

