/**
 * 세부톡방 목록 카드 — 방장·최고관리자 삭제 (확인 후 API 호출)
 */
(function (global) {
  'use strict';

  var bound = false;

  function config() {
    return global.__EOTTae_TALKROOM_DELETE__ || null;
  }

  function confirmMessage(roomName) {
    var name = (roomName || '').trim() || '이 톡방';
    return (
      '「' + name + '」 톡방을 삭제하시겠습니까?\n\n'
      + '멤버·게시글·신고·로그 데이터가 함께 삭제되며 되돌릴 수 없습니다.'
    );
  }

  function postDelete(roomId, ownerToken, procUrl) {
    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('room_id', String(roomId));
    fd.append('eottae_talkroom_owner_token', ownerToken);
    return fetch(procUrl, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
    }).then(function (res) {
      return res.json();
    });
  }

  function handleDeleteClick(btn) {
    var cfg = config();
    if (!cfg || !cfg.owner_token || !cfg.proc_url) {
      return false;
    }

    var roomId = btn.getAttribute('data-talk-room-delete');
    if (!roomId) {
      return false;
    }

    var roomName = btn.getAttribute('data-room-name') || '';
    if (!global.confirm(confirmMessage(roomName))) {
      return false;
    }

    if (btn.disabled) {
      return false;
    }

    btn.disabled = true;

    postDelete(roomId, cfg.owner_token, cfg.proc_url)
      .then(function (data) {
        if (data && data.success) {
          var card = btn.closest('.talk-room-card');
          if (card && card.parentNode) {
            card.parentNode.removeChild(card);
            if (!document.querySelector('.talk-room-card')) {
              global.location.reload();
            }
            return;
          }
          global.location.reload();
          return;
        }
        global.alert((data && data.message) ? data.message : '삭제에 실패했습니다.');
        btn.disabled = false;
      })
      .catch(function () {
        global.alert('네트워크 오류가 발생했습니다.');
        btn.disabled = false;
      });

    return false;
  }

  function onDocumentClick(ev) {
    var btn = ev.target && ev.target.closest
      ? ev.target.closest('[data-talk-room-delete]')
      : null;
    if (!btn) {
      return;
    }

    ev.preventDefault();
    ev.stopPropagation();
    if (typeof ev.stopImmediatePropagation === 'function') {
      ev.stopImmediatePropagation();
    }

    handleDeleteClick(btn);
  }

  function bindDelegation() {
    if (bound) {
      return;
    }
    bound = true;
    document.addEventListener('click', onDocumentClick, true);
  }

  global.eottaeTalkroomCardDeleteClick = function (btn, ev) {
    if (ev) {
      ev.preventDefault();
      ev.stopPropagation();
      if (typeof ev.stopImmediatePropagation === 'function') {
        ev.stopImmediatePropagation();
      }
    }
    return handleDeleteClick(btn);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindDelegation);
  } else {
    bindDelegation();
  }
})(typeof window !== 'undefined' ? window : this);
