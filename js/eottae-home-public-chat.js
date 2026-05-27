/**
 * 홈(빌더) — 히어로 3열: 검색 | 공개단체채팅 | 로그인+이벤트
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

  function authUrls(section) {
    return {
      login: section.getAttribute('data-login-url') || '/bbs/login.php',
      register: section.getAttribute('data-register-url') || '/bbs/register.php',
    };
  }

  function clearAuthNotice(section) {
    if (!section) {
      return;
    }
    var notice = section.querySelector('.public-group-chat__auth-notice');
    if (notice && notice.parentNode) {
      notice.parentNode.removeChild(notice);
    }
  }

  function showAuthNotice(section, message) {
    if (!section) {
      return;
    }

    var urls = authUrls(section);
    var panel = section.querySelector('.public-group-chat__panel');
    if (!panel) {
      window.alert(message || '회원가입 또는 로그인 후 메시지를 보낼 수 있습니다.');
      return;
    }

    clearAuthNotice(section);

    var notice = document.createElement('div');
    notice.className = 'public-group-chat__auth-notice';
    notice.setAttribute('role', 'alert');
    notice.innerHTML = ''
      + '<p class="public-group-chat__auth-notice-text">' + esc(message || '회원가입 또는 로그인 후 메시지를 보낼 수 있습니다.') + '</p>'
      + '<div class="public-group-chat__auth-notice-actions">'
      + '<a href="' + esc(urls.login) + '" class="public-group-chat__action">로그인</a>'
      + '<a href="' + esc(urls.register) + '" class="public-group-chat__action public-group-chat__action--register">회원가입</a>'
      + '</div>';

    panel.appendChild(notice);
  }

  function requiresAuth(section) {
    if (!section) {
      return true;
    }
    return section.getAttribute('data-is-member') !== '1' || section.getAttribute('data-can-send') !== '1';
  }

  function handleAuthRequired(section, message, data) {
    var urls = authUrls(section);
    if (data && data.login_url) {
      section.setAttribute('data-login-url', data.login_url);
    }
    if (data && data.register_url) {
      section.setAttribute('data-register-url', data.register_url);
    }
    showAuthNotice(section, message || '회원가입 또는 로그인 후 메시지를 보낼 수 있습니다.');
  }

  function findHeroGrid() {
    var headings = document.querySelectorAll('h1');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      if ((headings[i].textContent || '').indexOf('세부어때') !== -1) {
        var section = headings[i].closest('section');
        if (section && section.parentElement) {
          return section.parentElement;
        }
      }
    }
    return null;
  }

  function findHeroMainColumn(grid) {
    if (!grid) {
      return null;
    }

    var nodes = grid.children;
    var i;
    for (i = 0; i < nodes.length; i += 1) {
      if ((nodes[i].className || '').indexOf('lg:col-span-8') !== -1) {
        return nodes[i];
      }
    }

    return nodes.length ? nodes[0] : null;
  }

  function findHeroSidebarColumn(grid) {
    if (!grid) {
      return null;
    }

    var nodes = grid.children;
    var i;
    for (i = 0; i < nodes.length; i += 1) {
      if ((nodes[i].className || '').indexOf('lg:col-span-4') !== -1) {
        return nodes[i];
      }
    }

    return nodes.length > 1 ? nodes[nodes.length - 1] : null;
  }

  function hideMainColumnEvents(mainCol) {
    if (!mainCol) {
      return;
    }

    var sections = mainCol.querySelectorAll('section');
    var i;
    for (i = 0; i < sections.length; i += 1) {
      var h2 = sections[i].querySelector('h2');
      if (h2 && (h2.textContent || '').indexOf('업체 이벤트') !== -1) {
        sections[i].style.display = 'none';
      }
    }
  }

  function getSection() {
    return document.getElementById('eottae-home-public-chat');
  }

  function unwrapPendingSlot(chat) {
    var pending = chat.closest('.eottae-home-slot-pending');
    if (pending && pending.parentNode) {
      pending.parentNode.insertBefore(chat, pending);
      pending.parentNode.removeChild(pending);
    }
  }

  function cleanupLegacyHeroWidgets(grid) {
    if (!grid) {
      return;
    }

    var legacy = grid.querySelectorAll('[data-eottae-home-plaza-hero], [data-eottae-home-talk-sidebar]');
    var i;
    for (i = 0; i < legacy.length; i += 1) {
      if (legacy[i].parentNode) {
        legacy[i].parentNode.removeChild(legacy[i]);
      }
    }

    var feed = document.getElementById('eottae-home-talk-feed');
    if (feed && feed.parentNode) {
      feed.parentNode.removeChild(feed);
    }
  }

  function mountHero() {
    var chat = getSection();
    if (!chat || chat.dataset.heroMounted === '1') {
      return false;
    }

    var grid = findHeroGrid();
    var mainCol = findHeroMainColumn(grid);
    var sidebar = findHeroSidebarColumn(grid);
    if (!grid || !mainCol) {
      return false;
    }

    cleanupLegacyHeroWidgets(grid);
    hideMainColumnEvents(mainCol);
    unwrapPendingSlot(chat);

    chat.classList.add('public-group-chat--hero', 'public-group-chat--kakao', 'home-hero-chat-column');
    grid.classList.add('eottae-home-hero-grid--3col');

    if (sidebar) {
      grid.insertBefore(chat, sidebar);
    } else {
      grid.appendChild(chat);
    }

    chat.dataset.heroMounted = '1';
    return true;
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

  function renderMessage(message, section) {
    if (global.EottaePublicChatManage && global.EottaePublicChatManage.messageHtml) {
      return global.EottaePublicChatManage.messageHtml(message, section || getSection());
    }

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
      ? (message.ai_display_name || message.author || '어때봇 · AI 도우미')
      : (message.author || '익명');
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

    var actionHtml = '';
    if (message.action_label && message.action_url && /^https?:\/\//i.test(message.action_url)) {
      actionHtml = '<p class="public-group-chat__action-wrap"><a href="' + esc(message.action_url)
        + '" class="public-group-chat__cta" target="_blank" rel="noopener noreferrer">'
        + esc(message.action_label) + '</a></p>';
    }

    return ''
      + '<article class="' + classes.join(' ') + '" data-wr-id="' + esc(message.wr_id) + '">'
      + '<div class="public-group-chat__message-inner">'
      + meta
      + '<div class="public-group-chat__bubble-row">'
      + timeBefore
      + '<div class="public-group-chat__bubble">'
      + '<p class="public-group-chat__text">' + esc(message.text).replace(/\n/g, '<br>') + '</p>'
      + actionHtml
      + '</div>'
      + timeAfter
      + '</div>'
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
      html += renderMessage(message, section);
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
    var input = form.querySelector('#eottae-public-chat-input');
    var sendBtn = form.querySelector('.public-group-chat__send');
    var sendUrl = section.getAttribute('data-send-url');
    var token = section.getAttribute('data-member-token') || '';
    var message = input ? input.value.trim() : '';

    if (!sendUrl || message === '') {
      return;
    }

    if (form.dataset.sending === '1') {
      return;
    }

    if (requiresAuth(section)) {
      handleAuthRequired(section, '회원가입 또는 로그인 후 메시지를 보낼 수 있습니다.');
      return;
    }

    form.dataset.sending = '1';
    if (sendBtn) {
      sendBtn.disabled = true;
    }

    clearAuthNotice(section);

    var body = new FormData();
    body.append('action', 'send');
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

        if (data.member_token) {
          section.setAttribute('data-member-token', data.member_token);
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
      })
      .catch(function (err) {
        var errMessage = err && err.message ? err.message : '전송에 실패했습니다.';
        if (/로그인|회원가입/.test(errMessage)) {
          handleAuthRequired(section, errMessage);
          return;
        }
        window.alert(errMessage);
      })
      .then(function () {
        form.dataset.sending = '0';
        if (sendBtn) {
          sendBtn.disabled = section.getAttribute('data-can-send') !== '1';
        }
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

      var input = form.querySelector('#eottae-public-chat-input');
      if (input) {
        input.addEventListener('keydown', function (event) {
          if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage(section, form);
          }
        });
      }
    }

    poll(section);
    global.setInterval(function () {
      poll(section);
    }, POLL_MS);

    if (global.EottaePublicChatManage) {
      global.EottaePublicChatManage.appendMessages = appendMessages;
      global.EottaePublicChatManage.initSection(section);
    }
  }

  function runMount() {
    if (mountHero()) {
      var section = getSection();
      if (section) {
        bindSection(section);
      }
      return true;
    }
    return false;
  }

  function init() {
    var schedule = function () {
      if (typeof global.eottaeHomeAfterReactReady === 'function') {
        global.eottaeHomeAfterReactReady(runMount);
        return;
      }
      global.setTimeout(runMount, 1500);
    };

    schedule();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeHomePublicChat = init;
  global.findEottaeHeroGrid = findHeroGrid;
  global.findEottaeHeroSidebarColumn = function () {
    return findHeroSidebarColumn(findHeroGrid());
  };
}(window));
