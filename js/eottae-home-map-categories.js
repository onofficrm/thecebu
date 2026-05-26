/**
 * 홈(빌더) 지도 옆 카테고리 그리드 — 골프 버튼 추가
 * (렌트카 타일 제거 후 빈 칸 보완, React 빌드 수정 없이 동작)
 */
(function (global) {
  'use strict';

  var GOLF_LABEL = '골프';

  function cfg() {
    return global.__EOTTae_HOME_MAP_CATEGORIES__ || {};
  }

  function bbsBase() {
    return cfg().bbsUrl || '/bbs/board.php';
  }

  function shopTable() {
    return cfg().shopTable || 'shop';
  }

  function golfUrl() {
    var params = new URLSearchParams();
    params.set('bo_table', shopTable());
    params.set('sca', GOLF_LABEL);
    return bbsBase() + '?' + params.toString();
  }

  function normalizeText(node) {
    return (node && node.textContent ? node.textContent : '').replace(/\s+/g, '').trim();
  }

  function findCategoryGrid() {
    var root = document.getElementById('root');
    if (!root) {
      return null;
    }

    var grids = root.querySelectorAll(
      'div.grid.grid-cols-3, div.grid.grid-cols-4, div[class*="grid-cols-3"]'
    );
    var i;
    for (i = 0; i < grids.length; i += 1) {
      var grid = grids[i];
      var links = grid.querySelectorAll('a');
      var hasFood = false;
      var j;
      for (j = 0; j < links.length; j += 1) {
        if (normalizeText(links[j]) === '맛집') {
          hasFood = true;
          break;
        }
      }
      if (hasFood) {
        return grid;
      }
    }

    return null;
  }

  function hasGolfTile(grid) {
    var links = grid.querySelectorAll('a');
    var i;
    for (i = 0; i < links.length; i += 1) {
      if (normalizeText(links[i]) === GOLF_LABEL) {
        return true;
      }
    }
    return false;
  }

  function findTemplateLink(grid) {
    var preferred = ['카페', '투어', '병원', '맛집'];
    var links = grid.querySelectorAll('a');
    var p;
    var i;
    for (p = 0; p < preferred.length; p += 1) {
      for (i = 0; i < links.length; i += 1) {
        if (normalizeText(links[i]) === preferred[p]) {
          return links[i];
        }
      }
    }
    return links[0] || null;
  }

  function applyGolfTheme(tile) {
    var iconWrap = tile.querySelector('div.rounded-full');
    if (iconWrap) {
      iconWrap.className =
        'w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center transition-all duration-300 relative z-10 shadow-sm border border-white bg-emerald-50 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white';
      iconWrap.innerHTML =
        '<svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        + '<path d="M4 20h16"/>'
        + '<path d="M8 20V8l7-3.5v15.5"/>'
        + '<circle cx="17" cy="5" r="2"/>'
        + '</svg>';
    }

    var label = tile.querySelector('span');
    if (label) {
      label.textContent = GOLF_LABEL;
    }
  }

  function createGolfTile(template) {
    var tile;
    if (template) {
      tile = template.cloneNode(true);
    } else {
      tile = document.createElement('a');
      tile.className =
        'group relative flex flex-col items-center justify-center gap-1.5 bg-white border border-slate-100 hover:border-slate-200 rounded-2xl p-2.5 sm:p-3 transition-all shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] hover:shadow-md overflow-hidden aspect-square';
      tile.innerHTML =
        '<div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center relative z-10"></div>'
        + '<div class="text-center relative z-10 mt-1"><span class="block text-[11px] sm:text-xs font-black text-slate-800"></span></div>';
    }

    tile.setAttribute('href', golfUrl());
    tile.removeAttribute('data-discover');
    applyGolfTheme(tile);
    tile.setAttribute('data-eottae-map-category', 'golf');
    return tile;
  }

  function mountGolf() {
    var grid = findCategoryGrid();
    if (!grid || hasGolfTile(grid)) {
      return;
    }

    var template = findTemplateLink(grid);
    var golfTile = createGolfTile(template);
    grid.appendChild(golfTile);
  }

  function init() {
    var run = function () {
      mountGolf();
    };

    if (typeof global.eottaeHomeAfterReactReady === 'function') {
      global.eottaeHomeAfterReactReady(run);
      return;
    }

    global.setTimeout(run, 1500);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeHomeMapCategories = init;
}(typeof window !== 'undefined' ? window : this));
