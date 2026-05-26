/**
 * 톡방 상세 — 카카오톡 스타일 실시간 채팅
 */
(function (global) {
  'use strict';

  var POLL_MS = 12000;

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function parseJsonResponse(res) {
    return res.text().then(function (text) {
      var trimmed = (text || '').trim();
      if (!trimmed) {
        throw new Error('서버 응답이 비어 있습니다.');
      }
      try {
        return JSON.parse(trimmed);
      } catch (e) {
        if (/로그인|회원가입|auth_required/i.test(trimmed)) {
          return {
            success: false,
            auth_required: true,
            message: '회원가입 또는 로그인 후 메시지를 보낼 수 있습니다.',
          };
        }
        throw new Error('서버 응답 오류입니다.');
      }
    });
  }

  function getSection() {
    return document.getElementById('eottae-talkroom-chat');
  }

  function getMessagesEl(section) {
    return section ? section.querySelector('#eottae-talkroom-chat-messages') : null;
  }

  function authUrls(section) {
    return {
      login: section.getAttribute('data-login-url') || '/bbs/login.php',
      register: section.getAttribute('data-register-url') || '/bbs/register.php',
    };
  }

  function requiresAuth(section) {
    return section.getAttribute('data-is-member') !== '1' || section.getAttribute('data-can-send') !== '1';
  }

  function clearAuthNotice(section) {
    var notice = section.querySelector('.public-group-chat__auth-notice');
    if (notice && notice.parentNode) {
      notice.parentNode.removeChild(notice);
    }
  }

  function showAuthNotice(section, message) {
    var panel = section.querySelector('.public-group-chat__panel');
    if (!panel) {
      global.alert(message || '로그인이 필요합니다.');
      return;
    }

    clearAuthNotice(section);
    var urls = authUrls(section);
    var notice = document.createElement('div');
    notice.className = 'public-group-chat__auth-notice';
    notice.setAttribute('role', 'alert');
    notice.innerHTML =
      '<p class="public-group-chat__auth-notice-text">' + esc(message) + '</p>'
      + '<div class="public-group-chat__auth-notice-actions">'
      + '<a href="' + esc(urls.login) + '" class="public-group-chat__action">로그인</a>'
      + '<a href="' + esc(urls.register) + '" class="public-group-chat__action public-group-chat__action--register">회원가입</a>'
      + '</div>';
    panel.appendChild(notice);
  }

  function handleAuthRequired(section, message, data) {
    if (data && data.login_url) {
      section.setAttribute('data-login-url', data.login_url);
    }
    if (data && data.register_url) {
      section.setAttribute('data-register-url', data.register_url);
    }
    showAuthNotice(section, message || '회원가입 또는 로그인 후 메시지를 보낼 수 있습니다.');
  }

  function removeEmptyState(messagesEl) {
    var empty = messagesEl.querySelector('.public-group-chat__empty');
    if (empty && empty.parentNode) {
      empty.parentNode.removeChild(empty);
    }
  }

  function renderMessage(message) {
    if (!message || !message.wr_id || !message.text) {
      return '';
    }

    var classes = ['public-group-chat__message'];
    if (message.is_mine) {
      classes.push('public-group-chat__message--mine');
    }
    if (message.is_ai) {
      classes.push('public-group-chat__message--ai', 'is-talk-ai-message');
    }

    var author = message.is_ai
      ? message.ai_display_name || message.author || '어때봇 · AI 도우미'
      : message.author || '익명';
    var badge = message.is_ai
      ? '<span class="talk-ai-msg__badge talk-ai-msg__badge--sm" aria-label="AI 도우미">'
        + '<span class="talk-ai-msg__icon" aria-hidden="true">🤖</span>'
        + '<span class="talk-ai-msg__badge-label">' + esc(author) + '</span>'
        + '</span>'
      : '';
    var meta = '';
    if (!message.is_mine) {
      meta = '<div class="public-group-chat__meta">'
        + (message.is_ai ? badge : '<strong class="public-group-chat__author">' + esc(author) + '</strong>')
        + '</div>';
    }
    var timeBefore = message.is_mine && message.time_label
      ? '<time class="public-group-chat__time">' + esc(message.time_label) + '</time>'
      : '';
    var timeAfter = !message.is_mine && message.time_label
      ? '<time class="public-group-chat__time">' + esc(message.time_label) + '</time>'
      : '';

    return ''
      + '<article class="' + classes.join(' ') + '" data-wr-id="' + esc(message.wr_id) + '">'
      + '<div class="public-group-chat__message-inner">'
      + meta
      + '<div class="public-group-chat__bubble-row">'
      + timeBefore
      + '<div class="public-group-chat__bubble">'
      + '<p class="public-group-chat__text">' + esc(message.text).replace(/\n/g, '<br>') + '</p>'
      + '</div>'
      + timeAfter
      + '</div>'
      + '</div>'
      + '</article>';
  }

  function appendMessages(section, messages) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl || !messages || !messages.length) {
      return;
    }

    var html = '';
    var i;
    var lastId = parseInt(section.getAttribute('data-last-wr-id') || '0', 10) || 0;

    for (i = 0; i < messages.length; i += 1) {
      var message = messages[i];
      var wrId = parseInt(message.wr_id, 10) || 0;
      if (wrId < 1 || messagesEl.querySelector('[data-wr-id="' + wrId + '"]')) {
        continue;
      }
      html += renderMessage(message);
      if (wrId > lastId) {
        lastId = wrId;
      }
    }

    if (!html) {
      return;
    }

    removeEmptyState(messagesEl);
    messagesEl.insertAdjacentHTML('beforeend', html);
    section.setAttribute('data-last-wr-id', String(lastId));
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function poll(section) {
    var pollUrl = section.getAttribute('data-poll-url');
    var roomId = section.getAttribute('data-room-id');
    if (!pollUrl || !roomId) {
      return Promise.resolve();
    }

    var since = parseInt(section.getAttribute('data-last-wr-id') || '0', 10) || 0;
    var url =
      pollUrl
      + (pollUrl.indexOf('?') >= 0 ? '&' : '?')
      + 'action=poll&room_id='
      + encodeURIComponent(roomId)
      + '&since_wr_id='
      + encodeURIComponent(String(since));

    return fetch(url, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then(parseJsonResponse)
      .then(function (data) {
        if (!data || !data.success) {
          return;
        }
        appendMessages(section, data.messages || []);
      })
      .catch(function () {});
  }

  function sendMessage(section, form) {
    var input = form.querySelector('#eottae-talkroom-chat-input');
    var sendBtn = form.querySelector('.public-group-chat__send');
    var sendUrl = section.getAttribute('data-send-url');
    var roomId = section.getAttribute('data-room-id');
    var token = section.getAttribute('data-member-token') || '';
    var message = input ? input.value.trim() : '';

    if (!sendUrl || !roomId || message === '') {
      return;
    }

    if (requiresAuth(section)) {
      handleAuthRequired(section, '회원가입 또는 로그인 후 메시지를 보낼 수 있습니다.');
      return;
    }

    if (sendBtn) {
      sendBtn.disabled = true;
    }

    clearAuthNotice(section);

    var body = new FormData();
    body.append('action', 'send');
    body.append('room_id', roomId);
    body.append('message', message);
    body.append('eottae_talkroom_member_token', token);

    fetch(sendUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: body,
      headers: { Accept: 'application/json' },
    })
      .then(parseJsonResponse)
      .then(function (data) {
        if (data && data.auth_required) {
          handleAuthRequired(section, data.message, data);
          return;
        }
        if (!data || !data.success) {
          throw new Error((data && data.message) || '전송에 실패했습니다.');
        }

        if (input) {
          input.value = '';
          input.style.height = '';
        }

        if (data.message_row) {
          appendMessages(section, [data.message_row]);
        }
      })
      .catch(function (err) {
        var errMessage = err && err.message ? err.message : '전송에 실패했습니다.';
        if (/로그인|회원가입/.test(errMessage)) {
          handleAuthRequired(section, errMessage);
          return;
        }
        global.alert(errMessage);
      })
      .then(function () {
        if (sendBtn) {
          sendBtn.disabled = section.getAttribute('data-can-send') !== '1';
        }
      });
  }

  function bindComposer(section) {
    var form = section.querySelector('#eottae-talkroom-chat-form');
    if (!form || form.dataset.bound === '1') {
      return;
    }
    form.dataset.bound = '1';

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      sendMessage(section, form);
    });

    var input = form.querySelector('#eottae-talkroom-chat-input');
    if (input) {
      input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
          event.preventDefault();
          sendMessage(section, form);
        }
      });

      input.addEventListener('input', function () {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
      });
    }
  }

  function scrollMessagesToEnd() {
    var section = getSection();
    var messagesEl = getMessagesEl(section);
    if (messagesEl) {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }
  }

  function bindSection(section) {
    if (!section || section.dataset.chatBound === '1') {
      return;
    }
    section.dataset.chatBound = '1';

    bindComposer(section);
    scrollMessagesToEnd();

    poll(section);
    global.setInterval(function () {
      poll(section);
    }, POLL_MS);
  }

  function init() {
    var section = getSection();
    if (section) {
      bindSection(section);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeTalkroomChat = init;
}(typeof window !== 'undefined' ? window : this));
