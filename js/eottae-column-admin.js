(function () {
  'use strict';

  var root = document.querySelector('.sebu-column-admin');
  if (!root) {
    return;
  }

  var procUrl = root.getAttribute('data-proc-url');
  var adminToken = root.getAttribute('data-admin-token');

  function postAdmin(data) {
    data.admin_token = adminToken;
    return fetch(procUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams(data).toString(),
      credentials: 'same-origin'
    }).then(function (res) { return res.json(); });
  }

  root.querySelectorAll('[data-sebu-column-flags-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = { action: 'set_flags' };
      new FormData(form).forEach(function (value, key) {
        data[key] = value;
      });
      data.is_featured = form.querySelector('[name="is_featured"]') && form.querySelector('[name="is_featured"]').checked ? '1' : '0';
      data.is_recommended = form.querySelector('[name="is_recommended"]') && form.querySelector('[name="is_recommended"]').checked ? '1' : '0';
      postAdmin(data).then(function (res) {
        alert(res.message || (res.success ? '저장되었습니다.' : '실패했습니다.'));
      });
    });
  });

  var authorForm = root.querySelector('[data-sebu-column-author-form]');
  if (authorForm) {
    authorForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(authorForm);
      fd.append('admin_token', adminToken);
      fetch(procUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (res) {
          alert(res.message || (res.success ? '저장되었습니다.' : '실패했습니다.'));
          if (res.success) {
            window.location.reload();
          }
        });
    });
  }

  var monthlyForm = root.querySelector('[data-sebu-column-monthly-form]');
  if (monthlyForm) {
    monthlyForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = { action: 'save_monthly' };
      new FormData(monthlyForm).forEach(function (value, key) {
        data[key] = value;
      });
      data.show_on_main = monthlyForm.querySelector('[name="show_on_main"]') && monthlyForm.querySelector('[name="show_on_main"]').checked ? '1' : '0';
      postAdmin(data).then(function (res) {
        alert(res.message || (res.success ? '설정되었습니다.' : '실패했습니다.'));
        if (res.success) {
          window.location.reload();
        }
      });
    });
  }

  root.querySelectorAll('[data-sebu-column-report-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var submitter = e.submitter;
      var data = {
        action: 'handle_report',
        report_id: form.querySelector('[name="report_id"]').value,
        report_action: submitter ? submitter.value : 'dismiss'
      };
      postAdmin(data).then(function (res) {
        alert(res.message || (res.success ? '처리되었습니다.' : '실패했습니다.'));
        if (res.success) {
          window.location.reload();
        }
      });
    });
  });

  root.querySelectorAll('[data-sebu-column-application-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var submitter = e.submitter;
      var data = {
        action: 'review_application',
        application_id: form.querySelector('[name="application_id"]').value,
        review_memo: form.querySelector('[name="review_memo"]').value,
        decision: submitter ? submitter.value : 'reject'
      };
      postAdmin(data).then(function (res) {
        alert(res.message || (res.success ? '처리되었습니다.' : '실패했습니다.'));
        if (res.success) {
          window.location.reload();
        }
      });
    });
  });
})();
