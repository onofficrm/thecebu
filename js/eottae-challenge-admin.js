(function () {
  'use strict';

  var procUrl = window.eottaeChallengeAdminProcUrl || '/proc/eottae-challenge-admin.php';
  var adminToken = window.eottaeChallengeAdminToken || '';

  function adminPost(payload) {
    var fd = new FormData();
    Object.keys(payload).forEach(function (key) {
      fd.append(key, payload[key]);
    });
    fd.append('eottae_talkroom_admin_token', adminToken);

    return fetch(procUrl, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (res) {
      return res.json();
    });
  }

  var form = document.getElementById('sebuChallengeAdminForm');
  var statusEl = document.querySelector('[data-admin-status]');

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(form);
      fd.append('eottae_talkroom_admin_token', adminToken);
      fetch(procUrl, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      }).then(function (res) {
        return res.json();
      }).then(function (data) {
        if (statusEl) {
          statusEl.textContent = data.message || '';
        }
        if (data.success) {
          setTimeout(function () {
            window.location.href = data.challenge_id
              ? '/page/eottae-admin-challenges.php?edit=' + data.challenge_id
              : '/page/eottae-admin-challenges.php';
          }, 600);
        }
      });
    });
  }

  document.addEventListener('click', function (e) {
    var deleteBtn = e.target.closest('[data-challenge-delete]');
    if (deleteBtn) {
      if (!confirm('이 챌린지를 숨김 처리할까요?')) {
        return;
      }
      adminPost({
        action: 'delete',
        challenge_id: deleteBtn.getAttribute('data-challenge-delete')
      }).then(function (data) {
        alert(data.message || '');
        if (data.success) {
          window.location.reload();
        }
      });
    }

    var bestBtn = e.target.closest('[data-entry-best]');
    if (bestBtn) {
      adminPost({
        action: 'set_best',
        entry_id: bestBtn.getAttribute('data-entry-best'),
        is_best: bestBtn.getAttribute('data-is-best')
      }).then(function (data) {
        alert(data.message || '');
        if (data.success) {
          window.location.reload();
        }
      });
    }

    var hideBtn = e.target.closest('[data-entry-hide]');
    if (hideBtn) {
      if (!confirm('참여글을 숨김 처리할까요?')) {
        return;
      }
      adminPost({
        action: 'hide_entry',
        entry_id: hideBtn.getAttribute('data-entry-hide')
      }).then(function (data) {
        alert(data.message || '');
        if (data.success) {
          window.location.reload();
        }
      });
    }

    var reportBtn = e.target.closest('[data-report-handle]');
    if (reportBtn) {
      adminPost({
        action: 'handle_report',
        report_id: reportBtn.getAttribute('data-report-handle'),
        report_action: reportBtn.getAttribute('data-report-action')
      }).then(function (data) {
        alert(data.message || '');
        if (data.success) {
          window.location.reload();
        }
      });
    }
  });
})();
