<section class="founder-metrics" aria-label="Portfolio overview">
  <?php
    $metricCards = [
      ['label' => 'Apps', 'value' => $myStartups->count(), 'icon' => 'fa-building-user'],
      ['label' => 'Upvotes', 'value' => number_format($totals['upvotes'] ?? 0), 'icon' => 'fa-arrow-up'],
      ['label' => 'Comments', 'value' => number_format($totals['comments'] ?? 0), 'icon' => 'fa-comments'],
      ['label' => 'Community saves', 'value' => number_format($totals['saves'] ?? 0), 'icon' => 'fa-bookmark'],
    ];
    if (auth()->user()->isPro()) {
      $metricCards[] = ['label' => 'Views', 'value' => number_format($totals['views'] ?? 0), 'icon' => 'fa-eye'];
      $metricCards[] = ['label' => 'MRR', 'value' => '$' . number_format($totals['mrr'] ?? 0, 2), 'icon' => 'fa-chart-line'];
    }
  ?>
  <?php foreach ($metricCards as $metric): ?>
  <article class="founder-metric">
    <div class="founder-metric-icon" aria-hidden="true"><i class="fa-solid <?= e($metric['icon']) ?>"></i></div>
    <div>
      <p><?= e($metric['label']) ?></p>
      <strong><?= e($metric['value']) ?></strong>
    </div>
  </article>
  <?php endforeach; ?>
</section>
