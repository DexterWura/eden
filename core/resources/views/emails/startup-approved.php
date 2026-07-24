<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($startup->name) ?> is live on <?= e($siteName) ?></title>
</head>
<body style="margin:0;background:#f4f7f6;color:#1f2937;font-family:Arial,sans-serif;">
  <div style="max-width:620px;margin:0 auto;padding:32px 18px;">
    <div style="background:#ffffff;border:1px solid #dfe7e4;border-radius:14px;overflow:hidden;">
      <div style="padding:24px 28px;background:#087f6d;color:#ffffff;">
        <p style="margin:0 0 6px;font-size:14px;opacity:.9;"><?= e($siteName) ?></p>
        <h1 style="margin:0;font-size:25px;line-height:1.3;"><?= e($startup->name) ?> is now live</h1>
      </div>
      <div style="padding:28px;">
        <p style="margin:0 0 18px;">Hi <?= e($recipientName) ?>,</p>
        <p style="margin:0 0 18px;line-height:1.65;">Your startup has been approved and visitors can now discover it on <?= e($siteName) ?>.</p>

        <div style="margin:22px 0;padding:18px;border-radius:10px;background:#eef8f5;">
          <strong style="display:block;margin-bottom:7px;color:#087f6d;">Your live listing includes a dofollow website link</strong>
          <span style="line-height:1.55;">Visitors and search engines can follow the website link from your startup profile.</span>
        </div>

        <p style="margin:0 0 18px;line-height:1.65;">You can support the Eden community by adding your “Listed on <?= e($siteName) ?>” badge to your website. The badge is available in your founder dashboard and links back to your live profile with a normal dofollow link.</p>

        <p style="margin:24px 0;">
          <a href="<?= e($badgesUrl) ?>" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#087f6d;color:#ffffff;text-decoration:none;font-weight:bold;">Get your Eden badge</a>
          <a href="<?= e($startupUrl) ?>" style="display:inline-block;margin-left:8px;padding:12px 18px;border:1px solid #b8c9c4;border-radius:8px;color:#1f2937;text-decoration:none;font-weight:bold;">View your listing</a>
        </p>

        <p style="margin:22px 0 0;color:#66706b;font-size:14px;line-height:1.55;">Copy the badge embed code from the Badges page and paste it into your site footer, press page, or partners section. You can manage your listing from <a href="<?= e($dashboardUrl) ?>" style="color:#087f6d;">your dashboard</a>.</p>
      </div>
    </div>
  </div>
</body>
</html>
