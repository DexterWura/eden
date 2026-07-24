<h1 class="dash-page-title">Categories</h1>
<div class="dash-welcome">
  Manage app categories. These appear in the submit form, filters, and category browse page.
</div>

<div class="dash-card">
  <div class="dash-card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">All categories</span>
    <a href="{{ route('admin.categories.create') }}" class="dash-btn dash-btn-primary" style="text-decoration: none;"><i class="fa-solid fa-plus"></i> Add category</a>
  </div>
  <div class="dash-card-body">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Icon</th>
            <th>Apps</th>
            <th>Editorial completeness</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($categories as $category)
          <tr>
            <td>{{ $category->sort_order }}</td>
            <td>{{ $category->name }}</td>
            <td><code>{{ $category->slug }}</code></td>
            <td>@if($category->icon)<i class="{{ $category->icon }}" aria-hidden="true"></i>@else—@endif</td>
            <td>{{ $category->startups()->count() }}</td>
            @php
              $editorialChecks = [
                'Introduction' => filled($category->introduction),
                'Market context' => filled($category->market_context),
                'FAQ' => count($category->frequently_asked_questions ?? []) > 0,
              ];
              $completeCount = collect($editorialChecks)->filter()->count();
            @endphp
            <td>
              <strong>{{ $completeCount }}/{{ count($editorialChecks) }}</strong>
              <small style="display:block">{{ collect($editorialChecks)->filter(fn($complete) => !$complete)->keys()->implode(', ') ?: 'Complete' }}</small>
            </td>
            <td>
              <a href="{{ route('admin.categories.edit', $category) }}" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem; text-decoration: none;">Edit</a>
              <form action="{{ route('admin.categories.destroy', $category) }}" method="post" style="display: inline;" data-confirm="Delete this category?" data-confirm-label="Delete category">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-btn" style="padding: 4px 10px; font-size: 0.8rem; background: #dc2626; color: #fff; border: none;">Delete</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="dash-placeholder">No categories yet. <a href="{{ route('admin.categories.create') }}">Add one</a>.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
