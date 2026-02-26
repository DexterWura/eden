<?php

namespace App\Console\Commands;

use App\Models\ScheduledTask;
use App\Services\SitemapService;
use Illuminate\Console\Command;

class RunScheduledTasksCommand extends Command
{
    protected $signature = 'eden:run-scheduled-tasks';

    protected $description = 'Run Eden scheduled tasks that are due (sitemap, etc.)';

    public function handle(): int
    {
        $tasks = ScheduledTask::where('is_enabled', true)->get();

        foreach ($tasks as $task) {
            if (! $task->isDue()) {
                continue;
            }

            try {
                $this->runTask($task);
            } catch (\Throwable $e) {
                $task->markFailed($e->getMessage());
                $this->error("Task [{$task->name}] failed: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    private function runTask(ScheduledTask $task): void
    {
        switch ($task->name) {
            case 'sitemap':
                $service = app(SitemapService::class);
                $path = $service->generate();
                $task->markSuccess('Sitemap written to ' . $path);
                $this->info("Task [{$task->name}] completed.");
                break;
            default:
                $task->markFailed('Unknown task: ' . $task->name);
        }
    }
}
