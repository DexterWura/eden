<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1a1a1a;">
  <h1 style="font-size: 1.25rem; margin-bottom: 8px;">New listings on {{ $siteName }}</h1>
  <p style="color: #666; font-size: 0.9375rem; margin-bottom: 20px;">These startups match your saved search: {{ $subscription->summaryLabel() }}</p>
  @if($startups->isEmpty())
  <p style="line-height: 1.6;">No new matches in this period.</p>
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
  <p style="margin-top: 24px;"><a href="{{ url('/') }}" style="color: #2563eb;">Browse the directory →</a></p>
  @endif
  <p style="margin-top: 32px; font-size: 0.8125rem; color: #888;">
    <a href="{{ $unsubscribeUrl }}" style="color: #888;">Unsubscribe from this search alert</a>
  </p>
</div>
