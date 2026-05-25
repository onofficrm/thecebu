/**
 * 홈(빌더) — 오늘의 세부톡을 히어로 3열(우측)에 배치
 */
(function (global) {
  'use strict';

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

  function findSidebarColumn(grid) {
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

  function mount() {
    var feed = document.getElementById('eottae-home-talk-feed');
    var grid = findHeroGrid();
    if (!feed || feed.dataset.mounted === '1') {
      return;
    }

    var sidebar = findSidebarColumn(grid);
    if (sidebar) {
      feed.classList.add('talk-home-feed--sidebar');
      var talkPanel = sidebar.querySelector('[data-eottae-home-talk-sidebar]');
      var hiddenEvents = sidebar.querySelector('[data-eottae-hidden-events]');

      if (talkPanel && talkPanel.parentNode) {
        if (talkPanel.nextSibling) {
          talkPanel.parentNode.insertBefore(feed, talkPanel.nextSibling);
        } else {
          talkPanel.parentNode.appendChild(feed);
        }
      } else if (hiddenEvents && hiddenEvents.parentNode) {
        hiddenEvents.parentNode.insertBefore(feed, hiddenEvents);
      } else {
        sidebar.appendChild(feed);
      }

      feed.dataset.mounted = '1';
      return;
    }

    var root = document.getElementById('root');
    if (!root) {
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
