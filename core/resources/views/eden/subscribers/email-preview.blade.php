<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1a1a1a;">
  <h1 style="font-size: 1.25rem; margin-bottom: 16px;">{{ $subject }}</h1>
  <div style="line-height: 1.6; white-space: pre-wrap;">
    {{-- SAFE: e() escapes HTML special chars, nl2br() only converts \n to <br> --}}
    {!! nl2br(e($body)) !!}
  </div>
</div>
