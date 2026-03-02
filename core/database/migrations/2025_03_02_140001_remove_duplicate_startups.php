<?php

use App\Models\Startup;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $startups = Startup::whereNotNull('website')
            ->where('website', '!=', '')
            ->orderBy('created_at')
            ->get();

        $seen = [];
        $deleted = [];

        foreach ($startups as $startup) {
            $normalized = Startup::normalizeUrl($startup->website);
            if ($normalized === null || $normalized === '') {
                continue;
            }

            if (isset($seen[$normalized])) {
                $kept = $seen[$normalized];
                $deleted[] = [
                    'deleted_name' => $startup->name,
                    'deleted_website' => $startup->website,
                    'kept_name' => $kept->name,
                    'user_id' => $startup->user_id,
                    'founder_emails' => $this->getFounderEmails($startup),
                ];

                Log::info("Removing duplicate startup: '{$startup->name}' (ID {$startup->id}), duplicate of '{$kept->name}' (ID {$kept->id}), website: {$startup->website}");

                DB::table('startup_upvotes')->where('startup_id', $startup->id)->delete();
                $startup->delete();
            } else {
                $seen[$normalized] = $startup;
            }
        }

        $this->notifyAffectedUsers($deleted);
    }

    private function getFounderEmails(Startup $startup): array
    {
        $emails = [];
        foreach ($startup->founders ?? [] as $f) {
            $email = is_array($f) ? ($f['email'] ?? null) : ($f->email ?? null);
            if ($email && trim($email) !== '') {
                $emails[] = strtolower(trim($email));
            }
        }
        if ($startup->founder_email && trim($startup->founder_email) !== '') {
            $emails[] = strtolower(trim($startup->founder_email));
        }
        return array_unique($emails);
    }

    private function notifyAffectedUsers(array $deletedList): void
    {
        $userNotifications = [];

        foreach ($deletedList as $info) {
            $userIds = [];

            if ($info['user_id']) {
                $userIds[] = $info['user_id'];
            }

            foreach ($info['founder_emails'] as $email) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $userIds[] = $user->id;
                }
            }

            foreach (array_unique($userIds) as $uid) {
                $userNotifications[$uid][] = $info;
            }
        }

        foreach ($userNotifications as $userId => $startupInfos) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            $names = array_map(fn ($i) => $i['deleted_name'], $startupInfos);

            DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\DuplicateStartupRemoved',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'title' => 'Duplicate startup removed',
                    'message' => count($names) === 1
                        ? "Your startup \"{$names[0]}\" was removed because another startup with the same website link already exists."
                        : 'The following startups were removed because other startups with the same website links already exist: ' . implode(', ', $names) . '.',
                    'deleted_startups' => $names,
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('notifications')
            ->where('type', 'App\\Notifications\\DuplicateStartupRemoved')
            ->delete();
    }
};
