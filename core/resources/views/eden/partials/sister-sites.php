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
  <p class="site-footer__heading" style="margin-bottom: 0.35rem;">Also from us</p>
  <p style="margin: 0 0 0.75rem; font-size: 0.875rem; line-height: 1.5; color: var(--text-muted);">
    If you’re monetizing a site or trading digital assets, these are sister products worth a look:
  </p>
  <ul class="site-footer__sites" style="margin: 0; padding: 0; list-style: none;">
    <li style="margin-bottom: 0.65rem;">
      <a href="https://zimadsense.com" target="_blank" rel="noopener noreferrer" style="font-weight: 600;">ZimAdsense</a>
      <span style="color: var(--text-muted);"> — an ad network in the spirit of Google AdSense, built for local publishers and audiences.</span>
    </li>
    <li>
      <a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer" style="font-weight: 600;">FLIPit</a>
      <span style="color: var(--text-muted);"> — marketplace for online businesses, domains, SaaS, social accounts, and more.</span>
    </li>
  </ul>
  <p style="margin: 0.75rem 0 0; font-size: 0.8125rem;">
    <a href="https://dextersoft.com" target="_blank" rel="noopener noreferrer">dextersoft.com</a>
    <span style="color: var(--text-muted);"> — dev &amp; tools.</span>
  </p>
</div>
<?php endif; ?>
