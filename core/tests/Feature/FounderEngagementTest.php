<?php

namespace Tests\Feature;

use App\Models\Startup;
use App\Models\StartupComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class FounderEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_founder_can_paginate_and_mark_only_owned_notifications_read(): void
    {
        $founder = $this->user('founder@example.test');
        $other = $this->user('other@example.test');
        $ownedId = $this->notification($founder, 'Owned notice');
        $otherId = $this->notification($other, 'Private notice');

        $this->actingAs($founder)
            ->get(route('founder.notifications.index'))
            ->assertOk()
            ->assertSee('Owned notice')
            ->assertDontSee('Private notice');

        $this->actingAs($founder)
            ->post(route('founder.notifications.read', $otherId))
            ->assertNotFound();

        $this->actingAs($founder)->post(route('founder.notifications.read', $ownedId));
        $this->assertNotNull(DB::table('notifications')->where('id', $ownedId)->value('read_at'));
    }

    public function test_founder_can_reply_to_own_startup_comment_but_not_another_founders(): void
    {
        $founder = $this->user('owner@example.test');
        $otherFounder = $this->user('other-owner@example.test');
        $commenter = $this->user('commenter@example.test');
        $ownedComment = $this->comment($this->startup($founder, 'owned-startup'), $commenter);
        $otherComment = $this->comment($this->startup($otherFounder, 'other-startup'), $commenter);

        $this->actingAs($founder)
            ->post(route('founder.comments.reply', $ownedComment), ['reply' => 'Thanks for the thoughtful feedback.'])
            ->assertRedirect();

        $ownedComment->refresh();
        $this->assertSame('Thanks for the thoughtful feedback.', $ownedComment->founder_reply);
        $this->assertNotNull($ownedComment->addressed_at);

        $this->actingAs($founder)
            ->post(route('founder.comments.reply', $otherComment), ['reply' => 'This must not be saved.'])
            ->assertForbidden();
        $this->assertNull($otherComment->fresh()->founder_reply);
    }

    public function test_founder_settings_preserve_unknown_legacy_opt_outs(): void
    {
        $founder = $this->user('preferences@example.test');
        $founder->update(['notification_preferences' => [
            'NEW_BID_RECEIVED' => false,
            'LEGACY_ENABLED_NOTICE' => true,
        ]]);
        $preferences = collect(config('notification_preferences.types'))
            ->keys()
            ->mapWithKeys(fn (string $key): array => [$key => $key === 'STARTUP_COMMENT' ? '1' : '0'])
            ->all();

        $this->actingAs($founder)->put(route('founder.settings.update'), [
            'name' => $founder->name,
            'email' => $founder->email,
            'notification_preferences' => $preferences,
        ])->assertRedirect(route('founder.settings'));

        $founder->refresh();
        $this->assertTrue($founder->wantsNotification('STARTUP_COMMENT'));
        $this->assertFalse($founder->wantsNotification('STARTUP_AWARD'));
        $this->assertFalse($founder->wantsNotification('NEW_BID_RECEIVED'));
        $this->assertArrayNotHasKey('LEGACY_ENABLED_NOTICE', $founder->notification_preferences);
    }

    private function user(string $email): User
    {
        return User::query()->create([
            'name' => 'Test Founder',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }

    private function startup(User $founder, string $slug): Startup
    {
        return Startup::query()->create([
            'user_id' => $founder->id,
            'name' => Str::headline($slug),
            'slug' => $slug,
            'status' => Startup::STATUS_ACTIVE,
        ]);
    }

    private function comment(Startup $startup, User $commenter): StartupComment
    {
        return StartupComment::query()->create([
            'startup_id' => $startup->id,
            'user_id' => $commenter->id,
            'body' => 'A useful comment for the founder.',
        ]);
    }

    private function notification(User $user, string $title): string
    {
        $id = Str::uuid()->toString();
        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\\Notifications\\FounderTestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['title' => $title]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
