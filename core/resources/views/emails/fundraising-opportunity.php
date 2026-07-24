<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($startup->name) ?> is raising funding</title>
</head>
<body style="margin:0;background:#f5f7fb;color:#172033;font-family:Arial,sans-serif">
  <div style="max-width:620px;margin:0 auto;padding:32px 18px">
    <div style="background:#fff;border:1px solid #e4e8f0;border-radius:14px;padding:32px">
      <p style="margin:0 0 12px;color:#ff6154;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em">Founder opportunity</p>
      <h1 style="margin:0 0 18px;font-size:27px;line-height:1.25"><?= e($startup->name) ?> is raising funding</h1>
      <p style="margin:0 0 16px;line-height:1.7">Hi <?= e($recipientName) ?>,</p>
      <p style="margin:0 0 16px;line-height:1.7">A fellow founder on <?= e($siteName) ?> has opened a <?= e(strtolower($fundingRound->round_type_label)) ?> round.</p>
      <?php if ($fundingRound->amount_seeking): ?>
      <p style="margin:0 0 16px;font-size:20px;font-weight:700"><?= e(number_format((float)$fundingRound->amount_seeking, 0)) ?> <?= e($fundingRound->currency) ?> sought</p>
      <?php endif; ?>
      <?php if ($fundingRound->description): ?>
      <p style="margin:0 0 22px;line-height:1.7"><?= nl2br(e($fundingRound->description)) ?></p>
      <?php endif; ?>
      <p style="margin:0 0 22px"><a href="<?= e($startupUrl) ?>" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#ff6154;color:#fff;text-decoration:none;font-weight:700">View the opportunity</a></p>
      <p style="margin:0;color:#667085;font-size:12px;line-height:1.6">This is a founder-to-founder introduction, not investment advice or an endorsement by <?= e($siteName) ?>. Review the business and conduct your own due diligence before investing. You received this because you have an active app and investment opportunity emails are enabled in your notification preferences.</p>
    </div>
  </div>
</body>
</html>
