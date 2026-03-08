<h1 class="dash-page-title">Contact message</h1>
<div class="dash-welcome">
  View the original message and send a reply email directly from Eden.
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Original message</span>
  </div>
  <div class="dash-card-body">
    <dl style="display:grid;grid-template-columns:minmax(0,140px) minmax(0,1fr);gap:8px 16px;font-size:0.9rem;">
      <dt style="font-weight:600;color:var(--d-text-secondary);">Date</dt>
      <dd>{{ $message->created_at->format('M j, Y H:i') }}</dd>

      <dt style="font-weight:600;color:var(--d-text-secondary);">Name</dt>
      <dd>{{ $message->name }}</dd>

      <dt style="font-weight:600;color:var(--d-text-secondary);">Email</dt>
      <dd><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></dd>

      <dt style="font-weight:600;color:var(--d-text-secondary);">Subject</dt>
      <dd>{{ $message->subject ?: '—' }}</dd>

      <dt style="font-weight:600;color:var(--d-text-secondary);align-self:start;">Message</dt>
      <dd style="white-space:pre-wrap;">{{ $message->message }}</dd>
    </dl>
  </div>
</div>

<div class="dash-card" style="margin-top:20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">Reply by email</span>
  </div>
  <div class="dash-card-body">
    @if($message->replied_at)
      <p class="dash-hint" style="margin-bottom:12px;">Last reply sent {{ $message->replied_at->diffForHumans() }} with subject <strong>{{ $message->reply_subject }}</strong>. You can send another reply if needed.</p>
    @else
      <p class="dash-hint" style="margin-bottom:12px;">This will send an email to <strong>{{ $message->email }}</strong> using the global email template and <code>DEFAULT</code> notification layout.</p>
    @endif
    <form action="{{ route('admin.contact-messages.reply', $message) }}" method="post" class="dash-form" style="display:flex;flex-direction:column;gap:14px;max-width:640px;">
      @csrf
      <div>
        <label for="subject" class="dash-label">Subject</label>
        <input type="text" id="subject" name="subject" class="dash-input" value="{{ old('subject', $message->reply_subject ?: ('Re: ' . ($message->subject ?: 'Your message to Eden'))) }}">
        @error('subject') <span class="dash-error">{{ $errors->first('subject') }}</span> @enderror
      </div>
      <div>
        <label for="body" class="dash-label">Message</label>
        <textarea id="body" name="body" rows="8" class="dash-input" placeholder="Write your reply…">@php
          $defaultBody = $message->reply_body ?: 'Hi ' . $message->name . ",\n\nThanks for reaching out.\n\n";
        @endphp{{ old('body', $defaultBody) }}</textarea>
        @error('body') <span class="dash-error">{{ $errors->first('body') }}</span> @enderror
        <span class="dash-hint" style="display:block;margin-top:4px;font-size:0.8rem;color:var(--d-text-secondary);">
          The content you write here will be inserted into the global email template as {{ '{{' }}{{ 'message' }}{{ '}}' }}.
        </span>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button type="submit" class="dash-btn dash-btn-primary">
          <i class="fa-solid fa-paper-plane"></i> Send reply
        </button>
        <a href="{{ route('admin.contact-messages.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration:none;">
          Back to all messages
        </a>
      </div>
    </form>
  </div>
</div>

