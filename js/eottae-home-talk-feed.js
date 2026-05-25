/**
 * 홈(빌더) — 오늘의 세부톡 섹션을 React 레이아웃 footer 앞에 배치
 */
(function (global) {
  'use strict';

  function mount() {
    var feed = document.getElementById('eottae-home-talk-feed');
    var root = document.getElementById('root');
    if (!feed || !root || feed.dataset.mounted === '1') {
      return;
    }

    var footer = root.querySelector('footer');
    if (footer && footer.parentNode) {
      footer.parentNode.insertBefore(feed, footer);
      feed.dataset.mounted = '1';
    }
  }

  function init() {
    mount();
    if (typeof MutationObserver === 'undefined') {
      return;
    }

    var root = document.getElementById('root');
    if (!root) {
      return;
    }

    new MutationObserver(mount).observe(root, {
      childList: true,
      subtree: true,
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeHomeTalkFeed = init;
}(window));
