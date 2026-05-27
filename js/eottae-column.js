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
  }
})();
