<?php
$s = $startup ?? null;
if (!$s) return;
$logoPath = $s->logo_path ?? null;
$fundingRound = $s->activeFundingRound;
$logoLetters = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
$foundersDisplay = $s->founders_display ?? [];
$productImages = $s->product_images ?? [];
$buildPublicContactUrl = static function (array $params = []) {
  return url('/contact') . '?' . http_build_query(array_filter($params, static fn ($value) => $value !== null && $value !== ''));
};
?>
<section class="page-head">
  <div class="wrap">
    <?php if (($s->status ?? '') === 'pending'): ?>
    <div class="startup-launch-notice">
      <i class="fa-solid fa-clock"></i>
      This startup is pending review and is not yet visible to the public.
      <br>Share this link so people can get notified when you launch: <a href="<?= e(route('launch-notify.show', $s->slug)) ?>"><?= e(route('launch-notify.show', $s->slug)) ?></a>
    </div>
    <?php endif; ?>
    <a href="<?= e(url('/')) ?>" class="back-link">&larr; All startups</a>
    <div class="startup-hero">
      <div class="startup-hero-logo" role="img" aria-label="<?= e($s->name) ?> logo">
        <?php if ($logoPath): ?><img src="<?= e(asset($logoPath)) ?>" alt="<?= e($s->name) ?> – logo" class="startup-hero-logo-img" width="80" height="80" loading="eager"><?php else: ?><?= e($logoLetters) ?><?php endif; ?>
      </div>
      <div class="startup-hero-main">
        <h1><?= e($s->name) ?></h1>
        <?php
        $isProductOfDay = $isProductOfDay ?? false;
        $productOfDayDate = $productOfDayDate ?? null;
        $isProductOfDayToday = $isProductOfDayToday ?? false;
        ?>
        <div class="startup-hero-badges">
          <?php if ($productOfDayDate): ?>
          <?php include __DIR__ . '/partials/potd-seal.php'; ?>
          <?php endif; ?>
          <?php if ($fundingRound): ?><span class="badge badge-funding"><i class="fa-solid fa-hand-holding-dollar"></i> Raising</span><?php endif; ?>
          <?php if ($s->for_sale && !$s->sold_at && $s->flipit_listing_id): ?>
          <?php $flipitUrl = $s->getFlipitListingUrl(); ?>
          <?php if ($flipitUrl): ?><a href="<?= e($flipitUrl) ?>" target="_blank" rel="noopener noreferrer" class="badge badge-for-sale"><i class="fa-solid fa-tag" aria-hidden="true"></i> For sale</a><?php endif; ?>
          <?php endif; ?>
        </div>
        <?php if ($s->tagline): ?><p class="tagline"><?= e($s->tagline) ?></p><?php endif; ?>
        <div class="startup-meta">
          <?php if ($s->category): ?><span><?= e($s->category) ?></span><?php endif; ?>
          <?php if ($s->location): ?><span><a href="<?= e(url('/locations/' . \Illuminate\Support\Str::slug($s->location))) ?>"><?= e($s->location) ?></a></span><?php endif; ?>
          <?php if ($s->launch_date): ?><span><?= $s->launch_date->format('F Y') ?></span><?php endif; ?>
        </div>
        <div class="startup-hero-actions">
          <div class="upvote-ui startup-hero-actions-main">
            <?php $hasUpvoted = $hasUpvoted ?? false; ?>
            <?php if ($hasUpvoted): ?>
            <span class="upvote-btn startup-upvote-complete" aria-label="Upvoted"><i class="fa-solid fa-arrow-up"></i></span>
            <span class="upvote-count"><?= (int)$s->upvotes ?></span>
            <span class="startup-upvote-status">You upvoted</span>
            <?php else: ?>
            <form action="<?= e(route('startup.upvote', $s->slug)) ?>" method="POST" class="startup-inline-form">
              <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
              <button type="submit" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
            </form>
            <span class="upvote-count"><?= (int)$s->upvotes ?></span>
            <?php if (!auth()->check()): ?>
            <span class="startup-upvote-status">Log in to upvote</span>
            <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($s->website)): ?>
            <a href="<?= e(url('/startup/' . $s->slug . '/out')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Visit website</a>
            <?php endif; ?>
          </div>

          <div class="startup-hero-actions-side">
            <?php if (auth()->check()): ?>
            <?php $hasSaved = $hasSaved ?? false; ?>
            <?php if ($hasSaved): ?>
            <form action="<?= e(route('startup.unsave', $s->slug)) ?>" method="post" class="startup-inline-form">
              <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
              <button type="submit" class="btn btn-ghost"><i class="fa-solid fa-bookmark" aria-hidden="true"></i> Saved</button>
            </form>
            <?php else: ?>
            <form action="<?= e(route('startup.save', $s->slug)) ?>" method="post" class="startup-inline-form">
              <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
              <button type="submit" class="btn btn-ghost"><i class="fa-regular fa-bookmark" aria-hidden="true"></i> Save</button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
            <?php $showClaimButton = empty($s->user_id) && empty($s->founder_email); ?>
            <?php if ($showClaimButton): ?>
            <a href="<?= e(route('startup.claim', $s->slug)) ?>" class="btn btn-primary"><i class="fa-solid fa-hand-holding-hand" aria-hidden="true"></i> Claim this startup</a>
            <?php endif; ?>
            <div class="share-ui share-ui--inline">
            <button type="button" class="btn btn-ghost share-btn-trigger" id="shareTrigger" aria-label="Share" aria-expanded="false" aria-haspopup="true"><i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Share</button>
            <div class="share-dropdown" id="shareDropdown" role="menu" aria-label="Share options" hidden>
              <button type="button" class="share-dropdown-item" data-action="copy" data-url="<?= e(url('/startup/' . $s->slug)) ?>"><i class="fa-solid fa-link" aria-hidden="true"></i> Copy link</button>
              <a href="https://twitter.com/intent/tweet?url=<?= e(rawurlencode(url('/startup/' . $s->slug))) ?>&text=<?= e(rawurlencode(($s->tagline ?: $s->name) . ' — ' . $s->name)) ?>" target="_blank" rel="noopener noreferrer" class="share-dropdown-item"><i class="fa-brands fa-x-twitter" aria-hidden="true"></i> Share on X</a>
              <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= e(rawurlencode(url('/startup/' . $s->slug))) ?>" target="_blank" rel="noopener noreferrer" class="share-dropdown-item"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i> Share on LinkedIn</a>
            </div>
          </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="wrap startup-detail-layout">
  <div class="startup-detail-main">
  <?php if ($s->for_sale && !$s->sold_at && $s->flipit_listing_id): ?>
  <p class="startup-sale-note">This startup is listed for sale on <a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer">FLIPit</a>.</p>
  <?php endif; ?>
  <?php if (!empty($productImages)): ?>
  <section class="startup-section startup-product-images" aria-labelledby="product-heading">
    <h2 id="product-heading">Product</h2>
    <div class="product-images-grid">
      <?php foreach ($productImages as $i => $img): ?>
      <div class="product-image-wrap"><img src="<?= e(asset($img)) ?>" alt="<?= e($s->name) ?> – product<?= count($productImages) > 1 ? ' ' . ((int)$i + 1) : '' ?>" width="400" height="300" loading="lazy"></div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($s->description): ?>
  <section class="startup-section startup-prose">
    <h2>About</h2>
    <p><?= nl2br(e($s->description)) ?></p>
  </section>
  <?php endif; ?>

  <?php if ($s->problem_solved || $s->target_customer): ?>
  <section class="startup-section startup-story-grid" aria-labelledby="product-story-heading">
    <h2 id="product-story-heading">Why it exists</h2>
    <div class="startup-story-cards">
      <?php if ($s->problem_solved): ?>
      <article>
        <span class="startup-story-icon"><i class="fa-regular fa-lightbulb" aria-hidden="true"></i></span>
        <h3>The problem</h3>
        <p><?= nl2br(e($s->problem_solved)) ?></p>
      </article>
      <?php endif; ?>
      <?php if ($s->target_customer): ?>
      <article>
        <span class="startup-story-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
        <h3>Who it is for</h3>
        <p><?= nl2br(e($s->target_customer)) ?></p>
      </article>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (count(array_filter($s->key_features ?? [])) > 0): ?>
  <section class="startup-section" aria-labelledby="features-heading">
    <h2 id="features-heading">Key features</h2>
    <ul class="startup-feature-list">
      <?php foreach (array_filter($s->key_features ?? []) as $feature): ?>
      <li><i class="fa-solid fa-check" aria-hidden="true"></i><span><?= e($feature) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if ($s->traction || $s->founder_story): ?>
  <section class="startup-section startup-prose" aria-labelledby="progress-heading">
    <h2 id="progress-heading">Progress and story</h2>
    <?php if ($s->traction): ?>
    <h3>Traction</h3>
    <p><?= nl2br(e($s->traction)) ?></p>
    <?php endif; ?>
    <?php if ($s->founder_story): ?>
    <h3>Founder story</h3>
    <p><?= nl2br(e($s->founder_story)) ?></p>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if (count($foundersDisplay) > 0): ?>
  <section class="startup-section">
    <h2>Founder<?= count($foundersDisplay) > 1 ? 's' : '' ?></h2>
    <div class="startup-founders startup-founders--detailed">
      <?php foreach ($foundersDisplay as $f): ?>
      <div class="startup-founder-block startup-founder-block--card">
        <span class="startup-founder-avatar" title="<?= e($f['name']) ?>">
          <?php if (!empty($f['photo_url'])): ?><img src="<?= e(asset($f['photo_url'])) ?>" alt=""><?php else: ?><span class="startup-founder-initials"><?= e(\App\Models\Startup::founderInitials($f['name'])) ?></span><?php endif; ?>
        </span>
        <div class="startup-founder-info">
          <strong class="startup-founder-name"><?= e($f['name']) ?></strong>
          <?php if (!empty($f['email'])): ?>
          <?php $founderContactUrl = $buildPublicContactUrl([
            'subject' => 'listing',
            'startup' => $s->name,
            'message' => "Hi,\n\nI'm reaching out about " . $s->name . ' and would like to contact ' . $f['name'] . ".\n\n",
          ]); ?>
          <p class="startup-founder-email"><a href="<?= e($founderContactUrl) ?>" class="btn btn-ghost"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Email</a></p>
          <?php endif; ?>
          <?php if (!empty($f['twitter_url']) || !empty($f['linkedin_url'])): ?>
          <div class="startup-founder-links" aria-label="Social links for <?= e($f['name']) ?>">
            <?php if (!empty($f['twitter_url'])): ?><a href="<?= e($f['twitter_url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($f['name']) ?> on X"><i class="fa-brands fa-x-twitter" aria-hidden="true"></i> X</a><?php endif; ?>
            <?php if (!empty($f['linkedin_url'])): ?><a href="<?= e($f['linkedin_url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($f['name']) ?> on LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i> LinkedIn</a><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($fundingRound): ?>
  <section class="startup-section startup-funding" aria-labelledby="funding-heading">
    <h2 id="funding-heading"><i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i> Raising funding</h2>
    <div class="startup-funding-card">
      <div class="startup-funding-badge"><?= e($fundingRound->round_type_label) ?></div>
      <?php if ($fundingRound->amount_seeking): ?>
      <p class="startup-funding-amount"><?= e(number_format((float)$fundingRound->amount_seeking, 0)) ?> <?= e($fundingRound->currency) ?></p>
      <?php endif; ?>
      <?php if ($fundingRound->description): ?>
      <p class="startup-funding-desc"><?= nl2br(e($fundingRound->description)) ?></p>
      <?php endif; ?>
      <?php if ($fundingRound->contact_email): ?>
      <?php $fundingContactUrl = $buildPublicContactUrl([
        'subject' => 'listing',
        'startup' => $s->name,
        'message' => "Hi,\n\nI'm interested in the funding round for " . $s->name . ".\n\n",
      ]); ?>
      <a href="<?= e($fundingContactUrl) ?>" class="btn btn-primary"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Email</a>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="startup-section">
    <h2>Links</h2>
    <div class="card-links">
      <?php if ($s->website): ?><a href="<?= e($s->website) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-globe"></i> Website</a><?php endif; ?>
      <?php if (!empty($s->twitter_url)): ?><a href="<?= e($s->twitter_url) ?>" target="_blank" rel="noopener" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a><?php endif; ?>
      <?php if (!empty($s->linkedin_url)): ?><a href="<?= e($s->linkedin_url) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a><?php endif; ?>
      <?php if (!$s->website && empty($s->twitter_url) && empty($s->linkedin_url)): ?><span class="text-muted">No links yet.</span><?php endif; ?>
    </div>
  </section>

  <?php
  $trafficByDay = $trafficByDay ?? [];
  $trafficTotal = (int)($trafficTotal ?? 0);
  $showTraffic = $s->traffic_tracking_enabled ?? false;
  ?>
  <?php if ($showTraffic): ?>
  <section class="startup-section startup-traffic-section" aria-labelledby="traffic-heading">
    <h2 id="traffic-heading"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> Website traffic</h2>
    <div class="startup-traffic-card">
      <div class="startup-traffic-total">
        <span class="startup-traffic-number" data-count="<?= $trafficTotal ?>">0</span>
        <span class="startup-traffic-label">visits last 14 days</span>
      </div>
      <?php if (count($trafficByDay) > 0): ?>
      <?php
      $maxVisits = max(...array_values($trafficByDay)) ?: 1;
      $days = [];
      for ($i = 13; $i >= 0; $i--) {
        $d = now()->subDays($i)->format('Y-m-d');
        $days[] = ['date' => $d, 'label' => now()->subDays($i)->format('M j'), 'visits' => $trafficByDay[$d] ?? 0];
      }
      ?>
      <div class="startup-traffic-chart">
        <?php foreach ($days as $i => $day): ?>
        <div class="startup-traffic-bar-wrap" style="animation-delay: <?= $i * 40 ?>ms;" title="<?= e($day['label']) ?>: <?= (int)$day['visits'] ?> visits">
          <div class="startup-traffic-bar" style="--h: <?= $maxVisits > 0 ? round(100 * $day['visits'] / $maxVisits) : 0 ?>%;"></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="startup-traffic-labels">
        <span><?= $days[0]['label'] ?? '' ?></span>
        <span><?= $days[count($days)-1]['label'] ?? '' ?></span>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php $similarStartups = $similarStartups ?? collect(); ?>
  <?php if ($similarStartups->isNotEmpty()): ?>
  <section class="startup-section" aria-labelledby="similar-heading">
    <h2 id="similar-heading">Similar startups</h2>
    <?php
    $similarBlurb = 'You might also like.';
    if (!empty($s->category) && !empty($s->location)) {
        $similarBlurb = 'More in ' . $s->category . ' · ' . $s->location . '.';
    } elseif (!empty($s->category)) {
        $similarBlurb = 'More in ' . $s->category . '.';
    } elseif (!empty($s->location)) {
        $similarBlurb = 'More from ' . $s->location . '.';
    }
    ?>
    <p class="section-sub startup-similar-blurb"><?= e($similarBlurb) ?></p>
    <div class="section-cards-row startup-similar-cards">
      <?php foreach ($similarStartups as $startup):
        $rank = null;
        $showRank = false;
        $cardVariant = 'row';
        include __DIR__ . '/_startup-card.php';
      endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php
  $comments = $comments ?? collect();
  $canComment = auth()->check();
  ?>
  <section class="startup-section startup-comments" aria-labelledby="comments-heading">
    <h2 id="comments-heading">Comments <?= $comments->count() > 0 ? '(' . $comments->count() . ')' : '' ?></h2>
    <?php if ($comments->count() > 0): ?>
    <ul class="startup-comments-list" aria-label="Comments on <?= e($s->name) ?>">
      <?php foreach ($comments as $c): ?>
      <li class="startup-comment">
        <div class="startup-comment-header">
          <span class="startup-comment-author"><?= e($c->user->name ?? 'User') ?></span>
          <time class="startup-comment-date" datetime="<?= e($c->created_at->toIso8601String()) ?>"><?= e($c->created_at->diffForHumans()) ?></time>
        </div>
        <p class="startup-comment-body"><?= nl2br(e($c->body)) ?></p>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <?php if ($canComment): ?>
    <form action="<?= e(route('startup.comment', $s->slug)) ?>" method="POST" class="startup-comment-form">
      <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
      <label for="comment-body" class="visually-hidden">Write a comment</label>
      <textarea id="comment-body" name="body" rows="3" maxlength="2000" placeholder="Write a comment..." required><?= e(old('body', '')) ?></textarea>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Post comment</button>
    </form>
    <?php else: ?>
    <p class="startup-comments-login">
      <a href="<?= e(route('login')) ?>">Log in</a> to comment.
    </p>
    <?php endif; ?>
  </section>
  </div>

  <aside class="startup-detail-sidebar" aria-label="<?= e($s->name) ?> facts">
    <section class="startup-facts-card">
      <span class="sidebar-eyebrow">At a glance</span>
      <dl>
        <?php if ($s->category): ?><div><dt>Category</dt><dd><a href="<?= e(url('/categories/' . \Illuminate\Support\Str::slug($s->category))) ?>"><?= e($s->category) ?></a></dd></div><?php endif; ?>
        <?php if ($s->location): ?><div><dt>Location</dt><dd><?= e($s->location) ?></dd></div><?php endif; ?>
        <?php if ($s->markets_served): ?><div><dt>Markets</dt><dd><?= e($s->markets_served) ?></dd></div><?php endif; ?>
        <?php if ($s->pricing_model): ?><div><dt>Pricing</dt><dd><?= e($s->pricing_model) ?></dd></div><?php endif; ?>
        <?php if ($s->launch_date): ?><div><dt>Launched</dt><dd><?= e($s->launch_date->format('F Y')) ?></dd></div><?php endif; ?>
      </dl>
      <?php if ($s->website): ?>
      <a href="<?= e(url('/startup/' . $s->slug . '/out')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-block">Visit <?= e($s->name) ?></a>
      <?php endif; ?>
    </section>
    <section class="startup-facts-card">
      <span class="sidebar-eyebrow">Profile quality</span>
      <div class="profile-completeness" role="img" aria-label="Profile <?= (int)$s->content_completeness_score ?> percent complete">
        <span style="width: <?= (int)$s->content_completeness_score ?>%"></span>
      </div>
      <p><?= $s->editorial_reviewed_at ? 'Reviewed by Eden on ' . e($s->editorial_reviewed_at->format('F j, Y')) . '.' : 'Founder-provided information. Report anything that looks inaccurate.' ?></p>
    </section>
  </aside>
</div>

<div class="wrap">
  <?php $reportReasons = $reportReasons ?? []; ?>
  <?php if (!empty($reportReasons)): ?>
  <details class="startup-report-box">
    <summary class="startup-report-summary">
      <i class="fa-solid fa-flag startup-report-icon" aria-hidden="true"></i> Report this listing
    </summary>
    <p class="startup-report-intro">
      See something off? Tell us — we review every report. Your email is only used if we need to follow up.
    </p>
    <form action="<?= e(route('startup.report', $s->slug)) ?>" method="post" class="startup-report-form">
      <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
      <div class="startup-report-honeypot" aria-hidden="true">
        <label for="reportWebsiteHp">Website</label>
        <input type="text" name="website" id="reportWebsiteHp" tabindex="-1" autocomplete="off">
      </div>
      <div class="form-group startup-report-field">
        <label class="form-label" for="reportEmail">Your email</label>
        <input type="email" name="reporter_email" id="reportEmail" class="form-input" required value="<?= e(old('reporter_email', auth()->check() ? auth()->user()->email : '')) ?>" placeholder="you@example.com">
      </div>
      <div class="form-group startup-report-field">
        <label class="form-label" for="reportReason">What’s the issue?</label>
        <select name="reason" id="reportReason" class="form-input" required>
          <option value="">Choose a reason</option>
          <?php foreach ($reportReasons as $key => $label): ?>
          <option value="<?= e($key) ?>" <?= old('reason') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group startup-report-field">
        <label class="form-label" for="reportDetails">Details (optional)</label>
        <textarea name="details" id="reportDetails" class="form-input" rows="3" placeholder="Optional context — required if you chose “Other”."><?= e(old('details')) ?></textarea>
      </div>
      <button type="submit" class="btn btn-ghost startup-report-submit">Submit report</button>
    </form>
  </details>
  <?php endif; ?>

  <div class="cta-strip">
    <?php if ($showClaimButton ?? (empty($s->user_id) && empty($s->founder_email))): ?>
    <a href="<?= e(route('startup.claim', $s->slug)) ?>" class="btn btn-primary"><i class="fa-solid fa-hand-holding-hand" aria-hidden="true"></i> Claim this startup</a>
    <?php endif; ?>
    <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Browse more startups</a>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your startup</a>
  </div>
</div>

<script>
(function() {
  var trigger = document.getElementById('shareTrigger');
  var dropdown = document.getElementById('shareDropdown');
  if (!trigger || !dropdown) return;
  function close() { dropdown.setAttribute('hidden', ''); trigger.setAttribute('aria-expanded', 'false'); }
  trigger.addEventListener('click', function(e) {
    e.stopPropagation();
    if (dropdown.hasAttribute('hidden')) {
      dropdown.removeAttribute('hidden');
      trigger.setAttribute('aria-expanded', 'true');
    } else close();
  });
  document.addEventListener('click', function() { close(); });
  dropdown.addEventListener('click', function(e) {
    var item = e.target.closest('[data-action="copy"]');
    if (item) {
      e.preventDefault();
      var url = item.getAttribute('data-url');
      if (url && navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function() {
          var label = item.innerHTML;
          item.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Copied!';
          setTimeout(function() { item.innerHTML = label; }, 1500);
        });
      }
    }
  });
})();
</script>
<?php if ($showTraffic ?? false): ?>
<script>
(function(){
var el=document.querySelector('.startup-traffic-number');
if(!el)return;
var target=parseInt(el.getAttribute('data-count')||0,10);
if(target<=0){el.textContent='0';return;}
var dur=1200,start=Date.now();
function tick(){
  var t=Math.min((Date.now()-start)/dur,1);
  var eased=1-Math.pow(1-t,3);
  el.textContent=Math.round(target*eased);
  if(t<1)requestAnimationFrame(tick);
}
requestAnimationFrame(tick);
})();
</script>
<?php endif; ?>
