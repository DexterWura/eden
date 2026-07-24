<?php
$siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
$siteUrl = url('/');
?>
<section class="page-head">
  <div class="wrap">
    <h1>Privacy Policy</h1>
    <p>How we collect, use, and protect your information.</p>
  </div>
</section>

<div class="wrap content-block">
  <p><strong>Last updated:</strong> July 24, 2026</p>

  <h2>1. Who we are</h2>
  <p><?= e($siteName) ?> (“we”, “our”, or “the site”) is a startup directory at <?= e($siteUrl) ?>. This privacy policy explains how we handle your information when you use our website.</p>

  <h2>2. Information we collect</h2>
  <p>We may collect:</p>
  <ul>
    <li><strong>Information you provide:</strong> When you submit a startup, create an account, subscribe to our newsletter, or contact us, we collect the information you give (e.g. name, email, startup details).</li>
    <li><strong>Usage data:</strong> We may collect how you use the site (e.g. pages visited) to improve our service.</li>
    <li><strong>Cookies and similar technologies:</strong> As described below.</li>
  </ul>

  <h2>3. Cookies</h2>
  <p>We use cookies and similar technologies to:</p>
  <ul>
    <li>Keep you logged in and remember your preferences (e.g. theme).</li>
    <li>Understand how the site is used so we can improve it.</li>
    <li>Deliver and measure advertising (see section 4).</li>
  </ul>
  <p>Advertising storage is denied by default. You can choose “Allow advertising” or “Necessary only” in Eden’s consent notice. Your choice is stored for one year in the <code>eden_ad_consent</code> cookie; clearing site cookies lets you choose again.</p>

  <h2>4. Third-party advertising (Google AdSense)</h2>
  <p>When AdSense is enabled and you allow advertising, Google and its partners may use cookies and similar technologies to deliver and measure ads. Eden does not load the configured AdSense script before that choice.</p>
  <p><strong>How you can control ad personalisation:</strong></p>
  <ul>
    <li>Google Ad Settings: <a href="https://adssettings.google.com" target="_blank" rel="noopener noreferrer">adssettings.google.com</a> — manage how Google uses your data for ads.</li>
    <li>You can opt out of personalised ads; you may still see non-personalised ads.</li>
  </ul>
  <p>For more about how Google uses data when you use partner sites, see <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener noreferrer">Google’s partner sites policy</a>.</p>

  <h2>5. How we use your information</h2>
  <p>We use the information we collect to:</p>
  <ul>
    <li>Run and improve the directory (e.g. displaying startups, sending the weekly digest if you subscribed).</li>
    <li>Respond to your messages and support your use of the site.</li>
    <li>Comply with the law and protect our rights.</li>
  </ul>

  <h2>6. Sharing of information</h2>
  <p>We do not sell your personal information. We may share data with:</p>
  <ul>
    <li>Service providers that help us operate the site (e.g. hosting, email).</li>
    <li>Advertising partners (e.g. Google) as described in section 4.</li>
    <li>Authorities when required by law.</li>
  </ul>

  <h2>7. Security and retention</h2>
  <p>We take reasonable steps to protect your data. We retain information for as long as needed to provide the service and as required by law.</p>

  <h2>8. Your rights and contact</h2>
  <p>Depending on where you live, you may have rights to access, correct, or delete your data. To ask about privacy or exercise these rights, contact us via our <a href="<?= e(url('/contact')) ?>">Contact</a> page.</p>
  <p>For Google AdSense and related data, please use the links in section 4 and Google’s privacy policy: <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">policies.google.com/privacy</a>.</p>

  <h2>9. Changes</h2>
  <p>We may update this privacy policy from time to time. The “Last updated” date at the top will change when we do. Continued use of the site after changes means you accept the updated policy.</p>
</div>
