<script>
  var upvoteBtn = document.querySelector('.upvote-btn');
  if (upvoteBtn) {
    upvoteBtn.addEventListener('click', function() {
      var countEl = this.nextElementSibling;
      if (countEl) {
        if (this.classList.toggle('voted')) {
          countEl.textContent = parseInt(countEl.textContent, 10) + 1;
        } else {
          countEl.textContent = Math.max(0, parseInt(countEl.textContent, 10) - 1);
        }
      }
    });
  }
</script>
