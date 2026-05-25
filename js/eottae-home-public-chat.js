/**
 * 홈 — 세부공개단체톡 실시간 폴링·전송
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
        throw new Error('서버 응답 오류입니다.');
      }
    });
  }

  function getSection() {
    return document.getElementById('eottae-home-public-chat');
  }

  function getMessagesEl(section) {
    return section ? section.querySelector('#eottae-public-chat-messages') : null;
  }

  function removeEmptyState(messagesEl) {
    if (!messagesEl) {
      return;
    }
    var empty = messagesEl.querySelector('.public-group-chat__empty');
    if (empty) {
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

    return ''
      + '<article class="' + classes.join(' ') + '" data-wr-id="' + esc(message.wr_id) + '">'
      + '<div class="public-group-chat__bubble">'
      + '<strong class="public-group-chat__author">' + esc(message.author || '익명') + '</strong>'
      + '<p class="public-group-chat__text">' + esc(message.text).replace(/\n/g, '<br>') + '</p>'
      + (message.time_label ? '<time class="public-group-chat__time">' + esc(message.time_label) + '</time>' : '')
      + '</div>'
      + '</article>';
  }

  function appendMessages(section, messages) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl || !messages || !messages.length) {
      return 0;
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
      return lastId;
    }

    removeEmptyState(messagesEl);
    messagesEl.insertAdjacentHTML('beforeend', html);
    section.setAttribute('data-last-wr-id', String(lastId));
    messagesEl.scrollTop = messagesEl.scrollHeight;

    return lastId;
  }

  function poll(section) {
    var pollUrl = section.getAttribute('data-poll-url');
    if (!pollUrl) {
      return Promise.resolve();
    }

    var since = parseInt(section.getAttribute('data-last-wr-id') || '0', 10) || 0;
    var url = pollUrl + (pollUrl.indexOf('?') >= 0 ? '&' : '?') + 'action=poll&since_wr_id=' + encodeURIComponent(String(since));

    return fetch(url, {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' },
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
    var input = form.querySelector('#eottae-public-chat-input');
    var sendBtn = form.querySelector('.public-group-chat__send');
    var sendUrl = section.getAttribute('data-send-url');
    var token = section.getAttribute('data-member-token') || '';
    var message = input ? input.value.trim() : '';

    if (!sendUrl || message === '') {
      return;
    }

    if (sendBtn) {
      sendBtn.disabled = true;
    }

    var body = new FormData();
    body.append('action', 'send');
    body.append('message', message);
    body.append('eottae_talkroom_member_token', token);

    fetch(sendUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: body,
      headers: { 'Accept': 'application/json' },
    })
      .then(parseJsonResponse)
      .then(function (data) {
        if (!data || !data.success) {
          throw new Error((data && data.message) || '전송에 실패했습니다.');
        }

        if (input) {
          input.value = '';
        }

        if (data.message_row) {
          appendMessages(section, [data.message_row]);
        } else if (data.wr_id) {
          appendMessages(section, [{
            wr_id: data.wr_id,
            author: '',
            text: message,
            time_label: '방금 전',
            is_mine: 1,
          }]);
        }

        if (data.member_token) {
          section.setAttribute('data-member-token', data.member_token);
        }
      })
      .catch(function (err) {
        window.alert(err && err.message ? err.message : '전송에 실패했습니다.');
      })
      .then(function () {
        if (sendBtn) {
          sendBtn.disabled = section.getAttribute('data-can-send') !== '1';
        }
      });
  }

  function joinRoom(section, roomId) {
    var token = section.getAttribute('data-member-token') || '';
    var body = new FormData();
    body.append('action', 'join');
    body.append('room_id', String(roomId));
    body.append('eottae_talkroom_member_token', token);

    fetch('/proc/eottae-talkroom-member.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: body,
      headers: { 'Accept': 'application/json' },
    })
      .then(parseJsonResponse)
      .then(function (data) {
        if (!data || !data.success) {
          throw new Error((data && data.message) || '참여에 실패했습니다.');
        }
        section.setAttribute('data-can-send', '1');
        section.setAttribute('data-needs-join', '0');
        var sendBtn = section.querySelector('.public-group-chat__send');
        if (sendBtn) {
          sendBtn.disabled = false;
        }
        var joinBtn = section.querySelector('[data-public-chat-join]');
        if (joinBtn && joinBtn.parentNode) {
          joinBtn.parentNode.removeChild(joinBtn);
        }
      })
      .catch(function (err) {
        window.alert(err && err.message ? err.message : '참여에 실패했습니다.');
      });
  }

  function bindSection(section) {
    if (!section || section.dataset.bound === '1') {
      return;
    }
    section.dataset.bound = '1';

    var form = section.querySelector('#eottae-public-chat-form');
    if (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        sendMessage(section, form);
      });
    }

    section.addEventListener('click', function (event) {
      var joinBtn = event.target.closest('[data-public-chat-join]');
      if (!joinBtn) {
        return;
      }
      event.preventDefault();
      joinRoom(section, parseInt(joinBtn.getAttribute('data-public-chat-join'), 10) || 0);
    });

    poll(section);
    global.setInterval(function () {
      poll(section);
    }, POLL_MS);
  }

  function init() {
    bindSection(getSection());
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeHomePublicChat = init;
}(window));
