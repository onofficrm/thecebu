/**
 * 홈(빌더) — 히어로 3열 높이 (고정 규칙)
 *
 * 높이 기준: 3열 사이드바 콘텐츠만 (비회원 로그인 카드 | 회원 MY 카드) + 이벤트 카드
 * 1·2열은 이 높이에 맞추고, 채팅 메시지·입력창(로그인/비로그인)은 2열 내부에서만 스크롤/배치
 */
(function (global) {
  'use strict';

  var LAYOUT_VERSION = 'sidebar-v2';
  var SYNC_CLASS = 'eottae-home-hero-grid--height-sync';
  var MEASURE_CLASS = 'eottae-home-hero-grid--measuring';
  var MIN_COL_H = 280;
  var resizeTimer = null;
  var resizeObserver = null;
  var sidebarMutationObserver = null;
  var rootMutationObserver = null;
  var syncing = false;
  var lastSidebarFingerprint = '';
  var heroLayoutStable = false;
  var mobileLayoutReleased = false;
  var userScrolling = false;
  var scrollIdleTimer = null;

  function isDesktopHeroLayout() {
    return global.innerWidth >= 1024;
  }

  function markUserScrolling() {
    userScrolling = true;
    global.clearTimeout(scrollIdleTimer);
    scrollIdleTimer = global.setTimeout(function () {
      userScrolling = false;
    }, 180);
  }

  function releaseDesktopHeroSyncOnMobile(grid) {
    if (!grid) {
      return;
    }

    if (
      grid.classList.contains(SYNC_CLASS)
      || grid.getAttribute('data-eottae-hero-height')
      || grid.style.getPropertyValue('--eottae-hero-col-h')
    ) {
      clearHeroColumnHeights(grid);
    }

    mobileLayoutReleased = true;
    heroLayoutStable = true;
    lastSidebarFingerprint = '';
  }

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

  function isVisibleBlock(el) {
    if (!el) {
      return false;
    }

    var cs = global.getComputedStyle(el);
    if (cs.display === 'none' || cs.visibility === 'hidden') {
      return false;
    }

    if (parseFloat(cs.opacity || '1') < 0.01) {
      return false;
    }

    if (el.getAttribute('aria-hidden') === 'true') {
      return false;
    }

    return true;
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
    grid.removeAttribute('data-eottae-hero-sidebar-mode');
    grid.removeAttribute('data-eottae-hero-layout-version');
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
    if (!isVisibleBlock(child)) {
      return 0;
    }

    var h = elementContentHeight(child);
    var stack = child.querySelector ? child.querySelector('.home-events-stack') : null;
    if (stack) {
      h = Math.max(h, elementContentHeight(stack));
    }

    var loginBox = child.querySelector ? child.querySelector('.community-login-box') : null;
    if (loginBox) {
      h = Math.max(h, elementContentHeight(loginBox));
    }

    return h;
  }

  function getSidebarAuthMode(sidebar) {
    if (!sidebar) {
      return 'unknown';
    }

    if (sidebar.querySelector('.community-login-box--guest')) {
      return 'guest';
    }

    if (sidebar.querySelector('.community-login-box:not(.community-login-box--guest)')) {
      return 'member';
    }

    if (sidebar.querySelector('.community-login-box')) {
      return 'member';
    }

    return 'unknown';
  }

  function sidebarFingerprint(sidebar) {
    if (!sidebar) {
      return '';
    }

    var mode = getSidebarAuthMode(sidebar);
    var h = measureSidebarNaturalHeight(sidebar);
    return mode + ':' + h;
  }

  /** 3열 자연 높이 = 자식 블록(로그인/MY + 이벤트) 실제 콘텐츠 높이 합 + gap */
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
      if (!isVisibleBlock(child)) {
        continue;
      }
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
    var sidebar = findHeroSidebarColumn(grid);
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
    grid.setAttribute('data-eottae-hero-layout-version', LAYOUT_VERSION);
    if (sidebar) {
      grid.setAttribute('data-eottae-hero-sidebar-mode', getSidebarAuthMode(sidebar));
    }
  }

  function observeSidebarTargets(grid) {
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
      heroLayoutStable = false;
      scheduleSync(120);
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

  function observeSidebarMutations(grid) {
    if (typeof MutationObserver === 'undefined' || !grid) {
      return;
    }

    var sidebar = findHeroSidebarColumn(grid);
    if (!sidebar) {
      return;
    }

    if (sidebarMutationObserver) {
      sidebarMutationObserver.disconnect();
      sidebarMutationObserver = null;
    }

    sidebarMutationObserver = new MutationObserver(function () {
      var fp = sidebarFingerprint(sidebar);
      if (fp !== lastSidebarFingerprint) {
        heroLayoutStable = false;
        scheduleSync(80);
        scheduleSync(350);
      }
    });

    sidebarMutationObserver.observe(sidebar, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['class', 'style', 'hidden'],
    });
  }

  function syncHeroColumnHeights() {
    if (syncing) {
      return false;
    }

    var grid = findHeroGrid();
    if (!grid || !grid.classList.contains('eottae-home-hero-grid--3col')) {
      return false;
    }

    if (!isDesktopHeroLayout()) {
      if (resizeObserver) {
        resizeObserver.disconnect();
        resizeObserver = null;
      }
      if (sidebarMutationObserver) {
        sidebarMutationObserver.disconnect();
        sidebarMutationObserver = null;
      }
      if (!mobileLayoutReleased) {
        releaseDesktopHeroSyncOnMobile(grid);
      }
      return true;
    }

    mobileLayoutReleased = false;

    if (userScrolling) {
      scheduleSync(220);
      return false;
    }

    var cols = heroColumns(grid);
    if (!cols[0] || !cols[1] || !cols[2]) {
      return false;
    }

    var sidebar = findHeroSidebarColumn(grid);
    if (canSkipStableSync(grid, sidebar)) {
      return true;
    }

    syncing = true;

    if (resizeObserver) {
      resizeObserver.disconnect();
      resizeObserver = null;
    }

    clearHeroColumnHeights(grid);
    ensureSidebarMounted();

    sidebar = findHeroSidebarColumn(grid);
    if (!sidebar) {
      syncing = false;
      return false;
    }

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

    sidebar = findHeroSidebarColumn(grid);
    markHeroLayoutStable(grid, sidebar);

    syncing = false;
    observeSidebarTargets(grid);
    observeSidebarMutations(grid);

    if (typeof global.scheduleEottaeHomePublicChatScroll === 'function') {
      global.scheduleEottaeHomePublicChatScroll();
    }

    return true;
  }

  function canSkipStableSync(grid, sidebar) {
    if (!grid || !sidebar || !heroLayoutStable) {
      return false;
    }

    if (!grid.classList.contains(SYNC_CLASS)) {
      return false;
    }

    var fp = sidebarFingerprint(sidebar);
    if (!fp || fp !== lastSidebarFingerprint) {
      return false;
    }

    var currentH = parseInt(grid.getAttribute('data-eottae-hero-height') || '0', 10);
    return currentH >= MIN_COL_H;
  }

  function markHeroLayoutStable(grid, sidebar) {
    heroLayoutStable = true;
    lastSidebarFingerprint = sidebarFingerprint(sidebar);

    if (rootMutationObserver) {
      rootMutationObserver.disconnect();
      rootMutationObserver = null;
    }
  }

  function scheduleSync(delayMs) {
    if (!isDesktopHeroLayout()) {
      return;
    }

    var wait = typeof delayMs === 'number' ? delayMs : 0;
    global.clearTimeout(resizeTimer);
    resizeTimer = global.setTimeout(function () {
      global.requestAnimationFrame(function () {
        syncHeroColumnHeights();
      });
    }, wait);
  }

  function runInitialSyncPasses() {
    if (!isDesktopHeroLayout()) {
      syncHeroColumnHeights();
      return;
    }

    scheduleSync(0);
    scheduleSync(80);
    scheduleSync(280);
    scheduleSync(700);
    scheduleSync(1400);
  }

  function init() {
    global.addEventListener('scroll', markUserScrolling, { passive: true });

    var run = function () {
      runInitialSyncPasses();
    };

    if (typeof global.eottaeHomeAfterReactReady === 'function') {
      global.eottaeHomeAfterReactReady(run);
    } else {
      global.setTimeout(run, 1200);
    }

    if (global.document && global.document.fonts && typeof global.document.fonts.ready === 'object') {
      global.document.fonts.ready.then(function () {
        scheduleSync(100);
        scheduleSync(500);
      }).catch(function () {});
    }

    global.addEventListener('resize', function () {
      if (!isDesktopHeroLayout()) {
        mobileLayoutReleased = false;
        syncHeroColumnHeights();
        return;
      }

      mobileLayoutReleased = false;
      heroLayoutStable = false;
      scheduleSync(150);
    });

    global.addEventListener('load', function () {
      scheduleSync(200);
      scheduleSync(1200);
    });

    global.addEventListener('pageshow', function (event) {
      if (event && event.persisted) {
        heroLayoutStable = false;
        scheduleSync(50);
        scheduleSync(400);
      }
    });

    if (typeof MutationObserver === 'undefined') {
      return;
    }

    var root = document.getElementById('root');
    if (!root) {
      return;
    }

    var observerScheduled = false;
    rootMutationObserver = new MutationObserver(function () {
      if (heroLayoutStable || observerScheduled) {
        return;
      }
      observerScheduled = true;
      global.requestAnimationFrame(function () {
        observerScheduled = false;
        if (heroLayoutStable) {
          return;
        }
        var grid = findHeroGrid();
        if (!grid || !grid.classList.contains('eottae-home-hero-grid--3col')) {
          return;
        }
        if (grid.classList.contains(SYNC_CLASS)) {
          return;
        }
        scheduleSync(120);
      });
    });

    rootMutationObserver.observe(root, {
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
  global.EOTTae_HERO_LAYOUT_VERSION = LAYOUT_VERSION;
}(window));
