<section class="page-head">
  <div class="wrap">
    <h1>Contact</h1>
    <p>Questions, partnerships, or feedback. We'll get back to you.</p>
  </div>
</section>

<div class="wrap content-block">
  <form class="form-max" action="#" method="get">
    <div class="form-group">
      <label class="form-label" for="name">Name</label>
      <input type="text" id="name" class="form-input" placeholder="Your name" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="email">Email</label>
      <input type="email" id="email" class="form-input" placeholder="you@example.com" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="subject">Subject</label>
      <select id="subject" class="form-select">
        <option value="">Choose…</option>
        <option value="listing">Listing / startup</option>
        <option value="partnership">Partnership</option>
        <option value="press">Press</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="message">Message</label>
      <textarea id="message" class="form-textarea" placeholder="Your message" required></textarea>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Send message</button>
      <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
