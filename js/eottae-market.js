(function (global) {
  'use strict';

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function initStatusPanel(panel) {
    if (!panel || panel.dataset.bound === '1') return;
    panel.dataset.bound = '1';
    var procUrl = panel.getAttribute('data-proc-url');
    var boTable = panel.getAttribute('data-bo-table');
    var wrId = panel.getAttribute('data-wr-id');
    panel.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-market-status]');
      if (!btn) return;
      var status = btn.getAttribute('data-market-status');
      var body = new FormData();
      body.append('bo_table', boTable);
      body.append('wr_id', wrId);
      body.append('status', status);
      btn.disabled = true;
      fetch(procUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
        headers: { Accept: 'application/json' },
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (!data || !data.success) throw new Error((data && data.message) || '변경에 실패했습니다.');
          global.location.reload();
        })
        .catch(function (err) {
          alert(err && err.message ? err.message : '변경에 실패했습니다.');
          btn.disabled = false;
        });
    });
  }

  function initPhotoPreviews() {
    document.querySelectorAll('[data-photo-preview]').forEach(function (input) {
      if (input.dataset.bound === '1') return;
      input.dataset.bound = '1';
      input.addEventListener('change', function () {
        var slot = input.closest('.market-photo-slot');
        var preview = slot ? slot.querySelector('.market-photo-slot__preview') : null;
        if (!preview || !input.files || !input.files[0]) return;
        preview.src = URL.createObjectURL(input.files[0]);
        preview.hidden = false;
      });
    });
  }

  function init() {
    document.querySelectorAll('[data-market-status-panel]').forEach(initStatusPanel);
    initPhotoPreviews();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}(window));
