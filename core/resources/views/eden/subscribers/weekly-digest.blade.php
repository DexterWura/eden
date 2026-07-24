<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1a1a1a;">
  <h1 style="font-size: 1.25rem; margin-bottom: 8px;">New on {{ $siteName }} this week</h1>
  <p style="color: #666; font-size: 0.9375rem; margin-bottom: 20px;">Here are the apps that joined the directory in the last 7 days.</p>
  @if($startups->isEmpty())
  <p style="line-height: 1.6;">No new apps this week. Check back next time!</p>
  <p style="margin-top: 16px;"><a href="{{ url('/') }}" style="color: #2563eb;">Browse all apps →</a></p>
  @else
  <ul style="list-style: none; padding: 0; margin: 0;">
    @foreach($startups as $s)
    <li style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #eee;">
      <a href="{{ url('/startup/' . $s->slug) }}" style="color: #1a1a1a; text-decoration: none; font-weight: 600; font-size: 1rem;">{{ $s->name }}</a>
      @if($s->tagline)
      <p style="margin: 4px 0 0; font-size: 0.875rem; color: #555; line-height: 1.4;">{{ \Illuminate\Support\Str::limit($s->tagline ?? '', 120) }}</p>
      @endif
      <p style="margin: 6px 0 0; font-size: 0.8125rem;"><a href="{{ url('/startup/' . $s->slug) }}" style="color: #2563eb;">View on {{ $siteName }} →</a></p>
    </li>
    @endforeach
  </ul>
  <p style="margin-top: 24px;"><a href="{{ url('/') }}" style="color: #2563eb;">Browse all apps →</a></p>
  @endif
  <p style="margin-top: 32px; font-size: 0.8125rem; color: #888;">You received this because you subscribed at {{ $siteName }}. No spam, just weekly updates.</p>
</div>
