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

  function bindStatusForm(form) {
    if (!form || form.dataset.bound === '1') {
      return;
    }
    form.dataset.bound = '1';

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('[data-report-status-submit]');
      if (btn) {
        btn.disabled = true;
      }

      fetch(form.getAttribute('action'), {
        method: 'POST',
        credentials: 'same-origin',
        body: new FormData(form),
        headers: { Accept: 'application/json' },
      })
        .then(parseJson)
        .then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.message) || '상태 저장에 실패했습니다.');
          }
          global.location.reload();
        })
        .catch(function (err) {
          global.alert(err && err.message ? err.message : '상태 저장에 실패했습니다.');
          if (btn) {
            btn.disabled = false;
          }
        });
    });
  }

  function bindConvertForm(panel) {
    var select = panel.querySelector('[data-report-target-board]');
    var hidden = panel.querySelector('[data-report-target-input]');
    var form = panel.querySelector('[data-report-convert-form]');
    if (!select || !hidden || !form || form.dataset.bound === '1') {
      return;
    }
    form.dataset.bound = '1';

    select.addEventListener('change', function () {
      hidden.value = select.value;
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      hidden.value = select.value;
      if (!hidden.value) {
        global.alert('전환할 게시판을 선택해 주세요.');
        return;
      }
      if (!global.confirm('선택한 게시판에 제보 내용을 복사 등록할까요?\n원본 제보글은 삭제되지 않습니다.')) {
        return;
      }

      var btn = form.querySelector('[data-report-convert-submit]');
      if (btn) {
        btn.disabled = true;
      }

      fetch(form.getAttribute('action'), {
        method: 'POST',
        credentials: 'same-origin',
        body: new FormData(form),
        headers: { Accept: 'application/json' },
      })
        .then(parseJson)
        .then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.message) || '전환에 실패했습니다.');
          }
          if (data.view_url) {
            global.location.href = data.view_url;
            return;
          }
          global.location.reload();
        })
        .catch(function (err) {
          global.alert(err && err.message ? err.message : '전환에 실패했습니다.');
          if (btn) {
            btn.disabled = false;
          }
        });
    });
  }

  function bindMessageForm(form) {
    if (!form || form.dataset.bound === '1') {
      return;
    }
    form.dataset.bound = '1';

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!global.confirm('제보 작성자에게 쪽지 답변을 보낼까요?')) {
        return;
      }

      var btn = form.querySelector('[data-report-message-submit]');
      if (btn) {
        btn.disabled = true;
      }

      fetch(form.getAttribute('action'), {
        method: 'POST',
        credentials: 'same-origin',
        body: new FormData(form),
        headers: { Accept: 'application/json' },
      })
        .then(parseJson)
        .then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.message) || '쪽지 답변 발송에 실패했습니다.');
          }
          global.alert('쪽지 답변을 보냈습니다.');
          if (data.redirect) {
            global.location.href = data.redirect;
            return;
          }
          global.location.reload();
        })
        .catch(function (err) {
          global.alert(err && err.message ? err.message : '쪽지 답변 발송에 실패했습니다.');
          if (btn) {
            btn.disabled = false;
          }
        });
    });
  }

  function bindCopyButton(panel) {
    var btn = panel.querySelector('[data-report-copy-btn]');
    var textarea = panel.querySelector('[data-report-copy-content]');
    if (!btn || !textarea) {
      return;
    }
    btn.addEventListener('click', function () {
      var text = textarea.value || '';
      if (global.navigator && global.navigator.clipboard && global.navigator.clipboard.writeText) {
        global.navigator.clipboard.writeText(text).then(function () {
          global.alert('내용이 복사되었습니다.');
        }).catch(function () {
          textarea.select();
          global.document.execCommand('copy');
          global.alert('내용이 복사되었습니다.');
        });
        return;
      }
      textarea.select();
      global.document.execCommand('copy');
      global.alert('내용이 복사되었습니다.');
    });
  }

  function init() {
    var panel = global.document.getElementById('reportAdminPanel');
    if (!panel) {
      return;
    }
    bindStatusForm(panel.querySelector('[data-report-status-form]'));
    bindMessageForm(panel.querySelector('[data-report-message-form]'));
    bindConvertForm(panel);
    bindCopyButton(panel);
  }

  if (global.document.readyState === 'loading') {
    global.document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window);
