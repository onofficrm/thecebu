/**
 * 홈(빌더) — 히어로 3열 높이: 검색·채팅·사이드바 중 가장 높은 열 기준으로 맞춤
 */
(function (global) {
  'use strict';

  var SYNC_CLASS = 'eottae-home-hero-grid--height-sync';
  var MIN_COL_H = 280;
  var resizeTimer = null;
  var resizeObserver = null;
  var syncing = false;

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

  function measureMaxColumnHeight(grid) {
    var cols = heroColumns(grid);
    var maxH = 0;
    var i;
    var h;

    for (i = 0; i < cols.length; i += 1) {
      if (!cols[i]) {
        continue;
      }
      h = Math.ceil(cols[i].getBoundingClientRect().height);
      if (h > maxH) {
        maxH = h;
      }
    }

    return maxH;
  }

  function applyHeroColumnHeights(grid, targetH) {
    var cols = heroColumns(grid);
    var i;
    for (i = 0; i < cols.length; i += 1) {
      if (!cols[i]) {
        continue;
      }
      cols[i].style.height = targetH + 'px';
      cols[i].style.maxHeight = targetH + 'px';
      cols[i].style.minHeight = targetH + 'px';
      cols[i].style.overflow = 'hidden';
    }

    grid.style.setProperty('--eottae-hero-col-h', targetH + 'px');
    grid.classList.add(SYNC_CLASS);
    grid.setAttribute('data-eottae-hero-height', String(targetH));
  }

  function observeHeroColumns(grid) {
    if (typeof ResizeObserver === 'undefined' || !grid) {
      return;
    }

    if (resizeObserver) {
      resizeObserver.disconnect();
      resizeObserver = null;
    }

    var cols = heroColumns(grid);
    if (!cols.length) {
      return;
    }

    resizeObserver = new ResizeObserver(function () {
      scheduleSync(120);
    });

    var i;
    for (i = 0; i < cols.length; i += 1) {
      if (cols[i]) {
        resizeObserver.observe(cols[i]);
      }
    }
  }

  function syncHeroColumnHeights() {
    if (syncing) {
      return false;
    }

    var grid = findHeroGrid();
    if (!grid || !grid.classList.contains('eottae-home-hero-grid--3col')) {
      return false;
    }

    if (global.innerWidth < 1024) {
      clearHeroColumnHeights(grid);
      if (resizeObserver) {
        resizeObserver.disconnect();
        resizeObserver = null;
      }
      return true;
    }

    var cols = heroColumns(grid);
    if (!cols[0] || !cols[1] || !cols[2]) {
      return false;
    }

    syncing = true;

    if (resizeObserver) {
      resizeObserver.disconnect();
      resizeObserver = null;
    }

    var prevH = parseInt(grid.getAttribute('data-eottae-hero-height') || '0', 10) || 0;
    clearHeroColumnHeights(grid);

    var targetH = measureMaxColumnHeight(grid);
    if (targetH < MIN_COL_H) {
      syncing = false;
      return false;
    }

    if (prevH > 0 && Math.abs(targetH - prevH) < 3) {
      targetH = prevH;
    }

    applyHeroColumnHeights(grid, targetH);
    syncing = false;
    observeHeroColumns(grid);
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
      scheduleSync(400);
      scheduleSync(1200);
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
        var grid = findHeroGrid();
        if (!grid || !grid.classList.contains('eottae-home-hero-grid--3col')) {
          return;
        }
        scheduleSync(100);
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
