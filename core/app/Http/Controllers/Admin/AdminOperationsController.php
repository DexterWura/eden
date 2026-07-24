<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSpot;
use App\Models\AdminAuditLog;
use App\Models\AdminOperationNotification;
use App\Models\Startup;
use App\Models\StartupClaimVerification;
use App\Models\StartupReport;
use App\Services\StartupActivationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminOperationsController extends Controller
{
    public function __construct(
        private StartupActivationService $startupActivationService
    ) {
        parent::__construct();
    }

    public function moderation(): Response
    {
        return $this->page('Moderation queues', 'moderation', [
            'pendingStartups' => Startup::pending()->latest()->limit(100)->get(),
            'heroRequests' => Startup::where('hero_request_status', 'pending')->latest()->limit(100)->get(),
            'reports' => StartupReport::with('startup')->where('status', StartupReport::STATUS_PENDING)->latest()->limit(100)->get(),
            'claims' => StartupClaimVerification::with(['startup', 'user'])->whereNull('verified_at')->latest()->limit(100)->get(),
        ], 'moderation');
    }

    public function bulkModerate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'queue' => ['required', 'in:startups,hero,reports,claims'],
            'action' => ['required', 'in:approve,activate,disable,dismiss'],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'distinct'],
        ]);
        $allowed = [
            'startups' => ['activate', 'disable'],
            'hero' => ['approve', 'disable'],
            'reports' => ['approve', 'dismiss'],
            'claims' => ['approve', 'dismiss'],
        ];
        if (! in_array($validated['action'], $allowed[$validated['queue']], true)) {
            return back()->withErrors(['action' => 'That action is not valid for this queue.']);
        }

        $completed = DB::transaction(function () use ($validated): int {
            $count = 0;
            foreach ($validated['ids'] as $id) {
                if ($this->moderateItem($validated['queue'], $validated['action'], (int) $id)) {
                    $count++;
                }
            }
            return $count;
        });

        return back()->with('notify', [['success', "{$completed} item(s) updated; invalid or stale items were skipped."]]);
    }

    public function audit(Request $request): Response
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', 'max:100'],
            'admin_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $query = AdminAuditLog::with('admin')->latest();
        $query->when($validated['action'] ?? null, fn ($q, $action) => $q->where('action', 'like', $action . '%'));
        $query->when($validated['admin_id'] ?? null, fn ($q, $id) => $q->where('admin_id', $id));
        $query->when($validated['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date));
        $query->when($validated['to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));

        return $this->page('Audit log', 'audit', [
            'logs' => $query->paginate(40)->withQueryString(),
            'admins' => \App\Models\Admin::orderBy('name')->get(['id', 'name']),
        ], 'audit');
    }

    public function payments(Request $request): Response
    {
        $rows = $this->paymentRows($request);
        $ledger = (clone $rows)->orderByDesc('created_at')->orderByDesc('source_id')
            ->paginate(40)->withQueryString();
        $summary = (clone $rows)
            ->selectRaw('status, COUNT(*) as aggregate_count, SUM(amount) as aggregate_amount')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row): array => [$row->status => [
                'count' => (int) $row->aggregate_count,
                'amount' => (float) $row->aggregate_amount,
            ]]);

        return $this->page('Payment ledger', 'payments', [
            'ledger' => $ledger,
            'summary' => $summary,
        ], 'payments');
    }

    public function paymentCsv(Request $request): StreamedResponse
    {
        $rows = $this->paymentRows($request);
        $rowCount = (clone $rows)->count();
        admin_audit_log('payments.csv_exported', 'Unified payment ledger exported.', null, [], ['rows' => $rowCount]);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['type', 'date', 'customer', 'gateway', 'reference', 'status', 'amount', 'currency']);
            $rows->orderBy('source_id')->orderBy('type')->chunk(500, function ($chunk) use ($handle): void {
                foreach ($chunk as $row) {
                    fputcsv($handle, [$row->type, $row->created_at, $row->customer, $row->gateway, $row->reference, $row->status, $row->amount ?? 'unknown', $row->currency]);
                }
            });
            fclose($handle);
        }, 'eden-payment-ledger-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function notifications(): Response
    {
        $admin = auth('admin')->user();
        $notifications = AdminOperationNotification::query()
            ->leftJoin('admin_operation_notification_reads as delivery_reads', function ($join) use ($admin): void {
                $join->on('delivery_reads.admin_operation_notification_id', '=', 'admin_operation_notifications.id')
                    ->where('delivery_reads.admin_id', $admin->id);
            })
            ->where(fn ($q) => $q->whereNull('admin_operation_notifications.admin_id')->orWhere('admin_operation_notifications.admin_id', $admin->id))
            ->select('admin_operation_notifications.*')
            ->selectRaw('COALESCE(delivery_reads.read_at, admin_operation_notifications.read_at) as read_at')
            ->orderByDesc('admin_operation_notifications.created_at')
            ->paginate(40);

        return $this->page('Admin notifications', 'notifications', compact('notifications'), 'admin-notifications');
    }

    public function readNotifications(Request $request): RedirectResponse
    {
        $validated = $request->validate(['ids' => ['nullable', 'array'], 'ids.*' => ['integer']]);
        $adminId = auth('admin')->id();
        $query = AdminOperationNotification::where(fn ($q) => $q->whereNull('admin_id')->orWhere('admin_id', $adminId));
        if (! empty($validated['ids'])) {
            $query->whereIn('id', $validated['ids']);
        }
        $notificationIds = (clone $query)->pluck('id');
        (clone $query)->where('admin_id', $adminId)->update(['read_at' => now()]);
        $broadcastIds = AdminOperationNotification::query()
            ->whereIn('id', $notificationIds)
            ->whereNull('admin_id')
            ->pluck('id');
        $now = now();
        DB::table('admin_operation_notification_reads')->insertOrIgnore(
            $broadcastIds->map(fn (int $notificationId): array => [
                'admin_operation_notification_id' => $notificationId,
                'admin_id' => $adminId,
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );

        return back()->with('notify', [['success', 'Notifications marked as read.']]);
    }

    public function health(): Response
    {
        $database = false;
        try {
            DB::select('SELECT 1');
            $database = true;
        } catch (\Throwable) {
        }

        return $this->page('System health', 'health', [
            'checks' => [
                'database' => $database ? 'Connected' : 'Unavailable',
                'queue' => (string) config('queue.default'),
                'mail' => (string) config('mail.default'),
                'scheduler_last_seen' => optional(Cache::get('eden_last_cron_at'))->toDateTimeString() ?: 'Never recorded',
                'pending_migrations' => Schema::hasTable('migrations') ? 'Use the guarded migrations screen' : 'Migration table unavailable',
            ],
            'recentErrors' => $this->sanitizedRecentErrors(),
        ], 'health');
    }

    private function moderateItem(string $queue, string $action, int $id): bool
    {
        if ($queue === 'startups') {
            $startup = Startup::whereKey($id)->whereIn('status', [Startup::STATUS_PENDING, Startup::STATUS_ACTIVE, Startup::STATUS_DISABLED])->lockForUpdate()->first();
            if (! $startup) {
                return false;
            }
            $old = $startup->status;
            if ($action === 'activate') {
                $result = $this->startupActivationService->activate($startup);
                if (! $result['activated']) {
                    return false;
                }
                $status = Startup::STATUS_ACTIVE;
            } else {
                $status = Startup::STATUS_DISABLED;
                $startup->update(['status' => $status]);
            }
            admin_audit_log("moderation.startup.{$action}", "Startup {$action}: {$startup->name}", $startup, ['status' => $old], ['status' => $status]);
            return true;
        }

        if ($queue === 'hero') {
            $startup = Startup::whereKey($id)->where('hero_request_status', 'pending')->lockForUpdate()->first();
            if (! $startup) {
                return false;
            }
            $values = $action === 'approve'
                ? ['hero_request_status' => 'approved', 'featured_on_hero' => true]
                : ['hero_request_status' => 'rejected', 'featured_on_hero' => false];
            $startup->update($values);
            admin_audit_log("moderation.hero.{$action}", "Hero request {$action}: {$startup->name}", $startup, ['hero_request_status' => 'pending'], $values);
            return true;
        }

        if ($queue === 'reports') {
            $report = StartupReport::whereKey($id)->where('status', StartupReport::STATUS_PENDING)->lockForUpdate()->first();
            if (! $report) {
                return false;
            }
            $status = $action === 'approve' ? StartupReport::STATUS_REVIEWED : StartupReport::STATUS_DISMISSED;
            $report->update(['status' => $status, 'reviewed_at' => now()]);
            admin_audit_log("moderation.report.{$action}", "Startup report {$action}.", $report, ['status' => StartupReport::STATUS_PENDING], ['status' => $status]);
            return true;
        }

        $claim = StartupClaimVerification::with('startup')->whereKey($id)->whereNull('verified_at')->lockForUpdate()->first();
        if (! $claim || ! $claim->startup) {
            return false;
        }
        if ($action === 'approve') {
            $claim->startup->update(['user_id' => $claim->user_id]);
            $claim->update(['verified_at' => now()]);
        } else {
            $claim->delete();
        }
        admin_audit_log("moderation.claim.{$action}", "Ownership claim {$action}.", $claim->startup, [], ['user_id' => $claim->user_id]);
        return true;
    }

    private function paymentRows(Request $request)
    {
        $request->validate([
            'type' => ['nullable', 'in:pro,ad'],
            'status' => ['nullable', 'string', 'max:20'],
            'gateway' => ['nullable', 'string', 'max:32'],
            'reference' => ['nullable', 'string', 'max:64'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $pro = DB::table('pro_payments')
            ->leftJoin('users', 'users.id', '=', 'pro_payments.user_id')
            ->selectRaw("'pro' as type, pro_payments.id as source_id, pro_payments.created_at, users.email as customer, pro_payments.gateway, pro_payments.trx as reference, pro_payments.status, pro_payments.amount, pro_payments.currency");
        $ads = DB::table('ad_spots')
            ->selectRaw("'ad' as type, ad_spots.id as source_id, ad_spots.created_at, ad_spots.contact_email as customer, ad_spots.gateway, ad_spots.payment_reference as reference, ad_spots.status, ad_spots.amount, ad_spots.currency");

        $union = $request->type === 'pro' ? $pro : ($request->type === 'ad' ? $ads : $pro->unionAll($ads));
        $rows = DB::query()->fromSub($union, 'payment_ledger');
        $rows->when($request->status, fn ($query, $status) => $query->where('status', $status));
        $rows->when($request->gateway, fn ($query, $gateway) => $query->where('gateway', $gateway));
        $rows->when($request->reference, fn ($query, $reference) => $query->where('reference', 'like', '%' . $reference . '%'));
        $rows->when($request->from, fn ($query, $date) => $query->whereDate('created_at', '>=', $date));
        $rows->when($request->to, fn ($query, $date) => $query->whereDate('created_at', '<=', $date));

        return $rows;
    }

    private function sanitizedRecentErrors(): array
    {
        $path = storage_path('logs/laravel.log');
        if (! File::exists($path)) {
            return [];
        }
        $size = File::size($path);
        $contents = file_get_contents($path, false, null, max(0, $size - 200000), min($size, 200000));
        if ($contents === false) {
            return [];
        }
        $matches = [];
        preg_match_all('/\[(?<date>[^\]]+)\]\s+\w+\.ERROR:/', $contents, $matches, PREG_SET_ORDER);

        return collect($matches)->take(-10)->reverse()->values()->map(fn ($match) => [
            'occurred_at' => $match['date'],
            'summary' => 'Application error',
            'fingerprint' => substr(hash('sha256', $match[0]), 0, 12),
        ])->all();
    }

    private function page(string $title, string $view, array $data, string $activeNav): Response
    {
        $content = view("eden.admin-operations.{$view}", $data)->render();
        $admin = auth('admin')->user();

        return response()->view('eden.layout-dashboard', [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => $activeNav,
            'dashboardLogo' => (function_exists('gs') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => $admin->name,
            'avatarLetter' => strtoupper(substr($admin->name, 0, 1)),
            'content' => $content,
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }
}
