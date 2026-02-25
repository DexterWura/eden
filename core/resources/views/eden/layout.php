<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(isset($title) ? $title . ' — Eden' : 'Eden — Startup Directory') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
</head>
<body>
  <div class="bg-grid"></div>
  <div class="bg-glow"></div>

  <header class="site-header">
    <div class="wrap header-inner">
      <a href="<?= e(url('/')) ?>" class="logo">Eden</a>
      <nav class="nav-main">
        <a href="<?= e(url('/launching-today')) ?>">Launching today</a>
        <a href="<?= e(url('/categories')) ?>">Categories</a>
        <a href="<?= e(url('/#startups')) ?>">Startups</a>
        <a href="<?= e(url('/submit')) ?>">Submit</a>
        <a href="<?= e(url('/about')) ?>">About</a>
        <a href="<?= e(url('/contact')) ?>">Contact</a>
        <a href="#" class="btn btn-ghost" data-modal="login">Log in</a>
        <a href="#" class="btn btn-primary" data-modal="signup">Sign up</a>
      </nav>
      <button type="button" class="nav-toggle" aria-label="Open menu" id="navToggle"><i class="fa-solid fa-bars"></i></button>
    </div>
  </header>

  <main>
    <?= $content ?? '' ?>
  </main>

  <div class="nav-drawer-backdrop" id="navBackdrop"></div>
  <aside class="nav-drawer" id="navDrawer">
    <a href="<?= e(url('/')) ?>" class="logo">Eden</a>
    <a href="<?= e(url('/launching-today')) ?>">Launching today</a>
    <a href="<?= e(url('/categories')) ?>">Categories</a>
    <a href="<?= e(url('/#startups')) ?>">Startups</a>
    <a href="<?= e(url('/submit')) ?>">Submit</a>
    <a href="<?= e(url('/about')) ?>">About</a>
    <a href="<?= e(url('/contact')) ?>">Contact</a>
    <a href="#" class="btn btn-ghost" data-modal="login">Log in</a>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Add startup</a>
    <a href="<?= e(url('/startup')) ?>" style="font-size: 0.9rem; margin-top: 8px;">Startup dashboard</a>
    <a href="<?= e(url('/backoffice')) ?>" style="font-size: 0.9rem;">Admin</a>
  </aside>

  <div class="modal-overlay" id="modalLogin" aria-hidden="true">
    <div class="modal" role="dialog" aria-labelledby="loginTitle">
      <div class="modal-header">
        <h2 id="loginTitle">Log in</h2>
        <button type="button" class="modal-close" aria-label="Close" data-close="modalLogin"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <form action="#" method="get">
          <div class="form-group">
            <label class="form-label" for="loginEmail">Email</label>
            <input type="email" id="loginEmail" class="form-input" placeholder="you@example.com" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="loginPassword">Password</label>
            <input type="password" id="loginPassword" class="form-input" placeholder="••••••••" required>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        Don't have an account? <a href="#" data-switch="signup">Sign up</a>
        <a href="<?= e(url('/startup')) ?>">Startup dashboard</a>
        <a href="<?= e(url('/backoffice')) ?>">Admin</a>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modalSignup" aria-hidden="true">
    <div class="modal" role="dialog" aria-labelledby="signupTitle">
      <div class="modal-header">
        <h2 id="signupTitle">Sign up</h2>
        <button type="button" class="modal-close" aria-label="Close" data-close="modalSignup"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <form action="#" method="get">
          <div class="form-group">
            <label class="form-label" for="signupName">Name</label>
            <input type="text" id="signupName" class="form-input" placeholder="Your name" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="signupEmail">Email</label>
            <input type="email" id="signupEmail" class="form-input" placeholder="you@example.com" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="signupPassword">Password</label>
            <input type="password" id="signupPassword" class="form-input" placeholder="At least 8 characters" required minlength="8">
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Create account</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        Already have an account? <a href="#" data-switch="login">Log in</a>
      </div>
    </div>
  </div>

  <footer class="site-footer">
    <div class="wrap">
      <p><a href="<?= e(url('/')) ?>">Eden</a> — Startup directory. <a href="#">Privacy</a> · <a href="#">Terms</a> · <a href="<?= e(url('/contact')) ?>">Contact</a> · <a href="<?= e(url('/startup')) ?>">Dashboard</a> · <a href="<?= e(url('/backoffice')) ?>">Admin</a></p>
    </div>
  </footer>

  <script>
    (function() {
      var navToggle = document.getElementById('navToggle');
      var navDrawer = document.getElementById('navDrawer');
      var navBackdrop = document.getElementById('navBackdrop');
      if (navToggle && navDrawer) {
        navToggle.addEventListener('click', function() {
          navDrawer.classList.toggle('is-open');
          if (navBackdrop) navBackdrop.classList.toggle('is-open');
          document.body.style.overflow = navDrawer.classList.contains('is-open') ? 'hidden' : '';
        });
        if (navBackdrop) {
          navBackdrop.addEventListener('click', function() {
            navDrawer.classList.remove('is-open');
            navBackdrop.classList.remove('is-open');
            document.body.style.overflow = '';
          });
        }
      }
      document.querySelectorAll('[data-modal]').forEach(function(el) {
        el.addEventListener('click', function(e) {
          e.preventDefault();
          var id = 'modal' + (this.getAttribute('data-modal') === 'login' ? 'Login' : 'Signup');
          var modal = document.getElementById(id);
          if (modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
          }
        });
      });
      document.querySelectorAll('[data-close]').forEach(function(el) {
        el.addEventListener('click', function() {
          var modal = document.getElementById(this.getAttribute('data-close'));
          if (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
          }
        });
      });
      document.querySelectorAll('[data-switch]').forEach(function(el) {
        el.addEventListener('click', function(e) {
          e.preventDefault();
          document.querySelectorAll('.modal-overlay').forEach(function(m) {
            m.classList.remove('is-open');
            m.setAttribute('aria-hidden', 'true');
          });
          var id = 'modal' + (this.getAttribute('data-switch') === 'login' ? 'Login' : 'Signup');
          var modal = document.getElementById(id);
          if (modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
          }
        });
      });
      document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
          if (e.target === overlay) {
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
          }
        });
      });
    })();
  </script>
  <?= $scripts ?? '' ?>
</body>
</html>
