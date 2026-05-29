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

    if (typeof global.scheduleEottaeHeroColumnHeights === 'function') {
      global.scheduleEottaeHeroColumnHeights(80);
      global.scheduleEottaeHeroColumnHeights(400);
    } else if (typeof global.syncEottaeHeroColumnHeights === 'function') {
      global.syncEottaeHeroColumnHeights();
    }

    return true;
  }

  function getMessagesEl(section) {
    return section ? section.querySelector('#eottae-public-chat-messages') : null;
  }

  function buildActionHtml(message) {
    if (!message || !message.action_label) {
      return '';
    }

    var calendarEventId = parseInt(message.calendar_event_id, 10) || 0;
    if (calendarEventId > 0) {
      if (typeof global.eottaeCalendarInitEventModal === 'function') {
        global.eottaeCalendarInitEventModal();
      }
      return ''
        + '<p class="public-group-chat__action-wrap"><a href="#" class="public-group-chat__cta"'
        + ' data-sebu-cal-event="' + esc(String(calendarEventId)) + '" role="button">'
        + esc(message.action_label) + '</a></p>';
    }

    if (message.action_url && /^https?:\/\//i.test(message.action_url)) {
      return ''
        + '<p class="public-group-chat__action-wrap"><a href="' + esc(message.action_url)
        + '" class="public-group-chat__cta" target="_blank" rel="noopener noreferrer">'
        + esc(message.action_label) + '</a></p>';
    }

    return '';
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
    var timeBefore = mineTimeBeforeHtml(message);
    var timeAfter = !message.is_mine && message.time_label
      ? '<time class="public-group-chat__time">' + esc(message.time_label) + '</time>'
      : '';

    var actionHtml = buildActionHtml(message);

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

  function getOldestWrId(section) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl) {
      return 0;
    }

    var nodes = messagesEl.querySelectorAll('[data-wr-id]');
    var oldest = 0;
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

  function countRenderedMessages(section) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl) {
      return 0;
    }

    return messagesEl.querySelectorAll('[data-wr-id]').length;
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

    var stickToBottom = messagesEl.scrollTop + messagesEl.clientHeight >= messagesEl.scrollHeight - 24;
    messagesEl.insertAdjacentHTML('afterbegin', html);

    if (oldestId > 0) {
      section.setAttribute('data-oldest-wr-id', String(oldestId));
    }

    if (stickToBottom) {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    return inserted;
  }

  function fetchHistory(section, beforeWrId, limit) {
    var pollUrl = section.getAttribute('data-poll-url');
    if (!pollUrl || beforeWrId < 1) {
      return Promise.resolve({ success: false, messages: [] });
    }

    var url = pollUrl
      + (pollUrl.indexOf('?') >= 0 ? '&' : '?')
      + 'action=history'
      + '&before_wr_id=' + encodeURIComponent(String(beforeWrId))
      + '&limit=' + encodeURIComponent(String(limit || 3));

    return fetch(url, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then(parseJsonResponse)
      .catch(function () {
        return { success: false, messages: [] };
      });
  }

  function dispatchChatLayout(section) {
    if (typeof global.scheduleEottaeHeroColumnHeights === 'function') {
      global.scheduleEottaeHeroColumnHeights(0);
    }

    global.dispatchEvent(new CustomEvent('eottae-public-chat-layout', {
      detail: { section: section || getSection() },
    }));
  }

  function loadDeferredHistory(section) {
    if (!section || section.getAttribute('data-defer-history') !== '1') {
      return;
    }

    if (section.dataset.historyDone === '1' || section.dataset.historyLoading === '1') {
      return;
    }

    var totalLimit = parseInt(section.getAttribute('data-history-total') || '20', 10) || 20;
    var batchSize = 3;
    var delayMs = 140;

    section.dataset.historyLoading = '1';
    messagesElClass(section, true);

    function finish() {
      section.dataset.historyDone = '1';
      section.dataset.historyLoading = '0';
      messagesElClass(section, false);
      scheduleScrollMessagesToBottom(section);
      dispatchChatLayout(section);
    }

    function step() {
      var loadedCount = countRenderedMessages(section);
      var oldest = getOldestWrId(section);

      if (loadedCount >= totalLimit || oldest < 1) {
        finish();
        return;
      }

      var remaining = totalLimit - loadedCount;
      var limit = Math.min(batchSize, remaining);

      fetchHistory(section, oldest, limit).then(function (data) {
        if (!data || !data.success) {
          finish();
          return;
        }

        var inserted = prependMessages(section, data.messages || []);
        loadedCount = countRenderedMessages(section);

        if (inserted < 1 || loadedCount >= totalLimit) {
          finish();
          return;
        }

        global.setTimeout(step, delayMs);
      });
    }

    step();
  }

  function messagesElClass(section, loading) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl) {
      return;
    }

    messagesEl.classList.toggle('public-group-chat__messages--history-loading', !!loading);
  }

  function scheduleDeferredHistory(section) {
    if (!section || section.getAttribute('data-defer-history') !== '1') {
      return;
    }

    if (section.dataset.historyScheduled === '1') {
      return;
    }

    section.dataset.historyScheduled = '1';

    var start = function () {
      var run = function () {
        loadDeferredHistory(section);
      };

      if (typeof global.requestIdleCallback === 'function') {
        global.requestIdleCallback(run, { timeout: 3200 });
      } else {
        global.setTimeout(run, 400);
      }
    };

    var onReady = function () {
      global.setTimeout(start, global.innerWidth >= 1024 ? 900 : 500);
    };

    if (document.readyState === 'complete') {
      onReady();
      return;
    }

    global.addEventListener('load', onReady, { once: true });
    global.setTimeout(onReady, 2800);
  }

  function poll(section) {
    var pollUrl = section.getAttribute('data-poll-url');
    if (!pollUrl) {
      return Promise.resolve();
    }

    var since = parseInt(section.getAttribute('data-last-wr-id') || '0', 10) || 0;
    var url = pollUrl + (pollUrl.indexOf('?') >= 0 ? '&' : '?') + 'action=poll&since_wr_id=' + encodeURIComponent(String(since));
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

  function scrollMessagesToBottom(section) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl) {
      return;
    }

    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function scheduleScrollMessagesToBottom(section) {
    var target = section || getSection();
    if (!target) {
      return;
    }

    scrollMessagesToBottom(target);
    global.requestAnimationFrame(function () {
      scrollMessagesToBottom(target);
    });
  }

  function observeMessagesLayout(section) {
    var messagesEl = getMessagesEl(section);
    if (!messagesEl || messagesEl.dataset.scrollLayoutObserved === '1') {
      return;
    }
    if (typeof global.ResizeObserver === 'undefined') {
      return;
    }

    messagesEl.dataset.scrollLayoutObserved = '1';
    var scrollTimer = null;
    var observer = new global.ResizeObserver(function () {
      if (section.dataset.historyLoading === '1') {
        return;
      }
      global.clearTimeout(scrollTimer);
      scrollTimer = global.setTimeout(function () {
        scrollMessagesToBottom(section);
      }, 80);
    });
    observer.observe(messagesEl);
    global.setTimeout(function () {
      observer.disconnect();
    }, 4000);
  }

  function bindSection(section) {
    if (!section || section.dataset.bound === '1') {
      return;
    }
    section.dataset.bound = '1';

    scheduleScrollMessagesToBottom(section);
    observeMessagesLayout(section);

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

    scheduleDeferredHistory(section);

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
        if (typeof global.bindEottaePublicChatFullscreen === 'function') {
          global.bindEottaePublicChatFullscreen(section);
        } else if (typeof global.initEottaeChatFullscreen === 'function') {
          global.initEottaeChatFullscreen();
        }
        scheduleScrollMessagesToBottom(section);
      }
      return true;
    }
    return false;
  }

  global.addEventListener('eottae-public-chat-layout', function (ev) {
    var section = (ev && ev.detail && ev.detail.section) ? ev.detail.section : getSection();
    if (section) {
      scheduleScrollMessagesToBottom(section);
    }
  });

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
  global.scrollEottaeHomePublicChatToBottom = scrollMessagesToBottom;
  global.scheduleEottaeHomePublicChatScroll = function () {
    scheduleScrollMessagesToBottom(getSection());
  };
}(window));
