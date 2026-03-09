<h1 class="dash-page-title">Scheduled tasks</h1>
<div class="dash-welcome">
  Configure how often tasks (e.g. sitemap) run automatically. To run them in production you must add a cron job that calls your site&rsquo;s <code>/cron</code> URL every minute. Once cron is correctly configured, each enabled task on this page will run at its interval
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
        Add this line so the scheduler runs every minute (replace the URL with your site&rsquo;s URL if different):
        @php
          $cronSecret = env('CRON_SECRET');
          $cronUrl = url('/cron');
          if (!empty($cronSecret)) {
            $cronUrl .= '?secret=' . urlencode($cronSecret);
          }
          $cronCommand = '* * * * * curl -s ' . $cronUrl . ' > /dev/null 2>&1';
        @endphp
        <div class="cron-command-row" style="margin-top: 8px; display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
          <code id="cronCommandCode">* * * * * curl -s {{ $cronUrl }} &gt; /dev/null 2&gt;&amp;1</code>
          <button type="button" class="dash-btn dash-btn-secondary cron-copy-btn" style="padding: 4px 12px; font-size: 0.8125rem;" data-cron-command="{{ $cronCommand }}" title="Copy to clipboard">
            <i class="fa-solid fa-copy" aria-hidden="true"></i> Copy
          </button>
          <span class="cron-copy-feedback" style="font-size: 0.8125rem; color: var(--d-primary); display: none;">Copied!</span>
        </div>
      </li>
      <li>
        Save the crontab. After a few minutes, enabled tasks should show <strong>On · recent run</strong> in the Scheduler column below.
      </li>
      <li>
        If a task stays on <strong>No recent runs</strong>, check that cron is active and that <code>curl</code> can reach your site URL.
      </li>
      @if(empty(env('CRON_SECRET')))
      <li>
        <strong>Security tip:</strong> Add <code>CRON_SECRET=your-secret</code> to your <code>.env</code> file to protect the cron endpoint from unauthorized access. Then use the command shown above (it will include the secret).
      </li>
      @endif
      <li>
        <strong>If you see &ldquo;bad command&rdquo; or &ldquo;Invalid crontab file&rdquo;</strong>: paste can add hidden characters. Type the line by hand in crontab, or copy again from the button above. Use this exact line (one line, no line break):
        <div style="margin-top: 6px; padding: 8px 10px; background: var(--d-bg); border: 1px solid var(--d-border); border-radius: 6px; font-size: 0.8rem; font-family: monospace; word-break: break-all;">* * * * * curl -s {{ $cronUrl }} &gt; /dev/null 2&gt;&amp;1</div>
        If your server needs the full path to curl, use: <code>* * * * * /usr/bin/curl -s {{ $cronUrl }} &gt; /dev/null 2&gt;&amp;1</code>
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

<script>
(function() {
  var btn = document.querySelector('.cron-copy-btn');
  var feedback = document.querySelector('.cron-copy-feedback');
  if (!btn) return;
  btn.addEventListener('click', function() {
    var cmd = (this.getAttribute('data-cron-command') || '').replace(/\r\n/g, '\n').replace(/\r/g, '').trim();
    if (!cmd) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(cmd).then(function() {
        if (feedback) { feedback.style.display = 'inline'; setTimeout(function() { feedback.style.display = 'none'; }, 2000); }
      }).catch(function() { fallbackCopy(cmd); });
    } else {
      fallbackCopy(cmd);
    }
  });
  function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed'; ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
      if (feedback) { feedback.style.display = 'inline'; setTimeout(function() { feedback.style.display = 'none'; }, 2000); }
    } catch (e) {}
    document.body.removeChild(ta);
  }
})();
</script>
