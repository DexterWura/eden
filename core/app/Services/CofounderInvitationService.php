<?php

namespace App\Services;

use App\Models\CofounderInvitation;
use App\Models\Startup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class CofounderInvitationService
{
    public function invite(Startup $startup, User $inviter, string $email): CofounderInvitation
    {
        $email = mb_strtolower(trim($email));
        $pendingKey = hash('sha256', $startup->id . '|' . $email);
        $existing = CofounderInvitation::query()->where('pending_key', $pendingKey)->first();
        if ($existing?->isUsable()) {
            if ($existing->email_sent_at !== null) {
                return $existing;
            }

            $token = $existing->delivery_token;
            if (! is_string($token) || $token === '') {
                $token = Str::random(64);
                $existing->update([
                    'token_hash' => hash('sha256', $token),
                    'delivery_token' => $token,
                ]);
            }
            $this->deliverInvitation($existing, $startup, $inviter, $email, $token);

            return $existing;
        }

        $token = Str::random(64);
        $attributes = [
            'startup_id' => $startup->id,
            'invited_by_user_id' => $inviter->id,
            'accepted_by_user_id' => null,
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'delivery_token' => $token,
            'email_sent_at' => null,
            'expires_at' => now()->addDays(7),
        ];
        try {
            if ($existing) {
                $existing->update($attributes);
                $invitation = $existing;
            } else {
                $invitation = CofounderInvitation::query()->create(array_merge(
                    ['pending_key' => $pendingKey],
                    $attributes
                ));
            }
        } catch (QueryException $exception) {
            $invitation = CofounderInvitation::query()->where('pending_key', $pendingKey)->first();
            if (! $invitation) {
                throw $exception;
            }
            if ($invitation->isUsable()) {
                if ($invitation->email_sent_at === null && is_string($invitation->delivery_token)) {
                    $this->deliverInvitation(
                        $invitation,
                        $startup,
                        $inviter,
                        $email,
                        $invitation->delivery_token
                    );
                }
                return $invitation;
            }
            $invitation->update($attributes);
        }

        $this->deliverInvitation($invitation, $startup, $inviter, $email, $token);

        return $invitation;
    }

    private function deliverInvitation(
        CofounderInvitation $invitation,
        Startup $startup,
        User $inviter,
        string $email,
        string $token
    ): void {
        $recipient = User::query()->whereRaw('lower(email) = ?', [$email])->first();
        if (! $recipient || $recipient->wantsNotification('COFOUNDER_UPDATES')) {
            $url = route('cofounder-invitations.show', ['token' => $token]);
            Mail::html(view('emails.cofounder-invitation', compact('startup', 'inviter', 'url'))->render(), function ($message) use ($email, $startup): void {
                $message->to($email)->subject('Join ' . $startup->name . ' as a co-founder');
            });
        }
        $invitation->forceFill(['email_sent_at' => now()])->save();
    }

    public function findUsable(string $token): CofounderInvitation
    {
        $invitation = CofounderInvitation::query()
            ->with('startup:id,name,slug')
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();

        abort_unless($invitation->isUsable(), 410, 'This invitation has expired or has already been used.');

        return $invitation;
    }

    public function accept(string $token, User $user): Startup
    {
        return DB::transaction(function () use ($token, $user): Startup {
            $invitation = CofounderInvitation::query()
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($invitation->isUsable(), 410, 'This invitation has expired or has already been used.');
            abort_unless(mb_strtolower(trim($user->email)) === mb_strtolower($invitation->email), 403, 'Sign in with the invited email address.');

            $startup = Startup::query()->lockForUpdate()->findOrFail($invitation->startup_id);
            $founders = $startup->founders ?? [];
            $matched = false;
            foreach ($founders as &$founder) {
                if (is_array($founder) && mb_strtolower(trim((string) ($founder['email'] ?? ''))) === mb_strtolower($invitation->email)) {
                    $founder['user_id'] = $user->id;
                    $founder['name'] = trim((string) ($founder['name'] ?? '')) ?: $user->name;
                    $matched = true;
                }
            }
            unset($founder);
            if (! $matched) {
                $founders[] = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'photo_url' => null,
                    'twitter_url' => null,
                    'linkedin_url' => null,
                ];
            }
            $startup->update(['founders' => Startup::attachFounderUserIds($founders, $startup)]);
            $invitation->update([
                'accepted_by_user_id' => $user->id,
                'accepted_at' => now(),
                'pending_key' => null,
                'delivery_token' => null,
            ]);

            return $startup->fresh();
        });
    }
}
