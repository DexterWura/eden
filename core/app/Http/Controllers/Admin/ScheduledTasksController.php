<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTask;
use App\Services\SitemapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ScheduledTasksController extends Controller
{
    public function index(): Response
    {
        $tasks = ScheduledTask::orderBy('name')->get();
        $content = view('eden.scheduled-tasks.index', [
            'tasks' => $tasks,
            'intervalOptions' => $this->intervalOptions(),
        ])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Scheduled tasks',
            'sidebar' => 'admin',
            'activeNav' => 'scheduled-tasks',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    public function update(Request $request, ScheduledTask $task): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'interval_minutes' => 'required|integer|in:' . implode(',', array_keys($this->intervalOptions())),
            'is_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.scheduled-tasks.index')
                ->with('notify', [['error', 'Invalid interval.']])
                ->withErrors($validator);
        }

        $task->update([
            'interval_minutes' => (int) $request->interval_minutes,
            'is_enabled' => $request->boolean('is_enabled', true),
        ]);

        return redirect()->route('admin.scheduled-tasks.index')
            ->with('notify', [['success', 'Task updated.']]);
    }

    public function runNow(ScheduledTask $task): RedirectResponse
    {
        try {
            if ($task->name === 'sitemap') {
                app(SitemapService::class)->generate();
                $task->markSuccess('Sitemap generated manually.');
            } else {
                $task->markFailed('Unknown task.');
                return redirect()->route('admin.scheduled-tasks.index')
                    ->with('notify', [['error', 'Unknown task.']]);
            }
        } catch (\Throwable $e) {
            $task->markFailed($e->getMessage());
            return redirect()->route('admin.scheduled-tasks.index')
                ->with('notify', [['error', 'Task failed: ' . $e->getMessage()]]);
        }

        return redirect()->route('admin.scheduled-tasks.index')
            ->with('notify', [['success', 'Task ran successfully.']]);
    }

    private function intervalOptions(): array
    {
        return [
            15 => 'Every 15 minutes',
            30 => 'Every 30 minutes',
            60 => 'Every hour',
            360 => 'Every 6 hours',
            720 => 'Every 12 hours',
            1440 => 'Daily',
            10080 => 'Weekly',
        ];
    }
}
