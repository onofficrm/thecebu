(function (global) {
  'use strict';

  function parseJson(res) {
    return res.text().then(function (text) {
      var trimmed = (text || '').trim();
      if (!trimmed) {
        throw new Error('서버 응답이 비어 있습니다.');
      }
      return JSON.parse(trimmed);
    });
  }

  function initPanel(panel) {
    if (!panel || panel.dataset.bound === '1') {
      return;
    }
    panel.dataset.bound = '1';

    var procUrl = panel.getAttribute('data-proc-url');
    var boTable = panel.getAttribute('data-bo-table');
    var wrId = panel.getAttribute('data-wr-id');
    if (!procUrl || !boTable || !wrId) {
      return;
    }

    panel.addEventListener('click', function (event) {
      var btn = event.target.closest('[data-job-recruit-status]');
      if (!btn || btn.disabled) {
        return;
      }

      var status = btn.getAttribute('data-job-recruit-status');
      if (!status) {
        return;
      }

      var buttons = panel.querySelectorAll('[data-job-recruit-status]');
      var i;
      for (i = 0; i < buttons.length; i += 1) {
        buttons[i].disabled = true;
      }

      var body = new FormData();
      body.append('bo_table', boTable);
      body.append('wr_id', wrId);
      body.append('status', status);

      fetch(procUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
        headers: { Accept: 'application/json' },
      })
        .then(parseJson)
        .then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.message) || '상태 변경에 실패했습니다.');
          }
          var label = data.label || btn.textContent.trim();
          var badge = panel.querySelector('.job-recruit-badge--view');
          if (badge) {
            badge.textContent = label;
            badge.className = 'job-recruit-badge job-recruit-badge--view job-recruit-badge--' + (data.status || status);
          }
          for (i = 0; i < buttons.length; i += 1) {
            var active = buttons[i].getAttribute('data-job-recruit-status') === (data.status || status);
            buttons[i].classList.toggle('is-active', active);
            buttons[i].disabled = false;
            buttons[i].setAttribute('aria-pressed', active ? 'true' : 'false');
          }
        })
        .catch(function (err) {
          global.alert(err && err.message ? err.message : '상태 변경에 실패했습니다.');
          for (i = 0; i < buttons.length; i += 1) {
            buttons[i].disabled = false;
          }
        });
    });
  }

  function scan() {
    var panels = document.querySelectorAll('[data-job-recruit-panel]');
    var i;
    for (i = 0; i < panels.length; i += 1) {
      initPanel(panels[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scan);
  } else {
    scan();
  }
}(typeof window !== 'undefined' ? window : this));
