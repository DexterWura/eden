<section class="founder-onboarding" aria-labelledby="founder-onboarding-title">
  <div class="founder-onboarding-copy">
    <span class="founder-onboarding-icon" aria-hidden="true"><i class="fa-solid fa-rocket"></i></span>
    <p class="founder-home-eyebrow">Your first launch</p>
    <h2 id="founder-onboarding-title">Put your app on Eden</h2>
    <p>Create a useful profile to join discovery, earn community upvotes, and start building your leaderboard position.</p>
    <a href="<?= e(route('founder.startups.create')) ?>" class="dash-btn dash-btn-primary">
      Create your app profile <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
  </div>
  <ol class="founder-onboarding-steps" aria-label="Getting started steps">
    <li>
      <span>1</span>
      <div><strong>Build your profile</strong><p>Add your product story, audience, features, and media.</p></div>
    </li>
    <li>
      <span>2</span>
      <div><strong>Verify ownership</strong><p>Claim your website through DNS or a verification file.</p></div>
    </li>
    <li>
      <span>3</span>
      <div><strong>Launch and grow</strong><p>Share your listing and follow activity from this dashboard.</p></div>
    </li>
  </ol>
</section>

<div class="founder-empty-actions">
  <a href="<?= e(route('saved')) ?>" class="founder-action-tile">
    <i class="fa-regular fa-bookmark" aria-hidden="true"></i>
    <span><strong>Saved apps</strong><small>Revisit products that caught your eye</small></span>
  </a>
  <a href="<?= e(url('/leaderboard')) ?>" class="founder-action-tile">
    <i class="fa-solid fa-ranking-star" aria-hidden="true"></i>
    <span><strong>Explore the leaderboard</strong><small>See what the community is discovering</small></span>
  </a>
</div>
