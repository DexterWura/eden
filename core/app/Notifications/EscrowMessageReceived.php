<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class EscrowMessageReceived extends Notification
{
    use Queueable;

    public $escrow;
    public $message;
    public $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct($escrow, $message, $sender)
    {
        $this->escrow = $escrow;
        $this->message = $message;
        $this->sender = $sender;
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
        $senderName = $this->sender->username ?? 'Admin';
        $messagePreview = Str::limit($this->message->message, 50);
        
        return [
            'title' => 'New Escrow Message',
            'message' => $senderName . ' sent a message: ' . $messagePreview,
            'click_url' => route('user.escrow.details', $this->escrow->id),
            'type' => 'escrow_message',
        ];
    }
}

