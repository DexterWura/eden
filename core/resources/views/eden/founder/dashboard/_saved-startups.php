<section class="dash-card founder-rail-card" aria-labelledby="founder-saved-title">
  <div class="dash-card-header">
    <div>
      <h2 id="founder-saved-title" class="dash-card-title">Saved by you</h2>
      <p class="dash-card-subtitle"><?= e($savedStartups['count']) ?> <?= $savedStartups['count'] === 1 ? 'app' : 'apps' ?> saved</p>
    </div>
    <i class="fa-regular fa-bookmark founder-rail-header-icon" aria-hidden="true"></i>
  </div>
  <?php if ($savedStartups['recent']->isEmpty()): ?>
    <div class="founder-rail-empty">
      <p>Save interesting apps while exploring Eden and they will be easy to find here.</p>
      <a href="<?= e(url('/')) ?>" class="founder-text-link">Discover apps</a>
    </div>
  <?php else: ?>
    <ul class="founder-saved-list">
      <?php foreach ($savedStartups['recent'] as $saved): ?>
        <?php if ($saved->startup): ?>
        <li>
          <span class="founder-saved-mark" aria-hidden="true"><?= e($saved->startup->logo_letters) ?></span>
          <div>
            <a href="<?= e(route('startup.show', $saved->startup->slug)) ?>"><?= e($saved->startup->name) ?></a>
            <p><?= e(\Illuminate\Support\Str::limit($saved->startup->tagline ?: 'App on Eden', 55)) ?></p>
          </div>
        </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
    <div class="dash-card-footer"><a href="<?= e(route('saved')) ?>">View all saved apps <span aria-hidden="true">→</span></a></div>
  <?php endif; ?>
</section>
