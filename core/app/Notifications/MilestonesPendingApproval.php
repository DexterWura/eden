<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MilestonesPendingApproval extends Notification
{
    use Queueable;

    public $escrow;
    public $pendingCount;
    public $listingTitle;

    /**
     * Create a new notification instance.
     */
    public function __construct($escrow, $pendingCount, $listingTitle = null)
    {
        $this->escrow = $escrow;
        $this->pendingCount = $pendingCount;
        $this->listingTitle = $listingTitle ?? ($escrow->listing ? $escrow->listing->title : $escrow->title);
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
        $message = 'You have ' . $this->pendingCount . ' milestone(s) pending your approval.';
        if ($this->listingTitle) {
            $message .= ' Review milestones for: ' . $this->listingTitle;
        }

        return [
            'title' => 'Milestones Pending Approval',
            'message' => $message,
            'click_url' => route('user.escrow.milestone.index', $this->escrow->id),
            'type' => 'milestones_pending_approval',
            'escrow_id' => $this->escrow->id,
            'escrow_number' => $this->escrow->escrow_number,
            'pending_count' => $this->pendingCount,
        ];
    }
}

