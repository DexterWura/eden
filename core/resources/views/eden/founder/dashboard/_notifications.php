<?php if ($unreadNotifications->isNotEmpty()): ?>
<section class="founder-notices" aria-labelledby="founder-notices-title">
  <h2 id="founder-notices-title" class="dash-sr-only">Notifications</h2>
  <?php foreach ($unreadNotifications as $notification): ?>
    <?php
      $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
      $title = $data['title'] ?? 'Notice';
      $message = $data['message'] ?? '';
    ?>
    <article class="founder-notice">
      <i class="fa-solid fa-bell" aria-hidden="true"></i>
      <div class="founder-notice-copy">
        <strong><?= e($title) ?></strong>
        <?php if ($message !== ''): ?><p><?= e($message) ?></p><?php endif; ?>
      </div>
      <form action="<?= e(route('founder.notifications.dismiss', $notification->id)) ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="dash-btn dash-btn-secondary">Dismiss<span class="dash-sr-only"> <?= e($title) ?></span></button>
      </form>
    </article>
  <?php endforeach; ?>
</section>
<?php endif; ?>
