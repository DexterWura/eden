<h1 class="dash-page-title">Import subscribers</h1>
<div class="dash-welcome">
  Upload a CSV or text file with one email per row, or a spreadsheet column named &quot;email&quot;. Duplicates and invalid addresses are skipped.
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Upload file</span>
  </div>
  <div class="dash-card-body">
    <form action="{{ route('admin.subscribers.import.store') }}" method="post" enctype="multipart/form-data" class="dash-form">
      @csrf
      <div style="margin-bottom: 16px;">
        <label for="file" class="dash-label">CSV or text file</label>
        <input type="file" id="file" name="file" accept=".csv,.txt" class="dash-input" required>
        @error('file') <span class="dash-error">{{ $message }}</span> @enderror
        <p class="dash-hint" style="margin-top: 8px;">Max 2 MB. Use a column header containing &quot;email&quot; or the first column will be treated as email.</p>
      </div>
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-file-import"></i> Import</button>
        <a href="{{ route('admin.subscribers.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none;">Cancel</a>
      </div>
    </form>
  </div>
</div>
