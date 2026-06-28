<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\ProcessEndingAuctions;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CronjobController extends Controller
{
    /**
     * Get all scheduled cron jobs with their status
     */
    private function getAllCronJobs()
    {
        $cronJobs = [
            [
                'name' => 'Schedule Runner',
                'description' => 'Runs Laravel scheduler every minute',
                'schedule' => 'Every minute',
                'log_file' => null,
                'command' => 'schedule:run',
                'type' => 'system',
            ],
            [
                'name' => 'Auction Processing',
                'description' => 'Processes ending auctions every minute',
                'schedule' => 'Every minute',
                'log_file' => 'auction-processing.log',
                'command' => 'auctions:process-ending --minutes=5',
                'type' => 'critical',
            ],
            [
                'name' => 'Migration Check',
                'description' => 'Checks for pending migrations hourly',
                'schedule' => 'Hourly',
                'log_file' => 'migration-check.log',
                'command' => 'migrate:auto --check-only',
                'type' => 'important',
            ],
            [
                'name' => 'Auto Migration (Non-Production)',
                'description' => 'Auto-runs migrations in non-production environments',
                'schedule' => 'Hourly (dev/staging only)',
                'log_file' => 'migration-auto.log',
                'command' => 'migrate:auto',
                'type' => 'important',
                'conditional' => true,
            ],
            [
                'name' => 'Marketplace Cleanup',
                'description' => 'Cleans up marketplace data daily at 2 AM',
                'schedule' => 'Daily at 02:00',
                'log_file' => 'marketplace-cleanup.log',
                'command' => 'marketplace:cleanup',
                'type' => 'important',
            ],
            [
                'name' => 'NDA Expiration Reminders',
                'description' => 'Sends expiration reminders for NDAs daily at 9 AM',
                'schedule' => 'Daily at 09:00',
                'log_file' => 'nda-expiration-reminders.log',
                'command' => 'nda:expiration-reminders',
                'type' => 'normal',
            ],
            [
                'name' => 'Process Expired NDAs',
                'description' => 'Processes expired NDAs daily at midnight',
                'schedule' => 'Daily at 00:00',
                'log_file' => 'nda-expiration-processing.log',
                'command' => 'nda:process-expired',
                'type' => 'normal',
            ],
            [
                'name' => 'Monthly Revenue Report',
                'description' => 'Compiles monthly marketplace revenue, costs, transactions and emails report to super admins',
                'schedule' => 'Last day of every month at 23:55',
                'log_file' => 'monthly-revenue-report.log',
                'command' => 'monthly:revenue-report',
                'type' => 'important',
            ],
            [
                'name' => 'Startup Website Check',
                'description' => 'Pings startup websites (every 3 days per startup); marks dormant after 6 consecutive failures; reactivates when reachable; deletes after 30 days dormant',
                'schedule' => 'Daily',
                'log_file' => 'startup-website-check.log',
                'command' => 'startups:check-websites',
                'type' => 'normal',
            ],
        ];

        // Get status for each cron job
        foreach ($cronJobs as &$job) {
            $job['status'] = $this->getCronJobStatus($job);
        }

        return $cronJobs;
    }

    /**
     * Get status for a specific cron job
     */
    private function getCronJobStatus($job)
    {
        $status = [
            'active' => false,
            'last_run' => null,
            'last_success' => null,
            'last_error' => null,
            'error_count' => 0,
            'success_count' => 0,
            'log_size' => 0,
            'recent_errors' => [],
            'recent_logs' => [],
        ];

        if (!$job['log_file']) {
            // For schedule runner, check cache
            try {
                $scheduleRunCache = \Illuminate\Support\Facades\Cache::get('schedule:run:last');
                if ($scheduleRunCache) {
                    $scheduleRunTime = \Carbon\Carbon::parse($scheduleRunCache);
                    $minutesSinceRun = now()->diffInMinutes($scheduleRunTime, false);
                    if ($minutesSinceRun >= 0 && $minutesSinceRun <= 10) {
                        $status['active'] = true;
                        $status['last_run'] = $scheduleRunTime;
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
            return $status;
        }

        $logFile = storage_path('logs/' . $job['log_file']);
        
        if (!file_exists($logFile)) {
            return $status;
        }

        $status['log_size'] = filesize($logFile);
        $lastModified = filemtime($logFile);
        $status['last_run'] = \Carbon\Carbon::createFromTimestamp($lastModified);
        
        // Check if active (updated within threshold)
        // For minute-based: 15 minutes (more lenient)
        // For hourly: 2 hours
        // For daily: 26 hours (more lenient)
        // For weekly: 8 days
        $threshold = 15; // minutes
        if (strpos($job['schedule'], 'Hourly') !== false) {
            $threshold = 120; // 2 hours
        } elseif (strpos($job['schedule'], 'Daily') !== false) {
            $threshold = 1560; // 26 hours
        } elseif (strpos($job['schedule'], 'Weekly') !== false) {
            $threshold = 11520; // 8 days in minutes
        }
        
        // Calculate minutes since last update (always positive)
        $minutesSinceUpdate = now()->diffInMinutes($status['last_run']);
        
        // If log was updated within threshold, job is active
        if ($minutesSinceUpdate <= $threshold) {
            $status['active'] = true;
        }

        // Parse log file
        if ($status['log_size'] > 0) {
            $logContent = file_get_contents($logFile);
            $logLines = explode("\n", $logContent);
            $logLines = array_filter($logLines);
            
            // Get recent logs (last 100 lines)
            $recentLines = array_slice($logLines, -100);
            $status['recent_logs'] = array_reverse($recentLines);
            
            // Additional check: If log file exists and has content, and was modified recently, mark as active
            // This helps catch jobs that are running but might have stale timestamps
            if (!$status['active'] && count($logLines) > 0) {
                // For minute-based jobs: if modified within 20 minutes, consider active
                // For hourly: if modified within 2.5 hours
                // For daily: if modified within 27 hours
                $fallbackThreshold = 20;
                if (strpos($job['schedule'], 'Hourly') !== false) {
                    $fallbackThreshold = 150; // 2.5 hours
                } elseif (strpos($job['schedule'], 'Daily') !== false) {
                    $fallbackThreshold = 1620; // 27 hours
                } elseif (strpos($job['schedule'], 'Weekly') !== false) {
                    $fallbackThreshold = 12000; // ~8.3 days
                }
                
                if ($minutesSinceUpdate <= $fallbackThreshold) {
                    $status['active'] = true;
                }
            }
            
            // Count errors and successes
            foreach ($logLines as $line) {
                $lineLower = strtolower($line);
                if (strpos($lineLower, 'error') !== false || 
                    strpos($lineLower, 'failed') !== false || 
                    strpos($lineLower, 'exception') !== false ||
                    strpos($lineLower, 'fatal') !== false) {
                    $status['error_count']++;
                    if (count($status['recent_errors']) < 10) {
                        $status['recent_errors'][] = $line;
                    }
                } elseif (strpos($lineLower, 'success') !== false || 
                          strpos($lineLower, 'completed') !== false ||
                          strpos($lineLower, 'processed') !== false) {
                    $status['success_count']++;
                }
            }
            
            // Find last error
            foreach (array_reverse($logLines) as $line) {
                $lineLower = strtolower($line);
                if (strpos($lineLower, 'error') !== false || 
                    strpos($lineLower, 'failed') !== false || 
                    strpos($lineLower, 'exception') !== false) {
                    $status['last_error'] = $line;
                    break;
                }
            }
            
            // Find last success
            foreach (array_reverse($logLines) as $line) {
                $lineLower = strtolower($line);
                if (strpos($lineLower, 'success') !== false || 
                    strpos($lineLower, 'completed') !== false ||
                    strpos($lineLower, 'processed') !== false) {
                    $status['last_success'] = $line;
                    break;
                }
            }
        }

        return $status;
    }

    public function index()
    {
        $pageTitle = 'Cronjob Management';
        
        // Get all cron jobs with status
        $cronJobs = $this->getAllCronJobs();
        
        // Get cronjob settings from general settings
        $general = gs();
        $cronjobSettings = [
            'last_auction_processing_run' => $general->last_auction_processing_run ?? null,
        ];

        // Check for pending auctions
        $pendingAuctions = \App\Models\Listing::where('sale_type', 'auction')
            ->where('status', \App\Constants\Status::LISTING_ACTIVE)
            ->whereNotNull('auction_end')
            ->where('auction_end', '<=', now())
            ->count();

        // Detect if cron job is running
        $cronJobActive = false;
        $cronJobLastRun = null;
        
        // Method 1: Check file-based timestamp (most reliable)
        $timestampFile = storage_path('logs/.cron-last-run');
        if (file_exists($timestampFile)) {
            try {
                $timestampContent = trim(file_get_contents($timestampFile));
                if ($timestampContent) {
                    $lastRunTime = \Carbon\Carbon::parse($timestampContent);
                    $minutesSinceLastRun = now()->diffInMinutes($lastRunTime, false);
                    
                    // If cron ran in the last 5 minutes, it's active
                    if ($minutesSinceLastRun >= 0 && $minutesSinceLastRun <= 5) {
                        $cronJobActive = true;
                        $cronJobLastRun = $lastRunTime;
                    }
                }
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }
        
        // Method 2: Check cache for schedule:run indicator
        if (!$cronJobActive) {
            try {
                $scheduleRunCache = \Illuminate\Support\Facades\Cache::get('schedule:run:last');
                if ($scheduleRunCache) {
                    $scheduleRunTime = \Carbon\Carbon::parse($scheduleRunCache);
                    $minutesSinceScheduleRun = now()->diffInMinutes($scheduleRunTime, false);
                    if ($minutesSinceScheduleRun >= 0 && $minutesSinceScheduleRun <= 5) {
                        $cronJobActive = true;
                        $cronJobLastRun = $scheduleRunTime;
                    }
                }
            } catch (\Exception $e) {
                // Cache might not be available
            }
        }
        
        // Method 3: Check if any scheduled task is active (fallback detection)
        // If individual tasks are running, the cron job is likely configured
        if (!$cronJobActive) {
            foreach ($cronJobs as $job) {
                if (isset($job['status']['active']) && $job['status']['active'] === true) {
                    // If any task is active, assume cron is running
                    $cronJobActive = true;
                    if ($job['status']['last_run']) {
                        $cronJobLastRun = $job['status']['last_run'];
                    }
                    break;
                }
            }
        }

        // Generate the cron command
        $domain = url('/');
        $phpPath = PHP_BINARY; // Use PHP binary path
        
        // For cPanel: Command only (schedule is set separately in cPanel UI)
        $cronCommand = 'curl -s ' . $domain . '/cron > /dev/null 2>&1';
        
        // Full command with schedule (for standard cron/crontab)
        $cronCommandFull = '* * * * * curl -s ' . $domain . '/cron > /dev/null 2>&1';
        
        // Alternative: Direct PHP command (if curl is not available)
        $phpCommand = $phpPath . ' ' . base_path('artisan') . ' schedule:run >> /dev/null 2>&1';
        $phpCommandFull = '* * * * * ' . $phpCommand;

        // Optional: Run only startup website check weekly (for cPanel - copy and set schedule to 0 0 * * 0)
        $startupCheckCronCommand = 'cd ' . base_path() . ' && ' . $phpPath . ' artisan startups:check-websites >> /dev/null 2>&1';
        $startupCheckCronFull = '0 0 * * 0 ' . $startupCheckCronCommand;

        return view('admin.setting.cronjob', compact(
            'pageTitle',
            'cronJobs',
            'cronjobSettings',
            'pendingAuctions',
            'cronJobActive',
            'cronJobLastRun',
            'cronCommand',
            'cronCommandFull',
            'phpCommand',
            'phpCommandFull',
            'startupCheckCronCommand',
            'startupCheckCronFull'
        ));
    }

    public function viewLog($jobName)
    {
        $cronJobs = $this->getAllCronJobs();
        $job = collect($cronJobs)->firstWhere('name', urldecode($jobName));
        
        if (!$job || !$job['log_file']) {
            abort(404);
        }

        $logFile = storage_path('logs/' . $job['log_file']);
        $logExists = file_exists($logFile);
        $logSize = $logExists ? filesize($logFile) : 0;
        $logLastModified = $logExists ? filemtime($logFile) : null;

        $recentLogs = [];
        if ($logExists && $logSize > 0) {
            $logContent = file_get_contents($logFile);
            $logLines = explode("\n", $logContent);
            $recentLogs = array_slice(array_filter($logLines), -200);
            $recentLogs = array_reverse($recentLogs);
        }

        $pageTitle = 'Logs: ' . $job['name'];

        return view('admin.setting.cronjob-log', compact(
            'pageTitle',
            'job',
            'logExists',
            'logSize',
            'logLastModified',
            'recentLogs'
        ));
    }

    public function update(Request $request)
    {
        // Settings removed - auction processing is always enabled
        // This method kept for backward compatibility but does nothing
        $notify[] = ['info', 'Auction processing is always enabled and cannot be disabled.'];
        return back()->withNotify($notify);
    }

    public function runAuctionProcessing(Request $request)
    {
        try {
            $minutes = $request->input('minutes', 60);
            
            // Run the command
            Artisan::call('auctions:process-ending', [
                '--minutes' => $minutes
            ]);

            $output = Artisan::output();

            // Update last run time
            $general = gs();
            $general->last_auction_processing_run = now();
            $general->save();

            $notify[] = ['success', 'Auction processing command executed successfully'];
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Auction processing executed successfully',
                    'output' => $output
                ]);
            }

            return back()->withNotify($notify);
        } catch (\Exception $e) {
            Log::error('Manual auction processing failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $notify[] = ['error', 'Failed to execute auction processing: ' . $e->getMessage()];
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute: ' . $e->getMessage()
                ], 500);
            }

            return back()->withNotify($notify);
        }
    }

    public function clearLogs(Request $request)
    {
        try {
            $jobName = $request->input('job');
            $cronJobs = $this->getAllCronJobs();
            $job = collect($cronJobs)->firstWhere('name', $jobName);
            
            if (!$job || !$job['log_file']) {
                $notify[] = ['error', 'Invalid job specified'];
                return back()->withNotify($notify);
            }

            $logFile = storage_path('logs/' . $job['log_file']);
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
            }

            $notify[] = ['success', 'Log file cleared successfully'];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            $notify[] = ['error', 'Failed to clear log file: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function getStatus()
    {
        $pendingAuctions = \App\Models\Listing::where('sale_type', 'auction')
            ->where('status', \App\Constants\Status::LISTING_ACTIVE)
            ->whereNotNull('auction_end')
            ->where('auction_end', '<=', now())
            ->count();

        $general = gs();
        $lastRun = $general->last_auction_processing_run;

        return response()->json([
            'pending_auctions' => $pendingAuctions,
            'last_run' => $lastRun ? $lastRun->format('Y-m-d H:i:s') : null,
            'enabled' => true, // Always enabled
        ]);
    }
}

