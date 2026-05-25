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
    var link =
      loc.link && loc.link !== '#'
        ? '<a href="' + escapeHtml(loc.link) + '" class="marker-info-link marker-info-link--btn">상세보기</a>'
        : '';
    if (hasThumb) {
      return (
        '<div class="marker-info marker-info--thumb marker-info--compact">' +
        '<div class="marker-info-thumb-wrap"><img class="marker-info-thumb" src="' +
        escapeHtml(loc.thumbnail) +
        '" alt=""></div>' +
        '<div class="marker-info-body">' +
        '<div class="marker-info-head">' +
        '<h3 class="marker-info-title">' +
        escapeHtml(loc.name) +
        '</h3>' +
        markerInfoMetaHtml(loc) +
        '</div>' +
        (link ? '<div class="marker-info-actions">' + link + '</div>' : '') +
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
      link +
      '</div></div></div>'
    );
  }

  function openMarkerInfoWindow(infoWindow, map, marker, loc) {
    infoWindow.setOptions({ maxWidth: 240 });
    infoWindow.setContent(markerInfoHtml(loc));
    infoWindow.open(map, marker);
    global.google.maps.event.addListenerOnce(infoWindow, 'domready', function () {
      var root = document.querySelector('.gm-style-iw .marker-info--compact');
      if (!root) {
        return;
      }
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
      }
      var position = marker.getPosition && marker.getPosition();
      if (position) {
        global.requestAnimationFrame(function () {
          infoWindow.setPosition(position);
        });
      }
    });
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
    this.infoWindow = new global.google.maps.InfoWindow({ maxWidth: 240 });
    this.applyCardThumbnailFallbacks();
    this.renderMarkers();
    this.bindCardThumbRefresh();
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

  if (!self.locations.length) {
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
          self.fitMapToLocations();
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
      { lat: this.lat, lng: this.lng, thumbnail: this.thumbnail, name: this.name, link: '' },
      this.map,
      { infoWindow: new global.google.maps.InfoWindow({ maxWidth: 240 }) },
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
    panels.forEach(function (panel) {
      panel.init();
    });
    global.document.dispatchEvent(new CustomEvent('eottae:shop-maps-ready'));
  };
})(typeof window !== 'undefined' ? window : this);
