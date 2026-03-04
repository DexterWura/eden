<section class="page-head">
  <div class="wrap">
    <h1>Sign up</h1>
    <p>Create your account. Free.</p>
  </div>
</section>
<div class="wrap content-block form-max">
  <?php if (\App\Http\Controllers\Eden\LinkedInAuthController::isConfigured()): ?>
  <a href="<?= e(url('/auth/linkedin')) ?>" class="btn btn-ghost btn-block btn-linkedin" style="margin-bottom: 20px; justify-content: center; gap: 8px;">
    <i class="fa-brands fa-linkedin" aria-hidden="true"></i> Continue with LinkedIn
  </a>
  <div class="auth-divider"><span>or create an account with email</span></div>
  <?php endif; ?>
  <form action="<?= e(url('/register')) ?>" method="POST">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
    <div class="form-group">
      <label class="form-label" for="signupName">Name</label>
      <input type="text" id="signupName" name="name" class="form-input" value="<?= e(old('name')) ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="signupEmail">Email</label>
      <input type="email" id="signupEmail" name="email" class="form-input" value="<?= e(old('email')) ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="signupPassword">Password</label>
      <input type="password" id="signupPassword" name="password" class="form-input" required minlength="8">
    </div>
    <div class="form-group">
      <label class="form-label" for="signupPasswordConfirmation">Confirm password</label>
      <input type="password" id="signupPasswordConfirmation" name="password_confirmation" class="form-input" required minlength="8">
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Create account</button>
    </div>
    <?php if (isset($errors) && $errors->any()): ?>
    <p class="form-hint" style="color: var(--error, #c00); margin-top: 12px;"><?= e($errors->first()) ?></p>
    <?php endif; ?>
  </form>
  <p style="margin-top: 20px;"><a href="<?= e(url('/login')) ?>">Already have an account? Log in</a></p>
</div>
