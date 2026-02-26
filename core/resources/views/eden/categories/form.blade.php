<h1 class="dash-page-title">{{ isset($category->id) ? 'Edit category' : 'Add category' }}</h1>
<div class="dash-welcome">
  {{ isset($category->id) ? 'Update the category name, icon, or sort order.' : 'Create a new category. Name will be used in dropdowns and the slug in URLs.' }}
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">{{ isset($category->id) ? 'Edit category' : 'New category' }}</span>
  </div>
  <div class="dash-card-body">
    <form action="{{ isset($category->id) ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="post" class="dash-form">
      @csrf
      @if(isset($category->id)) @method('PUT') @endif
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div>
          <label for="name" class="dash-label">Name</label>
          <input type="text" id="name" name="name" class="dash-input" value="{{ old('name', $category->name ?? '') }}" placeholder="e.g. SaaS" required maxlength="64">
          @error('name') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 6px;">Display name shown in forms and on the site.</p>
        </div>
        <div>
          <label for="icon" class="dash-label">Icon (Font Awesome class)</label>
          <input type="text" id="icon" name="icon" class="dash-input" value="{{ old('icon', $category->icon ?? '') }}" placeholder="e.g. fa-solid fa-cloud" maxlength="64">
          @error('icon') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 6px;">Optional. Use Font Awesome class (e.g. <code>fa-solid fa-rocket</code>).</p>
        </div>
        <div>
          <label for="sort_order" class="dash-label">Sort order</label>
          <input type="number" id="sort_order" name="sort_order" class="dash-input" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0" step="1">
          @error('sort_order') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 6px;">Lower numbers appear first.</p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> {{ isset($category->id) ? 'Save changes' : 'Create category' }}</button>
          <a href="{{ route('admin.categories.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none;">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>
