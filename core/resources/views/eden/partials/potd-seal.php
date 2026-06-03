<?php
$productOfDayDate = $productOfDayDate ?? null;
$isProductOfDayToday = $isProductOfDayToday ?? false;
if (!$productOfDayDate) {
    return;
}
$dateLabel = $productOfDayDate->format('j F Y');
$ariaLabel = 'Product of the day, ' . $dateLabel;
if ($isProductOfDayToday) {
    $ariaLabel .= ', today';
}
?>
<div class="potd-seal-group">
  <span class="potd-seal" role="status" aria-label="<?= e($ariaLabel) ?>">
    <svg class="potd-seal-laurel" viewBox="0 0 14 60" width="14" height="60" aria-hidden="true" focusable="false">
      <g fill="currentColor">
        <ellipse cx="4" cy="6" rx="2.2" ry="4.5" transform="rotate(-35 4 6)"/>
        <ellipse cx="5.5" cy="14" rx="2.2" ry="4.5" transform="rotate(-20 5.5 14)"/>
        <ellipse cx="7" cy="22" rx="2.2" ry="4.5" transform="rotate(-5 7 22)"/>
        <ellipse cx="7.5" cy="30" rx="2.2" ry="4.5" transform="rotate(10 7.5 30)"/>
        <ellipse cx="7" cy="38" rx="2.2" ry="4.5" transform="rotate(25 7 38)"/>
        <ellipse cx="5.5" cy="46" rx="2.2" ry="4.5" transform="rotate(40 5.5 46)"/>
        <ellipse cx="4" cy="54" rx="2.2" ry="4.5" transform="rotate(55 4 54)"/>
      </g>
    </svg>
    <span class="potd-seal-body">
      <span class="potd-seal-kicker">Product</span>
      <span class="potd-seal-kicker">of the day</span>
      <span class="potd-seal-date"><?= e($dateLabel) ?></span>
    </span>
    <svg class="potd-seal-laurel potd-seal-laurel--right" viewBox="0 0 14 60" width="14" height="60" aria-hidden="true" focusable="false">
      <g fill="currentColor">
        <ellipse cx="4" cy="6" rx="2.2" ry="4.5" transform="rotate(-35 4 6)"/>
        <ellipse cx="5.5" cy="14" rx="2.2" ry="4.5" transform="rotate(-20 5.5 14)"/>
        <ellipse cx="7" cy="22" rx="2.2" ry="4.5" transform="rotate(-5 7 22)"/>
        <ellipse cx="7.5" cy="30" rx="2.2" ry="4.5" transform="rotate(10 7.5 30)"/>
        <ellipse cx="7" cy="38" rx="2.2" ry="4.5" transform="rotate(25 7 38)"/>
        <ellipse cx="5.5" cy="46" rx="2.2" ry="4.5" transform="rotate(40 5.5 46)"/>
        <ellipse cx="4" cy="54" rx="2.2" ry="4.5" transform="rotate(55 4 54)"/>
      </g>
    </svg>
  </span>
  <?php if ($isProductOfDayToday): ?>
  <span class="potd-seal-today" role="status" aria-label="Product of the day today">
    <span class="potd-seal-today-kicker">Product of the day</span>
    <span class="potd-seal-today-accent">today</span>
  </span>
  <?php endif; ?>
</div>
