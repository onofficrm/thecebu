/**
 * 공개/톡방 채팅 — 모바일 전체화면 토글
 */
(function (global) {
  'use strict';

  var MOBILE_MQ = '(max-width: 767px)';

  function dispatchLayout(section) {
    global.dispatchEvent(new CustomEvent('eottae-public-chat-layout', {
      detail: { section: section },
    }));

    if (typeof global.scheduleEottaeHeroColumnHeights === 'function') {
      global.scheduleEottaeHeroColumnHeights(60);
      global.scheduleEottaeHeroColumnHeights(280);
    } else if (typeof global.syncEottaeHeroColumnHeights === 'function') {
      global.syncEottaeHeroColumnHeights();
    }
  }

  function bindSection(section) {
    var btn = section.querySelector('[data-public-chat-fullscreen]');
    if (!btn || btn.getAttribute('data-fullscreen-bound') === '1') {
      return;
    }
    btn.setAttribute('data-fullscreen-bound', '1');

    var label = btn.querySelector('.public-group-chat__fullscreen-label');
    var mq = global.matchMedia(MOBILE_MQ);

    function isFullscreen() {
      return section.classList.contains('public-group-chat--fullscreen-active');
    }

    function setFullscreen(on) {
      section.classList.toggle('public-group-chat--fullscreen-active', on);
      document.body.classList.toggle('public-group-chat-fullscreen-open', on);
      document.documentElement.classList.toggle('public-group-chat-fullscreen-open', on);
      document.body.classList.toggle('talk-room-chat-fullscreen', on && section.id === 'eottae-talkroom-chat');

      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
      btn.setAttribute('aria-label', on ? '전체화면 닫기' : '전체화면으로 보기');
      if (label) {
        label.textContent = on ? '닫기' : '전체화면';
      }

      if (on) {
        global.scrollTo(0, 0);
      }

      dispatchLayout(section);
      global.setTimeout(function () {
        dispatchLayout(section);
      }, 120);
      global.setTimeout(function () {
        dispatchLayout(section);
      }, 360);
    }

    function syncButtonVisibility() {
      var show = mq.matches;
      btn.hidden = !show;
      if (!show && isFullscreen()) {
        setFullscreen(false);
      }
    }

    btn.addEventListener('click', function () {
      setFullscreen(!isFullscreen());
    });

    mq.addEventListener('change', syncButtonVisibility);
    syncButtonVisibility();

    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && isFullscreen()) {
        setFullscreen(false);
      }
    });
  }

  function init() {
    document.querySelectorAll('.public-group-chat').forEach(function (section) {
      if (section.querySelector('[data-public-chat-fullscreen]')) {
        bindSection(section);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeChatFullscreen = init;
  global.bindEottaePublicChatFullscreen = bindSection;
}(typeof window !== 'undefined' ? window : this));
