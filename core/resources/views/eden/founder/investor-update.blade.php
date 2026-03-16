@php
  $siteName = $siteName ?? 'Eden';
  $totalViews = $totalViews ?? 0;
  $totalClicks = $totalClicks ?? 0;
  $totalUpvotes = $totalUpvotes ?? 0;
  $totalComments = $totalComments ?? 0;
  $totalRevenue = $totalRevenue ?? 0;
  $totalMrr = $totalMrr ?? 0;
  $days = $days ?? 60;
  $primaryStartupName = $primaryStartup?->name ?? $siteName;
  $exportedAt = $exportedAt ?? now()->toDateTimeString();
@endphp

<h1 class="dash-page-title">Investor update</h1>
<div class="dash-welcome" style="max-width: 800px;">
  <strong>Pro-only</strong> — Generate a structured investor update using your latest Eden analytics. Review and tweak the text, then paste into your email tool.
</div>

<div class="dash-card" style="max-width: 900px; margin-top: 16px;">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">Summary snapshot</span>
    <span class="dash-card-subtitle">Last {{ $days }} days · generated {{ $exportedAt }}</span>
  </div>
  <div class="dash-card-body">
    <div class="analytics-kpi-grid">
      <div class="analytics-kpi analytics-kpi--revenue">
        <div class="analytics-kpi-icon"><i class="fa-solid fa-dollar-sign"></i></div>
        <div class="analytics-kpi-content">
          <span class="analytics-kpi-label">MRR</span>
          <span class="analytics-kpi-value">${{ number_format($totalMrr, 2) }}</span>
        </div>
      </div>
      <div class="analytics-kpi analytics-kpi--views">
        <div class="analytics-kpi-icon"><i class="fa-solid fa-eye"></i></div>
        <div class="analytics-kpi-content">
          <span class="analytics-kpi-label">Total views</span>
          <span class="analytics-kpi-value">{{ number_format($totalViews) }}</span>
        </div>
      </div>
      <div class="analytics-kpi analytics-kpi--upvotes">
        <div class="analytics-kpi-icon"><i class="fa-solid fa-arrow-up"></i></div>
        <div class="analytics-kpi-content">
          <span class="analytics-kpi-label">Upvotes</span>
          <span class="analytics-kpi-value">{{ number_format($totalUpvotes) }}</span>
        </div>
      </div>
      <div class="analytics-kpi analytics-kpi--comments">
        <div class="analytics-kpi-icon"><i class="fa-solid fa-comment"></i></div>
        <div class="analytics-kpi-content">
          <span class="analytics-kpi-label">Comments</span>
          <span class="analytics-kpi-value">{{ number_format($totalComments) }}</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="dash-card" style="max-width: 900px; margin-top: 16px;">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">Update draft</span>
    <button type="button" id="edenInvestorUpdateCopy" class="dash-btn dash-btn-secondary" style="margin-left: auto; font-size: 0.85rem;">
      <i class="fa-solid fa-copy"></i> Copy to clipboard
    </button>
  </div>
  <div class="dash-card-body">
    <textarea id="edenInvestorUpdateText" rows="18" style="width: 100%; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 0.9rem; line-height: 1.5; border-radius: 8px; border: 1px solid var(--d-border, #2a2e3d); padding: 12px; background: var(--d-surface, #0b0d16); color: var(--d-text, #e8eaef); resize: vertical;">Hi everyone,

Quick update on {{ $primaryStartupName }} for the last {{ $days }} days.

Headline metrics
- MRR: ${{ number_format($totalMrr, 2) }}
- Total revenue (all time): ${{ number_format($totalRevenue, 2) }}
- Traffic: {{ number_format($totalViews) }} views, {{ number_format($totalClicks) }} clicks
- Engagement: {{ number_format($totalUpvotes) }} upvotes, {{ number_format($totalComments) }} comments

Highlights
- [Add 2–3 bullets on product launches, growth, partnerships, or notable customers.]

Lowlights / challenges
- [Add 1–2 honest bullets on what’s not working yet or new risks.]

Focus for the next {{ max(2, (int) floor($days / 4)) }}–{{ max(4, (int) floor($days / 2)) }} weeks
- [Add 3–5 bullets on your key priorities — shipping, growth, hiring, fundraising.]

Asks
- [Add 2–3 specific ways investors can help: intros, hiring, feedback, etc.]

Thanks for the support,
[Your name]</textarea>
  </div>
</div>

<script>
(function() {
  var btn = document.getElementById('edenInvestorUpdateCopy');
  var textarea = document.getElementById('edenInvestorUpdateText');
  if (!btn || !textarea) return;

  btn.addEventListener('click', function() {
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length);
    var ok = false;
    try {
      ok = document.execCommand('copy');
    } catch (e) {}
    if (!ok && navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(textarea.value).then(function() {
        ok = true;
      }).catch(function() {});
    }
    if (typeof edenPromoToast === 'function') {
      edenPromoToast({
        key: 'investor_update_copy',
        message: ok ? 'Investor update copied. Paste into your email tool.' : 'Select the text and press Ctrl/Cmd+C to copy.',
        ctaText: null,
        ctaHref: null,
        duration: 4000
      });
    }
  });
})();
</script>

