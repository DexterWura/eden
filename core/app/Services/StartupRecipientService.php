<?php

namespace App\Services;

use App\Models\Startup;
use App\Models\User;
use Illuminate\Support\Collection;

class StartupRecipientService
{
    public function founderRecipients(Startup $startup): Collection
    {
        $recipients = collect();
        if ($startup->user_id) {
            $owner = User::query()->find($startup->user_id);
            if ($owner && filter_var($owner->email, FILTER_VALIDATE_EMAIL)) {
                $recipients->put(strtolower($owner->email), [
                    'email' => strtolower($owner->email),
                    'name' => $owner->name ?: 'Founder',
                    'user_id' => $owner->id,
                ]);
            }
        }

        $founders = $startup->founders ?? [];
        if ($startup->founder_email) {
            $founders[] = [
                'name' => $startup->founder_name,
                'email' => $startup->founder_email,
            ];
        }
        foreach ($founders as $founder) {
            $email = strtolower(trim((string) (is_array($founder) ? ($founder['email'] ?? '') : ($founder->email ?? ''))));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $recipients->has($email)) {
                continue;
            }
            $name = trim((string) (is_array($founder) ? ($founder['name'] ?? '') : ($founder->name ?? '')));
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            $recipients->put($email, [
                'email' => $email,
                'name' => $name !== '' ? $name : ($user?->name ?: 'Founder'),
                'user_id' => $user?->id,
            ]);
        }

        return $recipients->values();
    }
}
