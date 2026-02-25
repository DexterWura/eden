<script>
  document.querySelectorAll('.pill').forEach(function(pill) {
    pill.addEventListener('click', function() {
      document.querySelectorAll('.pill').forEach(function(p) { p.classList.remove('active'); });
      this.classList.add('active');
    });
  });

  (function() {
    var searchEl = document.querySelector('.search-input');
    if (!searchEl) return;
    var debounceMs = 300;
    var timeoutId = null;
    function runSearch() {
      var q = (searchEl.value || '').trim().toLowerCase();
      document.querySelectorAll('.startup-list').forEach(function(list) {
        var cards = list.querySelectorAll('.startup-card');
        var emptyNote = list.querySelector('.text-muted');
        var visible = 0;
        cards.forEach(function(card) {
          var match = !q || (card.getAttribute('data-search') || '').indexOf(q) !== -1;
          card.style.display = match ? '' : 'none';
          if (match) visible++;
        });
        if (emptyNote) emptyNote.style.display = (cards.length && !visible) ? '' : 'none';
      });
    }
    searchEl.addEventListener('input', function() {
      if (timeoutId) clearTimeout(timeoutId);
      timeoutId = setTimeout(function() {
        timeoutId = null;
        runSearch();
      }, debounceMs);
    });
    if (searchEl.value) runSearch();
  })();

  document.querySelectorAll('.upvote-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var countEl = this.nextElementSibling;
      if (countEl) {
        if (this.classList.toggle('voted')) {
          countEl.textContent = parseInt(countEl.textContent, 10) + 1;
        } else {
          countEl.textContent = Math.max(0, parseInt(countEl.textContent, 10) - 1);
        }
      }
    });
  });
</script>
