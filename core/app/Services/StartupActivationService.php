<?php

namespace App\Services;

use App\Models\Startup;

class StartupActivationService
{
    public function __construct(
        private StartupApprovalNotificationService $approvalNotificationService,
        private LaunchNotificationService $launchNotificationService
    ) {}

    public function activate(Startup $startup): array
    {
        $previousStatus = $startup->status;
        if (! $this->passesPublicationGate($startup, $previousStatus)) {
            return [
                'activated' => false,
                'was_pending' => true,
                'message' => 'Enrich or editorially review this profile before publishing it.',
            ];
        }

        $startup->update([
            'status' => Startup::STATUS_ACTIVE,
            'dormant_at' => null,
        ]);
        $this->sendTransitionNotifications($startup->fresh(), $previousStatus, true);

        return [
            'activated' => true,
            'was_pending' => $previousStatus === Startup::STATUS_PENDING,
            'message' => $previousStatus === Startup::STATUS_PENDING
                ? 'App approved and is now live.'
                : 'App activated.',
        ];
    }

    public function sendTransitionNotifications(
        Startup $startup,
        string $previousStatus,
        bool $notifyLaunchWhenAlreadyActive = false
    ): void {
        $becameActive = $previousStatus !== Startup::STATUS_ACTIVE
            && $startup->status === Startup::STATUS_ACTIVE;
        if ($becameActive && $previousStatus === Startup::STATUS_PENDING) {
            $this->approvalNotificationService->send($startup);
        }
        if ($becameActive || $notifyLaunchWhenAlreadyActive) {
            $this->launchNotificationService->sendLaunchEmails($startup);
        }
    }

    private function passesPublicationGate(Startup $startup, string $previousStatus): bool
    {
        if ($previousStatus !== Startup::STATUS_PENDING || (int) $startup->content_quality_version < 1) {
            return true;
        }

        return $startup->hasSubstantiveContent();
    }
}
