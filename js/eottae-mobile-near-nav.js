/**
 * 모바일 하단 네비 — 내주변: 현재 위치 기준 1km 지도·목록
 */
(function (global) {
  'use strict';

  function shopListUrlFromNav(nav) {
    if (!nav) {
      return '';
    }
    var fromData = nav.getAttribute('data-eottae-shop-list-url');
    if (fromData) {
      return fromData;
    }
    var link = nav.querySelector('[data-eottae-mobile-near="1"]');
    if (link && link.getAttribute('href')) {
      return link.getAttribute('href');
    }
    var fallback = nav.querySelector('a[href*="bo_table=shop"], a[href*="bo_table%3Dshop"]');
    return fallback ? fallback.getAttribute('href') : '';
  }

  function isNearModeUrl(url) {
    try {
      var u = new URL(url, global.location.href);
      return (
        u.searchParams.get('sst') === 'near' &&
        u.searchParams.get('eottae_lat') !== null &&
        u.searchParams.get('eottae_lat') !== '' &&
        u.searchParams.get('eottae_lng') !== null &&
        u.searchParams.get('eottae_lng') !== ''
      );
    } catch (e) {
      return false;
    }
  }

  function buildNearUrl(baseHref, lat, lng) {
    var u = new URL(baseHref, global.location.href);
    u.searchParams.set('eottae_lat', String(lat));
    u.searchParams.set('eottae_lng', String(lng));
    u.searchParams.set('sst', 'near');
    u.searchParams.set('sod', 'asc');
    return u.toString();
  }

  function isSameCoords(url, lat, lng) {
    try {
      var u = new URL(url, global.location.href);
      var uLat = parseFloat(u.searchParams.get('eottae_lat'));
      var uLng = parseFloat(u.searchParams.get('eottae_lng'));
      if (!isFinite(uLat) || !isFinite(uLng)) {
        return false;
      }
      return Math.abs(uLat - lat) < 0.00005 && Math.abs(uLng - lng) < 0.00005;
    } catch (e) {
      return false;
    }
  }

  function scrollToShopMap() {
    var map = document.querySelector('[data-eottae-shop-map]');
    if (map && typeof map.scrollIntoView === 'function') {
      map.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function applyMapNearView(lat, lng) {
    if (typeof global.eottaeShopMapApplyNearView === 'function') {
      if (global.eottaeShopMapApplyNearView(lat, lng)) {
        scrollToShopMap();
        return true;
      }
    }
    return false;
  }

  function requestNearNavigation(listUrl) {
    if (!listUrl) {
      return;
    }

    if (!navigator.geolocation) {
      global.location.href = listUrl;
      return;
    }

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        var lat = pos.coords.latitude;
        var lng = pos.coords.longitude;
        var current = global.location.href;

        if (isNearModeUrl(current) && isSameCoords(current, lat, lng)) {
          if (applyMapNearView(lat, lng)) {
            return;
          }
        }

        global.location.href = buildNearUrl(listUrl, lat, lng);
      },
      function () {
        if (isNearModeUrl(global.location.href)) {
          scrollToShopMap();
          return;
        }
        global.alert('위치 권한이 필요합니다. 내주변 페이지에서 「현재 위치 기준으로 내 주변 찾기」를 이용해 주세요.');
        global.location.href = listUrl;
      },
      { enableHighAccuracy: true, timeout: 12000, maximumAge: 30000 }
    );
  }

  function isNearNavLink(link) {
    if (!link || link.tagName !== 'A') {
      return false;
    }
    if (link.getAttribute('data-eottae-mobile-near') === '1') {
      return true;
    }
    var href = link.getAttribute('href') || '';
    return (
      /bo_table=(?:shop|%3Dshop)/i.test(href) &&
      (link.textContent || '').replace(/\s+/g, '').indexOf('내주변') !== -1
    );
  }

  function bindNav(nav) {
    if (!nav || nav.getAttribute('data-eottae-near-nav-bound') === '1') {
      return;
    }
    nav.setAttribute('data-eottae-near-nav-bound', '1');

    nav.addEventListener(
      'click',
      function (event) {
        var link = event.target.closest && event.target.closest('a');
        if (!link || !nav.contains(link) || !isNearNavLink(link)) {
          return;
        }

        event.preventDefault();
        requestNearNavigation(shopListUrlFromNav(nav) || link.getAttribute('href'));
      },
      true
    );
  }

  function init() {
    var navs = document.querySelectorAll('.mobile-bottom-nav--global');
    var i;
    for (i = 0; i < navs.length; i += 1) {
      bindNav(navs[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.eottaeMobileNearNavInit = init;
})(typeof window !== 'undefined' ? window : this);
