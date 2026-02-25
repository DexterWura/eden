<script>
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
