<h1 class="dash-page-title">Database migrations</h1>
<div class="dash-welcome">
  Run, rollback, or download a SQL backup of your database schema. Check the boxes below and click Confirm to run.
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
    <div class="dash-kpi-value">{{ count($migrationFiles) }}</div>
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
      <button type="button" class="dash-btn dash-btn-secondary" onclick="refreshStatus()">
        <i class="fa-solid fa-arrows-rotate"></i> Refresh status
      </button>
      <a class="dash-btn dash-btn-secondary" href="{{ route('admin.migration.download.sql') }}" style="text-decoration: none;">
        <i class="fa-solid fa-download"></i> Download SQL backup
      </a>
      <button type="button" class="dash-btn" style="background: #dc2626; color: #fff; border: none;" onclick="rollbackMigrations()">
        <i class="fa-solid fa-rotate-left"></i> Rollback last batch
      </button>
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
    <strong>Modified migrations</strong>
    <p style="margin: 4px 0 12px 0; color: var(--d-text-secondary); font-size: 0.9rem;">These were changed after they were run.</p>
    <ul style="margin: 0; padding: 0; list-style: none;">
      @foreach($modifiedMigrations as $migration)
      <li style="margin-bottom: 8px;">
        <strong>{{ $migration['migration_name'] }}</strong>
        <button type="button" class="dash-btn dash-btn-secondary" style="margin-left: 8px; padding: 4px 10px; font-size: 0.8rem;" onclick="runSpecificMigration('{{ $migration['migration_name'] }}')">
          <i class="fa-solid fa-play"></i> Rerun
        </button>
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
              if (isset($mod['migration_name']) && $mod['migration_name'] === $migrationName) {
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
            <td>{{ date('Y-m-d H:i:s', $file['modified']) }}</td>
            <td>
              @if(!$isRan)
                <button type="button" class="dash-btn dash-btn-primary" style="padding: 4px 10px; font-size: 0.8rem;" onclick="runSpecificMigration('{{ $migrationName }}')">
                  <i class="fa-solid fa-play"></i> Run
                </button>
              @elseif($isModified)
                <button type="button" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;" onclick="runSpecificMigration('{{ $migrationName }}')">
                  <i class="fa-solid fa-rotate-right"></i> Rerun
                </button>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Confirmation modal -->
<div class="dash-modal" id="confirmModal" tabindex="-1" aria-hidden="true" style="display: none; position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.4); align-items: center; justify-content: center; padding: 24px;">
  <div style="background: var(--d-surface); border-radius: var(--d-radius); box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-width: 440px; width: 100%; overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--d-border); display: flex; align-items: center; justify-content: space-between;">
      <h2 style="margin: 0; font-size: 1.25rem;">Confirm action</h2>
      <button type="button" onclick="hideConfirmModal()" aria-label="Close" style="width: 36px; height: 36px; border: none; background: transparent; border-radius: 50%; cursor: pointer; color: var(--d-text-secondary); font-size: 1.25rem;">×</button>
    </div>
    <div style="padding: 24px;">
      <p id="confirmMessage" style="margin: 0 0 20px 0; color: var(--d-text);"></p>
      <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; cursor: pointer;">
        <input type="checkbox" id="confirmCheck" required style="width: 18px; height: 18px;">
        <span>I understand and want to proceed</span>
      </label>
      @if(app()->environment('production'))
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
        <input type="checkbox" id="forceCheck" style="width: 18px; height: 18px;">
        <span><strong>Force (production mode)</strong></span>
      </label>
      @endif
    </div>
    <div style="padding: 16px 24px; border-top: 1px solid var(--d-border); display: flex; justify-content: flex-end; gap: 12px;">
      <button type="button" class="dash-btn dash-btn-secondary" onclick="hideConfirmModal()">Cancel</button>
      <button type="button" class="dash-btn dash-btn-primary" id="confirmBtn">Confirm</button>
    </div>
  </div>
</div>
