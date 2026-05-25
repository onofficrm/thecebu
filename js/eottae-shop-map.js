/**
 * 세부어때 — 내주변·업체 상세 Google Maps
 */
(function (global) {
  'use strict';

  function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function parseNum(val, fallback) {
    var n = parseFloat(val);
    return isFinite(n) ? n : fallback;
  }

  function parseLocations(raw) {
    if (!raw) return [];
    try {
      var data = JSON.parse(raw);
      return Array.isArray(data) ? data : [];
    } catch (e) {
      return [];
    }
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
      lat: lat,
      lng: lng,
      thumbnail: raw.thumbnail || '',
      link: raw.link || raw.url || ''
    };
  }

  function markerIcon(loc) {
    if (!loc.thumbnail) return null;
    return {
      url: loc.thumbnail,
      scaledSize: new global.google.maps.Size(46, 46),
      anchor: new global.google.maps.Point(23, 46)
    };
  }

  function markerInfoHtml(loc) {
    var thumb = loc.thumbnail
      ? '<img class="marker-info-thumb" src="' + escapeHtml(loc.thumbnail) + '" alt="">'
      : '';
    var cat = loc.category
      ? '<p class="marker-info-category">' + escapeHtml(loc.category) + '</p>'
      : '';
    var link =
      loc.link && loc.link !== '#'
        ? '<a href="' +
          escapeHtml(loc.link) +
          '" class="marker-info-link">상세보기</a>'
        : '';
    return (
      '<div class="marker-info">' +
      thumb +
      '<h3 class="marker-info-title">' +
      escapeHtml(loc.name) +
      '</h3>' +
      cat +
      '<div class="marker-info-actions">' +
      link +
      '</div></div>'
    );
  }

  function ShopMapPanel(root) {
    this.root = root;
    this.canvas = root.querySelector('.shop-map-panel__map');
    this.locateBtn = root.querySelector('#shopMapLocateBtn');
    this.locations = parseLocations(root.getAttribute('data-shop-locations'))
      .map(normalizeLoc)
      .filter(Boolean);
    this.center = {
      lat: parseNum(root.dataset.mapLat, 10.3157),
      lng: parseNum(root.dataset.mapLng, 123.8854)
    };
    this.zoom = parseInt(root.dataset.mapZoom, 10) || 13;
    this.map = null;
    this.markers = [];
    this.markerById = {};
    this.infoWindow = null;
  }

  ShopMapPanel.prototype.init = function () {
    if (!this.canvas || !global.google || !global.google.maps) {
      return;
    }

    this.map = new global.google.maps.Map(this.canvas, {
      center: this.center,
      zoom: this.zoom,
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
    this.bindEvents();
    this.bindCardSync();
    this.root.classList.add('is-live');
  };

  ShopMapPanel.prototype.clearMarkers = function () {
    this.markers.forEach(function (m) {
      m.setMap(null);
    });
    this.markers = [];
  };

  ShopMapPanel.prototype.renderMarkers = function () {
    var self = this;
    if (!self.map) return;

    self.clearMarkers();
    self.markerById = {};
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
      if (loc.id !== '' && loc.id != null) {
        self.markerById[String(loc.id)] = marker;
      }
    });

    if (self.locations.length > 1) {
      var bounds = new global.google.maps.LatLngBounds();
      self.locations.forEach(function (loc) {
        bounds.extend({ lat: loc.lat, lng: loc.lng });
      });
      self.map.fitBounds(bounds);
      global.google.maps.event.addListenerOnce(self.map, 'bounds_changed', function () {
        var maxZoom = Math.max(self.zoom, 12);
        if (self.map.getZoom() > maxZoom) {
          self.map.setZoom(maxZoom);
        }
      });
    } else if (self.locations.length === 1) {
      self.map.setCenter({ lat: self.locations[0].lat, lng: self.locations[0].lng });
      self.map.setZoom(15);
    }
  };

  ShopMapPanel.prototype.focusLocationById = function (id) {
    if (!this.map || id == null || id === '') {
      return;
    }
    var key = String(id);
    var marker = this.markerById[key];
    var loc = null;
    for (var i = 0; i < this.locations.length; i++) {
      if (String(this.locations[i].id) === key) {
        loc = this.locations[i];
        break;
      }
    }
    if (!marker || !loc) {
      return;
    }
    this.map.panTo({ lat: loc.lat, lng: loc.lng });
    this.map.setZoom(Math.max(this.zoom, 15));
    global.google.maps.event.trigger(marker, 'click');
  };

  ShopMapPanel.prototype.bindCardSync = function () {
    var self = this;
    var cards = document.querySelectorAll('[data-shop-card]');
    cards.forEach(function (card) {
      card.addEventListener('mouseenter', function () {
        var id = card.getAttribute('data-wr-id');
        document.querySelectorAll('[data-shop-card].is-map-focus').forEach(function (el) {
          el.classList.remove('is-map-focus');
        });
        card.classList.add('is-map-focus');
        self.focusLocationById(id);
      });
      card.addEventListener('mouseleave', function () {
        card.classList.remove('is-map-focus');
      });
    });
  };

  ShopMapPanel.prototype.goToCurrentLocation = function () {
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
        self.map.setZoom(15);
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

  ShopMapPanel.prototype.bindEvents = function () {
    var self = this;
    if (self.locateBtn) {
      self.locateBtn.addEventListener('click', function () {
        self.goToCurrentLocation();
      });
    }
  };

  function ShopDetailMap(root) {
    this.root = root;
    this.canvas = root.querySelector('.shop-detail-map__map');
    this.lat = parseNum(root.dataset.mapLat, NaN);
    this.lng = parseNum(root.dataset.mapLng, NaN);
    this.name = root.dataset.mapName || '업체';
    this.thumbnail = root.dataset.mapThumbnail || '';
    this.zoom = parseInt(root.dataset.mapZoom, 10) || 15;
    this.map = null;
    this.marker = null;
  }

  ShopDetailMap.prototype.init = function () {
    if (!this.canvas || !isFinite(this.lat) || !isFinite(this.lng)) {
      return;
    }
    if (!global.google || !global.google.maps) {
      return;
    }

    var center = { lat: this.lat, lng: this.lng };
    this.map = new global.google.maps.Map(this.canvas, {
      center: center,
      zoom: this.zoom,
      zoomControl: true,
      zoomControlOptions: {
        position: global.google.maps.ControlPosition.RIGHT_CENTER
      },
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: true
    });
    this.marker = new global.google.maps.Marker({
      position: center,
      map: this.map,
      title: this.name,
      icon: markerIcon({ thumbnail: this.thumbnail })
    });
    this.root.classList.add('is-live');
  };

  var panels = [];

  function collectPanels() {
    panels = [];
    document.querySelectorAll('[data-eottae-shop-map]').forEach(function (el) {
      if (!el._eottaeShopMapBound) {
        el._eottaeShopMapBound = true;
        panels.push(new ShopMapPanel(el));
      }
    });
    document.querySelectorAll('[data-eottae-shop-detail-map]').forEach(function (el) {
      if (!el._eottaeShopDetailMapBound) {
        el._eottaeShopDetailMapBound = true;
        panels.push(new ShopDetailMap(el));
      }
    });
  }

  global.initEottaeShopMaps = function () {
    collectPanels();
    panels.forEach(function (panel) {
      panel.init();
    });
    global.document.dispatchEvent(new CustomEvent('eottae:shop-maps-ready'));
  };
})(typeof window !== 'undefined' ? window : this);
