<div class="dash-card startup-form-card">
  <div class="dash-card-header"><span class="dash-card-title">Basics</span></div>
  <div class="dash-card-body startup-form-stack">
    <div>
      <label for="name" class="dash-label">Startup name <span class="dash-required">*</span></label>
      <input type="text" id="name" name="name" value="{{ old('name', $startup->name) }}" required class="dash-input" placeholder="e.g. Nexus Pay">
      @error('name') <span class="dash-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label for="tagline" class="dash-label">Tagline</label>
      <input type="text" id="tagline" name="tagline" value="{{ old('tagline', $startup->tagline) }}" @if($requiresEditorialContent) required minlength="12" @endif class="dash-input" placeholder="Short one-liner">
      @error('tagline') <span class="dash-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label for="description" class="dash-label">Description</label>
      <textarea id="description" name="description" rows="6" @if($requiresEditorialContent) required minlength="250" @endif class="dash-input" placeholder="What does the startup do?">{{ old('description', $startup->description) }}</textarea>
      @error('description') <span class="dash-error">{{ $message }}</span> @enderror
    </div>
    <div class="startup-form-grid">
      <div>
        <label for="category" class="dash-label">Category</label>
        <select id="category" name="category" class="dash-input">
          <option value="">Choose a category…</option>
          @foreach($categories ?? [] as $cat)
          <option value="{{ $cat->name }}" {{ old('category', $startup->category) === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
          @endforeach
        </select>
        @error('category') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div>
        <label for="location" class="dash-label">Location</label>
        <input type="text" id="location" name="location" value="{{ old('location', $startup->location) }}" class="dash-input" placeholder="e.g. Harare">
        @error('location') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
    </div>
    <div>
      <label for="launch_date" class="dash-label">Launch date</label>
      <input type="date" id="launch_date" name="launch_date" value="{{ old('launch_date', $startup->launch_date ? $startup->launch_date->format('Y-m-d') : '') }}" class="dash-input">
      @error('launch_date') <span class="dash-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label for="logo_path" class="dash-label">Startup logo</label>
      <p class="dash-hint startup-form-hint-top">80×80 px or smaller, square.</p>
      <input type="file" id="logo_path" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
      @error('logo') <span class="dash-error">{{ $message }}</span> @enderror
      @if($startup->logo_path)
        <p class="startup-current-logo"><img src="{{ asset($startup->logo_path) }}" alt="Current logo"> Current logo. @if(!($isAdmin ?? false)) Upload to replace. @endif</p>
      @endif
    </div>
    <div>
      <label class="dash-label">Product images</label>
      <p class="dash-hint">You can select multiple files or add another set.</p>
      <div id="product-images-container">
        <input type="file" name="product_images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple class="dash-input product-images-input">
      </div>
      <button type="button" id="add-more-product-images" class="dash-btn dash-btn-secondary startup-form-add-button"><i class="fa-solid fa-plus"></i> Add more images</button>
      <p id="product-images-summary" class="dash-hint startup-form-summary"></p>
      @error('product_images') <span class="dash-error">{{ $message }}</span> @enderror
      @if(!($isAdmin ?? false) && !empty($startup->product_images))
        <p class="dash-hint">{{ count($startup->product_images) }} image(s) currently. Upload above to add or replace.</p>
      @endif
    </div>
  </div>
</div>
