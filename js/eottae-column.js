(function () {
  'use strict';

  function postJson(url, data) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams(data).toString(),
      credentials: 'same-origin'
    }).then(function (res) { return res.json(); });
  }

  function syncColumnToken(scope, token) {
    if (!scope || !token) {
      return;
    }

    scope.setAttribute('data-member-token', token);
    scope.querySelectorAll('[data-token]').forEach(function (el) {
      el.setAttribute('data-token', token);
    });
  }

  var viewRoot = document.querySelector('[data-sebu-column-view]');
  if (viewRoot) {
    var wrId = viewRoot.getAttribute('data-wr-id');
    var procUrl = viewRoot.getAttribute('data-proc-url') || '/proc/eottae-column.php';

    var likeBtn = viewRoot.querySelector('[data-sebu-column-like]');
    if (likeBtn) {
      likeBtn.addEventListener('click', function () {
        var token = likeBtn.getAttribute('data-token');
        postJson(procUrl, {
          action: 'toggle_like',
          wr_id: wrId,
          eottae_column_token: token,
          response: 'json'
        }).then(function (res) {
          if (!res.success) {
            alert(res.message || '처리에 실패했습니다.');
            return;
          }
          syncColumnToken(viewRoot, res.column_token);
          likeBtn.classList.toggle('is-liked', !!res.liked);
          var countEl = likeBtn.querySelector('[data-sebu-column-like-count]');
          if (countEl) {
            countEl.textContent = String(res.like_count || 0);
          }
        });
      });
    }

    var bookmarkBtn = viewRoot.querySelector('[data-sebu-column-bookmark]');
    if (bookmarkBtn) {
      bookmarkBtn.addEventListener('click', function () {
        var token = bookmarkBtn.getAttribute('data-token');
        postJson(procUrl, {
          action: 'toggle_bookmark',
          wr_id: wrId,
          eottae_column_token: token,
          response: 'json'
        }).then(function (res) {
          if (!res.success) {
            alert(res.message || '처리에 실패했습니다.');
            return;
          }
          syncColumnToken(viewRoot, res.column_token);
          bookmarkBtn.classList.toggle('is-saved', !!res.bookmarked);
          bookmarkBtn.textContent = res.bookmarked ? '저장됨' : '저장하기';
        });
      });
    }

    var shareBtn = viewRoot.querySelector('[data-sebu-column-share]');
    if (shareBtn) {
      shareBtn.addEventListener('click', function () {
        var url = shareBtn.getAttribute('data-share-url') || window.location.href;
        var title = shareBtn.getAttribute('data-share-title') || document.title;
        if (navigator.share) {
          navigator.share({ title: title, url: url }).catch(function () {});
        } else if (navigator.clipboard) {
          navigator.clipboard.writeText(url).then(function () {
            alert('링크가 복사되었습니다.');
          });
        }
      });
    }

    var modal = document.getElementById('sebuColumnReportModal');
    var openBtn = viewRoot.querySelector('[data-sebu-column-report-open]');
    if (modal && openBtn) {
      openBtn.addEventListener('click', function () {
        modal.hidden = false;
      });
      modal.querySelectorAll('[data-sebu-column-report-close]').forEach(function (el) {
        el.addEventListener('click', function () {
          modal.hidden = true;
        });
      });
    }

    var reportForm = document.querySelector('[data-sebu-column-report-form]');
    if (reportForm) {
      reportForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(reportForm);
        var data = {};
        formData.forEach(function (value, key) { data[key] = value; });
        data.response = 'json';
        data.eottae_column_report_token = reportForm.getAttribute('data-token');
        data.eottae_column_token = reportForm.getAttribute('data-token');
        postJson(reportForm.getAttribute('data-proc-url') || procUrl, data).then(function (res) {
          alert(res.message || (res.success ? '신고되었습니다.' : '실패했습니다.'));
          if (res.success && modal) {
            modal.hidden = true;
          }
        });
      });
    }

    var deleteBtn = viewRoot.querySelector('[data-sebu-column-delete]');
    if (deleteBtn) {
      deleteBtn.addEventListener('click', function () {
        if (!window.confirm('이 컬럼을 삭제할까요? 삭제 후에는 복구할 수 없습니다.')) {
          return;
        }
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = procUrl;
        form.innerHTML = ''
          + '<input type="hidden" name="action" value="delete">'
          + '<input type="hidden" name="wr_id" value="' + String(wrId) + '">'
          + '<input type="hidden" name="eottae_column_token" value="' + (viewRoot.getAttribute('data-member-token') || '') + '">';
        document.body.appendChild(form);
        form.submit();
      });
    }
  }

  var applyRoot = document.querySelector('[data-sebu-column-apply]');
  if (applyRoot) {
    var avatarInput = applyRoot.querySelector('[data-column-apply-avatar-input]');
    var avatarImg = applyRoot.querySelector('[data-column-apply-avatar-img]');
    var avatarInitial = applyRoot.querySelector('[data-column-apply-avatar-initial]');
    var penNameInput = applyRoot.querySelector('[data-column-apply-pen-name]');

    function updateInitialFromPenName() {
      if (!avatarInitial || !penNameInput || (avatarImg && avatarImg.classList.contains('is-visible'))) {
        return;
      }
      var name = (penNameInput.value || '').trim();
      avatarInitial.textContent = name ? name.charAt(0) : '?';
    }

    if (penNameInput) {
      penNameInput.addEventListener('input', updateInitialFromPenName);
    }

    if (avatarInput && avatarImg) {
      avatarInput.addEventListener('change', function () {
        var file = avatarInput.files && avatarInput.files[0];
        if (!file || !file.type.match(/^image\//)) {
          avatarImg.removeAttribute('src');
          avatarImg.classList.remove('is-visible');
          updateInitialFromPenName();
          return;
        }
        var reader = new FileReader();
        reader.onload = function (ev) {
          avatarImg.src = ev.target && ev.target.result ? ev.target.result : '';
          avatarImg.classList.add('is-visible');
        };
        reader.readAsDataURL(file);
      });
    }
  }

  var writeRoot = document.querySelector('[data-sebu-column-write]');
  if (writeRoot) {
    var writeDeleteBtn = writeRoot.querySelector('[data-sebu-column-delete]');
    if (writeDeleteBtn) {
      writeDeleteBtn.addEventListener('click', function () {
        if (!window.confirm('이 컬럼을 삭제할까요? 삭제 후에는 복구할 수 없습니다.')) {
          return;
        }
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = writeDeleteBtn.getAttribute('data-proc-url') || writeRoot.getAttribute('data-proc-url') || '/proc/eottae-column.php';
        form.innerHTML = ''
          + '<input type="hidden" name="action" value="delete">'
          + '<input type="hidden" name="wr_id" value="' + (writeDeleteBtn.getAttribute('data-wr-id') || '0') + '">'
          + '<input type="hidden" name="eottae_column_token" value="' + (writeDeleteBtn.getAttribute('data-token') || '') + '">';
        document.body.appendChild(form);
        form.submit();
      });
    }
  }
})();
