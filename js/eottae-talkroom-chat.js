/**
 * 톡방 상세 — 카카오톡 스타일 실시간 채팅
 */
(function (global) {
  'use strict';

  var POLL_MS = 12000;
  var SCROLL_BOTTOM_THRESHOLD = 48;
  var SCROLL_TOP_THRESHOLD = 72;

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

  function unreadBeforeHtml(message) {
    if (!message || !message.is_mine) {
      return '';
    }
    var count = parseInt(message.unread_count, 10) || 0;
    if (count < 1) {
      return '';
    }
    return '<span class="public-group-chat__unread" aria-label="읽지 않은 '
      + count + '명">' + count + '</span>';
  }

  function mineTimeBeforeHtml(message) {
    var html = unreadBeforeHtml(message);
    if (message && message.is_mine && message.time_label) {
      html += '<time class="public-group-chat__time">' + esc(message.time_label) + '</time>';
    }
    return html;
  }

  function collectMineWrIds(section) {
    if (!section) {
      return [];
    }
    var nodes = section.querySelectorAll('.public-group-chat__message--mine[data-wr-id]');
    var ids = [];
    var i;
    for (i = 0; i < nodes.length; i += 1) {
      var wrId = parseInt(nodes[i].getAttribute('data-wr-id'), 10) || 0;
      if (wrId > 0) {
        ids.push(wrId);
      }
    }
    return ids;
  }

  function applyUnreadUpdates(section, updates) {
    if (!section || !updates) {
      return;
    }
    Object.keys(updates).forEach(function (key) {
      var wrId = parseInt(key, 10) || 0;
      var count = parseInt(updates[key], 10) || 0;
      if (wrId < 1) {
        return;
      }
      var article = section.querySelector('.public-group-chat__message--mine[data-wr-id="' + wrId + '"]');
      if (!article) {
        return;
      }
      var row = article.querySelector('.public-group-chat__bubble-row');
      if (!row) {
        return;
      }
      var unread = row.querySelector('.public-group-chat__unread');
      if (count < 1) {
        if (unread && unread.parentNode) {
          unread.parentNode.removeChild(unread);
        }
        return;
      }
      if (!unread) {
        unread = document.createElement('span');
        unread.className = 'public-group-chat__unread';
        var time = row.querySelector('.public-group-chat__time');
        if (time) {
          row.insertBefore(unread, time);
        } else {
          row.insertBefore(unread, row.firstChild);
        }
      }
      unread.textContent = String(count);
      unread.setAttribute('aria-label', '읽지 않은 ' + count + '명');
    });
  }

  function renderMessage(message, section) {
    if (global.EottaePublicChatManage && global.EottaePublicChatManage.messageHtml) {
      return global.EottaePublicChatManage.messageHtml(message, section || getSection());
    }

    if (!message || !message.wr_id || !message.text) {
      return '';
    }

    var avatar = global.EottaePublicChatManage && global.EottaePublicChatManage.avatarHtml
      ? global.EottaePublicChatManage.avatarHtml(message)
      : '';

    if (!avatar && !message.is_mine) {
      var authorName = message.author_display || message.author || '익명';
      var profileUrl = message.profile_url || '';
      var imageUrl = message.profile_image_url || '';
      var initial = message.profile_initial || String(authorName).charAt(0) || '?';
      var inner = imageUrl
        ? '<img src="' + esc(imageUrl) + '" alt="" class="public-group-chat__avatar-img" width="38" height="38" loading="lazy" decoding="async">'
        : '<span class="public-group-chat__avatar-initial" aria-hidden="true">' + esc(initial) + '</span>';
      if (message.is_ai) {
        avatar = '<span class="public-group-chat__avatar public-group-chat__avatar--ai" aria-hidden="true">'
          + '<span class="public-group-chat__avatar-emoji">🤖</span></span>';
      } else if (profileUrl) {
        avatar = '<a href="' + esc(profileUrl) + '" class="public-group-chat__avatar" title="'
          + esc(authorName) + ' 회원정보 보기">' + inner + '</a>';
      } else {
        avatar = '<span class="public-group-chat__avatar public-group-chat__avatar--static" aria-hidden="true">' + inner + '</span>';
      }
    }

    var classes = ['public-group-chat__message'];
    if (message.is_mine) {
      classes.push('public-group-chat__message--mine');
    }
    if (message.is_ai) {
      classes.push('public-group-chat__message--ai', 'is-talk-ai-message');
    }
    if (!message.is_mine) {
      classes.push('public-group-chat__message--peer');
    }

    var author = message.is_ai
      ? message.ai_display_name || message.author || '어때봇 · AI 도우미'
      : (message.author_display || message.author || '익명');
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
    var timeBefore = mineTimeBeforeHtml(message);
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
      + avatar
      + '<div class="public-group-chat__message-body">'
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
      + '</div>'
      + '</article>';
  }

  function appendMessages(section, messages, forceScroll) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl || !messages || !messages.length) {
      return;
    }

    var html = '';
    var i;
    var lastId = parseInt(section.getAttribute('data-last-wr-id') || '0', 10) || 0;
    var stickToBottom = forceScroll
      || messagesEl.scrollTop + messagesEl.clientHeight >= messagesEl.scrollHeight - SCROLL_BOTTOM_THRESHOLD;

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
      return;
    }

    removeEmptyState(messagesEl);
    messagesEl.insertAdjacentHTML('beforeend', html);
    section.setAttribute('data-last-wr-id', String(lastId));
    if (stickToBottom) {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }
  }

  function getOldestWrId(section) {
    var stored = parseInt(section.getAttribute('data-first-wr-id') || '0', 10) || 0;
    var messagesEl = getMessagesEl(section);
    if (!messagesEl) {
      return stored;
    }

    var nodes = messagesEl.querySelectorAll('[data-wr-id]');
    var oldest = stored;
    var i;
    for (i = 0; i < nodes.length; i += 1) {
      var wrId = parseInt(nodes[i].getAttribute('data-wr-id'), 10) || 0;
      if (wrId < 1) {
        continue;
      }
      if (oldest < 1 || wrId < oldest) {
        oldest = wrId;
      }
    }

    return oldest;
  }

  function syncFirstWrId(section) {
    var oldest = getOldestWrId(section);
    if (oldest > 0) {
      section.setAttribute('data-first-wr-id', String(oldest));
    }
  }

  function prependMessages(section, messages) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl || !messages || !messages.length) {
      return 0;
    }

    var html = '';
    var i;
    var inserted = 0;
    var oldestId = getOldestWrId(section);

    for (i = 0; i < messages.length; i += 1) {
      var message = messages[i];
      var wrId = parseInt(message.wr_id, 10) || 0;
      if (wrId < 1 || messagesEl.querySelector('[data-wr-id="' + wrId + '"]')) {
        continue;
      }
      html += renderMessage(message, section);
      inserted += 1;
      if (oldestId < 1 || wrId < oldestId) {
        oldestId = wrId;
      }
    }

    if (!html) {
      return 0;
    }

    removeEmptyState(messagesEl);

    var prevHeight = messagesEl.scrollHeight;
    var prevTop = messagesEl.scrollTop;
    var topNode = messagesEl.querySelector('[data-chat-history-top]');
    if (topNode && topNode.nextSibling) {
      topNode.insertAdjacentHTML('afterend', html);
    } else {
      messagesEl.insertAdjacentHTML('afterbegin', html);
    }
    messagesEl.scrollTop = prevTop + (messagesEl.scrollHeight - prevHeight);

    if (oldestId > 0) {
      section.setAttribute('data-first-wr-id', String(oldestId));
    }

    return inserted;
  }

  function setHistoryLoading(section, loading) {
    var messagesEl = getMessagesEl(section);
    var topNode = messagesEl ? messagesEl.querySelector('[data-chat-history-top]') : null;
    if (messagesEl) {
      messagesEl.classList.toggle('public-group-chat__messages--history-loading', !!loading);
    }
    if (topNode) {
      topNode.hidden = !loading;
    }
  }

  function fetchHistory(section, beforeWrId, limit) {
    var pollUrl = section.getAttribute('data-poll-url');
    var roomId = section.getAttribute('data-room-id');
    if (!pollUrl || !roomId || beforeWrId < 1) {
      return Promise.resolve({ success: false, messages: [] });
    }

    var url = pollUrl
      + (pollUrl.indexOf('?') >= 0 ? '&' : '?')
      + 'action=history'
      + '&room_id=' + encodeURIComponent(roomId)
      + '&before_wr_id=' + encodeURIComponent(String(beforeWrId))
      + '&limit=' + encodeURIComponent(String(limit || 15));

    return fetch(url, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then(parseJsonResponse)
      .catch(function () {
        return { success: false, messages: [] };
      });
  }

  function loadOlderMessages(section) {
    if (!section || section.getAttribute('data-has-older') !== '1') {
      return Promise.resolve();
    }
    if (section.dataset.historyLoading === '1') {
      return Promise.resolve();
    }

    var oldest = getOldestWrId(section);
    if (oldest < 1) {
      section.setAttribute('data-has-older', '0');
      return Promise.resolve();
    }

    var batch = parseInt(section.getAttribute('data-history-batch') || '15', 10) || 15;
    section.dataset.historyLoading = '1';
    setHistoryLoading(section, true);

    return fetchHistory(section, oldest, batch).then(function (data) {
      if (!data || !data.success) {
        return;
      }

      prependMessages(section, data.messages || []);

      if (!data.has_more) {
        section.setAttribute('data-has-older', '0');
      } else if (data.oldest_wr_id) {
        section.setAttribute('data-first-wr-id', String(parseInt(data.oldest_wr_id, 10) || oldest));
      }
    }).finally(function () {
      section.dataset.historyLoading = '0';
      setHistoryLoading(section, false);
    });
  }

  function bindScrollHistory(section) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl || messagesEl.dataset.historyScrollBound === '1') {
      return;
    }
    messagesEl.dataset.historyScrollBound = '1';

    messagesEl.addEventListener('scroll', function () {
      if (section.getAttribute('data-has-older') !== '1') {
        return;
      }
      if (messagesEl.scrollTop <= SCROLL_TOP_THRESHOLD) {
        loadOlderMessages(section);
      }
    }, { passive: true });
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
    var mineIds = collectMineWrIds(section);
    if (mineIds.length) {
      url += '&read_check_wr_ids=' + encodeURIComponent(mineIds.join(','));
    }

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
        applyUnreadUpdates(section, data.unread_updates || null);
      })
      .catch(function () {});
  }

  function syncMemberToken(section, token) {
    if (!section || !token) {
      return;
    }
    section.setAttribute('data-member-token', token);
  }

  function isLifeQaSection(section) {
    return section && section.getAttribute('data-life-qa') === '1';
  }

  function askLifeAi(section, form) {
    var input = form.querySelector('#eottae-talkroom-chat-input, #eottae-public-chat-input');
    var sendBtn = form.querySelector('.public-group-chat__send, [data-public-chat-life-ai]');
    var sendUrl = section.getAttribute('data-send-url');
    var roomId = section.getAttribute('data-room-id');
    var token = section.getAttribute('data-member-token') || '';
    var question = input ? input.value.trim() : '';
    var body = new FormData();

    if (!sendUrl || !roomId || question === '') {
      return;
    }

    if (requiresAuth(section)) {
      handleAuthRequired(section, '회원가입 또는 로그인 후 AI 질문을 사용할 수 있습니다.');
      return;
    }

    if (form.dataset.lifeAiSending === '1') {
      return;
    }

    form.dataset.lifeAiSending = '1';
    if (sendBtn) {
      sendBtn.disabled = true;
    }

    clearAuthNotice(section);

    body.append('action', 'life_qa');
    body.append('room_id', roomId);
    body.append('question', question);
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
          throw new Error((data && data.message) || 'AI 질문 요청에 실패했습니다.');
        }

        syncMemberToken(section, data.member_token);

        if (input) {
          input.value = '';
          input.style.height = '';
        }

        if (data.question_row) {
          appendMessages(section, [data.question_row], true);
        }
        if (data.message_row) {
          appendMessages(section, [data.message_row], true);
        }
      })
      .catch(function (err) {
        var errMessage = err && err.message ? err.message : 'AI 질문 요청에 실패했습니다.';
        if (/로그인|회원가입/.test(errMessage)) {
          handleAuthRequired(section, errMessage);
          return;
        }
        global.alert(errMessage);
      })
      .then(function () {
        form.dataset.lifeAiSending = '0';
        if (sendBtn) {
          sendBtn.disabled = section.getAttribute('data-can-send') !== '1';
        }
      });
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

        syncMemberToken(section, data.member_token);

        if (input) {
          input.value = '';
          input.style.height = '';
        }

        if (data.message_row) {
          appendMessages(section, [data.message_row], true);
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
        form.dataset.sending = '0';
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
      if (isLifeQaSection(section)) {
        askLifeAi(section, form);
        return;
      }
      sendMessage(section, form);
    });

    var input = form.querySelector('#eottae-talkroom-chat-input');
    if (input) {
      input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
          event.preventDefault();
          if (isLifeQaSection(section)) {
            askLifeAi(section, form);
            return;
          }
          sendMessage(section, form);
        }
      });

      input.addEventListener('input', function () {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
      });
    }
  }

  function scrollMessagesToEnd(force) {
    var section = getSection();
    var messagesEl = getMessagesEl(section);
    if (!messagesEl) {
      return;
    }
    if (!force) {
      var nearBottom = messagesEl.scrollTop + messagesEl.clientHeight >= messagesEl.scrollHeight - SCROLL_BOTTOM_THRESHOLD;
      if (!nearBottom) {
        return;
      }
    }
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function scheduleScrollMessagesToEnd(force) {
    scrollMessagesToEnd(force);
    global.requestAnimationFrame(function () {
      scrollMessagesToEnd(force);
    });
    global.setTimeout(function () {
      scrollMessagesToEnd(force);
    }, 80);
  }

  function bindSection(section) {
    if (!section || section.dataset.chatBound === '1') {
      return;
    }
    section.dataset.chatBound = '1';

    bindComposer(section);
    syncFirstWrId(section);
    bindScrollHistory(section);
    scheduleScrollMessagesToEnd(true);

    poll(section);
    global.setInterval(function () {
      poll(section);
    }, POLL_MS);

    if (global.EottaePublicChatManage) {
      global.EottaePublicChatManage.appendMessages = appendMessages;
      global.EottaePublicChatManage.initSection(section);
    }
  }

  function bindMobileChatLayout(section) {
    if (!section || !global.matchMedia) {
      return;
    }

    var page = section.closest('.talk-room-detail-page--chat');
    var drawer = page ? page.querySelector('.talk-room-detail__drawer') : null;
    var mq = global.matchMedia('(max-width: 767px)');
    var FAB_OFFSET = 56;
    var layoutScheduled = false;

    function detectFabOffset() {
      var fab =
        document.querySelector('[class*="chatbot"]') ||
        document.querySelector('.onoff-chatbot') ||
        document.querySelector('[id*="chatbot" i]');
      if (!fab || !fab.getBoundingClientRect) {
        return FAB_OFFSET;
      }
      var rect = fab.getBoundingClientRect();
      if (rect.height < 20 || rect.bottom < global.innerHeight * 0.5) {
        return FAB_OFFSET;
      }
      return Math.max(FAB_OFFSET, Math.ceil(rect.height + 12));
    }

    function setDrawerOpenState() {
      if (!drawer) {
        return;
      }
      document.body.classList.toggle('talk-room-drawer-open', !!drawer.open);
    }

    function clearMobileChatLayout() {
      section.style.removeProperty('--talkroom-chat-height');
      section.style.removeProperty('height');
      section.style.removeProperty('max-height');
      section.style.removeProperty('min-height');
      document.documentElement.style.removeProperty('--talkroom-chat-top');
      document.documentElement.style.removeProperty('--talkroom-chat-fab-offset');
      document.body.classList.remove('talk-room-drawer-open');
      var inner = section.querySelector('.public-group-chat__inner');
      if (inner) {
        inner.style.removeProperty('height');
        inner.style.removeProperty('max-height');
      }
    }

    function updateChatHeight() {
      if (!mq.matches) {
        clearMobileChatLayout();
        return;
      }

      setDrawerOpenState();

      var viewportH = global.visualViewport ? global.visualViewport.height : global.innerHeight;
      var header = document.getElementById('hd');
      var fabOffset = detectFabOffset();
      var available;
      if (section.classList.contains('public-group-chat--fullscreen-active')) {
        available = Math.max(280, Math.floor(viewportH - fabOffset));
      } else {
        var chatTop = section.getBoundingClientRect().top;
        available = Math.max(240, Math.floor(viewportH - chatTop - fabOffset - 4));
      }

      document.documentElement.style.setProperty('--talkroom-chat-fab-offset', fabOffset + 'px');
      section.style.setProperty('--talkroom-chat-height', available + 'px');
      section.style.height = available + 'px';
      section.style.maxHeight = available + 'px';
      section.style.minHeight = '0';

      var inner = section.querySelector('.public-group-chat__inner');
      if (inner) {
        inner.style.height = '100%';
        inner.style.maxHeight = '100%';
      }

      if (header && header.getBoundingClientRect) {
        document.documentElement.style.setProperty(
          '--eottae-header-h',
          Math.ceil(header.getBoundingClientRect().height) + 'px'
        );
      }
    }

    function scheduleLayoutUpdate() {
      if (layoutScheduled) {
        return;
      }
      layoutScheduled = true;
      global.requestAnimationFrame(function () {
        layoutScheduled = false;
        updateChatHeight();
      });
    }

    scheduleLayoutUpdate();
    global.setTimeout(scheduleLayoutUpdate, 80);
    global.setTimeout(function () {
      scheduleLayoutUpdate();
      scheduleScrollMessagesToEnd(true);
    }, 320);

    if (drawer) {
      drawer.addEventListener('toggle', function () {
        setDrawerOpenState();
        scheduleLayoutUpdate();
        global.setTimeout(scheduleLayoutUpdate, 180);
      });
    }

    if (global.visualViewport) {
      global.visualViewport.addEventListener('resize', scheduleLayoutUpdate);
      global.visualViewport.addEventListener('scroll', scheduleLayoutUpdate);
    }

    mq.addEventListener('change', scheduleLayoutUpdate);
    global.addEventListener('resize', scheduleLayoutUpdate);
    global.addEventListener('orientationchange', function () {
      global.setTimeout(scheduleLayoutUpdate, 120);
    });

    global.addEventListener('eottae-public-chat-layout', function () {
      scheduleLayoutUpdate();
    });

    if (global.ResizeObserver && page) {
      var ro = new global.ResizeObserver(scheduleLayoutUpdate);
      var back = page.querySelector('.mypage-subpage__back');
      var flash = page.querySelector('.talk-room-detail__flash');
      if (back) {
        ro.observe(back);
      }
      if (flash) {
        ro.observe(flash);
      }
      if (drawer) {
        ro.observe(drawer);
      }
      ro.observe(section);
    }
  }

  function bindDesktopChatLayout(section) {
    if (!section || !global.matchMedia) {
      return;
    }

    var mq = global.matchMedia('(min-width: 768px)');
    var page = section.closest('.talk-room-detail-page--chat');
    var layoutScheduled = false;

    function updateChatHeight() {
      if (!mq.matches) {
        section.style.removeProperty('--talkroom-chat-height');
        section.style.removeProperty('height');
        section.style.removeProperty('max-height');
        section.style.removeProperty('min-height');
        return;
      }

      var viewportH = global.innerHeight;
      var header = document.getElementById('hd');
      if (header && header.getBoundingClientRect) {
        document.documentElement.style.setProperty(
          '--eottae-header-h',
          Math.ceil(header.getBoundingClientRect().height) + 'px'
        );
      }

      var chatTop = section.getBoundingClientRect().top;
      var available = Math.max(420, Math.floor(viewportH - chatTop - 16));
      section.style.setProperty('--talkroom-chat-height', available + 'px');
      section.style.height = available + 'px';
      section.style.maxHeight = available + 'px';
      section.style.minHeight = '0';

      var inner = section.querySelector('.public-group-chat__inner');
      if (inner) {
        inner.style.height = '100%';
        inner.style.maxHeight = '100%';
      }

      scheduleScrollMessagesToEnd(true);
    }

    function scheduleLayoutUpdate() {
      if (layoutScheduled) {
        return;
      }
      layoutScheduled = true;
      global.requestAnimationFrame(function () {
        layoutScheduled = false;
        updateChatHeight();
      });
    }

    scheduleLayoutUpdate();
    mq.addEventListener('change', scheduleLayoutUpdate);
    global.addEventListener('resize', scheduleLayoutUpdate);
    global.addEventListener('orientationchange', function () {
      global.setTimeout(scheduleLayoutUpdate, 120);
    });

    if (global.ResizeObserver && page) {
      var ro = new global.ResizeObserver(scheduleLayoutUpdate);
      var drawer = page.querySelector('.talk-room-detail__drawer');
      if (drawer) {
        ro.observe(drawer);
      }
      ro.observe(section);
    }
  }

  function collapseStrayHeaderMenu() {
    var menu = document.getElementById('siteMobileNav');
    if (menu) {
      menu.classList.remove('is-open');
      menu.setAttribute('aria-hidden', 'true');
    }

    var overlay = document.querySelector('.eottae-gnb-header__overlay, .site-header__overlay');
    if (overlay) {
      overlay.classList.remove('is-open');
    }

    document.querySelectorAll('.eottae-gnb-header__menu-btn, .site-header__menu-btn').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-label', '메뉴 열기');
    });

    if (document.body.classList.contains('talk-room-chat-active')) {
      document.body.style.overflow = '';
    }
  }

  function init() {
    collapseStrayHeaderMenu();

    if (document.body.classList.contains('talk-room-chat-active')) {
      document.documentElement.classList.add('talk-room-chat-page');
    }

    var section = getSection();
    if (section) {
      bindSection(section);
      bindMobileChatLayout(section);
      bindDesktopChatLayout(section);
      if (typeof global.bindEottaePublicChatFullscreen === 'function') {
        global.bindEottaePublicChatFullscreen(section);
      } else if (typeof global.initEottaeChatFullscreen === 'function') {
        global.initEottaeChatFullscreen();
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeTalkroomChat = init;
}(typeof window !== 'undefined' ? window : this));
