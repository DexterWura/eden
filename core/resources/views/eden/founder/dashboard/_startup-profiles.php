<section class="dash-card founder-profile-card" aria-labelledby="founder-profile-title">
  <div class="dash-card-header">
    <div>
      <h2 id="founder-profile-title" class="dash-card-title">App health</h2>
      <p class="dash-card-subtitle">Complete profiles are easier to discover and trust.</p>
    </div>
    <a href="<?= e(route('founder.startups.index')) ?>" class="founder-text-link">Manage apps</a>
  </div>
  <div class="founder-profile-list">
    <?php foreach ($startupProfiles as $profile): ?>
      <?php
        $startup = $profile['startup'];
        $claimLabels = [
          'verified' => ['Verified owner', 'success', 'fa-circle-check'],
          'pending' => ['Verification pending', 'warning', 'fa-clock'],
          'unverified' => ['Ownership not verified', 'muted', 'fa-shield-halved'],
        ];
        $claim = $claimLabels[$profile['claimStatus']];
      ?>
      <article class="founder-profile">
        <div class="founder-profile-heading">
          <div class="founder-startup-identity">
            <span class="founder-startup-logo" aria-hidden="true"><?= e($startup->logo_letters) ?></span>
            <div>
              <h3><a href="<?= e(route('startup.show', $startup->slug)) ?>" target="_blank" rel="noopener"><?= e($startup->name) ?></a></h3>
              <p><?= e($startup->category ?: 'Uncategorised') ?> · <?= e(ucfirst($startup->status ?? 'pending')) ?></p>
            </div>
          </div>
          <a href="<?= e(route('founder.startups.edit', $startup)) ?>" class="dash-btn dash-btn-secondary">Edit profile</a>
        </div>

        <div class="founder-profile-facts">
          <div>
            <span>Global rank</span>
            <strong><?= $profile['globalRank'] ? '#' . e($profile['globalRank']) : 'Not ranked' ?></strong>
          </div>
          <div>
            <span><?= e($startup->category ?: 'Category') ?> rank</span>
            <strong><?= $profile['categoryRank'] ? '#' . e($profile['categoryRank']) : 'Not ranked' ?></strong>
          </div>
          <div>
            <span>Profile completeness</span>
            <strong><?= e($profile['completeness']) ?>%</strong>
          </div>
        </div>

        <div class="founder-completeness">
          <div class="founder-progress" role="progressbar" aria-label="<?= e($startup->name) ?> profile completeness" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e($profile['completeness']) ?>">
            <span style="width: <?= e($profile['completeness']) ?>%"></span>
          </div>
          <?php if ($profile['gaps']): ?>
            <details class="founder-gaps">
              <summary><?= e(count($profile['gaps'])) ?> profile <?= count($profile['gaps']) === 1 ? 'gap' : 'gaps' ?> to address</summary>
              <ul>
                <?php foreach ($profile['gaps'] as $gap): ?><li><?= e($gap) ?></li><?php endforeach; ?>
              </ul>
            </details>
          <?php else: ?>
            <p class="founder-complete-message"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Your profile covers every recommended section.</p>
          <?php endif; ?>
        </div>

        <div class="founder-profile-facts">
          <div>
            <span>Launch</span>
            <strong>
              <?php if ($profile['daysUntilLaunch'] !== null): ?>
                <?= e($profile['daysUntilLaunch']) ?> <?= $profile['daysUntilLaunch'] === 1 ? 'day' : 'days' ?> to go
              <?php elseif ($profile['launchDate']): ?>
                Launched <?= e($profile['launchDate']->format('M j, Y')) ?>
              <?php else: ?>
                Date not set
              <?php endif; ?>
            </strong>
          </div>
          <div>
            <span>Launch readiness</span>
            <strong><?= $profile['launchReadiness'] === 'ready' ? 'Ready' : 'Needs attention' ?></strong>
          </div>
          <div>
            <span>Founder growth</span>
            <strong><?= e($profile['cofounderStatus']) ?> invites · <?= e($profile['investorStatus']) ?> new leads</strong>
          </div>
        </div>
        <p class="dash-card-subtitle"><?= e($profile['launchGuidance']) ?></p>
        <details class="founder-gaps" style="margin-top:10px;">
          <summary>Preview public share card</summary>
          <div style="display:grid;grid-template-columns:<?= $profile['sharePreview']['metaImage'] ? '96px 1fr' : '1fr' ?>;gap:12px;padding-top:12px;">
            <?php if ($profile['sharePreview']['metaImage']): ?>
            <img src="<?= e($profile['sharePreview']['metaImage']) ?>" alt="" width="96" height="96" style="object-fit:cover;border-radius:8px;">
            <?php endif; ?>
            <div>
              <strong><?= e($profile['sharePreview']['pageTitle']) ?></strong>
              <p style="margin:4px 0;"><?= e($profile['sharePreview']['metaDescription']) ?></p>
              <small><?= e($profile['sharePreview']['canonicalUrl']) ?></small>
            </div>
          </div>
        </details>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
          <button type="button" class="dash-btn dash-btn-secondary" data-copy-text="<?= e($profile['sharePreview']['shareText'] . ' ' . $profile['sharePreview']['shareUrl']) ?>"><i class="fa-solid fa-copy"></i> Copy launch post</button>
          <a class="dash-btn dash-btn-secondary" target="_blank" rel="noopener" href="<?= e($profile['sharePreview']['xShareUrl']) ?>"><i class="fa-brands fa-x-twitter"></i> Share</a>
          <a class="dash-btn dash-btn-secondary" target="_blank" rel="noopener" href="<?= e($profile['sharePreview']['linkedInShareUrl']) ?>"><i class="fa-brands fa-linkedin"></i> LinkedIn</a>
        </div>

        <div class="founder-profile-footer">
          <div class="founder-claim-state">
            <span class="dash-badge dash-badge-<?= e($claim[1]) ?>"><i class="fa-solid <?= e($claim[2]) ?>" aria-hidden="true"></i> <?= e($claim[0]) ?></span>
            <?php if ($profile['claimStatus'] !== 'verified' && $startup->isActive()): ?>
              <a href="<?= e(route('startup.claim', $startup->slug)) ?>">Review verification</a>
            <?php endif; ?>
          </div>
          <?php if ($profile['awards']->isNotEmpty()): ?>
          <ul class="founder-awards" aria-label="<?= e($startup->name) ?> awards">
            <?php foreach ($profile['awards'] as $award): ?>
              <?php $awardDate = is_object($award['date']) ? $award['date']->format('M Y') : (string) $award['date']; ?>
              <li title="<?= e($awardDate) ?>"><i class="fa-solid fa-trophy" aria-hidden="true"></i> <?= e($award['label']) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php else: ?>
            <span class="founder-no-awards">No awards yet — keep building momentum.</span>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
