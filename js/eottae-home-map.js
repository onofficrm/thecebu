/**
 * 세부어때 — 홈(빌더) 지도: 세부시티·라푸라푸 중심 + 줌 컨트롤
 */
(function (global) {
  'use strict';

  var DEFAULT_CENTER = { lat: 10.313, lng: 123.9174 };
  var DEFAULT_ZOOM = 12;

  function parseNum(val, fallback) {
    var n = parseFloat(val);
    return isFinite(n) ? n : fallback;
  }

  function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function getConfig() {
    var cfg = global.__EOTTae_HOME_MAP__ || {};
    return {
      lat: parseNum(cfg.lat, DEFAULT_CENTER.lat),
      lng: parseNum(cfg.lng, DEFAULT_CENTER.lng),
      zoom: parseInt(cfg.zoom, 10) || DEFAULT_ZOOM,
      locations: Array.isArray(cfg.locations) ? cfg.locations : []
    };
  }

  function normalizeLoc(raw) {
    if (!raw || typeof raw !== 'object') return null;
    var lat = parseNum(raw.lat, NaN);
    var lng = parseNum(raw.lng, NaN);
    if (!isFinite(lat) || !isFinite(lng)) return null;
    return {
      id: raw.id != null ? raw.id : raw.wr_id || '',
      name: raw.name || '업체',
      category: raw.category || '',
      region: raw.region || '',
      lat: lat,
      lng: lng,
      thumbnail: raw.thumbnail || '',
      link: raw.link || raw.url || ''
    };
  }

  function markerIcon(loc) {
    if (!loc.thumbnail || !global.google || !global.google.maps) return null;
    return {
      url: loc.thumbnail,
      scaledSize: new global.google.maps.Size(46, 46),
      anchor: new global.google.maps.Point(23, 46)
    };
  }

  function markerInfoBadgesHtml(loc) {
    var html = '';
    if (loc.category) {
      html +=
        '<span class="marker-info-badge marker-info-badge--cate">' +
        escapeHtml(loc.category) +
        '</span>';
    }
    if (loc.region) {
      html +=
        '<span class="marker-info-badge marker-info-badge--region">' +
        escapeHtml(loc.region) +
        '</span>';
    }
    return html;
  }

  function markerInfoHtml(loc) {
    var hasThumb = !!loc.thumbnail;
    var thumb = hasThumb
      ? '<div class="marker-info-thumb-wrap"><img class="marker-info-thumb" src="' +
        escapeHtml(loc.thumbnail) +
        '" alt=""></div>'
      : '';
    var badges = markerInfoBadgesHtml(loc);
    var link =
      loc.link && loc.link !== '#'
        ? '<a href="' + escapeHtml(loc.link) + '" class="marker-info-link">상세보기</a>'
        : '';
    return (
      '<div class="marker-info' +
      (hasThumb ? ' marker-info--thumb' : '') +
      '">' +
      thumb +
      '<div class="marker-info-body">' +
      '<div class="marker-info-head">' +
      '<h3 class="marker-info-title">' +
      escapeHtml(loc.name) +
      '</h3>' +
      badges +
      '</div>' +
      '<div class="marker-info-actions">' +
      link +
      '</div></div></div>'
    );
  }

  function HomeMapPanel(hostEl, cfg) {
    this.hostEl = hostEl;
    this.cfg = cfg;
    this.map = null;
    this.markers = [];
    this.infoWindow = null;
    this.userMarker = null;
    this.locations = cfg.locations.map(normalizeLoc).filter(Boolean);
  }

  HomeMapPanel.prototype.init = function () {
    if (!global.google || !global.google.maps) return false;

    var mapEl = document.createElement('div');
    mapEl.className = 'eottae-home-map__map';
    mapEl.setAttribute('role', 'application');
    mapEl.setAttribute('aria-label', '세부시티·라푸라푸 지도');
    this.hostEl.insertBefore(mapEl, this.hostEl.firstChild);

    this.map = new global.google.maps.Map(mapEl, {
      center: { lat: this.cfg.lat, lng: this.cfg.lng },
      zoom: this.cfg.zoom,
      zoomControl: true,
      zoomControlOptions: {
        position: global.google.maps.ControlPosition.RIGHT_CENTER
      },
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: true
    });

    this.infoWindow = new global.google.maps.InfoWindow();
    this.renderMarkers();
    this.bindLocateButton();
    this.hostEl.classList.add('eottae-home-map--live');

    return true;
  };

  HomeMapPanel.prototype.renderMarkers = function () {
    var self = this;
    if (!self.map) return;

    self.markers.forEach(function (m) {
      m.setMap(null);
    });
    self.markers = [];

    self.locations.forEach(function (loc) {
      var marker = new global.google.maps.Marker({
        position: { lat: loc.lat, lng: loc.lng },
        map: self.map,
        title: loc.name,
        icon: markerIcon(loc)
      });
      marker.addListener('click', function () {
        self.infoWindow.setContent(markerInfoHtml(loc));
        self.infoWindow.open(self.map, marker);
      });
      self.markers.push(marker);
    });
  };

  HomeMapPanel.prototype.goToCurrentLocation = function () {
    var self = this;
    if (!self.map) return;
    if (!navigator.geolocation) {
      alert('현재 위치를 사용할 수 없습니다.');
      return;
    }
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        var loc = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        self.map.setCenter(loc);
        self.map.setZoom(Math.max(self.cfg.zoom, 14));
        if (!self.userMarker) {
          self.userMarker = new global.google.maps.Marker({
            map: self.map,
            title: '내 위치',
            icon: {
              path: global.google.maps.SymbolPath.CIRCLE,
              scale: 8,
              fillColor: '#0ea5e9',
              fillOpacity: 1,
              strokeColor: '#ffffff',
              strokeWeight: 3
            }
          });
        }
        self.userMarker.setPosition(loc);
      },
      function () {
        alert('위치 권한이 필요합니다.');
      },
      { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
    );
  };

  HomeMapPanel.prototype.bindLocateButton = function () {
    var self = this;
    var locateBtn = self.hostEl.querySelector('[class*="bottom-4"][class*="right-4"]');
    if (!locateBtn || locateBtn.dataset.eottaeLocateBound) return;
    locateBtn.dataset.eottaeLocateBound = '1';
    locateBtn.setAttribute('type', 'button');
    locateBtn.setAttribute('title', '내 위치');
    locateBtn.addEventListener('click', function (e) {
      e.preventDefault();
      self.goToCurrentLocation();
    });
  };

  var upgraded = false;

  function findMapHost() {
    var iframe = document.querySelector('iframe[src*="google.com/maps"]');
    if (!iframe) return null;
    var host = iframe.parentElement;
    if (!host || host.dataset.eottaeHomeMapReady === '1') return null;
    return { host: host, iframe: iframe };
  }

  function tryUpgrade() {
    if (upgraded || !global.google || !global.google.maps) return false;

    var found = findMapHost();
    if (!found) return false;

    found.host.dataset.eottaeHomeMapReady = '1';
    if (found.iframe && found.iframe.parentNode) {
      found.iframe.parentNode.removeChild(found.iframe);
    }

    var panel = new HomeMapPanel(found.host, getConfig());
    if (panel.init()) {
      upgraded = true;
      return true;
    }

    return false;
  }

  function watchForMapHost() {
    if (tryUpgrade()) return;

    var observer = new MutationObserver(function () {
      if (tryUpgrade()) observer.disconnect();
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });

    var attempts = 0;
    var timer = setInterval(function () {
      attempts += 1;
      if (tryUpgrade() || attempts > 40) clearInterval(timer);
    }, 250);
  }

  global.initEottaeHomeMaps = function () {
    watchForMapHost();
  };
})(typeof window !== 'undefined' ? window : this);
