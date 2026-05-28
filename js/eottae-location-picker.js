(function (global) {
  'use strict';

  var areaRules = [
    { key: 'mactan', label: '막탄', keywords: ['mactan', '막탄', 'mactan newtown', 'newtown'] },
    { key: 'lapu', label: '라푸라푸', keywords: ['lapu-lapu', 'lapu lapu', 'lapulapu', '라푸라푸'] },
    { key: 'mandaue', label: '만다우에', keywords: ['mandaue', '만다우에'] },
    { key: 'talisay', label: '탈리사이', keywords: ['talisay', '탈리사이'] },
    { key: 'consolacion', label: '콘솔라시온', keywords: ['consolacion', '콘솔라시온'] },
    { key: 'cebu', label: '세부시티', keywords: ['cebu city', 'cebu', '세부시티', '세부 시티', '세부', 'it park', 'i.t. park', 'ayala', 'lahug', 'banilad', 'talamban', 'sm seaside'] },
  ];

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function areaLabel(key) {
    for (var i = 0; i < areaRules.length; i += 1) {
      if (areaRules[i].key === key) return areaRules[i].label;
    }
    return '기타';
  }

  function autoDetectArea(address, lat, lng) {
    var hay = String(address || '').toLowerCase();
    for (var i = 0; i < areaRules.length; i += 1) {
      for (var k = 0; k < areaRules[i].keywords.length; k += 1) {
        if (hay.indexOf(areaRules[i].keywords[k]) !== -1) {
          return areaRules[i].key;
        }
      }
    }

    lat = parseFloat(lat);
    lng = parseFloat(lng);
    if (!isFinite(lat) || !isFinite(lng)) return 'other';
    if (lat >= 10.24 && lat <= 10.36 && lng >= 123.78 && lng <= 123.90) return 'cebu';
    if (lat >= 10.25 && lat <= 10.38 && lng >= 123.92 && lng <= 124.05) return 'mactan';
    if (lat >= 10.25 && lat <= 10.35 && lng >= 123.93 && lng <= 124.04) return 'lapu';
    if (lat >= 10.30 && lat <= 10.40 && lng >= 123.90 && lng <= 123.97) return 'mandaue';
    if (lat >= 10.20 && lat <= 10.29 && lng >= 123.80 && lng <= 123.90) return 'talisay';
    if (lat >= 10.36 && lat <= 10.43 && lng >= 123.91 && lng <= 124.00) return 'consolacion';
    return 'other';
  }

  function normalizeAddress(keyword) {
    keyword = String(keyword || '').trim();
    if (!keyword) return '';
    if (!/cebu|세부|mactan|막탄|mandaue|lapu/i.test(keyword)) {
      keyword += ', Cebu, Philippines';
    }
    return keyword;
  }

  function searchAddress(keyword) {
    return new Promise(function (resolve) {
      if (!global.google || !google.maps || !google.maps.Geocoder) {
        resolve({ ok: false, message: '지도를 불러오지 못했습니다. 상세위치 텍스트만 저장됩니다.' });
        return;
      }
      var geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: normalizeAddress(keyword) }, function (results, status) {
        if (status !== 'OK' || !results || !results[0] || !results[0].geometry) {
          resolve({ ok: false, message: '검색 결과를 찾지 못했습니다.' });
          return;
        }
        var loc = results[0].geometry.location;
        resolve({
          ok: true,
          address: results[0].formatted_address || keyword,
          lat: loc.lat(),
          lng: loc.lng(),
        });
      });
    });
  }

  function reverseGeocode(lat, lng) {
    return new Promise(function (resolve) {
      if (!global.google || !google.maps || !google.maps.Geocoder) {
        resolve('');
        return;
      }
      var geocoder = new google.maps.Geocoder();
      geocoder.geocode({ location: { lat: lat, lng: lng } }, function (results, status) {
        resolve(status === 'OK' && results && results[0] ? (results[0].formatted_address || '') : '');
      });
    });
  }

  function LocationPicker(root, options) {
    this.root = root;
    this.options = options || {};
    this.addressInput = qs('[data-location-address]', root);
    this.latInput = qs('[data-location-lat]', root);
    this.lngInput = qs('[data-location-lng]', root);
    this.areaInput = qs('[data-location-area]', root);
    this.areaLabel = qs('[data-location-area-label]', root);
    this.status = qs('[data-location-status]', root);
    this.mapEl = qs('[data-location-map]', root);
    this.map = null;
    this.marker = null;
    this.init();
  }

  LocationPicker.prototype.setStatus = function (message) {
    if (!this.status) return;
    this.status.childNodes[0].nodeValue = message + ' ';
  };

  LocationPicker.prototype.setMarker = function (lat, lng) {
    if (!this.map || !this.marker || !isFinite(lat) || !isFinite(lng)) return;
    var pos = { lat: lat, lng: lng };
    this.map.setCenter(pos);
    this.map.setZoom(15);
    this.marker.setPosition(pos);
  };

  LocationPicker.prototype.updateLocationFields = function (data) {
    data = data || {};
    var address = data.address || (this.addressInput ? this.addressInput.value : '');
    var lat = data.lat != null ? parseFloat(data.lat) : parseFloat(this.latInput && this.latInput.value);
    var lng = data.lng != null ? parseFloat(data.lng) : parseFloat(this.lngInput && this.lngInput.value);
    var area = data.area || autoDetectArea(address, lat, lng);

    if (this.addressInput && address) this.addressInput.value = address;
    if (this.latInput && isFinite(lat)) this.latInput.value = lat.toFixed(7);
    if (this.lngInput && isFinite(lng)) this.lngInput.value = lng.toFixed(7);
    if (this.areaInput) this.areaInput.value = area || 'other';
    if (this.areaLabel) this.areaLabel.textContent = areaLabel(area || 'other');
    if (isFinite(lat) && isFinite(lng)) this.setMarker(lat, lng);
  };

  LocationPicker.prototype.initMap = function () {
    if (!this.mapEl || !global.google || !google.maps) return;
    var lat = parseFloat(this.latInput && this.latInput.value ? this.latInput.value : '10.3157');
    var lng = parseFloat(this.lngInput && this.lngInput.value ? this.lngInput.value : '123.8854');
    var center = { lat: isFinite(lat) ? lat : 10.3157, lng: isFinite(lng) ? lng : 123.8854 };
    var self = this;

    this.map = new google.maps.Map(this.mapEl, {
      center: center,
      zoom: 13,
      mapTypeControl: false,
      streetViewControl: false,
    });
    this.marker = new google.maps.Marker({
      position: center,
      map: this.map,
      draggable: true,
    });
    this.mapEl.classList.add('is-live');

    function applyPosition(latLng) {
      reverseGeocode(latLng.lat(), latLng.lng()).then(function (address) {
        self.updateLocationFields({
          address: address || (latLng.lat().toFixed(6) + ', ' + latLng.lng().toFixed(6)),
          lat: latLng.lat(),
          lng: latLng.lng(),
        });
        self.setStatus('지도 위치가 저장되었습니다.');
      });
    }

    this.map.addListener('click', function (e) {
      self.marker.setPosition(e.latLng);
      applyPosition(e.latLng);
    });
    this.marker.addListener('dragend', function () {
      applyPosition(self.marker.getPosition());
    });
  };

  LocationPicker.prototype.getCurrentLocation = function () {
    var self = this;
    if (!navigator.geolocation) {
      this.setStatus('이 브라우저에서는 현재위치를 사용할 수 없습니다.');
      return;
    }
    this.setStatus('현재위치를 확인하는 중입니다.');
    navigator.geolocation.getCurrentPosition(function (pos) {
      var lat = pos.coords.latitude;
      var lng = pos.coords.longitude;
      reverseGeocode(lat, lng).then(function (address) {
        self.updateLocationFields({ address: address || '현재 위치 근처', lat: lat, lng: lng });
        self.setStatus('현재위치가 저장되었습니다.');
      });
    }, function () {
      self.setStatus('현재위치 권한이 거부되었습니다. 주소 검색 또는 지도 선택을 이용해 주세요.');
    }, { enableHighAccuracy: true, timeout: 10000 });
  };

  LocationPicker.prototype.bind = function () {
    var self = this;
    var searchBtn = qs('[data-location-search]', this.root);
    var currentBtn = qs('[data-location-current]', this.root);

    if (searchBtn) {
      searchBtn.addEventListener('click', function () {
        var keyword = self.addressInput ? self.addressInput.value.trim() : '';
        if (!keyword) {
          alert('주소 또는 장소명을 입력해 주세요.');
          return;
        }
        searchBtn.disabled = true;
        self.setStatus('주소를 검색하는 중입니다.');
        searchAddress(keyword).then(function (data) {
          searchBtn.disabled = false;
          if (!data.ok) {
            self.updateLocationFields({ address: keyword, area: autoDetectArea(keyword) });
            self.setStatus(data.message || '주소 검색에 실패했습니다.');
            return;
          }
          self.updateLocationFields(data);
          self.setStatus('주소와 좌표가 저장되었습니다.');
        });
      });
    }

    if (currentBtn) {
      currentBtn.addEventListener('click', function () {
        self.getCurrentLocation();
      });
    }

    if (this.addressInput) {
      this.addressInput.addEventListener('blur', function () {
        self.updateLocationFields({ address: self.addressInput.value });
      });
    }
  };

  LocationPicker.prototype.init = function () {
    this.updateLocationFields({});
    this.initMap();
    this.bind();
  };

  function initLocationPicker(options) {
    options = options || {};
    var root = options.root || (options.selector ? qs(options.selector) : null);
    if (!root) return null;
    if (root.__eottaeLocationPicker) return root.__eottaeLocationPicker;
    root.__eottaeLocationPicker = new LocationPicker(root, options);
    return root.__eottaeLocationPicker;
  }

  function initAll() {
    document.querySelectorAll('[data-location-picker]').forEach(function (root) {
      initLocationPicker({ root: root });
    });
  }

  global.initLocationPicker = initLocationPicker;
  global.eottaeLocationPicker = {
    initLocationPicker: initLocationPicker,
    autoDetectArea: autoDetectArea,
    searchAddress: searchAddress,
    reverseGeocode: reverseGeocode,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  document.addEventListener('eottae:geocoder-ready', initAll);
  document.addEventListener('eottae:shop-maps-ready', initAll);
}(window));
