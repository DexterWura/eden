<h1 class="dash-page-title">Database migrations</h1>
<div class="dash-welcome">
  Review immutable execution checksums and run pending migrations. Applied files are never rerun; drift is repaired with a new forward migration.
</div>

@if(!$migrationsTableExists)
<div class="dash-card" style="border-color: #f59e0b;">
  <div class="dash-card-body">
    <p style="margin-bottom: 12px;"><strong>Migrations table not found.</strong> Run the migration installer first.</p>
    <button type="button" class="dash-btn dash-btn-primary" onclick="installMigrationsTable()">
      <i class="fa-solid fa-database"></i> Install migrations table
    </button>
  </div>
</div>
@endif

<div class="dash-kpi-row">
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Pending</div>
    <div class="dash-kpi-value">{{ count($pendingMigrations) }}</div>
    <div class="dash-kpi-spark"></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Modified</div>
    <div class="dash-kpi-value">{{ count($modifiedMigrations) }}</div>
    <div class="dash-kpi-spark"></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Ran</div>
    <div class="dash-kpi-value">{{ count($ranMigrations) }}</div>
    <div class="dash-kpi-spark"></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Total</div>
    <div class="dash-kpi-value">{{ $migrationFiles->total() }}</div>
    <div class="dash-kpi-spark"></div>
  </div>
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Actions</span>
  </div>
  <div class="dash-card-body">
    <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
      <button type="button" class="dash-btn dash-btn-primary" onclick="runMigrations()">
        <i class="fa-solid fa-play"></i> Run pending migrations
      </button>
      <button type="button" class="dash-btn dash-btn-secondary" onclick="checkStatus()">
        <i class="fa-solid fa-arrows-rotate"></i> Check status
      </button>
      <a class="dash-btn dash-btn-secondary" href="{{ route('admin.migration.download.sql') }}" style="text-decoration: none;">
        <i class="fa-solid fa-download"></i> Download SQL backup
      </a>
      @unless(app()->environment('production'))
      <button type="button" class="dash-btn" style="background: #dc2626; color: #fff; border: none;" onclick="rollbackMigrations()">
        <i class="fa-solid fa-rotate-left"></i> Rollback last batch
      </button>
      @endunless
    </div>
  </div>
</div>

@if(count($pendingMigrations) > 0)
<div class="dash-card" style="border-left: 4px solid var(--d-primary);">
  <div class="dash-card-body">
    <strong>Pending migrations</strong>
    <ul style="margin: 8px 0 0 20px; padding: 0;">
      @foreach($pendingMigrations as $migration)
      <li>{{ $migration['name'] }}</li>
      @endforeach
    </ul>
  </div>
</div>
@endif

@if(count($modifiedMigrations) > 0)
<div class="dash-card" style="border-left: 4px solid #f59e0b;">
  <div class="dash-card-body">
    <strong>Migration drift requires review</strong>
    <p style="margin: 4px 0 12px 0; color: var(--d-text-secondary); font-size: 0.9rem;">Never edit, rollback, or rerun an applied migration in production. Restore the original file, or create a new additive repair migration for any required database change.</p>
    <ul style="margin: 0; padding: 0; list-style: none;">
      @foreach($modifiedMigrations as $migration)
      <li style="margin-bottom: 8px;">
        <strong>{{ $migration['name'] }}</strong>
        <span style="margin-left: 8px;">{{ str_replace('_', ' ', ucfirst($migration['state'])) }}</span>
        @php
          $repairName = 'repair_' . preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migration['name']);
        @endphp
        <div style="margin-top: 4px; color: var(--d-text-secondary); font-size: 0.85rem;">
          Repair workflow: restore the deployed file if it changed accidentally. If the database needs a correction, run
          <code>php artisan make:migration {{ $repairName }}</code>, add only the forward correction, review it, deploy it, then run pending migrations.
        </div>
      </li>
      @endforeach
    </ul>
  </div>
</div>
@endif

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">All migrations</span>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Migration name</th>
            <th>Status</th>
            <th>Batch</th>
            <th>Size</th>
            <th>Modified</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($migrationFiles as $file)
          @php
            $migrationName = str_replace('.php', '', $file['name']);
            $isRan = isset($migrationStatus[$migrationName]);
            $isModified = false;
            foreach ($modifiedMigrations as $mod) {
              if (($mod['name'] ?? null) === $migrationName) {
                $isModified = true;
                break;
              }
            }
          @endphp
          <tr>
            <td>{{ $file['name'] }}</td>
            <td>
              @if($isModified)
                <span style="display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; background: #fef3c7; color: #92400e;">Modified</span>
              @elseif($isRan)
                <span style="display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; background: #d1fae5; color: #065f46;">Ran</span>
              @else
                <span style="display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; background: #fee2e2; color: #991b1b;">Pending</span>
              @endif
            </td>
            <td>{{ $isRan ? $migrationStatus[$migrationName] : '—' }}</td>
            <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
            <td>{{ $file['modified_at'] }}</td>
            <td>
              @if(!$isRan)
                <button type="button" class="dash-btn dash-btn-primary" style="padding: 4px 10px; font-size: 0.8rem;" onclick="runSpecificMigration('{{ $migrationName }}')">
                  <i class="fa-solid fa-play"></i> Run
                </button>
              @elseif($isModified)
                <span style="color: #92400e; font-size: 0.8rem;">Create repair migration</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($migrationFiles->hasPages())
    <div class="dash-card-footer" style="padding: 12px 20px; border-top: 1px solid var(--d-border);">
      {{ $migrationFiles->links() }}
    </div>
    @endif
  </div>
</div>

<div class="dash-modal" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle" aria-describedby="confirmMessage" hidden>
  <div class="dash-dialog-backdrop"></div>
  <div class="dash-dialog-box">
    <div class="dash-dialog-header">
      <h2 id="confirmModalTitle" class="dash-dialog-title">Confirm action</h2>
      <button type="button" onclick="hideConfirmModal()" aria-label="Close" class="dash-dialog-close">×</button>
    </div>
    <div class="dash-dialog-content">
      <p id="confirmMessage" class="dash-dialog-confirm-message"></p>
      <label class="dash-dialog-check">
        <input type="checkbox" id="confirmCheck" required data-dialog-initial-focus>
        <span>I understand and want to proceed</span>
      </label>
    </div>
    <div class="dash-dialog-actions">
      <button type="button" class="dash-btn dash-btn-secondary" onclick="hideConfirmModal()">Cancel</button>
      <button type="button" class="dash-btn dash-btn-primary" id="confirmBtn">Confirm</button>
    </div>
  </div>
</div>

<script>
(function () {
  var runBtn = document.querySelector('button[onclick="runMigrations()"]');
  if (!runBtn) return;
  runBtn.addEventListener('click', function (e) {
    if (typeof window.runMigrations !== 'function') {
      e.preventDefault();
      e.stopPropagation();
      if (typeof notify === 'function') notify('error', 'Migration controls did not load. Refresh the page before retrying.');
    }
  });
})();
</script>
