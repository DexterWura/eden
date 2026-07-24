<?php
$siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
$siteUrl = url('/');
?>
<section class="page-head">
  <div class="wrap">
    <h1>Terms of Service</h1>
    <p>Rules and terms for using <?= e($siteName) ?>.</p>
  </div>
</section>

<div class="wrap content-block">
  <p><strong>Last updated:</strong> July 24, 2026</p>

  <h2>1. Acceptance</h2>
  <p>By using <?= e($siteUrl) ?> (“the site”), you agree to these terms. If you do not agree, please do not use the site.</p>

  <h2>2. Use of the site</h2>
  <p>You may use the site to browse apps, submit your own app (if you are a founder or authorised to do so), and use features we offer. You must:</p>
  <ul>
    <li>Provide accurate information when submitting an app or creating an account.</li>
    <li>Not use the site for anything illegal, misleading, or that infringes others’ rights.</li>
    <li>Not attempt to break or abuse the site, other users’ accounts, or our systems.</li>
  </ul>

  <h2>3. Submissions and content</h2>
  <p>When you submit an app or other content, you grant us a licence to display and use it to operate the directory. You are responsible for ensuring you have the right to submit that content. We may remove or reject content that violates our guidelines or these terms.</p>

  <h2>4. Accounts</h2>
  <p>You are responsible for keeping your account credentials secure. Notify us if you suspect unauthorised access.</p>

  <h2>5. Advertising</h2>
  <p>We may show third-party advertisements (e.g. via Google AdSense). Ad content is provided by advertisers; we are not responsible for external ads or linked sites. Use of the site may be subject to additional terms from our ad partners.</p>

  <h2>6. Disclaimer</h2>
  <p>The site and listings are provided “as is”. We do not guarantee the accuracy of app information or that the site will be error-free or always available.</p>

  <h2>7. Limitation of liability</h2>
  <p>To the extent permitted by law, we are not liable for any indirect, incidental, or consequential damages arising from your use of the site.</p>

  <h2>8. Changes</h2>
  <p>We may change these terms. The “Last updated” date will be revised when we do. Continued use of the site after changes means you accept the new terms.</p>

  <h2>9. Contact</h2>
  <p>For questions about these terms, use our <a href="<?= e(url('/contact')) ?>">Contact</a> page.</p>
</div>
