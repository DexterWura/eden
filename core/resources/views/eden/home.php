<section class="hero">
  <div class="wrap">
    <h1>Discover the next wave of startups</h1>
    <p>Explore, search, and connect with innovative companies. One directory. Zero noise.</p>
    <div class="search-wrap">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="search" class="search-input" placeholder="Search startups, tags, or categories…" aria-label="Search">
    </div>
  </div>
</section>

<div class="wrap">
  <div class="launch-strip">
    <div class="wrap launch-strip-inner">
      <div>
        <h2>Startups launching today</h2>
        <p>Fresh launches. Be the first to discover them.</p>
      </div>
      <a href="<?= e(url('/launching-today')) ?>" class="btn btn-primary">View all</a>
    </div>
  </div>

  <section class="product-of-day">
    <h2 class="section-title">Product of the day</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">Top 5 by upvotes today.</p>
    <div class="startup-list">
      <div class="startup-card featured">
        <span class="card-rank">1</span>
        <div class="card-top">
          <div class="card-logo">Nx</div>
          <div class="card-badges"><span class="badge">Featured</span><span class="badge sponsored">Sponsored</span></div>
          <div class="upvote-ui">
            <button type="button" class="upvote-btn voted" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
            <span class="upvote-count">127</span>
          </div>
        </div>
        <a href="<?= e(url('/startup')) ?>" class="card-link">
          <h3 class="card-title">Nexus Pay</h3>
          <p class="card-desc">Instant cross-border payments and treasury for African businesses.</p>
          <div class="card-meta"><span>Fintech</span><span>Harare</span><span>2024</span></div>
          <p class="card-founder">Founded by <strong>Sarah Chen</strong></p>
          <div class="card-links">
            <a href="#"><i class="fa-solid fa-globe" aria-hidden="true"></i> Website</a>
            <a href="#" aria-label="X"><i class="fa-brands fa-x-twitter" aria-hidden="true"></i></a>
            <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
          </div>
        </a>
      </div>
      <div class="startup-card">
        <span class="card-rank">2</span>
        <div class="card-top">
          <div class="card-logo">Vx</div>
          <div class="card-badges"><span class="badge">New</span></div>
          <div class="upvote-ui">
            <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
            <span class="upvote-count">98</span>
          </div>
        </div>
        <a href="<?= e(url('/startup')) ?>" class="card-link">
          <h3 class="card-title">VitaFlow</h3>
          <p class="card-desc">Telehealth and prescription delivery across Zimbabwe.</p>
          <div class="card-meta"><span>Health</span><span>Bulawayo</span><span>2024</span></div>
          <p class="card-founder">Founded by <strong>James Moyo</strong></p>
          <div class="card-links">
            <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
            <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
          </div>
        </a>
      </div>
      <div class="startup-card">
        <span class="card-rank">3</span>
        <div class="card-top">
          <div class="card-logo">Qp</div>
          <div class="card-badges"><span class="badge launch">Launch</span></div>
          <div class="upvote-ui">
            <button type="button" class="upvote-btn voted" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
            <span class="upvote-count">76</span>
          </div>
        </div>
        <a href="<?= e(url('/startup')) ?>" class="card-link">
          <h3 class="card-title">QuickPay</h3>
          <p class="card-desc">One-tap payments for merchants. No hardware, no monthly fees.</p>
          <div class="card-meta"><span>Fintech</span><span>Harare</span><span>Today</span></div>
          <p class="card-founder">Founded by <strong>Tendai Banda</strong></p>
          <div class="card-links">
            <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
            <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
            <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
          </div>
        </a>
      </div>
      <div class="startup-card">
        <span class="card-rank">4</span>
        <div class="card-top">
          <div class="card-logo">Lm</div>
          <div class="card-badges"><span class="badge">Featured</span></div>
          <div class="upvote-ui">
            <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
            <span class="upvote-count">64</span>
          </div>
        </div>
        <a href="<?= e(url('/startup')) ?>" class="card-link">
          <h3 class="card-title">LearnMate</h3>
          <p class="card-desc">Adaptive learning platform for secondary school exam prep.</p>
          <div class="card-meta"><span>EdTech</span><span>Harare</span><span>2023</span></div>
          <p class="card-founder">Founded by <strong>Rudo Ncube</strong></p>
          <div class="card-links">
            <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
            <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
          </div>
        </a>
      </div>
      <div class="startup-card">
        <span class="card-rank">5</span>
        <div class="card-top">
          <div class="card-logo">Ax</div>
          <div class="card-badges"></div>
          <div class="upvote-ui">
            <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
            <span class="upvote-count">52</span>
          </div>
        </div>
        <a href="<?= e(url('/startup')) ?>" class="card-link">
          <h3 class="card-title">AgriSmart</h3>
          <p class="card-desc">AI-powered crop insights and market prices for smallholder farmers.</p>
          <div class="card-meta"><span>AgTech</span><span>Mutare</span><span>2023</span></div>
          <p class="card-founder">Founded by <strong>Peter Dube</strong></p>
          <div class="card-links">
            <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
            <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
          </div>
        </a>
      </div>
    </div>
  </section>

  <div class="filters" id="categories">
    <span>Category:</span>
    <button type="button" class="pill active">All</button>
    <button type="button" class="pill">Fintech</button>
    <button type="button" class="pill">Health</button>
    <button type="button" class="pill">AI & ML</button>
    <button type="button" class="pill">SaaS</button>
    <button type="button" class="pill">Marketplace</button>
    <button type="button" class="pill">EdTech</button>
    <button type="button" class="pill">Climate</button>
  </div>

  <h2 class="section-title">Startups <a href="<?= e(url('/launching-today')) ?>" class="section-link">Launching today →</a></h2>
  <div class="startup-list" id="startups">
    <div class="startup-card featured">
      <div class="card-top">
        <div class="card-logo">Nx</div>
        <div class="card-badges"><span class="badge">Featured</span><span class="badge sponsored">Sponsored</span></div>
        <div class="upvote-ui">
          <button type="button" class="upvote-btn voted" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
          <span class="upvote-count">127</span>
        </div>
      </div>
      <a href="<?= e(url('/startup')) ?>" class="card-link">
        <h3 class="card-title">Nexus Pay</h3>
        <p class="card-desc">Instant cross-border payments and treasury for African businesses.</p>
        <div class="card-meta"><span>Fintech</span><span>Harare</span><span>2024</span></div>
        <p class="card-founder">Founded by <strong>Sarah Chen</strong></p>
        <div class="card-links">
          <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
          <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
          <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </a>
    </div>
    <div class="startup-card">
      <div class="card-top">
        <div class="card-logo">Vx</div>
        <div class="card-badges"><span class="badge">New</span></div>
        <div class="upvote-ui">
          <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
          <span class="upvote-count">98</span>
        </div>
      </div>
      <a href="<?= e(url('/startup')) ?>" class="card-link">
        <h3 class="card-title">VitaFlow</h3>
        <p class="card-desc">Telehealth and prescription delivery across Zimbabwe.</p>
        <div class="card-meta"><span>Health</span><span>Bulawayo</span><span>2024</span></div>
        <p class="card-founder">Founded by <strong>James Moyo</strong></p>
        <div class="card-links">
          <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
          <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
        </div>
      </a>
    </div>
    <div class="startup-card">
      <div class="card-top">
        <div class="card-logo">Ax</div>
        <div class="card-badges"></div>
        <div class="upvote-ui">
          <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
          <span class="upvote-count">52</span>
        </div>
      </div>
      <a href="<?= e(url('/startup')) ?>" class="card-link">
        <h3 class="card-title">AgriSmart</h3>
        <p class="card-desc">AI-powered crop insights and market prices for smallholder farmers.</p>
        <div class="card-meta"><span>AgTech</span><span>Mutare</span><span>2023</span></div>
        <p class="card-founder">Founded by <strong>Peter Dube</strong></p>
        <div class="card-links">
          <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
          <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
        </div>
      </a>
    </div>
    <div class="startup-card">
      <div class="card-top">
        <div class="card-logo">Lm</div>
        <div class="card-badges"><span class="badge">Featured</span></div>
        <div class="upvote-ui">
          <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
          <span class="upvote-count">64</span>
        </div>
      </div>
      <a href="<?= e(url('/startup')) ?>" class="card-link">
        <h3 class="card-title">LearnMate</h3>
        <p class="card-desc">Adaptive learning platform for secondary school exam prep.</p>
        <div class="card-meta"><span>EdTech</span><span>Harare</span><span>2023</span></div>
        <p class="card-founder">Founded by <strong>Rudo Ncube</strong></p>
        <div class="card-links">
          <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
          <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </a>
    </div>
    <div class="startup-card">
      <div class="card-top">
        <div class="card-logo">Cr</div>
        <div class="card-badges"></div>
        <div class="upvote-ui">
          <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
          <span class="upvote-count">41</span>
        </div>
      </div>
      <a href="<?= e(url('/startup')) ?>" class="card-link">
        <h3 class="card-title">CarbonTrace</h3>
        <p class="card-desc">Carbon footprint tracking and offsets for enterprises.</p>
        <div class="card-meta"><span>Climate</span><span>Remote</span><span>2024</span></div>
        <p class="card-founder">Founded by <strong>Lisa Okonkwo</strong></p>
        <div class="card-links">
          <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
          <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </a>
    </div>
    <div class="startup-card">
      <div class="card-top">
        <div class="card-logo">Mk</div>
        <div class="card-badges"><span class="badge">New</span></div>
        <div class="upvote-ui">
          <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
          <span class="upvote-count">38</span>
        </div>
      </div>
      <a href="<?= e(url('/startup')) ?>" class="card-link">
        <h3 class="card-title">MarketHub</h3>
        <p class="card-desc">B2B marketplace connecting manufacturers and retailers.</p>
        <div class="card-meta"><span>Marketplace</span><span>Harare</span><span>2024</span></div>
        <p class="card-founder">Founded by <strong>David Sibanda</strong></p>
        <div class="card-links">
          <a href="#"><i class="fa-solid fa-globe"></i> Website</a>
          <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
          <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </a>
    </div>
  </div>

  <div class="cta-strip" id="submit">
    <h3>Launching something?</h3>
    <p>Get your startup in front of investors and customers. Submit in under 2 minutes.</p>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your startup</a>
    <a href="<?= e(url('/about')) ?>" class="btn btn-ghost">View guidelines</a>
  </div>

  <div class="newsletter">
    <input type="email" placeholder="Your email" aria-label="Email">
    <button type="button" class="btn btn-primary">Subscribe</button>
    <p class="newsletter-note">Weekly digest of new startups. No spam.</p>
  </div>
</div>
