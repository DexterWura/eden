<section class="page-head">
  <div class="wrap">
    <h1>Log in</h1>
    <p>Sign in to your account.</p>
  </div>
</section>
<div class="wrap content-block form-max">
  <form action="<?= e(url('/login')) ?>" method="POST">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
    <div class="form-group">
      <label class="form-label" for="loginEmail">Email</label>
      <input type="email" id="loginEmail" name="email" class="form-input" value="<?= e(old('email')) ?>" required autofocus>
    </div>
    <div class="form-group">
      <label class="form-label" for="loginPassword">Password</label>
      <input type="password" id="loginPassword" name="password" class="form-input" required>
    </div>
    <div class="form-group">
      <label class="form-label">
        <input type="checkbox" name="remember"> Remember me
      </label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Log in</button>
    </div>
    <?php if (isset($errors) && $errors->any()): ?>
    <p class="form-hint" style="color: var(--error, #c00); margin-top: 12px;"><?= e($errors->first()) ?></p>
    <?php endif; ?>
  </form>
  <p style="margin-top: 20px;"><a href="<?= e(url('/register')) ?>">Create an account</a></p>
</div>
