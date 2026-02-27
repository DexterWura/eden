<script>
  (function() {
    var csrf = '<?= e(csrf_token()) ?>';
    function toast(type, msg) {
      if (typeof notify === 'function') notify(type, msg);
      else alert(msg);
    }

    document.querySelectorAll('.upvote-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var url = btn.getAttribute('data-upvote-url');
        if (!url) return;
        if (btn.dataset.loading === '1') return;
        btn.dataset.loading = '1';

        fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({})
        }).then(function(r) {
          return r.json().then(function(data) { return { status: r.status, data: data }; });
        }).then(function(res) {
          var data = res.data || {};
          if (res.status === 401) {
            toast('info', data.message || 'Log in to upvote.');
            return;
          }
          if (res.status >= 400) {
            toast('error', data.message || 'Upvote failed.');
            return;
          }

          var countEl = btn.nextElementSibling;
          if (countEl && typeof data.upvotes !== 'undefined') {
            countEl.textContent = String(data.upvotes);
          }
          btn.classList.add('voted');
          toast(data.already ? 'info' : 'success', data.message || (data.already ? 'Already upvoted.' : 'Upvoted.'));
        }).catch(function() {
          toast('error', 'Upvote failed.');
        }).finally(function() {
          btn.dataset.loading = '0';
        });
      });
    });
  })();
</script>
