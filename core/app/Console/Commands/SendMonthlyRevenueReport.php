<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMonthlyRevenueReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monthly:revenue-report
                            {--month= : Year-month (Y-m) to report; default: current month}
                            {--year= : Year (alternative to --month)}
                            {--dry-run : Build report and log only, do not send email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile monthly marketplace revenue, costs, and transactions and email report to super admins (runs at end of month)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $month = $this->option('month');
        $year = $this->option('year');
        $dryRun = (bool) $this->option('dry-run');

        if ($month) {
            $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } elseif ($year) {
            $start = now()->setYear((int) $year)->startOfYear();
            $end = $start->copy()->endOfYear();
        } else {
            $start = now()->copy()->startOfMonth();
            $end = now()->copy()->endOfMonth();
        }

        $this->info('Building report for ' . $start->format('F Y') . ' (' . $start->toDateString() . ' to ' . $end->toDateString() . ')');

        try {
            $report = $this->buildReport($start, $end);
            $html = $this->reportToHtml($report, $start);
            $subject = gs('site_name') . ' – Monthly Revenue Report: ' . $start->format('F Y');

            if ($dryRun) {
                $this->info('Dry run – report built (not sent).');
                Log::info('Monthly revenue report (dry run)', ['report' => $report]);
                return 0;
            }

            $admins = Admin::where('is_super_admin', true)
                ->where('username', '!=', 'demoadmin')
                ->where('status', Admin::STATUS_ENABLED)
                ->get();

            if ($admins->isEmpty()) {
                $this->warn('No super admin (excluding demoadmin) found to send the report.');
                Log::warning('Monthly revenue report: no super admin recipients');
                return 0;
            }

            foreach ($admins as $admin) {
                try {
                    $user = [
                        'email' => $admin->email,
                        'username' => $admin->username,
                        'fullname' => $admin->name ?? $admin->username,
                    ];
                    notify($user, 'DEFAULT', [
                        'subject' => $subject,
                        'message' => $html,
                    ], ['email'], createLog: false);
                    $this->info('Sent to ' . $admin->email);
                } catch (\Exception $e) {
                    Log::error('Monthly revenue report send failed', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error('Failed to send to ' . $admin->email . ': ' . $e->getMessage());
                }
            }

            $this->info('Monthly revenue report sent to ' . $admins->count() . ' super admin(s).');
            return 0;
        } catch (\Exception $e) {
            Log::error('Monthly revenue report failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error($e->getMessage());
            return 1;
        }
    }

    /**
     * Build report data for the given month range.
     */
    private function buildReport(\Carbon\Carbon $start, \Carbon\Carbon $end): array
    {
        $base = Transaction::whereBetween('created_at', [$start, $end]);

        // Platform revenue (fees we keep): user deductions for escrow_charge, direct_payout_fee, featured_listing_fee
        $revenueEscrow = (clone $base)->where('remark', 'escrow_charge')->sum('amount');
        $revenueDirectPayout = (clone $base)->where('remark', 'direct_payout_fee')->sum('amount');
        $revenueFeatured = (clone $base)->where('remark', 'featured_listing_fee')->sum('amount');
        $marketplaceRevenue = $revenueEscrow + $revenueDirectPayout + $revenueFeatured;

        // Costs (money we pay out to users): affiliate signup
        $costAffiliate = (clone $base)->where('remark', 'affiliate_signup')->sum('amount');
        $totalCosts = $costAffiliate;

        // User money flow
        $totalDeposits = (clone $base)->where('remark', 'deposit')->where('trx_type', '+')->sum('amount');
        $totalWithdrawals = (clone $base)->where('remark', 'withdraw')->where('trx_type', '-')->sum('amount');

        // Transaction counts by type
        $countByRemark = (clone $base)->selectRaw('remark, count(*) as cnt')->groupBy('remark')->pluck('cnt', 'remark')->toArray();
        $totalTransactions = (clone $base)->count();

        $profit = $marketplaceRevenue - $totalCosts;

        return [
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'period_label' => $start->format('F Y'),
            'marketplace_revenue' => $marketplaceRevenue,
            'revenue_escrow_charge' => $revenueEscrow,
            'revenue_direct_payout_fee' => $revenueDirectPayout,
            'revenue_featured_fee' => $revenueFeatured,
            'total_costs' => $totalCosts,
            'cost_affiliate' => $costAffiliate,
            'affiliate_referral_payouts' => $costAffiliate,
            'profit' => $profit,
            'total_deposits' => $totalDeposits,
            'total_withdrawals' => $totalWithdrawals,
            'total_transactions' => $totalTransactions,
            'count_by_remark' => $countByRemark,
        ];
    }

    /**
     * Turn report array into HTML email body.
     */
    private function reportToHtml(array $report, \Carbon\Carbon $start): string
    {
        $sym = gs('cur_sym') ?? '';
        $cur = gs('cur_text') ?? '';

        $rows = [
            '<strong>Period</strong>',
            $report['period_start'] . ' to ' . $report['period_end'],
            '<strong>Marketplace revenue (platform)</strong>',
            $sym . number_format($report['marketplace_revenue'], 2) . ' ' . $cur,
            '&nbsp;&nbsp;– Escrow charges',
            $sym . number_format($report['revenue_escrow_charge'], 2),
            '&nbsp;&nbsp;– Direct payout fees',
            $sym . number_format($report['revenue_direct_payout_fee'], 2),
            '&nbsp;&nbsp;– Featured listing fees',
            $sym . number_format($report['revenue_featured_fee'], 2),
            '<strong>Costs</strong>',
            $sym . number_format($report['total_costs'], 2) . ' ' . $cur,
            '&nbsp;&nbsp;– Affiliate referral payouts (paid this period)',
            $sym . number_format($report['affiliate_referral_payouts'], 2),
            '<strong>Profit (revenue − costs)</strong>',
            $sym . number_format($report['profit'], 2) . ' ' . $cur,
            '<strong>User money – Total deposits</strong>',
            $sym . number_format($report['total_deposits'], 2) . ' ' . $cur,
            '<strong>User money – Total withdrawals</strong>',
            $sym . number_format($report['total_withdrawals'], 2) . ' ' . $cur,
            '<strong>Total transactions (count)</strong>',
            (string) $report['total_transactions'],
        ];

        $tableRows = '';
        for ($i = 0; $i < count($rows); $i += 2) {
            $tableRows .= '<tr><td style="padding:6px 12px;border:1px solid #ddd;">' . $rows[$i] . '</td><td style="padding:6px 12px;border:1px solid #ddd;">' . ($rows[$i + 1] ?? '') . '</td></tr>';
        }

        $countSection = '';
        if (!empty($report['count_by_remark'])) {
            $countSection = '<h4 style="margin-top:20px;">Transaction count by type</h4><table style="border-collapse:collapse;width:100%;max-width:400px;">';
            foreach ($report['count_by_remark'] as $remark => $cnt) {
                $countSection .= '<tr><td style="padding:6px 12px;border:1px solid #ddd;">' . e($remark) . '</td><td style="padding:6px 12px;border:1px solid #ddd;">' . (int) $cnt . '</td></tr>';
            }
            $countSection .= '</table>';
        }

        return '<p>Monthly revenue report for <strong>' . e($report['period_label']) . '</strong>.</p>' .
            '<table style="border-collapse:collapse;width:100%;max-width:500px;">' . $tableRows . '</table>' .
            $countSection .
            '<p style="margin-top:24px;color:#666;">This is an automated report sent at the end of the month.</p>';
    }
}
