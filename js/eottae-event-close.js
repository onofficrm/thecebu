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

    var btn = panel.querySelector('[data-event-close-btn]');
    if (!btn) {
      return;
    }

    var procUrl = panel.getAttribute('data-proc-url');
    var boTable = panel.getAttribute('data-bo-table');
    var wrId = panel.getAttribute('data-wr-id');
    if (!procUrl || !boTable || !wrId) {
      return;
    }

    btn.addEventListener('click', function () {
      if (!global.confirm('이 이벤트를 종료 처리할까요? 종료 후에는 진행중으로 되돌릴 수 없습니다.')) {
        return;
      }

      btn.disabled = true;

      var body = new FormData();
      body.append('bo_table', boTable);
      body.append('wr_id', wrId);

      fetch(procUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
        headers: { Accept: 'application/json' },
      })
        .then(parseJson)
        .then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.message) || '종료 처리에 실패했습니다.');
          }
          global.location.reload();
        })
        .catch(function (err) {
          global.alert(err && err.message ? err.message : '종료 처리에 실패했습니다.');
          btn.disabled = false;
        });
    });
  }

  function scan() {
    var panels = document.querySelectorAll('[data-event-close-panel]');
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
