/**
 * 세부공개단체톡 — 관리자 AI 말걸기·삭제
 */
(function (global) {
  'use strict';

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

  function canManageAi(section) {
    return section && section.getAttribute('data-can-manage-ai') === '1';
  }

  function deleteButtonHtml(message) {
    if (!message || !message.can_delete || !message.wr_id) {
      return '';
    }
    return '<button type="button" class="public-group-chat__delete" data-public-chat-delete="'
      + esc(message.wr_id)
      + '" aria-label="AI 메시지 삭제">삭제</button>';
  }

  function messageHtml(message, section) {
    if (!message || !message.wr_id || !message.text) {
      return '';
    }

    var manage = canManageAi(section);
    var classes = ['public-group-chat__message'];
    if (message.is_mine) {
      classes.push('public-group-chat__message--mine');
    }
    if (message.is_ai) {
      classes.push('public-group-chat__message--ai', 'is-talk-ai-message');
    }

    var author = message.is_ai
      ? (message.ai_display_name || message.author || '어때봇 · AI 도우미')
      : (message.author_display || message.author || '익명');
    var badge = message.is_ai
      ? '<span class="talk-ai-msg__badge talk-ai-msg__badge--sm" aria-label="AI 도우미">'
        + '<span class="talk-ai-msg__icon" aria-hidden="true">🤖</span>'
        + '<span class="talk-ai-msg__badge-label">' + esc(author) + '</span>'
        + '</span>'
      : '';
    var deleteBtn = manage ? deleteButtonHtml(message) : '';
    var meta = '';

    if (!message.is_mine) {
      meta = '<div class="public-group-chat__meta">'
        + (message.is_ai ? badge : '<strong class="public-group-chat__author">' + esc(author) + '</strong>')
        + deleteBtn
        + '</div>';
    } else if (deleteBtn) {
      meta = '<div class="public-group-chat__meta public-group-chat__meta--actions">' + deleteBtn + '</div>';
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

  function postAction(section, action, extra) {
    var sendUrl = section.getAttribute('data-send-url');
    var token = section.getAttribute('data-member-token') || '';
    var roomId = section.getAttribute('data-room-id');

    if (!sendUrl) {
      return Promise.reject(new Error('요청 URL이 없습니다.'));
    }

    var body = new FormData();
    body.append('action', action);
    body.append('eottae_talkroom_member_token', token);
    if (roomId) {
      body.append('room_id', roomId);
    }
    if (extra) {
      Object.keys(extra).forEach(function (key) {
        body.append(key, extra[key]);
      });
    }

    return fetch(sendUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: body,
      headers: { Accept: 'application/json' },
    }).then(parseJsonResponse);
  }

  function removeMessageNode(section, wrId) {
    if (!section || wrId < 1) {
      return;
    }
    var node = section.querySelector('[data-wr-id="' + wrId + '"]');
    if (node && node.parentNode) {
      node.parentNode.removeChild(node);
    }
  }

  function bindAiSpeak(section) {
    var btn = section.querySelector('[data-public-chat-ai-speak]');
    if (!btn || btn.dataset.bound === '1') {
      return;
    }
    btn.dataset.bound = '1';

    btn.addEventListener('click', function () {
      if (btn.dataset.sending === '1') {
        return;
      }
      btn.dataset.sending = '1';
      btn.disabled = true;

      postAction(section, 'ai_speak')
        .then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.message) || 'AI 메시지 전송에 실패했습니다.');
          }
          if (data.member_token) {
            section.setAttribute('data-member-token', data.member_token);
          }
          if (data.message_row && global.EottaePublicChatManage && global.EottaePublicChatManage.appendMessages) {
            global.EottaePublicChatManage.appendMessages(section, [data.message_row]);
          }
        })
        .catch(function (err) {
          global.alert(err && err.message ? err.message : 'AI 메시지 전송에 실패했습니다.');
        })
        .then(function () {
          btn.dataset.sending = '0';
          btn.disabled = false;
        });
    });
  }

  function bindDelete(section) {
    if (section.dataset.manageDeleteBound === '1') {
      return;
    }
    section.dataset.manageDeleteBound = '1';

    section.addEventListener('click', function (event) {
      var target = event.target;
      if (!target || !target.getAttribute) {
        return;
      }
      var wrId = target.getAttribute('data-public-chat-delete');
      if (!wrId) {
        return;
      }

      event.preventDefault();
      if (target.dataset.deleting === '1') {
        return;
      }
      if (!global.confirm('이 AI 메시지를 삭제할까요?')) {
        return;
      }

      target.dataset.deleting = '1';
      target.disabled = true;

      postAction(section, 'delete_message', { wr_id: wrId })
        .then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.message) || '삭제에 실패했습니다.');
          }
          if (data.member_token) {
            section.setAttribute('data-member-token', data.member_token);
          }
          removeMessageNode(section, parseInt(wrId, 10) || 0);
        })
        .catch(function (err) {
          global.alert(err && err.message ? err.message : '삭제에 실패했습니다.');
          target.dataset.deleting = '0';
          target.disabled = false;
        });
    });
  }

  function initSection(section) {
    if (!section || !canManageAi(section) || section.dataset.manageBound === '1') {
      return;
    }
    section.dataset.manageBound = '1';
    bindAiSpeak(section);
    bindDelete(section);
  }

  function scan() {
    var sections = document.querySelectorAll('.public-group-chat[data-can-manage-ai="1"]');
    var i;
    for (i = 0; i < sections.length; i += 1) {
      initSection(sections[i]);
    }
  }

  global.EottaePublicChatManage = {
    canManageAi: canManageAi,
    messageHtml: messageHtml,
    initSection: initSection,
    scan: scan,
    appendMessages: null,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scan);
  } else {
    scan();
  }
}(typeof window !== 'undefined' ? window : this));
