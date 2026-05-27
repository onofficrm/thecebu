/**
 * 홈(빌더) — 히어로 3열 높이: 3열(로그인+이벤트 사이드바) 콘텐츠 높이 기준으로 1·2·3열 맞춤
 */
(function (global) {
  'use strict';

  var SYNC_CLASS = 'eottae-home-hero-grid--height-sync';
  var MEASURE_CLASS = 'eottae-home-hero-grid--measuring';
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
    return grid.querySelector('.home-hero-chat-column, #eottae-home-public-chat');
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
      cols[i].style.visibility = '';
      cols[i].style.position = '';
      cols[i].style.left = '';
      cols[i].style.width = '';
      cols[i].style.pointerEvents = '';
    }

    grid.classList.remove(SYNC_CLASS);
    grid.classList.remove(MEASURE_CLASS);
    grid.style.removeProperty('--eottae-hero-col-h');
    grid.style.removeProperty('grid-template-rows');
    grid.removeAttribute('data-eottae-hero-height');
    grid.removeAttribute('data-eottae-hero-height-source');
  }

  /** 1·2열이 그리드 행 높이에 영향 주지 않게 잠시 격리 */
  function isolateColumnsForMeasure(grid) {
    var main = findHeroMainColumn(grid);
    var chat = findHeroChatColumn(grid);
    var list = [main, chat];
    var i;
    var el;

    for (i = 0; i < list.length; i += 1) {
      el = list[i];
      if (!el) {
        continue;
      }
      el.style.visibility = 'hidden';
      el.style.position = 'absolute';
      el.style.left = '-9999px';
      el.style.width = '1px';
      el.style.height = 'auto';
      el.style.maxHeight = 'none';
      el.style.minHeight = '0';
      el.style.overflow = 'hidden';
      el.style.pointerEvents = 'none';
    }
  }

  function restoreIsolatedColumns(grid) {
    var cols = heroColumns(grid);
    var i;
    for (i = 0; i < cols.length; i += 1) {
      if (!cols[i]) {
        continue;
      }
      cols[i].style.visibility = '';
      cols[i].style.position = '';
      cols[i].style.left = '';
      cols[i].style.width = '';
      cols[i].style.pointerEvents = '';
    }
  }

  function elementContentHeight(el) {
    if (!el) {
      return 0;
    }

    return Math.max(
      Math.ceil(el.getBoundingClientRect().height),
      Math.ceil(el.scrollHeight || 0),
      Math.ceil(el.offsetHeight || 0)
    );
  }

  function measureChildBlockHeight(child) {
    if (!child) {
      return 0;
    }

    var cs = global.getComputedStyle(child);
    if (cs.display === 'none' || cs.visibility === 'hidden') {
      return 0;
    }

    var h = elementContentHeight(child);
    var stack = child.querySelector ? child.querySelector('.home-events-stack') : null;
    if (stack) {
      h = Math.max(h, elementContentHeight(stack));
    }

    return h;
  }

  /** 3열 자연 높이 = 자식 블록(이벤트 카드 포함) 실제 콘텐츠 높이 합 + gap */
  function measureSidebarNaturalHeight(sidebar) {
    if (!sidebar) {
      return 0;
    }

    var style = global.getComputedStyle(sidebar);
    var gap = parseFloat(style.rowGap || style.gap || '0') || 16;
    var padTop = parseFloat(style.paddingTop || '0') || 0;
    var padBottom = parseFloat(style.paddingBottom || '0') || 0;
    var total = padTop + padBottom;
    var children = sidebar.children;
    var visibleCount = 0;
    var i;
    var child;
    var h;

    for (i = 0; i < children.length; i += 1) {
      child = children[i];
      h = measureChildBlockHeight(child);
      if (h < 1) {
        continue;
      }
      total += h;
      visibleCount += 1;
    }

    if (visibleCount > 1) {
      total += gap * (visibleCount - 1);
    }

    total = Math.max(total, elementContentHeight(sidebar));

    return total;
  }

  function ensureSidebarMounted() {
    if (typeof global.mountEottaeHomeEventsBanner === 'function') {
      global.mountEottaeHomeEventsBanner();
    }
    if (typeof global.mountEottaeHomeHeroSidebar === 'function') {
      global.mountEottaeHomeHeroSidebar();
    }
  }

  function remeasureSidebarExpandedHeight(grid, targetH) {
    var sidebar = findHeroSidebarColumn(grid);
    if (!sidebar) {
      return targetH;
    }

    var prevHeight = sidebar.style.height;
    var prevMax = sidebar.style.maxHeight;
    var prevMin = sidebar.style.minHeight;

    sidebar.style.height = 'auto';
    sidebar.style.maxHeight = 'none';
    sidebar.style.minHeight = '0';

    var natural = measureSidebarNaturalHeight(sidebar);

    sidebar.style.height = prevHeight;
    sidebar.style.maxHeight = prevMax;
    sidebar.style.minHeight = prevMin;

    return Math.max(targetH, natural);
  }

  function measureSidebarColumnHeight(grid) {
    var sidebar = findHeroSidebarColumn(grid);
    if (!sidebar) {
      return 0;
    }

    grid.classList.add(MEASURE_CLASS);
    isolateColumnsForMeasure(grid);

    var h = measureSidebarNaturalHeight(sidebar);

    restoreIsolatedColumns(grid);
    grid.classList.remove(MEASURE_CLASS);

    return h;
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
      if (cols[i].classList.contains('home-hero-chat-column') || cols[i].id === 'eottae-home-public-chat') {
        cols[i].style.overflow = 'hidden';
      }
    }

    grid.style.setProperty('--eottae-hero-col-h', targetH + 'px');
    grid.style.gridTemplateRows = targetH + 'px';
    grid.classList.add(SYNC_CLASS);
    grid.setAttribute('data-eottae-hero-height', String(targetH));
    grid.setAttribute('data-eottae-hero-height-source', 'sidebar');
  }

  function observeHeroSidebar(grid) {
    if (typeof ResizeObserver === 'undefined' || !grid) {
      return;
    }

    if (resizeObserver) {
      resizeObserver.disconnect();
      resizeObserver = null;
    }

    var sidebar = findHeroSidebarColumn(grid);
    if (!sidebar) {
      return;
    }

    resizeObserver = new ResizeObserver(function () {
      scheduleSync(150);
    });

    resizeObserver.observe(sidebar);

    var events = sidebar.querySelector('.home-hero-sidebar-events');
    if (events) {
      resizeObserver.observe(events);
    }

    var stack = sidebar.querySelector('.home-events-stack');
    if (stack) {
      resizeObserver.observe(stack);
    }

    var loginBox = sidebar.querySelector('.community-login-box');
    if (loginBox) {
      resizeObserver.observe(loginBox);
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

    clearHeroColumnHeights(grid);
    ensureSidebarMounted();

    var targetH = measureSidebarColumnHeight(grid);
    if (targetH < MIN_COL_H) {
      syncing = false;
      scheduleSync(400);
      return false;
    }

    targetH = remeasureSidebarExpandedHeight(grid, targetH);
    applyHeroColumnHeights(grid, targetH);
    targetH = remeasureSidebarExpandedHeight(grid, targetH);
    if (parseInt(grid.getAttribute('data-eottae-hero-height') || '0', 10) !== targetH) {
      applyHeroColumnHeights(grid, targetH);
    }

    syncing = false;
    observeHeroSidebar(grid);

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
      scheduleSync(50);
      scheduleSync(300);
      scheduleSync(900);
      scheduleSync(1800);
      scheduleSync(3200);
    };

    if (typeof global.eottaeHomeAfterReactReady === 'function') {
      global.eottaeHomeAfterReactReady(run);
    } else {
      global.setTimeout(run, 1200);
    }

    global.addEventListener('resize', function () {
      scheduleSync(150);
    });

    global.addEventListener('load', function () {
      scheduleSync(200);
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
        scheduleSync(120);
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
