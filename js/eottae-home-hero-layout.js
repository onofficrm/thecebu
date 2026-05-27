/**
 * 홈(빌더) — 히어로 3열 높이: 3열(로그인+이벤트) 콘텐츠 높이 기준으로 1·2열 맞춤
 */
(function (global) {
  'use strict';

  var SYNC_CLASS = 'eottae-home-hero-grid--height-sync';
  var MIN_COL_H = 280;
  var resizeTimer = null;

  function findHeroGrid() {
    if (typeof global.findEottaeHeroGrid === 'function') {
      return global.findEottaeHeroGrid();
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
    if (typeof global.findEottaeHeroSidebarColumn === 'function') {
      return global.findEottaeHeroSidebarColumn();
    }

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

  function findHeroChatColumn(grid) {
    if (!grid) {
      return null;
    }
    return grid.querySelector('.home-hero-chat-column');
  }

  function heroColumns(grid) {
    return [
      findHeroMainColumn(grid),
      findHeroChatColumn(grid),
      findHeroSidebarColumn(grid),
    ];
  }

  function clearHeroColumnHeights(grid) {
    if (!grid) {
      return;
    }

    var cols = heroColumns(grid);
    var i;
    for (i = 0; i < cols.length; i += 1) {
      if (!cols[i]) {
        continue;
      }
      cols[i].style.height = '';
      cols[i].style.maxHeight = '';
      cols[i].style.minHeight = '';
      cols[i].style.overflow = '';
    }

    grid.classList.remove(SYNC_CLASS);
    grid.style.removeProperty('--eottae-hero-col-h');
    grid.removeAttribute('data-eottae-hero-height');
  }

  function syncHeroColumnHeights() {
    var grid = findHeroGrid();
    if (!grid || !grid.classList.contains('eottae-home-hero-grid--3col')) {
      return false;
    }

    if (global.innerWidth < 1024) {
      clearHeroColumnHeights(grid);
      return true;
    }

    var sidebar = findHeroSidebarColumn(grid);
    if (!sidebar) {
      return false;
    }

    clearHeroColumnHeights(grid);

    var targetH = Math.ceil(sidebar.getBoundingClientRect().height);
    if (targetH < MIN_COL_H) {
      return false;
    }

    var cols = heroColumns(grid);
    var i;
    for (i = 0; i < cols.length; i += 1) {
      if (!cols[i]) {
        continue;
      }
      cols[i].style.height = targetH + 'px';
      cols[i].style.maxHeight = targetH + 'px';
      cols[i].style.minHeight = targetH + 'px';
      if (cols[i] !== sidebar) {
        cols[i].style.overflow = 'hidden';
      }
    }

    grid.style.setProperty('--eottae-hero-col-h', targetH + 'px');
    grid.classList.add(SYNC_CLASS);
    grid.setAttribute('data-eottae-hero-height', String(targetH));

    if (typeof global.scheduleEottaeHomePublicChatScroll === 'function') {
      global.scheduleEottaeHomePublicChatScroll();
    }

    return true;
  }

  function scheduleSync(delayMs) {
    var wait = typeof delayMs === 'number' ? delayMs : 0;
    global.clearTimeout(resizeTimer);
    resizeTimer = global.setTimeout(function () {
      global.requestAnimationFrame(function () {
        syncHeroColumnHeights();
      });
    }, wait);
  }

  function init() {
    var run = function () {
      scheduleSync(80);
    };

    if (typeof global.eottaeHomeAfterReactReady === 'function') {
      global.eottaeHomeAfterReactReady(run);
    } else {
      global.setTimeout(run, 1500);
    }

    global.addEventListener('resize', function () {
      scheduleSync(120);
    });

    if (typeof MutationObserver === 'undefined') {
      return;
    }

    var root = document.getElementById('root');
    if (!root) {
      return;
    }

    var observerScheduled = false;
    var observer = new MutationObserver(function () {
      if (observerScheduled) {
        return;
      }
      observerScheduled = true;
      global.requestAnimationFrame(function () {
        observerScheduled = false;
        if (findHeroGrid() && findHeroGrid().classList.contains('eottae-home-hero-grid--3col')) {
          scheduleSync(100);
        }
      });
    });

    observer.observe(root, {
      childList: true,
      subtree: true,
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.syncEottaeHeroColumnHeights = syncHeroColumnHeights;
  global.scheduleEottaeHeroColumnHeights = scheduleSync;
}(window));
