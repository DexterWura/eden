<?php

use App\Models\Startup;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Startup::query()
            ->whereNotNull('founders')
            ->lazyById()
            ->each(function (Startup $startup) {
                $founders = $startup->founders ?? [];
                if ($founders === []) {
                    return;
                }

                $changed = false;

                foreach ($founders as &$founder) {
                    if (! is_array($founder) || ! empty($founder['user_id'])) {
                        continue;
                    }

                    $email = isset($founder['email']) ? trim((string) $founder['email']) : '';
                    if ($email !== '') {
                        $matchedUser = User::query()->where('email', $email)->first();
                        if ($matchedUser) {
                            $founder['user_id'] = (int) $matchedUser->id;
                            $changed = true;
                        }
                    }
                }
                unset($founder);

                $ownerId = (int) ($startup->user_id ?? 0);
                if ($ownerId > 0) {
                    $ownerLinked = collect($founders)->contains(
                        fn ($founder) => is_array($founder) && (int) ($founder['user_id'] ?? 0) === $ownerId
                    );

                    if (! $ownerLinked && isset($founders[0]) && is_array($founders[0])) {
                        $founders[0]['user_id'] = $ownerId;
                        $changed = true;
                    }
                }

                if ($changed) {
                    $startup->update(['founders' => $founders]);
                }
            });
    }

    public function down(): void
    {
        // Non-destructive backfill; no rollback needed.
    }
};
