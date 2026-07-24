<div class="dash-card startup-form-card">
  <div class="dash-card-header">
    <span class="dash-card-title">{{ ($isAdmin ?? false) ? 'Editorial profile' : 'Product story' }}</span>
    <span class="startup-form-completeness">{{ ($isAdmin ?? false) ? 'Completeness' : 'Profile completeness' }}: {{ $startup->exists ? $startup->content_completeness_score : 0 }}%</span>
  </div>
  <div class="dash-card-body startup-form-stack">
    <div>
      <label for="problem_solved" class="dash-label">Problem solved</label>
      <textarea id="problem_solved" name="problem_solved" rows="4" maxlength="3000" @if($requiresEditorialContent) required minlength="80" @endif class="dash-input">{{ old('problem_solved', $startup->problem_solved) }}</textarea>
      @error('problem_solved') <span class="dash-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label for="target_customer" class="dash-label">Target customer</label>
      <textarea id="target_customer" name="target_customer" rows="3" maxlength="1500" @if($requiresEditorialContent) required minlength="40" @endif class="dash-input">{{ old('target_customer', $startup->target_customer) }}</textarea>
      @error('target_customer') <span class="dash-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label class="dash-label">Key features</label>
      @php $profileFeatures = old('key_features', $startup->key_features ?: ['', '', '']); @endphp
      <div id="startup-features-list" class="startup-features-grid" data-min-features="3" data-max-features="8">
        @for($featureIndex = 0; $featureIndex < max(3, count($profileFeatures)); $featureIndex++)
        <div class="startup-feature-field">
          <input type="text" name="key_features[]" maxlength="180" @if($requiresEditorialContent) required minlength="5" @endif class="dash-input" placeholder="Feature {{ $featureIndex + 1 }}" aria-label="Feature {{ $featureIndex + 1 }}" value="{{ $profileFeatures[$featureIndex] ?? '' }}">
          <button type="button" class="startup-feature-remove" aria-label="Remove feature {{ $featureIndex + 1 }}" title="Remove feature"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endfor
      </div>
      <button type="button" id="startup-feature-add" class="dash-btn dash-btn-secondary startup-feature-add"><i class="fa-solid fa-plus"></i> Add feature</button>
      <p class="dash-hint">Add up to 8 key features.</p>
      @error('key_features') <span class="dash-error">{{ $message }}</span> @enderror
      @error('key_features.*') <span class="dash-error">{{ $message }}</span> @enderror
    </div>
    <div class="startup-form-grid">
      <div>
        <label for="pricing_model" class="dash-label">Pricing or business model</label>
        <input id="pricing_model" name="pricing_model" maxlength="120" class="dash-input" value="{{ old('pricing_model', $startup->pricing_model) }}">
      </div>
      <div>
        <label for="markets_served" class="dash-label">Markets served</label>
        <input id="markets_served" name="markets_served" maxlength="500" class="dash-input" value="{{ old('markets_served', $startup->markets_served) }}">
      </div>
    </div>
    <div>
      <label for="traction" class="dash-label">Traction or proof</label>
      <textarea id="traction" name="traction" rows="3" maxlength="3000" class="dash-input">{{ old('traction', $startup->traction) }}</textarea>
    </div>
    <div>
      <label for="founder_story" class="dash-label">Founder story</label>
      <textarea id="founder_story" name="founder_story" rows="4" maxlength="5000" class="dash-input">{{ old('founder_story', $startup->founder_story) }}</textarea>
    </div>
    @if($isAdmin ?? false)
    <div>
      <label for="editorial_reviewed_at" class="dash-label">Editorially reviewed at</label>
      <input type="datetime-local" id="editorial_reviewed_at" name="editorial_reviewed_at" class="dash-input" value="{{ old('editorial_reviewed_at', $startup->editorial_reviewed_at?->format('Y-m-d\\TH:i')) }}">
      <p class="dash-hint">Set this only after checking the claims, links and originality of the profile.</p>
    </div>
    @endif
  </div>
</div>
