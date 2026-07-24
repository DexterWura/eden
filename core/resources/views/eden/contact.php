<section class="page-head">
  <div class="wrap">
    <h1>Contact</h1>
    <p>Questions, partnerships, or feedback. We'll get back to you.</p>
  </div>
</section>

<?php
$contactPrefill = $contactPrefill ?? [];
$subjectValue = old('subject', $contactPrefill['subject'] ?? null);
$prefillStartup = $contactPrefill['startup'] ?? null;
$messageValue = old('message', $contactPrefill['message'] ?? null);
?>
<div class="wrap content-block contact-form-wrap" style="max-width: 560px; margin-left: auto; margin-right: auto;">
  <?php if (session('success')): ?>
  <p class="form-hint" style="color: var(--success, #0a0); margin-bottom: 16px;"><?= e(session('success')) ?></p>
  <?php endif; ?>
  <form class="form-max" action="<?= e(url('/contact')) ?>" method="POST">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
    <div class="form-group">
      <label class="form-label" for="name">Name</label>
      <input type="text" id="name" name="name" class="form-input" placeholder="Your name" value="<?= e(old('name')) ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="email">Email</label>
      <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" value="<?= e(old('email')) ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="subject">Subject</label>
      <select id="subject" name="subject" class="form-select">
        <option value="">Choose...</option>
        <option value="listing" <?= ($subjectValue === 'listing') ? 'selected' : '' ?>>Listing / app</option>
        <option value="partnership" <?= ($subjectValue === 'partnership') ? 'selected' : '' ?>>Partnership</option>
        <option value="press" <?= ($subjectValue === 'press') ? 'selected' : '' ?>>Press</option>
        <option value="other" <?= ($subjectValue === 'other') ? 'selected' : '' ?>>Other</option>
      </select>
    </div>
    <?php if ($prefillStartup): ?>
    <div class="form-group">
      <label class="form-label" for="startupContext">App</label>
      <input type="text" id="startupContext" class="form-input" value="<?= e($prefillStartup) ?>" readonly>
    </div>
    <?php endif; ?>
    <div class="form-group">
      <label class="form-label" for="message">Message</label>
      <textarea id="message" name="message" class="form-textarea" placeholder="Your message" required><?= e($messageValue) ?></textarea>
    </div>
    <?php if (isset($errors) && $errors->any()): ?>
    <p class="form-hint" style="color: var(--error, #c00); margin-bottom: 12px;"><?= e($errors->first()) ?></p>
    <?php endif; ?>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Send message</button>
      <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
