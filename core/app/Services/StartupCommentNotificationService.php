<?php

namespace App\Services;

use App\Models\StartupComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StartupCommentNotificationService
{
    public function __construct(
        private StartupRecipientService $recipientService
    ) {}

    public function send(StartupComment $comment): void
    {
        $comment->loadMissing(['startup', 'user']);
        $recipients = $this->recipientService->founderRecipients($comment->startup);

        foreach ($recipients as $recipient) {
            if (! $recipient['user_id'] || (int) $recipient['user_id'] === (int) $comment->user_id) {
                continue;
            }

            $notificationId = $this->notificationId($comment->id, (int) $recipient['user_id']);
            DB::table('notifications')->updateOrInsert(['id' => $notificationId], [
                'type' => 'App\\Notifications\\StartupCommentReceived',
                'notifiable_type' => User::class,
                'notifiable_id' => $recipient['user_id'],
                'data' => json_encode([
                    'title' => 'New comment on ' . $comment->startup->name,
                    'message' => ($comment->user->name ?? 'A community member') . ' left a comment.',
                    'url' => route('founder.comments.index'),
                ]),
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            $user = User::query()->find($recipient['user_id']);
            if (! $user || ! $user->wantsNotification('STARTUP_COMMENT')) {
                continue;
            }

            try {
                $body = '<p>Hello ' . e($recipient['name']) . ',</p><p>'
                    . e($comment->user->name ?? 'A community member') . ' commented on '
                    . e($comment->startup->name) . '.</p><p><a href="'
                    . e(route('founder.comments.index')) . '">Open your comment inbox</a></p>';
                Mail::html($body, function ($message) use ($recipient, $comment): void {
                    $message->to($recipient['email'], $recipient['name'])
                        ->subject('New comment on ' . $comment->startup->name);
                });
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }

    private function notificationId(int $commentId, int $userId): string
    {
        $hash = md5("startup-comment:{$commentId}:{$userId}");

        return substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4)
            . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 12);
    }
}
