(function () {
  'use strict';

  var procUrl = window.eottaeChallengeProcUrl || '/proc/eottae-challenge.php';

  function postFormData(data) {
    return fetch(procUrl, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (res) {
      return res.json();
    });
  }

  document.addEventListener('click', function (e) {
    var likeBtn = e.target.closest('[data-sebu-challenge-like]');
    if (likeBtn) {
      e.preventDefault();
      var entryId = likeBtn.getAttribute('data-entry-id');
      var token = likeBtn.getAttribute('data-token');
      var fd = new FormData();
      fd.append('action', 'toggle_like');
      fd.append('response', 'json');
      fd.append('entry_id', entryId);
      fd.append('eottae_challenge_token', token);
      postFormData(fd).then(function (data) {
        if (!data.success) {
          alert(data.message || '공감 처리에 실패했습니다.');
          return;
        }
        likeBtn.classList.toggle('is-liked', !!data.liked);
        var countEl = likeBtn.querySelector('[data-sebu-challenge-like-count]');
        if (countEl) {
          countEl.textContent = String(data.like_count || 0);
        }
      }).catch(function () {
        alert('네트워크 오류가 발생했습니다.');
      });
    }
  });

  var reportOpen = document.querySelector('[data-sebu-challenge-report-open]');
  var reportModal = document.querySelector('[data-sebu-challenge-report-modal]');
  var reportClose = document.querySelector('[data-sebu-challenge-report-close]');

  if (reportOpen && reportModal) {
    reportOpen.addEventListener('click', function () {
      if (typeof reportModal.showModal === 'function') {
        reportModal.showModal();
      } else {
        reportModal.setAttribute('open', 'open');
      }
    });
  }

  if (reportClose && reportModal) {
    reportClose.addEventListener('click', function () {
      reportModal.close();
    });
  }

  var reportForm = document.querySelector('[data-sebu-challenge-report-form]');
  if (reportForm) {
    reportForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(reportForm);
      fd.append('response', 'json');
      postFormData(fd).then(function (data) {
        alert(data.message || (data.success ? '신고가 접수되었습니다.' : '신고 실패'));
        if (data.success && reportModal) {
          reportModal.close();
        }
      });
    });
  }

  var commentForm = document.querySelector('[data-sebu-challenge-comment-form]');
  if (commentForm) {
    commentForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(commentForm);
      fd.append('response', 'json');
      postFormData(fd).then(function (data) {
        if (!data.success) {
          alert(data.message || '댓글 등록에 실패했습니다.');
          return;
        }
        window.location.reload();
      });
    });
  }

  document.querySelectorAll('.sebu-challenge-entry-delete-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(form);
      postFormData(fd).then(function (data) {
        alert(data.message || '');
        if (data.success && data.redirect) {
          window.location.href = data.redirect;
        }
      });
    });
  });
})();
