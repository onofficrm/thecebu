(function (global) {
  'use strict';

  var TYPE_META = {
    market: { label: '중고장터', icon: '🛍', color: '#f97316' },
    job: { label: '구인구직', icon: '💼', color: '#7c3aed' },
    estate: { label: '부동산', icon: '🏠', color: '#0284c7' },
  };

  var DEFAULT_RADIUS_KM = 5;

  function parseRadiusKm(value, fallback) {
    var radius = parseFloat(value);
    if (!isFinite(radius) || radius <= 0) {
      return fallback;
    }
    return radius;
  }

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
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

  function parseData() {
    var el = document.getElementById('cebuMapData');
    if (!el) return [];
    try {
      var data = JSON.parse(el.textContent || '[]');
      return Array.isArray(data) ? data : [];
    } catch (e) {
      return [];
    }
  }

  function normalizeMarker(item) {
    if (!item || typeof item !== 'object') return null;
    var lat = parseFloat(item.lat);
    var lng = parseFloat(item.lng);
    if (!isFinite(lat) || !isFinite(lng)) return null;
    var type = item.type || 'market';
    return {
      type: type,
      label: item.label || (TYPE_META[type] ? TYPE_META[type].label : type),
      bo_table: item.bo_table || '',
      wr_id: item.wr_id || '',
      title: item.title || '',
      price: item.price || '',
      status: item.status || '',
      status_key: item.status_key || '',
      area: item.area || '기타',
      area_key: item.area_key || 'other',
      location_text: item.location_text || '',
      location: item.location || '',
      lat: lat,
      lng: lng,
      url: item.url || '#',
      share_url: item.share_url || '',
      directions_url: item.directions_url || '',
      thumbnail: item.thumbnail || '',
      is_dimmed: !!item.is_dimmed,
      timestamp: parseInt(item.timestamp, 10) || 0,
      price_num: parseInt(item.price_num, 10) || 0,
      distance_km: null
    };
  }

  function distanceKm(aLat, aLng, bLat, bLng) {
    var toRad = function (n) { return n * Math.PI / 180; };
    var r = 6371;
    var dLat = toRad(bLat - aLat);
    var dLng = toRad(bLng - aLng);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(toRad(aLat)) * Math.cos(toRad(bLat)) *
      Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return r * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
  }

  function shareIconSvg() {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><path d="M8.59 13.51 15.42 17.49"></path><path d="M15.41 6.51 8.59 10.49"></path></svg>';
  }

  function directionsUrl(loc) {
    if (loc.directions_url) return loc.directions_url;
    if (isFinite(loc.lat) && isFinite(loc.lng)) {
      return 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(loc.lat + ',' + loc.lng);
    }
    return '#';
  }

  function shareUrl(loc) {
    return loc.share_url || loc.url || window.location.href;
  }

  function thumbHtml(loc) {
    if (loc.thumbnail) {
      return (
        '<div class="cebu-map-card__thumb-wrap">' +
        '<img src="' + escapeHtml(loc.thumbnail) + '" alt="" class="cebu-map-card__thumb" width="96" height="96" loading="lazy" decoding="async">' +
        '</div>'
      );
    }
    return '<div class="cebu-map-card__thumb-wrap"><div class="cebu-map-card__thumb cebu-map-card__thumb--empty" aria-hidden="true"></div></div>';
  }

  function actionsHtml(loc) {
    return (
      '<div class="cebu-map-card__actions inquiry-button inquiry-button--list">' +
      '<a href="' + escapeHtml(loc.url) + '" class="inquiry-button__btn inquiry-button__btn--primary">상세보기</a>' +
      '<a href="' + escapeHtml(directionsUrl(loc)) + '" class="inquiry-button__btn inquiry-button__btn--map inquiry-button__btn--outline" target="_blank" rel="noopener noreferrer">길찾기</a>' +
      '<button type="button" class="inquiry-button__btn inquiry-button__btn--share inquiry-button__btn--share-compact" data-share-url="' + escapeHtml(shareUrl(loc)) + '" aria-label="공유하기">' +
      '<span class="inquiry-button__icon">' + shareIconSvg() + '</span>' +
      '</button>' +
      '</div>'
    );
  }

  function markerIcon(loc) {
    var meta = TYPE_META[loc.type] || TYPE_META.market;
    if (!global.google || !google.maps) return null;
    var svg =
      '<svg xmlns="http://www.w3.org/2000/svg" width="44" height="54" viewBox="0 0 44 54">' +
      '<path d="M22 53s18-15.2 18-31A18 18 0 1 0 4 22c0 15.8 18 31 18 31z" fill="' + meta.color + '"/>' +
      '<circle cx="22" cy="22" r="13" fill="white" opacity=".95"/>' +
      '<text x="22" y="28" text-anchor="middle" font-size="17">' + meta.icon + '</text>' +
      '</svg>';
    return {
      url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
      scaledSize: new google.maps.Size(44, 54),
      anchor: new google.maps.Point(22, 52)
    };
  }

  function cardHtml(loc) {
    var meta = TYPE_META[loc.type] || TYPE_META.market;
    var priceHtml = loc.price ? '<span class="cebu-map-card__price">' + escapeHtml(loc.price) + '</span>' : '';
    var status = loc.status ? '<span class="cebu-map-card__status">' + escapeHtml(loc.status) + '</span>' : '';
    return (
      '<article class="cebu-map-card cebu-map-card--' + escapeHtml(loc.type) + (loc.is_dimmed ? ' is-dimmed' : '') + '">' +
      thumbHtml(loc) +
      '<div class="cebu-map-card__body">' +
      '<div class="cebu-map-card__top">' +
      '<span class="cebu-map-card__type"><span aria-hidden="true">' + meta.icon + '</span> ' + escapeHtml(loc.label) + '</span>' +
      status +
      '</div>' +
      '<div class="cebu-map-card__headline">' +
      '<h3 class="cebu-map-card__title">' + escapeHtml(loc.title) + '</h3>' +
      priceHtml +
      '</div>' +
      '<p class="cebu-map-card__location">' + escapeHtml(loc.location || loc.area) + '</p>' +
      actionsHtml(loc) +
      '</div>' +
      '</article>'
    );
  }

  function CebuLifeMap(root) {
    this.root = root;
    this.data = parseData().map(normalizeMarker).filter(Boolean);
    this.filtered = this.data.slice();
    this.mapEl = qs('[data-map-canvas]', root);
    this.listEl = qs('[data-map-list]', root);
    this.countEl = qs('[data-map-count]', root);
    this.activeCardEl = qs('[data-map-active-card]', root);
    this.statusEl = qs('[data-map-status]', root);
    this.nearBtn = qs('[data-map-near]', root);
    this.map = null;
    this.infoWindow = null;
    this.markers = [];
    this.radiusCircle = null;
    this.userMarker = null;
    this.userLocation = null;
    this.filters = { type: 'all', status: 'all', radius: String(DEFAULT_RADIUS_KM), keyword: '', sort: 'latest' };
    this.bind();
    this.syncRadiusDefault();
    this.applyFilters();
    this.initMap();
    this.bootstrapNearbyView();
  }

  CebuLifeMap.prototype.bind = function () {
    var self = this;
    qsa('[data-map-filter]', this.root).forEach(function (el) {
      var eventName = el.tagName === 'INPUT' ? 'input' : 'change';
      el.addEventListener(eventName, function () {
        var key = el.getAttribute('data-map-filter');
        self.filters[key] = el.value || (key === 'keyword' ? '' : 'all');
        self.applyFilters();
      });
    });
    if (this.nearBtn) {
      this.nearBtn.addEventListener('click', function () {
        self.requestNearby();
      });
    }
  };

  CebuLifeMap.prototype.syncRadiusDefault = function () {
    var cfg = global.__CEBU_LIFE_MAP_CONFIG__ || {};
    var radiusKm = parseRadiusKm(cfg.defaultRadiusKm, DEFAULT_RADIUS_KM);
    this.filters.radius = String(radiusKm);
  };

  CebuLifeMap.prototype.getActiveRadiusKm = function () {
    return parseRadiusKm(this.filters.radius, DEFAULT_RADIUS_KM);
  };

  CebuLifeMap.prototype.getDefaultCenter = function () {
    var cfg = global.__CEBU_LIFE_MAP_CONFIG__ || {};
    return {
      lat: parseFloat(cfg.defaultLat) || 10.313,
      lng: parseFloat(cfg.defaultLng) || 123.9174
    };
  };

  CebuLifeMap.prototype.clearRadiusOverlay = function () {
    if (this.radiusCircle) {
      this.radiusCircle.setMap(null);
      this.radiusCircle = null;
    }
  };

  CebuLifeMap.prototype.setUserMarkerAt = function (center) {
    if (!this.map || !center || !global.google || !google.maps) {
      return;
    }
    if (!this.userMarker) {
      this.userMarker = new google.maps.Marker({
        map: this.map,
        title: '기준 위치',
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
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

  CebuLifeMap.prototype.applyRadiusView = function (center) {
    if (!this.map || !center || !global.google || !google.maps) {
      return;
    }

    var radiusKm = this.getActiveRadiusKm() || DEFAULT_RADIUS_KM;
    this.clearRadiusOverlay();
    this.radiusCircle = new google.maps.Circle({
      strokeColor: '#0ea5e9',
      strokeOpacity: 0.85,
      strokeWeight: 2,
      fillColor: '#0ea5e9',
      fillOpacity: 0.08,
      map: this.map,
      center: center,
      radius: radiusKm * 1000
    });

    var bounds = this.radiusCircle.getBounds();
    if (bounds) {
      this.map.fitBounds(bounds, 48);
    } else {
      this.map.setCenter(center);
      this.map.setZoom(14);
    }

    this.setUserMarkerAt(center);
  };

  CebuLifeMap.prototype.updateDistances = function () {
    var self = this;
    if (!this.userLocation) {
      return;
    }
    this.data.forEach(function (loc) {
      loc.distance_km = distanceKm(self.userLocation.lat, self.userLocation.lng, loc.lat, loc.lng);
    });
  };

  CebuLifeMap.prototype.activateNearby = function (center, statusMessage, useNearSort) {
    this.userLocation = center;
    this.updateDistances();

    var radiusKm = this.getActiveRadiusKm() || DEFAULT_RADIUS_KM;
    this.filters.radius = String(radiusKm);

    if (useNearSort) {
      this.filters.sort = 'near';
      var sortEl = qs('[data-map-filter="sort"]', this.root);
      if (sortEl) {
        sortEl.value = 'near';
      }
    }

    this.setStatus(statusMessage);
    this.applyFilters();
  };

  CebuLifeMap.prototype.setStatus = function (message) {
    if (this.statusEl) this.statusEl.textContent = message || '';
  };

  CebuLifeMap.prototype.bootstrapNearbyView = function () {
    var self = this;
    var fallbackCenter = this.getDefaultCenter();
    var radiusKm = this.getActiveRadiusKm() || DEFAULT_RADIUS_KM;
    var fallbackMessage = '등록된 정보가 없어도 기본 위치 기준 ' + radiusKm + 'km 반경으로 지도를 표시합니다.';

    if (!navigator.geolocation) {
      this.activateNearby(fallbackCenter, fallbackMessage, false);
      return;
    }

    this.setStatus('현재위치를 확인하는 중입니다.');
    navigator.geolocation.getCurrentPosition(function (pos) {
      self.activateNearby(
        { lat: pos.coords.latitude, lng: pos.coords.longitude },
        '현재위치 기준 ' + radiusKm + 'km 반경의 생활정보를 보여줍니다.',
        true
      );
    }, function () {
      self.activateNearby(fallbackCenter, fallbackMessage, false);
    }, { enableHighAccuracy: true, timeout: 10000 });
  };

  CebuLifeMap.prototype.matchesStatus = function (loc) {
    var val = this.filters.status;
    if (val === 'all') return true;
    if (val === 'market:not-sold') return loc.type !== 'market' || loc.status_key !== 'sold';
    var parts = val.split(':');
    return parts.length === 2 && loc.type === parts[0] && loc.status_key === parts[1];
  };

  CebuLifeMap.prototype.applyFilters = function () {
    var self = this;
    var keyword = String(this.filters.keyword || '').trim().toLowerCase();
    var radius = this.getActiveRadiusKm();
    this.filtered = this.data.filter(function (loc) {
      if (self.filters.type !== 'all' && loc.type !== self.filters.type) return false;
      if (!self.matchesStatus(loc)) return false;
      if (radius > 0 && self.userLocation) {
        if (!self.userLocation || loc.distance_km == null || loc.distance_km > radius) return false;
      }
      if (keyword) {
        var hay = [
          loc.title, loc.price, loc.status, loc.area, loc.location_text, loc.location, loc.label
        ].join(' ').toLowerCase();
        if (hay.indexOf(keyword) === -1) return false;
      }
      return true;
    });
    this.sortFiltered();
    this.renderList();
    this.renderMarkers();
  };

  CebuLifeMap.prototype.sortFiltered = function () {
    var sort = this.filters.sort || 'latest';
    var hasUserLocation = !!this.userLocation;
    this.filtered.sort(function (a, b) {
      if (sort === 'near' && hasUserLocation) {
        return (a.distance_km == null ? 999999 : a.distance_km) - (b.distance_km == null ? 999999 : b.distance_km);
      }
      if (sort === 'price_asc') {
        var ap = a.price_num || 999999999;
        var bp = b.price_num || 999999999;
        if (ap !== bp) return ap - bp;
      }
      return (b.timestamp || 0) - (a.timestamp || 0);
    });
  };

  CebuLifeMap.prototype.requestNearby = function () {
    var self = this;
    var radiusKm = this.getActiveRadiusKm() || DEFAULT_RADIUS_KM;

    if (!navigator.geolocation) {
      this.activateNearby(this.getDefaultCenter(), '현재위치를 사용할 수 없어 기본 위치 기준 ' + radiusKm + 'km 반경으로 표시합니다.', false);
      return;
    }

    this.setStatus('현재위치를 확인하는 중입니다.');
    navigator.geolocation.getCurrentPosition(function (pos) {
      self.activateNearby(
        { lat: pos.coords.latitude, lng: pos.coords.longitude },
        '현재위치 기준 ' + radiusKm + 'km 반경의 생활정보를 가까운순으로 보여줍니다.',
        true
      );
    }, function () {
      self.activateNearby(
        self.getDefaultCenter(),
        '현재위치 권한이 거부되어 기본 위치 기준 ' + radiusKm + 'km 반경으로 표시합니다.',
        false
      );
    }, { enableHighAccuracy: true, timeout: 10000 });
  };

  CebuLifeMap.prototype.renderList = function () {
    var self = this;
    if (this.countEl) this.countEl.textContent = String(this.filtered.length);
    if (!this.listEl) return;
    if (!this.filtered.length) {
      this.listEl.innerHTML = '<p class="cebu-map-empty">조건에 맞는 위치 정보가 없습니다.</p>';
      return;
    }
    this.listEl.innerHTML = this.filtered.map(function (loc, idx) {
      return '<div class="cebu-map-list-item" data-map-list-index="' + idx + '" tabindex="0" role="button" aria-label="' + escapeHtml(loc.title) + '">' + cardHtml(loc) + '</div>';
    }).join('');
    qsa('[data-map-list-index]', this.listEl).forEach(function (item) {
      item.addEventListener('click', function (e) {
        if (e.target.closest('.cebu-map-card__actions, a, button')) return;
        var loc = self.filtered[parseInt(item.getAttribute('data-map-list-index'), 10)];
        self.focusMarker(loc);
      });
      item.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        if (e.target.closest('.cebu-map-card__actions, a, button')) return;
        e.preventDefault();
        var loc = self.filtered[parseInt(item.getAttribute('data-map-list-index'), 10)];
        self.focusMarker(loc);
      });
    });
  };

  CebuLifeMap.prototype.initMap = function () {
    var cfg = global.__CEBU_LIFE_MAP_CONFIG__ || {};
    if (!this.mapEl || !cfg.hasApiKey || !global.google || !google.maps) {
      this.root.classList.add('is-map-fallback');
      return;
    }
    this.map = new google.maps.Map(this.mapEl, {
      center: { lat: parseFloat(cfg.defaultLat) || 10.313, lng: parseFloat(cfg.defaultLng) || 123.9174 },
      zoom: parseInt(cfg.defaultZoom, 10) || 12,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: true
    });
    this.infoWindow = new google.maps.InfoWindow({ maxWidth: 340 });
    this.renderMarkers();
    if (this.userLocation) {
      this.applyRadiusView(this.userLocation);
    }
  };

  CebuLifeMap.prototype.clearMarkers = function () {
    this.markers.forEach(function (entry) {
      entry.marker.setMap(null);
    });
    this.markers = [];
  };

  CebuLifeMap.prototype.renderMarkers = function () {
    var self = this;
    if (!this.map || !global.google || !google.maps) return;
    this.clearMarkers();
    var bounds = new google.maps.LatLngBounds();
    if (this.userLocation) {
      bounds.extend(new google.maps.LatLng(this.userLocation.lat, this.userLocation.lng));
    }
    this.filtered.forEach(function (loc) {
      var marker = new google.maps.Marker({
        position: { lat: loc.lat, lng: loc.lng },
        map: self.map,
        title: loc.title,
        icon: markerIcon(loc),
        opacity: loc.is_dimmed ? 0.48 : 1
      });
      marker.addListener('click', function () {
        self.openMarker(marker, loc);
      });
      self.markers.push({ marker: marker, loc: loc });
      bounds.extend(marker.getPosition());
    });
    if (this.filtered.length === 1) {
      this.map.setCenter({ lat: this.filtered[0].lat, lng: this.filtered[0].lng });
      this.map.setZoom(15);
    } else if (this.filtered.length > 1) {
      this.map.fitBounds(bounds, 48);
    } else if (this.userLocation && this.getActiveRadiusKm() > 0) {
      this.applyRadiusView(this.userLocation);
    }
  };

  CebuLifeMap.prototype.openMarker = function (marker, loc) {
    if (this.infoWindow && this.map && marker) {
      this.infoWindow.setContent(cardHtml(loc));
      this.infoWindow.open(this.map, marker);
    }
    if (this.activeCardEl) {
      this.activeCardEl.innerHTML = cardHtml(loc);
      this.activeCardEl.hidden = false;
    }
  };

  CebuLifeMap.prototype.focusMarker = function (loc) {
    var entry = this.markers.filter(function (row) {
      return row.loc.type === loc.type && String(row.loc.wr_id) === String(loc.wr_id);
    })[0];
    if (entry && this.map) {
      this.map.panTo(entry.marker.getPosition());
      this.map.setZoom(Math.max(this.map.getZoom() || 12, 15));
      this.openMarker(entry.marker, entry.loc);
      return;
    }
    if (this.activeCardEl) {
      this.activeCardEl.innerHTML = cardHtml(loc);
      this.activeCardEl.hidden = false;
    }
  };

  function init() {
    qsa('[data-cebu-map-page]').forEach(function (root) {
      if (!root.__cebuLifeMap) {
        root.__cebuLifeMap = new CebuLifeMap(root);
        return;
      }
      if (!root.__cebuLifeMap.map) {
        root.__cebuLifeMap.initMap();
      }
    });
  }

  global.initCebuLifeMap = function () {
    init();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window);
