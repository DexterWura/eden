<section class="dash-card" aria-labelledby="founder-activity-title">
  <div class="dash-card-header">
    <div>
      <h2 id="founder-activity-title" class="dash-card-title">Recent activity</h2>
      <p class="dash-card-subtitle">Upvotes, comments, revenue, and launches across your startups.</p>
    </div>
  </div>
  <?php if ($activity->isEmpty()): ?>
    <div class="dash-placeholder">
      <div class="dash-placeholder-icon"><i class="fa-regular fa-clock" aria-hidden="true"></i></div>
      Activity will appear here as your startups gain momentum.
    </div>
  <?php else: ?>
  <ol class="founder-activity-list">
    <?php foreach ($activity as $event): ?>
      <?php
        $startup = $event['startup'];
        $eventMeta = [
          'upvote' => ['fa-arrow-up', 'New upvote'],
          'comment' => ['fa-comment', 'New comment'],
          'revenue' => ['fa-coins', 'Revenue recorded'],
          'launch' => ['fa-rocket', 'Startup launched'],
        ][$event['type']];
      ?>
      <li class="founder-activity-item founder-activity-item--<?= e($event['type']) ?>">
        <span class="founder-activity-icon" aria-hidden="true"><i class="fa-solid <?= e($eventMeta[0]) ?>"></i></span>
        <div class="founder-activity-copy">
          <div>
            <strong><?= e($eventMeta[1]) ?></strong>
            <a href="<?= e(route('startup.show', $startup->slug)) ?>" target="_blank" rel="noopener"><?= e($startup->name) ?></a>
          </div>
          <?php if ($event['type'] === 'comment'): ?>
            <p><?= e($event['actor'] ?: 'A community member') ?>: “<?= e(\Illuminate\Support\Str::limit($event['body'], 100)) ?>”</p>
          <?php elseif ($event['type'] === 'upvote'): ?>
            <p><?= e($event['actor'] ?: 'A community member') ?> supported your startup.</p>
          <?php elseif ($event['type'] === 'revenue'): ?>
            <p><?= e(strtoupper($event['currency'] ?: 'USD')) ?> <?= e(number_format($event['amount'], 2)) ?> was added to your revenue activity.</p>
          <?php else: ?>
            <p>Your launch date is now part of your startup timeline.</p>
          <?php endif; ?>
        </div>
        <time datetime="<?= e($event['occurredAt']->toAtomString()) ?>" title="<?= e($event['occurredAt']->format('M j, Y g:i A')) ?>">
          <?= e($event['occurredAt']->diffForHumans()) ?>
        </time>
      </li>
    <?php endforeach; ?>
  </ol>
  <?php endif; ?>
</section>
