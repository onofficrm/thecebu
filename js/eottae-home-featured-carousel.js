/**
 * 홈 — 추천 인기 업소 캐러셀 터치 스와이프
 */
(function (global) {
  'use strict';

  var SWIPE_MIN = 40;

  function bind(root) {
    if (!root || root.dataset.swipeBound === '1') {
      return;
    }
    root.dataset.swipeBound = '1';

    var startX = 0;
    var startY = 0;

    root.addEventListener('touchstart', function (e) {
      if (!e.changedTouches || !e.changedTouches.length) {
        return;
      }
      startX = e.changedTouches[0].clientX;
      startY = e.changedTouches[0].clientY;
    }, { passive: true });

    root.addEventListener('touchend', function (e) {
      if (!e.changedTouches || !e.changedTouches.length) {
        return;
      }
      var dx = e.changedTouches[0].clientX - startX;
      var dy = e.changedTouches[0].clientY - startY;
      if (Math.abs(dx) < SWIPE_MIN || Math.abs(dx) < Math.abs(dy)) {
        return;
      }

      var dots = root.querySelectorAll('button[aria-label$="번 슬라이드"]');
      if (!dots.length) {
        return;
      }

      var active = 0;
      dots.forEach(function (dot, idx) {
        if (dot.className.indexOf('w-4') >= 0) {
          active = idx;
        }
      });

      var next = dx < 0
        ? (active + 1) % dots.length
        : (active - 1 + dots.length) % dots.length;
      dots[next].click();
    }, { passive: true });
  }

  function scan() {
    document.querySelectorAll('[data-featured-shops-carousel]').forEach(bind);
  }

  function init() {
    scan();
    if (typeof MutationObserver !== 'undefined') {
      new MutationObserver(scan).observe(document.documentElement, {
        childList: true,
        subtree: true,
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeHomeFeaturedCarousel = init;
}(window));
