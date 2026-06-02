/**
 * 세부어때 — 내주변·업체 상세 Google Maps
 */
(function (global) {
  'use strict';

  var MARKER_SIZE = 46;

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
      region: raw.region || '',
      lat: lat,
      lng: lng,
      thumbnail: raw.thumbnail || '',
      link: raw.link || raw.url || '',
      rating: raw.rating != null ? raw.rating : 0,
      review_count: raw.review_count != null ? raw.review_count : 0
    };
  }

  function resolveAbsoluteUrl(url) {
    if (!url) {
      return '';
    }
    var value = String(url);
    if (/^https?:\/\//i.test(value)) {
      return value;
    }
    if (value.indexOf('//') === 0) {
      return global.location.protocol + value;
    }
    if (value.charAt(0) === '/') {
      return global.location.origin + value;
    }
    return global.location.origin + '/' + value;
  }

  function cardThumbnailById(id) {
    if (id == null || id === '') return '';
    var card = document.querySelector('[data-shop-card][data-wr-id="' + String(id).replace(/"/g, '\\"') + '"]');
    if (!card) return '';
    var img = card.querySelector('.shop-list-card__thumb');
    if (!img || !img.getAttribute) return '';

    return img.currentSrc || img.getAttribute('src') || '';
  }

  function markerIconConfig(url, size) {
    if (!url || !global.google || !global.google.maps) {
      return null;
    }
    return {
      url: url,
      scaledSize: new global.google.maps.Size(size, size),
      anchor: new global.google.maps.Point(size / 2, size)
    };
  }

  function markerIconFromImage(thumbnail, size, callback) {
    if (!thumbnail) {
      callback(null);
      return;
    }

    var img = new Image();
    img.onload = function () {
      var iconUrl = thumbnail;
      try {
        var canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        var ctx = canvas.getContext('2d');
        if (ctx) {
          ctx.drawImage(img, 0, 0, size, size);
          iconUrl = canvas.toDataURL('image/png');
        }
      } catch (e) {}
      callback(markerIconConfig(iconUrl, size));
    };
    img.onerror = function () {
      callback(markerIconConfig(thumbnail, size));
    };
    img.src = thumbnail;
  }

  function createMapMarker(loc, map, panel, callback) {
    var position = { lat: loc.lat, lng: loc.lng };
    var markerOptions = {
      position: position,
      map: map,
      title: loc.name
    };

    function finish(icon) {
      if (icon) {
        markerOptions.icon = icon;
      }
      var marker = new global.google.maps.Marker(markerOptions);
      marker.addListener('click', function () {
        openMarkerInfoWindow(panel.infoWindow, map, marker, loc);
      });
      callback(marker);
    }

    if (!loc.thumbnail) {
      finish(null);
      return;
    }

    markerIconFromImage(loc.thumbnail, MARKER_SIZE, finish);
  }

  function markerInfoReviewHref(loc) {
    var base = loc.link && loc.link !== '#' ? String(loc.link) : '';
    if (!base) {
      return '#shop-reviews';
    }
    return base.indexOf('#') >= 0 ? base : base + '#shop-reviews';
  }

  function markerInfoRatingLabel(rating) {
    var n = parseFloat(rating);
    if (!isFinite(n) || n < 0) {
      n = 0;
    }
    return n.toFixed(1);
  }

  function markerInfoReviewCountLabel(count) {
    var n = parseInt(count, 10);
    if (!isFinite(n) || n < 0) {
      n = 0;
    }
    return String(n);
  }

  function markerInfoMetaHtml(loc) {
    var html = '<div class="marker-info-meta">';
    if (loc.category) {
      html +=
        '<span class="marker-info-badge marker-info-badge--cate">' +
        escapeHtml(loc.category) +
        '</span>';
    }
    var reviewHref = markerInfoReviewHref(loc);
    html +=
      '<a href="' +
      escapeHtml(reviewHref) +
      '" class="marker-info-chip marker-info-chip--rating">★ ' +
      markerInfoRatingLabel(loc.rating) +
      '</a>';
    html +=
      '<a href="' +
      escapeHtml(reviewHref) +
      '" class="marker-info-chip marker-info-chip--reviews">리뷰 ' +
      markerInfoReviewCountLabel(loc.review_count) +
      '</a>';
    html += '</div>';
    return html;
  }

  function markerDirectionsUrl(loc) {
    var lat = parseNum(loc.lat, NaN);
    var lng = parseNum(loc.lng, NaN);
    if (isFinite(lat) && isFinite(lng)) {
      return (
        'https://www.google.com/maps/dir/?api=1&destination=' +
        encodeURIComponent(lat + ',' + lng)
      );
    }
    return '#';
  }

  function markerInfoActionsHtml(loc) {
    var detail =
      loc.link && loc.link !== '#'
        ? '<a href="' +
          escapeHtml(loc.link) +
          '" class="marker-info-link marker-info-link--btn marker-info-link--detail">상세정보 보기</a>'
        : '';
    var dirUrl = markerDirectionsUrl(loc);
    var directions =
      dirUrl !== '#'
        ? '<a href="' +
          escapeHtml(dirUrl) +
          '" class="marker-info-link marker-info-link--btn marker-info-link--directions" target="_blank" rel="noopener noreferrer">길찾기</a>'
        : '';
    if (!detail && !directions) {
      return '';
    }
    return '<div class="marker-info-actions">' + detail + directions + '</div>';
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
    var actions = markerInfoActionsHtml(loc);
    if (hasThumb) {
      return (
        '<div class="marker-info marker-info--thumb marker-info--compact">' +
        '<div class="marker-info-thumb-wrap"><img class="marker-info-thumb" src="' +
        escapeHtml(loc.thumbnail) +
        '" alt="" draggable="false" decoding="async"></div>' +
        '<div class="marker-info-body">' +
        '<div class="marker-info-head">' +
        '<h3 class="marker-info-title">' +
        escapeHtml(loc.name) +
        '</h3>' +
        markerInfoMetaHtml(loc) +
        '</div>' +
        actions +
        '</div></div>'
      );
    }
    var badges = markerInfoBadgesHtml(loc);
    return (
      '<div class="marker-info">' +
      '<div class="marker-info-body">' +
      '<div class="marker-info-head">' +
      '<h3 class="marker-info-title">' +
      escapeHtml(loc.name) +
      '</h3>' +
      badges +
      '</div>' +
      actions +
      '</div></div>'
    );
  }

  function markerInfoWindowMaxWidth() {
    if (global.matchMedia && global.matchMedia('(max-width: 767px)').matches) {
      return 300;
    }
    return 260;
  }

  function bindMarkerInfoWindowThumb(root) {
    var thumb = root.querySelector('.marker-info-thumb');
    if (!thumb) {
      return;
    }
    thumb.setAttribute('draggable', 'false');
    thumb.addEventListener(
      'click',
      function (e) {
        e.preventDefault();
        e.stopPropagation();
      },
      { passive: false }
    );
  }

  function openMarkerInfoWindow(infoWindow, map, marker, loc) {
    infoWindow.setOptions({ maxWidth: markerInfoWindowMaxWidth() });
    infoWindow.setContent(markerInfoHtml(loc));
    infoWindow.open(map, marker);
    global.google.maps.event.addListenerOnce(infoWindow, 'domready', function () {
      var root = document.querySelector('.gm-style-iw .marker-info--compact');
      if (!root) {
        root = document.querySelector('.gm-style-iw .marker-info');
      }
      if (!root) {
        return;
      }
      bindMarkerInfoWindowThumb(root);
      var iw = root.closest('.gm-style-iw');
      if (!iw) {
        return;
      }
      var header = iw.querySelector('.gm-style-iw-ch');
      if (header) {
        header.style.display = 'none';
        header.style.height = '0';
      }
      var contentPane = iw.querySelector('.gm-style-iw-d');
      if (contentPane) {
        contentPane.style.overflow = 'visible';
        contentPane.style.height = 'auto';
        contentPane.style.maxHeight = 'none';
      }
      var position = marker.getPosition && marker.getPosition();
      if (position) {
        global.requestAnimationFrame(function () {
          infoWindow.setPosition(position);
        });
      }
    });
  }

  function listFiltersFromUrl() {
    var u = new URL(global.location.href);
    var keys = ['sca', 'sfl', 'stx', 'sst', 'sod', 'eottae_lat', 'eottae_lng'];
    var params = new URLSearchParams();
    keys.forEach(function (key) {
      var val = u.searchParams.get(key);
      if (val !== null && val !== '') {
        params.set(key, val);
      }
    });
    return params;
  }

  function haversineKm(lat1, lng1, lat2, lng2) {
    var r = 6371;
    var dLat = ((lat2 - lat1) * Math.PI) / 180;
    var dLng = ((lng2 - lng1) * Math.PI) / 180;
    var a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos((lat1 * Math.PI) / 180) *
        Math.cos((lat2 * Math.PI) / 180) *
        Math.sin(dLng / 2) *
        Math.sin(dLng / 2);
    return r * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }

  function isNearModeFromUrl() {
    var u = new URL(global.location.href);
    return (
      u.searchParams.get('sst') === 'near' &&
      u.searchParams.get('eottae_lat') !== null &&
      u.searchParams.get('eottae_lat') !== '' &&
      u.searchParams.get('eottae_lng') !== null &&
      u.searchParams.get('eottae_lng') !== ''
    );
  }

  function locationErrorMessage(error) {
    if (!global.isSecureContext && global.location.protocol !== 'https:' && global.location.hostname !== 'localhost') {
      return '현재 위치 검색은 HTTPS 보안 연결에서만 사용할 수 있습니다.';
    }
    if (error && error.code === 1) {
      return '브라우저 위치 권한이 차단되었습니다. 주소창의 위치 권한을 허용한 뒤 다시 시도해 주세요.';
    }
    if (error && error.code === 2) {
      return '현재 위치를 확인하지 못했습니다. Wi-Fi/GPS를 켠 뒤 다시 시도해 주세요.';
    }
    if (error && error.code === 3) {
      return '현재 위치 확인 시간이 초과되었습니다. 잠시 후 다시 시도해 주세요.';
    }
    return '현재 위치를 확인하지 못했습니다. 브라우저 위치 권한을 확인해 주세요.';
  }

  function ShopMapPanel(root) {
    this.root = root;
    this.canvas = root.querySelector('.shop-map-panel__map');
    this.locateBtn = root.querySelector('#shopMapLocateBtn');
    this.statusEl = document.querySelector('[data-shop-infinite-status]');
    this.markersApi = root.getAttribute('data-map-markers-api') || '';
    this.boTable = root.getAttribute('data-map-bo-table') || 'shop';
    this.locations = parseLocations(root.getAttribute('data-shop-locations'))
      .map(normalizeLoc)
      .filter(Boolean);
    this.center = {
      lat: parseNum(root.dataset.mapLat, 10.3157),
      lng: parseNum(root.dataset.mapLng, 123.8854)
    };
    this.zoom = parseInt(root.dataset.mapZoom, 10) || 13;
    this.nearRadiusKm = parseNum(root.dataset.mapNearRadiusKm, 1);
    if (this.nearRadiusKm <= 0) {
      this.nearRadiusKm = 1;
    }
    this.map = null;
    this.markers = [];
    this.markerById = {};
    this.infoWindow = null;
    this.radiusCircle = null;
    this.userMarker = null;
  }

  ShopMapPanel.prototype.setStatus = function (message, type) {
    if (!this.statusEl) {
      return;
    }
    this.statusEl.hidden = !message;
    this.statusEl.textContent = message || '';
    this.statusEl.classList.toggle('is-error', type === 'error');
    this.statusEl.classList.toggle('is-success', type === 'success');
  };

  ShopMapPanel.prototype.isNearRadiusMode = function () {
    return this.root.dataset.mapNearActive === '1' || isNearModeFromUrl();
  };

  ShopMapPanel.prototype.getNearRadiusKm = function () {
    return this.nearRadiusKm > 0 ? this.nearRadiusKm : 1;
  };

  ShopMapPanel.prototype.getNearCenter = function () {
    if (this.root.dataset.mapUserLat && this.root.dataset.mapUserLng) {
      return {
        lat: parseNum(this.root.dataset.mapUserLat, NaN),
        lng: parseNum(this.root.dataset.mapUserLng, NaN)
      };
    }
    var u = new URL(global.location.href);
    var lat = parseNum(u.searchParams.get('eottae_lat'), NaN);
    var lng = parseNum(u.searchParams.get('eottae_lng'), NaN);
    if (isFinite(lat) && isFinite(lng)) {
      return { lat: lat, lng: lng };
    }
    return null;
  };

  ShopMapPanel.prototype.filterLocationsWithinRadius = function (center, radiusKm) {
    if (!center || !Array.isArray(this.locations)) {
      return;
    }
    this.locations = this.locations.filter(function (loc) {
      return haversineKm(center.lat, center.lng, loc.lat, loc.lng) <= radiusKm;
    });
  };

  ShopMapPanel.prototype.clearRadiusOverlay = function () {
    if (this.radiusCircle) {
      this.radiusCircle.setMap(null);
      this.radiusCircle = null;
    }
  };

  ShopMapPanel.prototype.setUserMarkerAt = function (center) {
    if (!this.map || !center) {
      return;
    }
    if (!this.userMarker) {
      this.userMarker = new global.google.maps.Marker({
        map: this.map,
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
    this.userMarker.setPosition(center);
  };

  ShopMapPanel.prototype.applyNearRadiusView = function (center) {
    if (!this.map) {
      return;
    }
    center = center || this.getNearCenter();
    if (!center || !isFinite(center.lat) || !isFinite(center.lng)) {
      return;
    }

    var radiusKm = this.getNearRadiusKm();
    var radiusM = radiusKm * 1000;

    this.clearRadiusOverlay();
    this.radiusCircle = new global.google.maps.Circle({
      strokeColor: '#0ea5e9',
      strokeOpacity: 0.85,
      strokeWeight: 2,
      fillColor: '#0ea5e9',
      fillOpacity: 0.08,
      map: this.map,
      center: center,
      radius: radiusM
    });

    var bounds = this.radiusCircle.getBounds();
    if (bounds) {
      this.map.fitBounds(bounds);
    } else {
      this.map.setCenter(center);
      this.map.setZoom(15);
    }

    this.setUserMarkerAt(center);
  };

  ShopMapPanel.prototype.fetchAllMarkers = function () {
    var self = this;
    if (!self.markersApi) {
      return Promise.resolve();
    }

    var params = listFiltersFromUrl();
    params.set('action', 'map_markers');
    params.set('bo_table', self.boTable);

    return fetch(self.markersApi + '?' + params.toString(), { credentials: 'same-origin' })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (!data || !data.success || !Array.isArray(data.locations)) {
          return;
        }
        self.locations = data.locations.map(normalizeLoc).filter(Boolean);
      })
      .catch(function () {});
  };

  ShopMapPanel.prototype.applyCardThumbnailFallbacks = function () {
    this.locations.forEach(function (loc) {
      var cardThumbnail = cardThumbnailById(loc.id);
      if (cardThumbnail) {
        loc.cardThumbnail = resolveAbsoluteUrl(cardThumbnail);
        loc.thumbnail = loc.cardThumbnail;
      } else if (loc.thumbnail) {
        loc.thumbnail = resolveAbsoluteUrl(loc.thumbnail);
      }
    });
  };

  ShopMapPanel.prototype.init = function () {
    var self = this;
    if (!self.canvas || !global.google || !global.google.maps) {
      return;
    }

    self.fetchAllMarkers().then(function () {
      self.map = new global.google.maps.Map(self.canvas, {
        center: self.center,
        zoom: self.zoom,
        zoomControl: true,
        zoomControlOptions: {
          position: global.google.maps.ControlPosition.RIGHT_CENTER
        },
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true
      });
      self.infoWindow = new global.google.maps.InfoWindow({ maxWidth: markerInfoWindowMaxWidth() });
      self.applyCardThumbnailFallbacks();
      self.renderMarkers();
      self.bindCardThumbRefresh();
      self.bindEvents();
      self.bindCardSync();
      self.root.classList.add('is-live');

      global.document.addEventListener('eottae:shop-list-appended', function () {
        self.applyCardThumbnailFallbacks();
        self.bindCardSync();
      });
    });
  };

  ShopMapPanel.prototype.clearMarkers = function () {
    this.markers.forEach(function (m) {
      m.setMap(null);
    });
    this.markers = [];
  };

  ShopMapPanel.prototype.fitMapToLocations = function () {
    if (!this.map || !this.locations.length) {
      return;
    }

    if (this.locations.length > 1) {
      var bounds = new global.google.maps.LatLngBounds();
      this.locations.forEach(function (loc) {
        bounds.extend({ lat: loc.lat, lng: loc.lng });
      });
      this.map.fitBounds(bounds);
      global.google.maps.event.addListenerOnce(this.map, 'bounds_changed', function () {
        var maxZoom = Math.max(this.zoom, 12);
        if (this.map.getZoom() > maxZoom) {
          this.map.setZoom(maxZoom);
        }
      }.bind(this));
    } else {
      this.map.setCenter({ lat: this.locations[0].lat, lng: this.locations[0].lng });
      this.map.setZoom(15);
    }
  };

  ShopMapPanel.prototype.renderMarkers = function () {
    var self = this;
    if (!self.map) return;

    self.clearMarkers();
    self.markerById = {};

    if (self.isNearRadiusMode()) {
      var nearCenter = self.getNearCenter();
      if (nearCenter) {
        self.filterLocationsWithinRadius(nearCenter, self.getNearRadiusKm());
      }
    }

    if (!self.locations.length) {
      if (self.isNearRadiusMode()) {
        self.applyNearRadiusView();
      }
      return;
    }

    var pending = self.locations.length;
    var completed = 0;

    self.locations.forEach(function (loc) {
      createMapMarker(loc, self.map, self, function (marker) {
        self.markers.push(marker);
        if (loc.id !== '' && loc.id != null) {
          self.markerById[String(loc.id)] = marker;
        }
        completed += 1;
        if (completed >= pending) {
          if (self.isNearRadiusMode()) {
            self.applyNearRadiusView();
          } else {
            self.fitMapToLocations();
          }
        }
      });
    });
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
    if (self._cardSyncBound) {
      return;
    }
    self._cardSyncBound = true;

    var listRoot = document.querySelector('[data-shop-infinite-list]') || document;
    listRoot.addEventListener('mouseover', function (e) {
      var card = e.target.closest && e.target.closest('[data-shop-card]');
      if (!card || !listRoot.contains(card)) {
        return;
      }
      var id = card.getAttribute('data-wr-id');
      document.querySelectorAll('[data-shop-card].is-map-focus').forEach(function (el) {
        el.classList.remove('is-map-focus');
      });
      card.classList.add('is-map-focus');
      self.focusLocationById(id);
    });
    listRoot.addEventListener('mouseout', function (e) {
      var card = e.target.closest && e.target.closest('[data-shop-card]');
      if (!card || !listRoot.contains(card)) {
        return;
      }
      if (card.contains(e.relatedTarget)) {
        return;
      }
      card.classList.remove('is-map-focus');
    });
  };

  ShopMapPanel.prototype.goToCurrentLocation = function () {
    var self = this;
    if (!self.map) return;
    if (!navigator.geolocation) {
      self.setStatus('이 브라우저에서는 현재 위치를 사용할 수 없습니다.', 'error');
      return;
    }
    if (self.locateBtn) {
      self.locateBtn.disabled = true;
    }
    self.setStatus('현재 위치를 확인하는 중입니다...', 'success');
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        var loc = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        self.applyNearRadiusView(loc);
        self.setStatus('현재 위치 주변을 지도에 표시했습니다.', 'success');
        if (self.locateBtn) {
          self.locateBtn.disabled = false;
        }
      },
      function (error) {
        self.setStatus(locationErrorMessage(error), 'error');
        if (self.locateBtn) {
          self.locateBtn.disabled = false;
        }
      },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
    );
  };

  ShopMapPanel.prototype.bindCardThumbRefresh = function () {
    var self = this;
    document.querySelectorAll('[data-shop-card] .shop-list-card__thumb').forEach(function (img) {
      if (img.complete) {
        return;
      }
      img.addEventListener(
        'load',
        function () {
          self.applyCardThumbnailFallbacks();
          self.renderMarkers();
        },
        { once: true }
      );
    });
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
    this.thumbnail = resolveAbsoluteUrl(root.dataset.mapThumbnail || '');
    this.link = root.dataset.mapLink || global.location.href;
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

    var self = this;
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

    createMapMarker(
      {
        lat: this.lat,
        lng: this.lng,
        thumbnail: this.thumbnail,
        name: this.name,
        link: this.link
      },
      this.map,
      { infoWindow: new global.google.maps.InfoWindow({ maxWidth: markerInfoWindowMaxWidth() }) },
      function (marker) {
        self.marker = marker;
      }
    );

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
    global.__eottaeShopMapPanels = panels;
    panels.forEach(function (panel) {
      if (typeof panel.init === 'function') {
        panel.init();
      }
    });
    global.document.dispatchEvent(new CustomEvent('eottae:shop-maps-ready'));
  };

  global.eottaeShopMapApplyNearView = function (lat, lng) {
    if (!isFinite(lat) || !isFinite(lng)) {
      return false;
    }
    collectPanels();
    global.__eottaeShopMapPanels = panels;
    var center = { lat: lat, lng: lng };
    var i;
    var applied = false;
    for (i = 0; i < panels.length; i += 1) {
      if (panels[i] && typeof panels[i].applyNearRadiusView === 'function') {
        panels[i].applyNearRadiusView(center);
        applied = true;
      }
    }
    return applied;
  };
})(typeof window !== 'undefined' ? window : this);
