(function () {
  'use strict';

  var cfg = window.EOTTaeGolfJoinChat || {};
  var box = document.getElementById('golf-join-chat-messages');
  var form = document.getElementById('golf-join-chat-form');
  var input = document.getElementById('golf-join-chat-input');
  var emptyEl = document.getElementById('golf-join-chat-empty');
  var lastId = cfg.lastId || 0;
  var pollTimer = null;

  function toast(msg) {
    if (typeof window.showEottaeToast === 'function') {
      window.showEottaeToast(msg);
    }
  }

  function scrollBottom() {
    if (!box) {
      return;
    }
    box.scrollTop = box.scrollHeight;
  }

  function appendMessage(msg) {
    if (!box || !msg || !msg.id) {
      return;
    }
    if (box.querySelector('[data-message-id="' + msg.id + '"]')) {
      return;
    }
    if (emptyEl) {
      emptyEl.remove();
    }
    var wrap = document.createElement('div');
    var isMine = msg.user_id === cfg.viewerId;
    wrap.className = 'golf-join-chat-bubble-wrap' + (isMine ? ' is-mine' : '');
    wrap.setAttribute('data-message-id', String(msg.id));
    wrap.innerHTML =
      '<p class="golf-join-chat-bubble__meta">' +
      escapeHtml(msg.nickname || '') +
      ' · ' +
      escapeHtml(msg.time_label || '') +
      '</p><div class="golf-join-chat-bubble">' +
      escapeHtml(msg.message || '').replace(/\n/g, '<br>') +
      '</div>';
    box.appendChild(wrap);
    lastId = Math.max(lastId, parseInt(msg.id, 10) || 0);
    scrollBottom();
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function poll() {
    var url =
      cfg.procUrl +
      '?join_id=' +
      encodeURIComponent(cfg.joinId) +
      '&action=poll&since_id=' +
      encodeURIComponent(lastId);
    fetch(url, { credentials: 'same-origin' })
      .then(function (r) {
        return r.json();
      })
      .then(function (res) {
        if (!res.success) {
          return;
        }
        (res.messages || []).forEach(appendMessage);
        if (res.last_id) {
          lastId = Math.max(lastId, res.last_id);
        }
      })
      .catch(function () {});
  }

  function sendMessage(text) {
    var body = new URLSearchParams();
    body.append('action', 'send');
    body.append('join_id', cfg.joinId);
    body.append('eottae_golf_join_token', cfg.memberToken);
    body.append('message', text);

    return fetch(cfg.procUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
    }).then(function (r) {
      return r.json();
    });
  }

  if (form && input) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var text = input.value.trim();
      if (!text) {
        return;
      }
      input.disabled = true;
      sendMessage(text)
        .then(function (res) {
          if (res.success) {
            input.value = '';
            poll();
          } else {
            toast(res.message || '전송 실패');
          }
        })
        .finally(function () {
          input.disabled = false;
          input.focus();
        });
    });
  }

  scrollBottom();
  pollTimer = window.setInterval(poll, 8000);
  window.addEventListener('beforeunload', function () {
    if (pollTimer) {
      clearInterval(pollTimer);
    }
  });
})();
