<?php

namespace Tests\Feature;

use App\Models\CofounderInvitation;
use App\Models\InvestorLead;
use App\Models\LaunchNotification;
use App\Models\Startup;
use App\Models\StartupFundingRound;
use App\Models\User;
use App\Services\CofounderInvitationService;
use App\Services\LaunchNotificationService;
use App\Services\StartupSharePreviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FounderGrowthTest extends TestCase
{
    use RefreshDatabase;

    public function test_share_preview_is_shared_between_public_and_founder_surfaces(): void
    {
        $founder = $this->user('owner@example.test');
        $startup = $this->startup($founder);
        $preview = app(StartupSharePreviewService::class)->build($startup);

        $this->get(route('startup.show', $startup->slug))
            ->assertOk()
            ->assertSee($preview['metaDescription'], false)
            ->assertSee($preview['xShareUrl'], false);
        $this->actingAs($founder)->get(route('founder.dashboard'))
            ->assertOk()
            ->assertSee('Launch readiness')
            ->assertSee($preview['shareText']);
    }

    public function test_cofounder_invitation_is_owned_expiring_single_use_and_attaches_user(): void
    {
        Mail::fake();
        $owner = $this->user('owner@example.test');
        $invitee = $this->user('invitee@example.test');
        $other = $this->user('other@example.test');
        $startup = $this->startup($owner);
        $token = 'opaque-invitation-token';
        $invitation = CofounderInvitation::query()->create([
            'startup_id' => $startup->id,
            'invited_by_user_id' => $owner->id,
            'email' => $invitee->email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($other)
            ->post(route('founder.cofounder-invitations.store', $startup), ['email' => 'new@example.test'])
            ->assertForbidden();
        $this->actingAs($other)
            ->post(route('cofounder-invitations.accept', $token))
            ->assertForbidden();

        $this->actingAs($invitee)
            ->post(route('cofounder-invitations.accept', $token))
            ->assertRedirect(route('founder.startups.edit', $startup));
        $this->assertTrue($startup->fresh()->userCanManage($invitee));
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->actingAs($invitee)->post(route('cofounder-invitations.accept', $token))->assertGone();
    }

    public function test_cofounder_email_preference_is_respected_and_pending_invites_are_idempotent(): void
    {
        Mail::fake();
        $owner = $this->user('owner@example.test');
        $invitee = $this->user('invitee@example.test');
        $invitee->update(['notification_preferences' => ['COFOUNDER_UPDATES' => false]]);
        $startup = $this->startup($owner);
        $service = app(CofounderInvitationService::class);

        $service->invite($startup, $owner, $invitee->email);
        $service->invite($startup, $owner, strtoupper($invitee->email));

        $this->assertSame(1, CofounderInvitation::query()->count());
        Mail::assertNothingSent();
    }

    public function test_retrying_a_usable_cofounder_invitation_keeps_the_original_link(): void
    {
        Mail::fake();
        $owner = $this->user('owner@example.test');
        $startup = $this->startup($owner);
        $service = app(CofounderInvitationService::class);

        $first = $service->invite($startup, $owner, 'new-cofounder@example.test');
        $originalHash = $first->token_hash;
        $second = $service->invite($startup, $owner, 'new-cofounder@example.test');

        $this->assertTrue($first->is($second));
        $this->assertSame($originalHash, $second->fresh()->token_hash);
        Mail::assertSentCount(1);
    }

    public function test_public_investor_interest_requires_active_round_and_founder_owns_inbox_updates(): void
    {
        $owner = $this->user('owner@example.test');
        $other = $this->user('other@example.test');
        $startup = $this->startup($owner);
        $round = StartupFundingRound::query()->create([
            'startup_id' => $startup->id,
            'round_type' => 'seed',
            'status' => StartupFundingRound::STATUS_OPEN,
        ]);

        $payload = [
            'name' => 'Investor One',
            'email' => 'investor@example.test',
            'organization' => 'Capital Co',
            'message' => 'I would like to learn more.',
            'website' => '',
        ];
        $this->post(route('startup.investor-interest', $startup->slug), $payload)->assertRedirect();
        $this->post(route('startup.investor-interest', $startup->slug), $payload)->assertRedirect();
        $lead = InvestorLead::query()->sole();

        $this->actingAs($other)
            ->patch(route('founder.fundraising.leads.update', $lead), ['status' => 'contacted'])
            ->assertNotFound();
        $this->actingAs($owner)
            ->patch(route('founder.fundraising.leads.update', $lead), ['status' => 'contacted'])
            ->assertRedirect(route('pricing'));
        $owner->update(['is_pro' => true, 'pro_since' => now()]);
        $this->actingAs($owner)
            ->patch(route('founder.fundraising.leads.update', $lead), [
                'status' => InvestorLead::STATUS_CONTACTED,
                'notes' => 'Follow up next week.',
            ])->assertRedirect();

        $this->assertSame(InvestorLead::STATUS_CONTACTED, $lead->fresh()->status);
        $this->assertSame('Follow up next week.', $lead->fresh()->notes);
        $round->update(['status' => StartupFundingRound::STATUS_CLOSED]);
        $this->actingAs($owner)
            ->get(route('founder.fundraising.index'))
            ->assertOk()
            ->assertSee('investor@example.test');
        $this->post(route('startup.investor-interest', $startup->slug), array_merge($payload, ['email' => 'second@example.test']))
            ->assertNotFound();
    }

    public function test_launch_opt_out_skips_mail_without_deleting_subscription(): void
    {
        Mail::fake();
        $owner = $this->user('owner@example.test');
        $subscriber = $this->user('subscriber@example.test');
        $subscriber->update(['notification_preferences' => ['LAUNCH_UPDATES' => false]]);
        $startup = $this->startup($owner);
        $subscription = LaunchNotification::query()->create([
            'startup_id' => $startup->id,
            'email' => strtoupper($subscriber->email),
        ]);

        $sent = app(LaunchNotificationService::class)->sendLaunchEmails($startup);

        $this->assertSame(0, $sent);
        $this->assertTrue($subscription->fresh()->exists);
        Mail::assertNothingSent();
    }

    public function test_investor_honeypot_is_rejected(): void
    {
        $owner = $this->user('owner@example.test');
        $startup = $this->startup($owner);
        StartupFundingRound::query()->create([
            'startup_id' => $startup->id,
            'round_type' => 'seed',
            'status' => StartupFundingRound::STATUS_OPEN,
        ]);

        $this->post(route('startup.investor-interest', $startup->slug), [
            'name' => 'Spam Bot',
            'email' => 'spam@example.test',
            'website' => 'https://spam.example.test',
        ])->assertSessionHasErrors('website');
        $this->assertDatabaseCount('investor_leads', 0);
    }

    public function test_investor_lead_email_is_unique_per_round_at_database_level(): void
    {
        $owner = $this->user('owner@example.test');
        $round = StartupFundingRound::query()->create([
            'startup_id' => $this->startup($owner)->id,
            'round_type' => 'seed',
            'status' => StartupFundingRound::STATUS_OPEN,
        ]);
        $round->investorLeads()->create([
            'name' => 'Investor One',
            'email' => 'investor@example.test',
            'status' => InvestorLead::STATUS_NEW,
        ]);

        $this->expectException(QueryException::class);
        $round->investorLeads()->create([
            'name' => 'Investor Duplicate',
            'email' => 'investor@example.test',
            'status' => InvestorLead::STATUS_NEW,
        ]);
    }

    private function user(string $email): User
    {
        return User::query()->create([
            'name' => 'Test Founder',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }

    private function startup(User $founder): Startup
    {
        return Startup::query()->create([
            'user_id' => $founder->id,
            'name' => 'Growth Rocket',
            'slug' => 'growth-rocket',
            'tagline' => 'Launch and grow with founder tools',
            'description' => str_repeat('A useful founder growth platform for startup teams. ', 5),
            'website' => 'https://growth-rocket.example.test',
            'logo_path' => 'images/startups/growth-rocket.png',
            'launch_date' => now()->addDays(5),
            'status' => Startup::STATUS_ACTIVE,
        ]);
    }
}
