<h1 class="dash-page-title">Scheduled tasks</h1>
<div class="dash-welcome">
  Configure how often tasks (e.g. sitemap) run automatically. To run them in production you must configure a cron job to execute
  <code>php artisan schedule:run</code> every minute. Once cron is correctly configured, each enabled task on this page will run at its interval
  and the <strong>Scheduler</strong> column will show whether it has run recently.
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header"><span class="dash-card-title">How to enable scheduled tasks (cron)</span></div>
  <div class="dash-card-body">
    <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem; color: var(--d-text-secondary); display: flex; flex-direction: column; gap: 6px;">
      <li>
        On your server, open the crontab for the web user (for example: <code>crontab -e</code>).
      </li>
      <li>
        Add this line so Laravel&rsquo;s scheduler runs every minute:
        <div style="margin-top: 4px;">
          <code>* * * * * cd {{ base_path() }} &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>
        </div>
      </li>
      <li>
        Save the crontab. After a few minutes, enabled tasks should show <strong>On · recent run</strong> in the Scheduler column below.
      </li>
      <li>
        If a task stays on <strong>No recent runs</strong>, check that cron is active and that the PHP path in your cron entry is correct (for example <code>/usr/bin/php</code>).
      </li>
    </ol>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header"><span class="dash-card-title">Tasks</span></div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Task</th>
            <th>Interval</th>
            <th>Enabled</th>
            <th>Scheduler</th>
            <th>Last run</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($tasks as $task)
          <tr>
            <td>
              <strong>{{ $task->display_name }}</strong>
              @if($task->description)
                <div style="font-size: 0.8rem; color: var(--d-text-secondary); margin-top: 2px;">{{ $task->description }}</div>
              @endif
            </td>
            <td>
              <form action="{{ route('admin.scheduled-tasks.update', $task) }}" method="post" style="display: inline;" class="scheduled-task-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="is_enabled" value="{{ $task->is_enabled ? '1' : '0' }}">
                <select name="interval_minutes" class="dash-input" style="width: auto; padding: 6px 10px; font-size: 0.85rem;" onchange="this.form.submit();">
                  @foreach($intervalOptions as $minutes => $label)
                    <option value="{{ $minutes }}" {{ (int)$task->interval_minutes === (int)$minutes ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </form>
            </td>
            <td>
              <form action="{{ route('admin.scheduled-tasks.update', $task) }}" method="post" style="display: inline;" class="scheduled-task-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="interval_minutes" value="{{ $task->interval_minutes }}">
                <input type="hidden" name="is_enabled" value="0">
                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                  <input type="checkbox" name="is_enabled" value="1" {{ $task->is_enabled ? 'checked' : '' }} onchange="this.form.submit();">
                  <span style="font-size: 0.875rem;">On</span>
                </label>
              </form>
            </td>
            <td>
              @php
                $lastRun = $task->last_run_at;
                $isEnabled = $task->is_enabled;
                $interval = (int) $task->interval_minutes;
                $healthy = false;
                $stale = false;
                if ($isEnabled) {
                    if ($lastRun) {
                        $diffMinutes = $lastRun->diffInMinutes(now());
                        $threshold = max($interval * 2, $interval + 10);
                        $healthy = $diffMinutes <= $threshold;
                        $stale = ! $healthy;
                    } else {
                        $stale = true;
                    }
                }
              @endphp

              @if (! $isEnabled)
                <span class="dash-badge dash-badge-muted">Off</span>
              @elseif ($healthy)
                <span class="dash-badge dash-badge-success" title="Scheduler has run this task recently.">On · recent run</span>
              @elseif ($stale)
                <span class="dash-badge dash-badge-warning" title="This enabled task has not run recently. Check your cron configuration (php artisan schedule:run).">No recent runs</span>
              @else
                <span style="color: var(--d-text-secondary); font-size: 0.875rem;">—</span>
              @endif
            </td>
            <td>
              @if($task->last_run_at)
                <span title="{{ $task->last_run_at->format('Y-m-d H:i:s') }}">{{ $task->last_run_at->diffForHumans() }}</span>
              @else
                <span style="color: var(--d-text-secondary);">Never</span>
              @endif
            </td>
            <td>
              @if($task->last_status === 'success')
                <span class="dash-badge dash-badge-success">Success</span>
              @elseif($task->last_status === 'failed')
                <span class="dash-badge dash-badge-danger">Failed</span>
                @if($task->last_message)
                  <div style="font-size: 0.75rem; color: var(--d-text-secondary); margin-top: 2px;" title="{{ $task->last_message }}">{{ Str::limit($task->last_message, 40) }}</div>
                @endif
              @else
                <span style="color: var(--d-text-secondary); font-size: 0.875rem;">—</span>
              @endif
            </td>
            <td>
              <form action="{{ route('admin.scheduled-tasks.run', $task) }}" method="post" style="display: inline;">
                @csrf
                <button type="submit" class="dash-btn dash-btn-primary" style="padding: 4px 10px; font-size: 0.8rem;">Run now</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@if($tasks->isEmpty())
<p class="dash-placeholder" style="padding: 24px;">No scheduled tasks. Run migrations to seed the sitemap task.</p>
@endif

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header"><span class="dash-card-title">Sitemap</span></div>
  <div class="dash-card-body">
    <p style="margin: 0 0 8px; font-size: 0.875rem; color: var(--d-text-secondary);">When the Sitemap task runs, it generates <code>public/sitemap.xml</code> with the homepage, about, contact, submit, categories, launching-today, leaderboard, and all active startup URLs. Serve it at <a href="{{ url('/sitemap.xml') }}" target="_blank" rel="noopener">{{ url('/sitemap.xml') }}</a>.</p>
  </div>
</div>

<style>
.dash-badge { display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-danger { background: #fee2e2; color: #991b1b; }
.dash-badge-warning { background: #fef3c7; color: #92400e; }
.dash-badge-muted { background: #e5e7eb; color: #374151; }
</style>
