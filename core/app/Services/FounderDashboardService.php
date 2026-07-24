<?php

namespace App\Services;

use App\Models\ProductOfDayWinner;
use App\Models\ProductOfMonthWinner;
use App\Models\ProductOfYearWinner;
use App\Models\CofounderInvitation;
use App\Models\InvestorLead;
use App\Models\SavedStartup;
use App\Models\Startup;
use App\Models\StartupClaimVerification;
use App\Models\StartupComment;
use App\Models\StartupRevenueEvent;
use App\Models\StartupUpvote;
use App\Models\User;
use App\Support\StartupContentPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FounderDashboardService
{
    private const ACTIVITY_LIMIT = 5;
    private const RANK_CACHE_SECONDS = 300;

    public function __construct(
        private StartupSharePreviewService $sharePreviewService
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forFounder(User $user): array
    {
        $startups = Startup::query()
            ->visibleToUser($user)
            ->withCount(['comments', 'savedByUsers'])
            ->orderByDesc('updated_at')
            ->get();

        $startupIds = $startups->pluck('id');
        $rankings = $this->rankings();
        $verifications = $this->claimVerifications($startupIds);
        $awards = $this->awards($startupIds);
        $cofounderStatuses = CofounderInvitation::query()
            ->whereIn('startup_id', $startupIds)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->selectRaw('startup_id, count(*) as total')
            ->groupBy('startup_id')
            ->pluck('total', 'startup_id');
        $investorStatuses = InvestorLead::query()
            ->join('startup_funding_rounds', 'startup_funding_rounds.id', '=', 'investor_leads.startup_funding_round_id')
            ->whereIn('startup_funding_rounds.startup_id', $startupIds)
            ->where('investor_leads.status', InvestorLead::STATUS_NEW)
            ->selectRaw('startup_funding_rounds.startup_id, count(*) as total')
            ->groupBy('startup_funding_rounds.startup_id')
            ->pluck('total', 'startup_funding_rounds.startup_id');

        return [
            'myStartups' => $startups,
            'startupProfiles' => $startups->map(function (Startup $startup) use ($rankings, $verifications, $awards, $cofounderStatuses, $investorStatuses) {
                $verification = $verifications->get($startup->id);
                $launchDate = $startup->launch_date;
                $daysUntilLaunch = $launchDate?->isFuture() ? today()->diffInDays($launchDate, false) : null;
                $readiness = $startup->content_completeness_score >= 80 && $startup->logo_path && $startup->website;

                return [
                    'startup' => $startup,
                    'completeness' => $startup->content_completeness_score,
                    'gaps' => StartupContentPolicy::profileGaps($startup),
                    'globalRank' => $rankings['global'][$startup->id] ?? null,
                    'categoryRank' => $rankings['category'][$startup->id] ?? null,
                    'awards' => $awards->get($startup->id, collect()),
                    'claimStatus' => $verification?->isVerified() ? 'verified' : ($verification ? 'pending' : 'unverified'),
                    'claimMethod' => $verification?->method,
                    'launchDate' => $launchDate,
                    'daysUntilLaunch' => $daysUntilLaunch,
                    'launchReadiness' => $readiness ? 'ready' : 'needs_attention',
                    'launchGuidance' => $readiness
                        ? 'Your launch profile is ready to share.'
                        : 'Add a logo, website, and complete at least 80% of the profile before launch.',
                    'sharePreview' => $this->sharePreviewService->build($startup),
                    'cofounderStatus' => (int) $cofounderStatuses->get($startup->id, 0),
                    'investorStatus' => (int) $investorStatuses->get($startup->id, 0),
                ];
            }),
            'totals' => [
                'upvotes' => (int) $startups->sum('upvotes'),
                'views' => (int) $startups->sum('views'),
                'clicks' => (int) $startups->sum('clicks'),
                'comments' => (int) $startups->sum('comments_count'),
                'saves' => (int) $startups->sum('saved_by_users_count'),
                'revenue' => (float) $startups->sum('revenue'),
                'mrr' => (float) $startups->sum('mrr'),
            ],
            'activity' => $this->activity($startups, $startupIds),
            'savedStartups' => $this->savedStartups($user),
            'unreadNotifications' => $user->unreadNotifications()->latest()->limit(5)->get(),
            'founderNavStatus' => $this->navigationStatus($user),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function navigationStatus(User $user): array
    {
        $startupIds = Startup::query()->visibleToUser($user)->pluck('id');

        return [
            'saved' => SavedStartup::query()->where('user_id', $user->id)->count(),
            'claims' => StartupClaimVerification::query()
                ->whereIn('startup_id', $startupIds)
                ->whereNull('verified_at')
                ->count(),
            'awards' => ProductOfDayWinner::query()->whereIn('startup_id', $startupIds)->count()
                + ProductOfMonthWinner::query()->whereIn('startup_id', $startupIds)->count()
                + ProductOfYearWinner::query()->whereIn('startup_id', $startupIds)->count(),
            'cofounders' => CofounderInvitation::query()
                ->whereIn('startup_id', $startupIds)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->count(),
            'investors' => InvestorLead::query()
                ->whereHas('fundingRound', fn ($query) => $query->whereIn('startup_id', $startupIds))
                ->where('status', InvestorLead::STATUS_NEW)
                ->count(),
        ];
    }

    /**
     * @return array{global: array<int, int>, category: array<int, int>}
     */
    private function rankings(): array
    {
        return Cache::remember('founder-dashboard:rankings:v1', self::RANK_CACHE_SECONDS, function () {
            $ranked = Startup::query()
                ->active()
                ->orderByDesc('upvotes')
                ->orderBy('id')
                ->get(['id', 'category', 'upvotes']);
            $global = [];
            $category = [];
            $categoryPositions = [];

            foreach ($ranked as $position => $startup) {
                $global[$startup->id] = $position + 1;
                $categoryKey = trim((string) $startup->category);
                $categoryPositions[$categoryKey] = ($categoryPositions[$categoryKey] ?? 0) + 1;
                $category[$startup->id] = $categoryPositions[$categoryKey];
            }

            return ['global' => $global, 'category' => $category];
        });
    }

    /**
     * @param Collection<int, int> $startupIds
     * @return Collection<int, StartupClaimVerification>
     */
    private function claimVerifications(Collection $startupIds): Collection
    {
        if ($startupIds->isEmpty()) {
            return collect();
        }

        return StartupClaimVerification::query()
            ->whereIn('startup_id', $startupIds)
            ->orderByDesc('verified_at')
            ->orderByDesc('created_at')
            ->get()
            ->unique('startup_id')
            ->keyBy('startup_id');
    }

    /**
     * @param Collection<int, int> $startupIds
     * @return Collection<int, Collection<int, array{label: string, date: mixed}>>
     */
    private function awards(Collection $startupIds): Collection
    {
        $result = collect();
        if ($startupIds->isEmpty()) {
            return $result;
        }

        ProductOfDayWinner::query()->whereIn('startup_id', $startupIds)->get()
            ->each(function (ProductOfDayWinner $winner) use ($result) {
                $result->push([
                    'startup_id' => $winner->startup_id,
                    'label' => 'Product of the day',
                    'date' => $winner->award_date,
                ]);
            });
        ProductOfMonthWinner::query()->whereIn('startup_id', $startupIds)->get()
            ->each(function (ProductOfMonthWinner $winner) use ($result) {
                $result->push([
                    'startup_id' => $winner->startup_id,
                    'label' => 'Product of the month',
                    'date' => $winner->award_month,
                ]);
            });
        ProductOfYearWinner::query()->whereIn('startup_id', $startupIds)->get()
            ->each(function (ProductOfYearWinner $winner) use ($result) {
                $result->push([
                    'startup_id' => $winner->startup_id,
                    'label' => 'Product of the year',
                    'date' => $winner->award_year,
                ]);
            });

        return $result->groupBy('startup_id');
    }

    /**
     * @param Collection<int, Startup> $startups
     * @param Collection<int, int> $startupIds
     * @return Collection<int, array<string, mixed>>
     */
    private function activity(Collection $startups, Collection $startupIds): Collection
    {
        if ($startupIds->isEmpty()) {
            return collect();
        }

        $upvotes = StartupUpvote::query()
            ->whereIn('startup_id', $startupIds)
            ->with(['startup:id,name,slug', 'user:id,name'])
            ->latest()
            ->limit(self::ACTIVITY_LIMIT)
            ->get()
            ->map(fn (StartupUpvote $upvote) => [
                'type' => 'upvote',
                'startup' => $upvote->startup,
                'actor' => $upvote->user?->name,
                'occurredAt' => $upvote->created_at,
            ]);
        $comments = StartupComment::query()
            ->whereIn('startup_id', $startupIds)
            ->with(['startup:id,name,slug', 'user:id,name'])
            ->latest()
            ->limit(self::ACTIVITY_LIMIT)
            ->get()
            ->map(fn (StartupComment $comment) => [
                'type' => 'comment',
                'startup' => $comment->startup,
                'actor' => $comment->user?->name,
                'body' => $comment->body,
                'occurredAt' => $comment->created_at,
            ]);
        $revenue = StartupRevenueEvent::query()
            ->whereIn('startup_id', $startupIds)
            ->with('startup:id,name,slug')
            ->latest()
            ->limit(self::ACTIVITY_LIMIT)
            ->get()
            ->map(fn (StartupRevenueEvent $event) => [
                'type' => 'revenue',
                'startup' => $event->startup,
                'amount' => (float) $event->amount,
                'currency' => $event->currency,
                'occurredAt' => $event->created_at,
            ]);
        $launches = $startups
            ->filter(fn (Startup $startup) => $startup->launch_date !== null && $startup->launch_date->isPast())
            ->sortByDesc('launch_date')
            ->take(self::ACTIVITY_LIMIT)
            ->map(fn (Startup $startup) => [
                'type' => 'launch',
                'startup' => $startup,
                'occurredAt' => $startup->launch_date,
            ]);

        return $upvotes
            ->concat($comments)
            ->concat($revenue)
            ->concat($launches)
            ->sortByDesc('occurredAt')
            ->take(self::ACTIVITY_LIMIT * 2)
            ->values();
    }

    /**
     * @return array{count: int, recent: Collection<int, SavedStartup>}
     */
    private function savedStartups(User $user): array
    {
        $query = SavedStartup::query()->where('user_id', $user->id);

        return [
            'count' => (clone $query)->count(),
            'recent' => $query->with('startup:id,name,slug,tagline,status')
                ->latest()
                ->limit(3)
                ->get(),
        ];
    }
}
