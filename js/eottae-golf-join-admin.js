(function () {
  'use strict';

  var cfg = window.EOTTaeGolfJoinAdmin || {};

  function post(action, data) {
    var body = new URLSearchParams();
    body.append('action', action);
    body.append('eottae_talkroom_admin_token', cfg.adminToken || '');
    Object.keys(data || {}).forEach(function (key) {
      body.append(key, data[key]);
    });
    return fetch(cfg.procUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
    }).then(function (r) {
      return r.json();
    });
  }

  function toast(msg) {
    if (typeof window.showEottaeToast === 'function') {
      window.showEottaeToast(msg);
    } else {
      alert(msg);
    }
  }

  document.querySelectorAll('.golf-admin-status-select').forEach(function (sel) {
    sel.addEventListener('change', function () {
      var joinId = sel.getAttribute('data-join-id');
      post('set_status', { join_id: joinId, status: sel.value }).then(function (res) {
        toast(res.message || '');
        if (res.reload) {
          location.reload();
        }
      });
    });
  });

  document.querySelectorAll('[data-golf-hide]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('이 모집글을 숨기시겠습니까?')) {
        return;
      }
      post('hide', { join_id: btn.getAttribute('data-golf-hide') }).then(function (res) {
        toast(res.message || '');
        if (res.reload) {
          location.reload();
        }
      });
    });
  });

  document.querySelectorAll('[data-golf-restore]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      post('restore', { join_id: btn.getAttribute('data-golf-restore') }).then(function (res) {
        toast(res.message || '');
        if (res.reload) {
          location.reload();
        }
      });
    });
  });

  document.querySelectorAll('[data-golf-report-resolve]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var memo = window.prompt('처리 메모 (선택)', '') || '';
      post('resolve_report', { report_id: btn.getAttribute('data-golf-report-resolve'), admin_memo: memo }).then(function (res) {
        toast(res.message || '');
        if (res.reload) {
          location.reload();
        }
      });
    });
  });

  var courseForm = document.getElementById('golf-admin-course-form');
  if (courseForm) {
    courseForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(courseForm);
      var data = {
        course_id: fd.get('course_id') || '0',
        region: fd.get('region'),
        name: fd.get('name'),
        address: fd.get('address'),
        is_active: fd.get('is_active') ? '1' : '0',
      };
      post('save_course', data).then(function (res) {
        toast(res.message || '');
        if (res.reload) {
          location.reload();
        }
      });
    });
  }

  document.querySelectorAll('[data-golf-course-edit]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = document.getElementById('golf-admin-course-form');
      if (!form) {
        return;
      }
      form.querySelector('[name="course_id"]').value = btn.getAttribute('data-golf-course-edit');
      form.querySelector('[name="region"]').value = btn.getAttribute('data-region');
      form.querySelector('[name="name"]').value = btn.getAttribute('data-name');
      form.querySelector('[name="address"]').value = btn.getAttribute('data-address');
      form.querySelector('[name="is_active"]').checked = btn.getAttribute('data-active') === '1';
      form.scrollIntoView({ behavior: 'smooth' });
    });
  });
})();
