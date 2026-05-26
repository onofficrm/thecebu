/**
 * 세부어때 — 빌더 홈 히어로 검색 (React UI에 핸들러 연결)
 */
(function (global) {
  'use strict';

  var REGION_LABELS = {
    cebucity: '세부시티',
    mactan: '막탄',
    mandaue: '만다우에',
    lapulapu: '라푸라푸',
    ayala: '아얄라',
    itpark: 'IT Park',
  };

  var CATEGORY_BOARDS = {
    restaurant: 'food',
    massage: 'massage',
    tour: 'tour',
    rentcar: 'rentcar',
  };

  var CATEGORY_SCA = {
    restaurant: '맛집',
    massage: '마사지',
    tour: '투어',
    rentcar: '렌트카',
    cafe: '카페',
    golf: '골프',
  };

  function cfg() {
    return global.__EOTTae_HOME_SEARCH__ || {};
  }

  function bbsBase() {
    var c = cfg();
    return c.bbsUrl || '/bbs/board.php';
  }

  function defaultShopTable() {
    return cfg().shopTable || 'shop';
  }

  function buildSearchUrl(regionValue, categoryValue, keyword) {
    var params = new URLSearchParams();
    var boTable = defaultShopTable();
    var categoryKey = (categoryValue || '').trim();
    var regionKey = (regionValue || '').trim();
    var q = (keyword || '').trim();

    if (categoryKey && CATEGORY_BOARDS[categoryKey]) {
      boTable = CATEGORY_BOARDS[categoryKey];
    }

    params.set('bo_table', boTable);

    if (categoryKey && CATEGORY_SCA[categoryKey] && boTable === defaultShopTable()) {
      params.set('sca', CATEGORY_SCA[categoryKey]);
    }

    if (q !== '') {
      params.set('sfl', 'wr_subject||wr_content');
      params.set('stx', q);
    } else if (regionKey !== '') {
      params.set('sfl', 'wr_2');
      params.set('stx', REGION_LABELS[regionKey] || regionKey);
    }

    return bbsBase() + '?' + params.toString();
  }

  function buildNearUrl(lat, lng) {
    var params = new URLSearchParams();
    params.set('bo_table', defaultShopTable());
    params.set('sst', 'near');
    params.set('sod', 'asc');
    params.set('eottae_lat', String(lat));
    params.set('eottae_lng', String(lng));
    return bbsBase() + '?' + params.toString();
  }

  function findSearchRoot() {
    var h1s = document.querySelectorAll('h1');
    var i;
    for (i = 0; i < h1s.length; i++) {
      if (h1s[i].textContent.indexOf('세부어때') === -1) {
        continue;
      }
      var section = h1s[i].closest('section');
      if (!section) {
        continue;
      }
      var box = section.querySelector('.max-w-2xl');
      if (box) {
        return box;
      }
    }
    return null;
  }

  function bindSearch(root) {
    if (!root || root.getAttribute('data-eottae-search-bound') === '1') {
      return;
    }

    var selects = root.querySelectorAll('select');
    var regionSelect = selects[0] || null;
    var categorySelect = selects[1] || null;
    var keywordInput = root.querySelector('input[type="text"], input[type="search"]');
    var searchRow = root.querySelector('[class*="flex-col"][class*="sm:flex-row"]');
    var geoRow = root.querySelector('.border-t');
    var submitBtn = searchRow ? searchRow.querySelector('button') : null;
    var locateBtn = geoRow ? geoRow.querySelector('button') : null;

    if (!submitBtn && !keywordInput) {
      return;
    }

    root.setAttribute('data-eottae-search-bound', '1');

    function goSearch(ev) {
      if (ev) {
        ev.preventDefault();
      }
      var region = regionSelect ? regionSelect.value : '';
      var category = categorySelect ? categorySelect.value : '';
      var keyword = keywordInput ? keywordInput.value : '';
      global.location.href = buildSearchUrl(region, category, keyword);
    }

    if (submitBtn) {
      submitBtn.addEventListener('click', goSearch);
    }

    if (keywordInput) {
      keywordInput.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') {
          goSearch(ev);
        }
      });
    }

    if (locateBtn) {
      locateBtn.addEventListener('click', function (ev) {
        ev.preventDefault();
        if (!global.navigator || !global.navigator.geolocation) {
          alert('현재 브라우저에서는 위치 정보를 사용할 수 없습니다.');
          return;
        }
        locateBtn.disabled = true;
        global.navigator.geolocation.getCurrentPosition(
          function (pos) {
            global.location.href = buildNearUrl(pos.coords.latitude, pos.coords.longitude);
          },
          function () {
            alert('위치 정보를 가져오지 못했습니다. 브라우저 설정을 확인해 주세요.');
            locateBtn.disabled = false;
          },
          { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
        );
      });
    }
  }

  function init() {
    var root = findSearchRoot();
    if (root) {
      bindSearch(root);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.setTimeout(init, 400);
  global.setTimeout(init, 1200);

  global.EottaeHomeSearch = {
    buildSearchUrl: buildSearchUrl,
    buildNearUrl: buildNearUrl,
    init: init,
  };
})(window);
