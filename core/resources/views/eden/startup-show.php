<?php
$s = $startup ?? null;
if (!$s) return;
$logoPath = $s->logo_path ?? null;
$fundingRound = $s->activeFundingRound;
$logoLetters = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
$foundersDisplay = $s->founders_display ?? [];
$productImages = $s->product_images ?? [];
?>
<section class="page-head">
  <div class="wrap">
    <?php if (($s->status ?? '') === 'pending'): ?>
    <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:14px 18px;margin-bottom:16px;font-size:0.92rem;color:#92400e;text-align:center">
      <i class="fa-solid fa-clock" style="margin-right:6px"></i>
      This startup is pending review and is not yet visible to the public.
      <br>Share this link so people can get notified when you launch: <a href="<?= e(route('launch-notify.show', $s->slug)) ?>" style="color:#92400e;text-decoration:underline"><?= e(route('launch-notify.show', $s->slug)) ?></a>
    </div>
    <?php endif; ?>
    <a href="<?= e(url('/')) ?>" class="back-link">&larr; All startups</a>
    <div class="startup-hero">
      <div class="startup-hero-logo" role="img" aria-label="<?= e($s->name) ?> logo">
        <?php if ($logoPath): ?><img src="<?= e(asset($logoPath)) ?>" alt="<?= e($s->name) ?> – logo" class="startup-hero-logo-img" width="80" height="80" loading="eager"><?php else: ?><?= e($logoLetters) ?><?php endif; ?>
      </div>
      <div>
        <h1><?= e($s->name) ?></h1>
        <?php $isProductOfDay = $isProductOfDay ?? false; ?>
        <?php if ($isProductOfDay): ?><span class="badge badge-product-of-day" style="display: inline-block; margin-bottom: 8px;">Product of the day</span><?php endif; ?>
        <?php if ($fundingRound): ?><span class="badge badge-funding" style="display: inline-block; margin-bottom: 8px; margin-left: 6px;"><i class="fa-solid fa-hand-holding-dollar"></i> Raising</span><?php endif; ?>
        <?php if ($s->for_sale && !$s->sold_at && $s->flipit_listing_id): ?>
          <?php $flipitUrl = $s->getFlipitListingUrl(); ?>
          <?php if ($flipitUrl): ?><a href="<?= e($flipitUrl) ?>" target="_blank" rel="noopener noreferrer" class="badge badge-for-sale" style="display: inline-block; margin-bottom: 8px; margin-left: 6px;"><i class="fa-solid fa-tag" aria-hidden="true"></i> For sale</a><?php endif; ?>
        <?php endif; ?>
        <?php if ($s->tagline): ?><p class="tagline"><?= e($s->tagline) ?></p><?php endif; ?>
        <div class="startup-meta">
          <?php if ($s->category): ?><span><?= e($s->category) ?></span><?php endif; ?>
          <?php if ($s->location): ?><span><?= e($s->location) ?></span><?php endif; ?>
          <?php if ($s->launch_date): ?><span><?= $s->launch_date->format('F Y') ?></span><?php endif; ?>
        </div>
        <div class="upvote-ui" style="margin-top: 12px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
          <?php $hasUpvoted = $hasUpvoted ?? false; ?>
          <?php if ($hasUpvoted): ?>
          <span class="upvote-btn" style="opacity: 0.8; cursor: default;" aria-label="Upvoted"><i class="fa-solid fa-arrow-up"></i></span>
          <span class="upvote-count"><?= (int)$s->upvotes ?></span>
          <span style="font-size: 0.875rem; color: var(--text-muted, #64748b);">You upvoted</span>
          <?php else: ?>
          <form action="<?= e(route('startup.upvote', $s->slug)) ?>" method="POST" style="display: inline;">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
          </form>
          <span class="upvote-count"><?= (int)$s->upvotes ?></span>
          <?php if (!auth()->check()): ?>
          <span style="font-size: 0.875rem; color: var(--text-muted, #64748b);">Log in to upvote</span>
          <?php endif; ?>
          <?php endif; ?>
          <?php if (!empty($s->website)): ?>
          <a href="<?= e(url('/startup/' . $s->slug . '/out')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="margin-left: 4px;"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Visit website</a>
          <?php endif; ?>
          <?php if (auth()->check()): ?>
          <?php $hasSaved = $hasSaved ?? false; ?>
          <?php if ($hasSaved): ?>
          <form action="<?= e(route('startup.unsave', $s->slug)) ?>" method="post" style="display: inline; margin-left: 4px;">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="btn btn-ghost"><i class="fa-solid fa-bookmark" aria-hidden="true"></i> Saved</button>
          </form>
          <?php else: ?>
          <form action="<?= e(route('startup.save', $s->slug)) ?>" method="post" style="display: inline; margin-left: 4px;">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="btn btn-ghost"><i class="fa-regular fa-bookmark" aria-hidden="true"></i> Save</button>
          </form>
          <?php endif; ?>
          <?php endif; ?>
          <a href="<?= e(route('startup.claim', $s->slug)) ?>" class="btn btn-ghost" style="margin-left: 4px;"><i class="fa-solid fa-hand-holding-hand" aria-hidden="true"></i> Claim this startup</a>
          <div class="share-ui share-ui--inline" style="margin-left: 8px; position: relative; display: inline-block;">
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
</section>

<div class="wrap">
  <?php if ($s->for_sale && !$s->sold_at && $s->flipit_listing_id): ?>
  <p style="font-size: 0.875rem; color: var(--text-muted, #64748b); margin-bottom: 16px;">This startup is listed for sale on <a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer">FLIPit</a>.</p>
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
  <section class="startup-section">
    <h2>About</h2>
    <p><?= nl2br(e($s->description)) ?></p>
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
          <?php if (!empty($f['email'])): ?><p class="startup-founder-email"><a href="mailto:<?= e($f['email']) ?>"><?= e($f['email']) ?></a></p><?php endif; ?>
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
      <a href="mailto:<?= e($fundingRound->contact_email) ?>" class="btn btn-primary"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Contact for investment</a>
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
    <p class="section-sub" style="margin-bottom: 16px;">More in <?= e($s->category ?: 'the directory') ?>.</p>
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

  <div class="cta-strip">
    <a href="<?= e(route('startup.claim', $s->slug)) ?>" class="btn btn-primary"><i class="fa-solid fa-hand-holding-hand" aria-hidden="true"></i> Claim this startup</a>
    <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Browse more startups</a>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your startup</a>
  </div>
</div>

<style>
.share-ui--inline .share-dropdown { position: absolute; top: 100%; left: 0; margin-top: 4px; min-width: 160px; background: var(--surface, #12141c); border: 1px solid var(--border, #2a2e3d); border-radius: var(--radius-sm, 8px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); z-index: 50; padding: 4px; }
.share-ui--inline .share-dropdown[hidden] { display: none; }
.share-dropdown-item { display: flex; align-items: center; gap: 8px; width: 100%; padding: 10px 12px; border: none; background: none; color: var(--text, #e8eaef); font: inherit; font-size: 0.9rem; text-align: left; cursor: pointer; border-radius: 6px; text-decoration: none; }
.share-dropdown-item:hover { background: var(--surface-hover, #1a1d28); color: var(--accent, #00d4aa); }
.share-dropdown-item i { width: 18px; opacity: 0.9; }
</style>
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
<style>
.startup-traffic-section { margin-top: 32px; }
.startup-traffic-card { background: linear-gradient(135deg, var(--surface, #1a1d28) 0%, var(--surface-hover, #22262f) 100%); border: 1px solid var(--border, #2a2e3d); border-radius: 12px; padding: 24px; }
.startup-traffic-total { display: flex; align-items: baseline; gap: 8px; margin-bottom: 20px; }
.startup-traffic-number { font-size: 2.5rem; font-weight: 700; color: var(--accent, #00d4aa); line-height: 1; }
.startup-traffic-label { font-size: 0.9rem; color: var(--text-muted, #64748b); }
.startup-traffic-chart { display: flex; align-items: flex-end; gap: 6px; height: 80px; margin-bottom: 8px; }
.startup-traffic-bar-wrap { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: flex-end; height: 100%; animation: startup-traffic-bar-in 0.5s ease-out forwards; opacity: 0; transform-origin: bottom; }
.startup-traffic-bar { height: var(--h, 0%); min-height: 2px; background: linear-gradient(to top, var(--accent, #00d4aa), rgba(0, 212, 170, 0.6)); border-radius: 4px 4px 0 0; }
@keyframes startup-traffic-bar-in { from { opacity: 0; transform: scaleY(0); } to { opacity: 1; transform: scaleY(1); } }
.startup-traffic-labels { display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted, #64748b); }
</style>
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
