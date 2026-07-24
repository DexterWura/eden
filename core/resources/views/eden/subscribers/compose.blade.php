<h1 class="dash-page-title">Compose email</h1>
<div class="dash-welcome">
  Draft your message, choose the audience, then preview and send.
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Compose</span>
  </div>
  <div class="dash-card-body">
    <form id="compose-form" action="{{ route('admin.subscribers.send') }}" method="post" class="dash-form">
      @csrf
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div>
          <label for="subject" class="dash-label">Subject</label>
          <input type="text" id="subject" name="subject" value="{{ old('subject') }}" class="dash-input" placeholder="Email subject" required maxlength="500">
          @error('subject') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="body" class="dash-label">Body</label>
          <textarea id="body" name="body" rows="10" class="dash-input" placeholder="Write your message…" required>{{ old('body') }}</textarea>
          @error('body') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <span class="dash-label">Audience</span>
          <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-top: 8px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="audience" value="founders" {{ old('audience', 'subscribers') === 'founders' ? 'checked' : '' }}>
              Founders (users with at least one app)
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="audience" value="subscribers" {{ old('audience', 'subscribers') === 'subscribers' ? 'checked' : '' }}>
              Subscribers (newsletter list)
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="audience" value="all" {{ old('audience') === 'all' ? 'checked' : '' }}>
              All (founders + subscribers)
            </label>
          </div>
          @error('audience') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <button type="button" id="preview-btn" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-eye"></i> Preview</button>
          <button type="submit" class="dash-btn dash-btn-primary" id="send-btn"><i class="fa-solid fa-paper-plane"></i> Send</button>
          <a href="{{ route('admin.subscribers.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none;">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>

<div id="preview-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: var(--d-bg, #1a1d24); border-radius: 12px; max-width: 640px; width: 100%; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--d-border); display: flex; justify-content: space-between; align-items: center;">
      <strong>Email preview</strong>
      <button type="button" id="preview-close" class="dash-btn dash-btn-secondary" style="padding: 6px 12px;">Close</button>
    </div>
    <div id="preview-content" style="padding: 20px; overflow: auto; flex: 1; background: #fff; color: #1a1a1a;"></div>
  </div>
</div>

<script>
(function() {
  var form = document.getElementById('compose-form');
  var previewBtn = document.getElementById('preview-btn');
  var previewModal = document.getElementById('preview-modal');
  var previewContent = document.getElementById('preview-content');
  var previewClose = document.getElementById('preview-close');
  if (!form || !previewBtn || !previewModal || !previewContent) return;

  function showPreview(html) {
    previewContent.innerHTML = html;
    previewModal.style.display = 'flex';
  }

  previewBtn.addEventListener('click', function() {
    var subject = document.getElementById('subject').value.trim();
    var body = document.getElementById('body').value.trim();
    if (!subject || !body) {
      alert('Please enter subject and body.');
      return;
    }
    var fd = new FormData();
    fd.append('_token', form.querySelector('input[name="_token"]').value);
    fd.append('subject', subject);
    fd.append('body', body);
    fetch('{{ route("admin.subscribers.preview") }}', {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).then(function(r) { return r.json(); }).then(function(data) {
      if (data.html) showPreview(data.html);
    }).catch(function() { alert('Preview failed.'); });
  });

  previewClose.addEventListener('click', function() {
    previewModal.style.display = 'none';
  });
  previewModal.addEventListener('click', function(e) {
    if (e.target === previewModal) previewModal.style.display = 'none';
  });
})();
</script>
