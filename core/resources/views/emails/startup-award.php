<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($awardName) ?></title>
</head>
<body style="margin:0;background:#f5f7fb;color:#172033;font-family:Arial,sans-serif">
  <div style="max-width:620px;margin:0 auto;padding:32px 18px">
    <div style="background:#fff;border:1px solid #e4e8f0;border-radius:14px;padding:32px">
      <p style="margin:0 0 12px;color:#ff6154;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em">Winner on <?= e($siteName) ?></p>
      <h1 style="margin:0 0 18px;font-size:28px;line-height:1.25">Congratulations — <?= e($startup->name) ?> is <?= e($awardName) ?>!</h1>
      <p style="margin:0 0 16px;line-height:1.7">Hi <?= e($recipientName) ?>,</p>
      <p style="margin:0 0 16px;line-height:1.7">The Eden community’s upvotes selected <strong><?= e($startup->name) ?></strong> as <?= e($awardName) ?> for <?= e($periodLabel) ?>.</p>
      <p style="margin:0 0 24px;line-height:1.7">Share the achievement with your audience and keep your profile updated so new visitors can learn what you are building.</p>
      <p style="margin:0 0 14px"><a href="<?= e($startupUrl) ?>" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#ff6154;color:#fff;text-decoration:none;font-weight:700">View your winning profile</a></p>
      <?php if ($awardName === 'Product of the Day'): ?>
      <p style="margin:0;font-size:14px;line-height:1.6;color:#667085">You can also <a href="<?= e($badgesUrl) ?>">add your Product of the Day badge</a> to your website.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
