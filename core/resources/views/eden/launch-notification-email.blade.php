<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1a1a1a;">
  <h1 style="font-size: 1.25rem; margin-bottom: 16px;">{{ $startupName }} is now live</h1>
  @if($tagline)
  <p style="color: #555; margin-bottom: 20px;">{{ \Illuminate\Support\Str::limit($tagline, 200) }}</p>
  @endif
  <p style="margin-bottom: 24px;"><a href="{{ $startupUrl }}" style="color: #2563eb; font-weight: 600;">View on {{ $siteName }} →</a></p>
  <p style="font-size: 0.8125rem; color: #888;">You received this because you asked to be notified when {{ $startupName }} launched on {{ $siteName }}.</p>
</div>
