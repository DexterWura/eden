/**
 * Eden backoffice – migration page logic.
 * Expects window.EDEN_MIGRATION = { runUrl, runSpecificUrlTemplate, rollbackUrl, refreshUrl, csrfToken }.
 */
(function () {
  'use strict';

  var config = window.EDEN_MIGRATION || {};
  var runUrl = config.runUrl || '';
  var runSpecificUrlTemplate = config.runSpecificUrlTemplate || '';
  var rollbackUrl = config.rollbackUrl || '';
  var refreshUrl = config.refreshUrl || '';
  var csrfToken = config.csrfToken || '';

  var currentAction = null;
  var currentMigration = null;
  var confirmModalEl = document.getElementById('confirmModal');
  var confirmModal = confirmModalEl && typeof bootstrap !== 'undefined'
    ? bootstrap.Modal.getOrCreateInstance(confirmModalEl)
    : null;

  function showConfirmModal() {
    if (confirmModal) {
      confirmModal.show();
    } else if (typeof $ !== 'undefined' && $('#confirmModal').length) {
      $('#confirmModal').modal('show');
    }
  }

  function hideConfirmModal() {
    if (confirmModal) {
      confirmModal.hide();
    } else if (typeof $ !== 'undefined') {
      $('#confirmModal').modal('hide');
    }
  }

  window.runMigrations = function () {
    currentAction = 'run';
    var msg = document.getElementById('confirmMessage');
    if (msg) msg.textContent = 'Are you sure you want to run all pending migrations?';
    showConfirmModal();
  };

  window.runSpecificMigration = function (migrationName) {
    currentAction = 'run-specific';
    currentMigration = migrationName;
    var msg = document.getElementById('confirmMessage');
    if (msg) msg.textContent = 'Are you sure you want to run migration: ' + migrationName + '?';
    showConfirmModal();
  };

  window.rollbackMigrations = function () {
    currentAction = 'rollback';
    var msg = document.getElementById('confirmMessage');
    if (msg) msg.textContent = 'Are you sure you want to rollback the last batch of migrations? This action cannot be undone.';
    showConfirmModal();
  };

  window.refreshStatus = function () {
    $.ajax({
      url: refreshUrl,
      method: 'POST',
      data: { _token: csrfToken },
      success: function (response) {
        if (response.status === 'success') {
          notify('success', response.message);
          setTimeout(function () { location.reload(); }, 1000);
        } else {
          notify('info', response.message);
        }
      },
      error: function (xhr) {
        notify('error', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to refresh status');
      }
    });
  };

  window.installMigrationsTable = function () {
    if (!confirm('This will create the migrations table. Continue?')) return;
    var forceCheck = document.getElementById('forceCheck');
    $.ajax({
      url: runUrl,
      method: 'POST',
      data: {
        _token: csrfToken,
        confirm: 1,
        force: forceCheck ? forceCheck.checked : false
      },
      success: function (response) {
        if (response.status === 'success') {
          notify('success', response.message);
          setTimeout(function () { location.reload(); }, 2000);
        } else {
          notify('info', response.message);
        }
      },
      error: function (xhr) {
        notify('error', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to install migrations table');
      }
    });
  };

  function buildUrl() {
    if (currentAction === 'run') return runUrl;
    if (currentAction === 'run-specific' && currentMigration) {
      return runSpecificUrlTemplate.replace('REPLACE_MIGRATION', encodeURIComponent(currentMigration));
    }
    if (currentAction === 'rollback') return rollbackUrl;
    return '';
  }

  function onConfirmClick() {
    var confirmCheck = document.getElementById('confirmCheck');
    if (!confirmCheck || !confirmCheck.checked) {
      notify('error', 'Please confirm you want to proceed');
      return;
    }

    var url = buildUrl();
    if (!url) return;

    var forceCheck = document.getElementById('forceCheck');
    var data = {
      _token: csrfToken,
      confirm: 1,
      force: forceCheck ? forceCheck.checked : false
    };
    if (currentAction === 'rollback') data.steps = 1;

    var $btn = $('#confirmBtn');
    $btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Processing...');

    $.ajax({
      url: url,
      method: 'POST',
      data: data,
      success: function (response) {
        hideConfirmModal();
        $btn.prop('disabled', false).html('Confirm');
        if (response.status === 'success') {
          notify('success', response.message);
          if (response.data && response.data.output) console.log('Migration Output:', response.data.output);
          setTimeout(function () { location.reload(); }, 2000);
        } else {
          notify('info', response.message || 'No changes made.');
        }
      },
      error: function (xhr) {
        $btn.prop('disabled', false).html('Confirm');
        notify('error', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred');
      }
    });
  }

  function resetModalState() {
    var check = document.getElementById('confirmCheck');
    var forceCheck = document.getElementById('forceCheck');
    var btn = document.getElementById('confirmBtn');
    if (check) check.checked = false;
    if (forceCheck) forceCheck.checked = false;
    if (btn) { btn.disabled = false; btn.innerHTML = 'Confirm'; }
    currentAction = null;
    currentMigration = null;
  }

  $(function () {
    $('#confirmBtn').on('click', onConfirmClick);
    if (confirmModalEl) {
      confirmModalEl.addEventListener('hidden.bs.modal', resetModalState);
    }
    $(document).on('hidden.bs.modal', '#confirmModal', resetModalState);
  });
})();
