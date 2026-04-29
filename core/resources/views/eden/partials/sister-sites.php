<?php
/** @var string $style footer (default) | compact */
$style = $style ?? 'footer';
?>
<?php if ($style === 'compact'): ?>
<p class="sister-sites sister-sites--compact" style="margin: 0; font-size: 0.8125rem; line-height: 1.5; color: var(--d-text-secondary, var(--text-muted, #94a3b8));">
  Also from us:
  <a href="https://zimadsense.com" target="_blank" rel="noopener noreferrer" style="color: var(--accent, #00d4aa);">ZimAdsense</a>
  — ads for local publishers (Adsense-style, without the Silicon Valley default).
  ·
  <a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer" style="color: var(--accent, #00d4aa);">FLIPit</a>
  — buy &amp; sell online businesses, domains, SaaS, social accounts, and more.
</p>
<?php else: ?>
<div class="sister-sites sister-sites--footer">
  <p class="site-footer__heading">Also from us</p>
  <ul class="site-footer__sites">
    <li>
      <a href="https://zimadsense.com" target="_blank" rel="noopener noreferrer"><strong>ZimAdsense</strong></a>
      <span style="color: var(--text-muted);"> - Local-first ad network for publishers.</span>
    </li>
    <li>
      <a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer"><strong>FLIPit</strong></a>
      <span style="color: var(--text-muted);"> - Buy and sell online businesses and digital assets.</span>
    </li>
  </ul>
</div>
<?php endif; ?>
