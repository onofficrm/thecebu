(function () {
  'use strict';

  function qs(sel, ctx) {
    return (ctx || document).querySelector(sel);
  }

  function qsa(sel, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
  }

  function parseJsonResponse(res) {
    return res.text().then(function (text) {
      var trimmed = (text || '').trim();
      if (!trimmed) {
        throw new Error('서버 응답이 비어 있습니다.');
      }
      try {
        return JSON.parse(trimmed);
      } catch (e) {
        throw new Error('서버 응답 오류입니다. 잠시 후 다시 시도해 주세요.');
      }
    });
  }

  function ensureInquiryModal() {
    var modal = qs('#eottaeInquiryModal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'eottaeInquiryModal';
    modal.className = 'eottae-inquiry-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.innerHTML =
      '<div class="eottae-inquiry-modal__panel">' +
      '<button type="button" class="eottae-inquiry-modal__close" aria-label="닫기">&times;</button>' +
      '<h2 class="eottae-inquiry-modal__title">빠른 문의하기</h2>' +
      '<p class="eottae-inquiry-modal__desc">문의 내용을 남겨 주시면 빠르게 연락드리겠습니다.</p>' +
      '<form class="eottae-inquiry-modal__form" method="post" action="/proc/inquiry-submit.php">' +
      '<input type="hidden" name="inquiry_code" value="">' +
      '<div class="eottae-field"><label>이름</label><input type="text" name="name" required></div>' +
      '<div class="eottae-field"><label>연락처</label><input type="tel" name="phone" required></div>' +
      '<div class="eottae-field"><label>문의 내용</label><textarea name="message" required></textarea></div>' +
      '<button type="submit" class="inquiry-button__btn inquiry-button__btn--inquiry" style="width:100%">문의 보내기</button>' +
      '</form></div>';
    document.body.appendChild(modal);

    qs('.eottae-inquiry-modal__close', modal).addEventListener('click', function () {
      modal.classList.remove('is-open');
    });
    modal.addEventListener('click', function (e) {
      if (e.target === modal) modal.classList.remove('is-open');
    });

    return modal;
  }

  function openInquiryModal(code) {
    var modal = ensureInquiryModal();
    var input = qs('input[name="inquiry_code"]', modal);
    if (input) input.value = code || '';
    modal.classList.add('is-open');
    var nameInput = qs('input[name="name"]', modal);
    if (nameInput) nameInput.focus();
  }

  function handleShare(btn) {
    var url = btn.getAttribute('data-share-url') || window.location.href;
    if (navigator.share) {
      navigator.share({ title: document.title, url: url }).catch(function () {});
      return;
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(function () {
        alert('링크가 복사되었습니다.');
      });
      return;
    }
    prompt('링크를 복사하세요:', url);
  }

  document.addEventListener('click', function (e) {
    var inquiryBtn = e.target.closest('[data-inquiry-action="open"], .inquiry-button__btn--inquiry');
    if (inquiryBtn && inquiryBtn.tagName === 'BUTTON') {
      e.preventDefault();
      var code = inquiryBtn.getAttribute('data-inquiry-code') || '';
      openInquiryModal(code);
      return;
    }
    var shareBtn = e.target.closest('.inquiry-button__btn--share');
    if (shareBtn) {
      e.preventDefault();
      handleShare(shareBtn);
    }
  });

  /* Shop register 7-step wizard */
  function initShopRegisterWizard() {
    var root = qs('.shop-register-page');
    if (!root) return;

    var panels = qsa('.shop-register-page__panel', root);
    var steps = qsa('.shop-register-page__step', root);
    var btnPrev = qs('[data-wizard="prev"]', root);
    var btnNext = qs('[data-wizard="next"]', root);
    var btnSubmit = qs('[data-wizard="submit"]', root);
    var current = 0;

    function render() {
      panels.forEach(function (p, i) {
        p.classList.toggle('is-active', i === current);
      });
      steps.forEach(function (s, i) {
        s.classList.toggle('is-active', i === current);
        s.classList.toggle('is-done', i < current);
      });
      if (btnPrev) btnPrev.style.display = current === 0 ? 'none' : '';
      if (btnNext) btnNext.style.display = current >= panels.length - 1 ? 'none' : '';
      if (btnSubmit) btnSubmit.style.display = current >= panels.length - 1 ? '' : 'none';
    }

    if (btnPrev) {
      btnPrev.addEventListener('click', function () {
        if (current > 0) {
          current--;
          render();
        }
      });
    }
    if (btnNext) {
      btnNext.addEventListener('click', function () {
        if (current === 0) {
          var subjectInput = qs('#wr_subject', root);
          if (subjectInput && !subjectInput.value.trim()) {
            alert('업체명을 입력해 주세요.');
            subjectInput.focus();
            return;
          }
        }
        if (current < panels.length - 1) {
          current++;
          render();
          if (current === panels.length - 1) {
            updateShopRegisterSummary(root);
          }
        }
      });
    }

    var caSelect = qs('#ca_name', root);
    var wr1Input = qs('#wr_1', root);
    if (caSelect && wr1Input) {
      caSelect.addEventListener('change', function () {
        wr1Input.value = caSelect.value;
      });
    }

    initShopAiGenerator(root);
    initShopMapThumbAi(root);
    render();
  }

  function shopSetAiStatus(root, message, isError) {
    qsa('[data-shop-ai-status]', root).forEach(function (el) {
      el.textContent = message || '';
      el.classList.toggle('is-error', !!isError);
    });
  }

  function setAiBtnLabel(btn, text) {
    if (!btn) return;
    var label = btn.querySelector('.eottae-ai-btn__label');
    if (label) {
      label.textContent = text;
      return;
    }
    btn.textContent = text;
  }

  function setAiBtnLoading(btn, loading) {
    if (!btn) return;
    btn.classList.toggle('is-loading', !!loading);
    btn.disabled = !!loading;
  }

  function getAiBtnDefaultLabel(btn) {
    if (!btn) return '';
    return btn.getAttribute('data-default-label') || (btn.querySelector('.eottae-ai-btn__label') ? btn.querySelector('.eottae-ai-btn__label').textContent : btn.textContent) || '';
  }

  function shopAiValue(root, selector) {
    var el = qs(selector, root);
    return el ? (el.value || '').trim() : '';
  }

  function shopFillAiValue(root, selector, value, overwrite) {
    var el = qs(selector, root);
    if (!el || !value) return;
    if (!overwrite && (el.value || '').trim() !== '') return;
    el.value = value;
    el.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function initShopAiGenerator(root) {
    var buttons = qsa('[data-shop-ai-generate]', root);
    if (!buttons.length || !window.fetch) return;

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var subject = shopAiValue(root, '#wr_subject');
        if (!subject) {
          alert('업체명을 먼저 입력해 주세요.');
          var subjectInput = qs('#wr_subject', root);
          if (subjectInput) subjectInput.focus();
          return;
        }

        var mode = btn.getAttribute('data-shop-ai-generate') || 'all';
        var form = new FormData();
        form.append('bo_table', shopAiValue(root, 'input[name="bo_table"]') || 'shop');
        form.append('name', subject);
        form.append('category', shopAiValue(root, '#ca_name'));
        form.append('region', shopAiValue(root, '#wr_2'));
        form.append('address', shopAiValue(root, '#wr_3'));
        form.append('phone', shopAiValue(root, '#wr_4'));
        form.append('hours', shopAiValue(root, '#wr_6'));
        form.append('closed', shopAiValue(root, '#wr_7'));
        form.append('website', shopAiValue(root, '#wr_link1'));
        form.append('instagram', shopAiValue(root, '#eottae_sns_instagram'));
        form.append('tiktok', shopAiValue(root, '#eottae_sns_tiktok'));
        form.append('facebook', shopAiValue(root, '#eottae_sns_facebook'));
        form.append('naver_blog', shopAiValue(root, '#eottae_sns_naver_blog'));
        form.append('youtube', shopAiValue(root, '#eottae_sns_youtube'));
        form.append('intro', shopAiValue(root, '#wr_content'));

        buttons.forEach(function (b) {
          setAiBtnLoading(b, true);
          setAiBtnLabel(b, 'AI 생성 중…');
        });
        shopSetAiStatus(root, 'AI가 업체 소개와 SEO 문구를 작성 중입니다...', false);

        fetch('/proc/eottae-shop-ai-generate.php', {
          method: 'POST',
          credentials: 'same-origin',
          body: form
        })
          .then(function (res) { return parseJsonResponse(res); })
          .then(function (json) {
            if (!json || !json.success) {
              throw new Error((json && json.message) || 'AI 자동생성에 실패했습니다.');
            }
            var data = json.data || {};
            if (mode !== 'seo') {
              shopFillAiValue(root, '#wr_content', data.intro, true);
            }
            shopFillAiValue(root, '#eottae_seo_title', data.seo_title, true);
            shopFillAiValue(root, '#eottae_seo_intro', data.seo_intro, true);
            shopFillAiValue(root, '#eottae_seo_description', data.meta_description, true);
            shopFillAiValue(root, '#eottae_seo_keyword', data.focus_keyword, true);
            shopSetAiStatus(root, 'AI 문구를 입력했습니다. 등록 전 내용이 맞는지 한 번 확인해 주세요.', false);
          })
          .catch(function (err) {
            shopSetAiStatus(root, err.message || 'AI 자동생성에 실패했습니다.', true);
          })
          .finally(function () {
            buttons.forEach(function (b) {
              setAiBtnLoading(b, false);
              setAiBtnLabel(b, b.getAttribute('data-shop-ai-generate') === 'seo'
                ? 'AI로 SEO 문구 자동생성'
                : 'AI로 업체소개·SEO 자동생성');
            });
          });
      });
    });
  }

  function initShopMapThumbAi(root) {
    var btn = qs('[data-map-thumb-ai-generate]', root);
    if (!btn || !window.fetch) return;

    btn.addEventListener('click', function () {
      var subject = shopAiValue(root, '#wr_subject');
      if (!subject) {
        alert('업체명을 먼저 입력해 주세요.');
        var subjectInput = qs('#wr_subject', root);
        if (subjectInput) subjectInput.focus();
        return;
      }

      var status = qs('[data-map-thumb-ai-status]', root);
      var tmpInput = qs('#eottae_map_thumb_ai_tmp', root);
      var previewImg = qs('[data-map-thumb-preview-img]', root);
      var previewSlot = previewImg ? previewImg.closest('.shop-register-page__photo-slot') : null;
      var fileInput = qs('#eottae_map_thumb', root);
      var form = new FormData();
      form.append('bo_table', shopAiValue(root, 'input[name="bo_table"]') || 'shop');
      form.append('name', subject);
      form.append('category', shopAiValue(root, '#ca_name'));
      form.append('region', shopAiValue(root, '#wr_2'));
      form.append('address', shopAiValue(root, '#wr_3'));
      form.append('intro', shopAiValue(root, '#wr_content'));

      setAiBtnLoading(btn, true);
      setAiBtnLabel(btn, 'AI 썸네일 생성 중…');
      if (status) {
        status.textContent = '지도 마커용 정사각형 썸네일을 생성하고 있습니다.';
        status.classList.remove('is-error');
      }

      fetch('/proc/eottae-shop-map-thumb-ai.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: form
      })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || 'AI 썸네일 생성에 실패했습니다.');
          }
          var data = json.data || {};
          if (tmpInput) tmpInput.value = data.tmp || '';
          if (fileInput) fileInput.value = '';
          if (previewImg && data.url) {
            previewImg.src = data.url + '?v=' + Date.now();
            previewImg.hidden = false;
            if (previewSlot) previewSlot.classList.add('has-preview');
          }
          if (status) status.textContent = 'AI 썸네일을 생성했습니다. 다시 생성 버튼을 누르면 새 이미지로 교체됩니다.';
        })
        .catch(function (err) {
          if (status) {
            status.textContent = err.message || 'AI 썸네일 생성에 실패했습니다.';
            status.classList.add('is-error');
          }
        })
        .finally(function () {
          setAiBtnLoading(btn, false);
          setAiBtnLabel(btn, previewImg && previewImg.src && !previewImg.hidden ? 'AI 지도 썸네일 다시 생성' : getAiBtnDefaultLabel(btn));
        });
    });

    var manualInput = qs('#eottae_map_thumb', root);
    if (manualInput) {
      manualInput.addEventListener('change', function () {
        var tmpInput = qs('#eottae_map_thumb_ai_tmp', root);
        if (tmpInput) tmpInput.value = '';
        setAiBtnLabel(btn, getAiBtnDefaultLabel(btn));
      });
    }
  }

  function updateShopRegisterSummary(root) {
    var box = qs('#shopRegisterSummary', root);
    if (!box) return;

    var fields = [
      ['업체명', qs('#wr_subject', root)],
      ['카테고리', qs('#ca_name', root)],
      ['지역', qs('#wr_2', root)],
      ['주소', qs('#wr_3', root)],
      ['전화', qs('#wr_4', root)],
      ['인스타그램', qs('#eottae_sns_instagram', root)],
      ['틱톡', qs('#eottae_sns_tiktok', root)],
      ['페이스북', qs('#eottae_sns_facebook', root)],
      ['네이버블로그', qs('#eottae_sns_naver_blog', root)],
      ['유튜브', qs('#eottae_sns_youtube', root)],
      ['영업시간', qs('#wr_6', root)],
      ['영업상태', qs('#wr_8', root)],
      ['SEO 타이틀', qs('#eottae_seo_title', root)],
      ['SEO 소개', qs('#eottae_seo_intro', root)],
      ['메타 디스크립션', qs('#eottae_seo_description', root)],
      ['포커스 키워드', qs('#eottae_seo_keyword', root)]
    ];

    var html = '<dl>';
    var hasValue = false;
    fields.forEach(function (item) {
      var el = item[1];
      if (!el) return;
      var val = (el.value || '').trim();
      if (val === '') return;
      hasValue = true;
      html += '<dt>' + item[0] + '</dt><dd>' + val.replace(/</g, '&lt;') + '</dd>';
    });
    html += '</dl>';

    box.innerHTML = hasValue ? html : '<p class="shop-register-page__summary-empty">입력 내용을 확인해 주세요.</p>';
  }

  function initShopDetailGallery() {
    var hero = qs('#shopDetailHeroImg');
    if (!hero) return;

    qsa('.shop-detail-page__thumb').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var src = btn.getAttribute('data-gallery-src');
        if (src) hero.src = src;
        qsa('.shop-detail-page__thumb').forEach(function (b) {
          b.classList.toggle('is-active', b === btn);
        });
      });
    });
  }

  function initPhotoPreview() {
    function applyPhotoPreview(input) {
      var slot = input.closest('.community-write-page__photo-slot, .shop-register-page__photo-slot');
      if (!slot) return;

      var preview = qs('.community-write-page__photo-preview, .shop-register-page__photo-preview', slot);
      if (!preview) return;

      if (!input.files || !input.files[0]) {
        return;
      }

      var reader = new FileReader();
      reader.onload = function (ev) {
        preview.src = ev.target.result;
        preview.hidden = false;
        slot.classList.add('has-preview');
      };
      reader.readAsDataURL(input.files[0]);
    }

    document.addEventListener('change', function (e) {
      var input = e.target;
      if (!input.matches('[data-photo-preview], [data-photo-input]')) return;
      applyPhotoPreview(input);
    });
  }

  /* Auth member type → mb_1 */
  function initMemberType() {
    var form = qs('#fregisterform');
    if (!form) return;
    var mb1 = qs('input[name="mb_1"]', form);
    if (!mb1) return;
    qsa('input[name="eottae_member_type"]', form).forEach(function (radio) {
      radio.addEventListener('change', function () {
        mb1.value = this.value === 'business' ? 'business' : 'member';
      });
    });
  }

  /* Shop register geocode (browser Geocoder — HTTP referrer 제한 API 키 대응) */
  var shopRegionRules = [
    { region: 'IT Park', keywords: ['it park', 'i.t. park', 'cebu it park', 'lahug'] },
    { region: '막탄', keywords: ['mactan', '막탄', 'mactan island'] },
    { region: '아얄라', keywords: ['ayala', 'cebu business park', '아얄라'] },
    { region: '만다우에', keywords: ['mandaue', '만다우에'] },
    { region: '라푸라푸', keywords: ['lapu-lapu', 'lapu lapu', 'lapulapu', '라푸라푸'] }
  ];

  function shopNormalizeAddress(address) {
    var query = (address || '').trim();
    if (!query) return '';
    if (!/cebu|세부/i.test(query)) {
      query += ', Cebu, Philippines';
    }
    return query;
  }

  function shopDetectRegionFromText(text) {
    var hay = (text || '').toLowerCase();
    if (!hay) return '';
    var i;
    for (i = 0; i < shopRegionRules.length; i++) {
      var rule = shopRegionRules[i];
      var k;
      for (k = 0; k < rule.keywords.length; k++) {
        if (hay.indexOf(rule.keywords[k]) !== -1) {
          return rule.region;
        }
      }
    }
    if (/cebu city|세부시티|세부|talamban|banilad|sm seaside|sugbu/.test(hay)) {
      return '세부시티';
    }
    return '';
  }

  window.shopDetectRegionFromText = shopDetectRegionFromText;

  function shopApplyRegionFromAddress(address, regionInput, regionDisplay, status) {
    var region = shopDetectRegionFromText(address);
    if (!region) {
      return false;
    }
    if (regionInput) {
      regionInput.value = region;
    }
    if (regionDisplay) {
      regionDisplay.textContent = '대표 지역: ' + region;
    }
    if (status) {
      status.textContent = '대표 지역이 설정되었습니다.';
    }
    return true;
  }

  function shopDetectRegionFromGeocodeResult(result) {
    if (!result) return '';
    var parts = [result.formatted_address || ''];
    if (result.address_components) {
      result.address_components.forEach(function (component) {
        if (component.long_name) parts.push(component.long_name);
        if (component.short_name) parts.push(component.short_name);
      });
    }
    return shopDetectRegionFromText(parts.join(' '));
  }

  function shopGeocodeErrorMessage(data) {
    var status = data && data.status ? data.status : '';
    if (status === 'REQUEST_DENIED') {
      return '좌표 API 권한을 확인하지 못했습니다. 대표 지역은 주소로 자동 설정되며, 좌표는 필요 시 직접 입력할 수 있습니다.';
    }
    if (status === 'OVER_QUERY_LIMIT') {
      return '좌표 API 사용량이 많습니다. 대표 지역은 주소로 자동 설정되며, 좌표는 필요 시 직접 입력할 수 있습니다.';
    }
    if (data && data.message) {
      return data.message;
    }
    return '좌표를 찾지 못했습니다. 주소를 확인해 주세요.';
  }

  function shopApplyGeocodeResult(data, latInput, lngInput, regionInput, regionDisplay, status) {
    if (latInput) latInput.value = data.lat;
    if (lngInput) lngInput.value = data.lng;
    if (regionInput && data.region) {
      regionInput.value = data.region;
      if (regionDisplay) {
        regionDisplay.textContent = '대표 지역: ' + data.region;
      }
    } else if (regionDisplay && regionInput && !regionInput.value) {
      regionDisplay.textContent = '대표 지역을 자동 분류하지 못했습니다. 주소에 Cebu City·Mactan 등 지역명을 포함해 주세요.';
    }
    if (status) {
      status.textContent = data.region
        ? '좌표와 대표 지역이 설정되었습니다.'
        : '좌표가 설정되었습니다.';
    }
    document.dispatchEvent(new CustomEvent('eottae:shop-coords-updated', {
      detail: { lat: data.lat, lng: data.lng, source: 'geocode' }
    }));
  }

  function shopGeocodeWithGoogle(address) {
    return new Promise(function (resolve) {
      if (!window.google || !google.maps || !google.maps.Geocoder) {
        resolve({ ok: false, status: 'MAPS_NOT_READY' });
        return;
      }
      var geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: shopNormalizeAddress(address) }, function (results, status) {
        if (status !== 'OK' || !results || !results[0] || !results[0].geometry) {
          resolve({ ok: false, status: status || 'ZERO_RESULTS' });
          return;
        }
        var loc = results[0].geometry.location;
        resolve({
          ok: true,
          lat: loc.lat(),
          lng: loc.lng(),
          address: results[0].formatted_address || address,
          region: shopDetectRegionFromGeocodeResult(results[0])
        });
      });
    });
  }

  function shopRunGeocode(address) {
    return shopGeocodeWithGoogle(address).then(function (data) {
      if (data.ok || data.status !== 'MAPS_NOT_READY') {
        return data;
      }
      return { ok: false, status: 'MAPS_NOT_READY' };
    });
  }

  function initShopGeocode() {
    var btn = qs('#shopGeocodeBtn');
    if (!btn) return;

    var addressInput = qs('#wr_3');
    var latInput = qs('#wr_9');
    var lngInput = qs('#wr_10');
    var regionInput = qs('#wr_2');
    var regionDisplay = qs('#shopRegionDisplay');
    var status = qs('#shopGeocodeStatus');
    var geocodeTimer = null;
    var lastGeocodedAddress = '';

    function runGeocode(trigger) {
      var address = addressInput ? addressInput.value.trim() : '';
      if (!address) {
        if (trigger === 'manual') {
          alert('주소를 입력해 주세요.');
        }
        return;
      }
      if (trigger === 'auto' && address === lastGeocodedAddress) {
        return;
      }

      btn.disabled = true;
      if (status) status.textContent = '주소를 확인하는 중…';

      shopRunGeocode(address)
        .then(function (data) {
          btn.disabled = false;
          if (!data.ok) {
            if (shopApplyRegionFromAddress(address, regionInput, regionDisplay, status)) {
              if (latInput && !latInput.value) latInput.value = '';
              if (lngInput && !lngInput.value) lngInput.value = '';
              return;
            }
            if (status) status.textContent = shopGeocodeErrorMessage(data);
            return;
          }
          lastGeocodedAddress = address;
          shopApplyGeocodeResult(data, latInput, lngInput, regionInput, regionDisplay, status);
        })
        .catch(function () {
          btn.disabled = false;
          if (status) status.textContent = '요청에 실패했습니다.';
        });
    }

    btn.addEventListener('click', function () {
      runGeocode('manual');
    });

    if (addressInput) {
      addressInput.addEventListener('blur', function () {
        if (addressInput.value.trim().length < 8) {
          return;
        }
        clearTimeout(geocodeTimer);
        geocodeTimer = setTimeout(function () {
          runGeocode('auto');
        }, 400);
      });
    }

    document.addEventListener('eottae:geocoder-ready', onMapsReady);
    document.addEventListener('eottae:shop-maps-ready', onMapsReady);

    function onMapsReady() {
      if (addressInput && addressInput.value.trim().length >= 8 && !lastGeocodedAddress) {
        runGeocode('auto');
      }
    }
  }

  function initShopCoordinatePicker() {
    var mapEl = qs('#shopCoordMap');
    var details = qs('.shop-register-page__advanced');
    if (!mapEl || !details) return;

    var latInput = qs('#wr_9');
    var lngInput = qs('#wr_10');
    var status = qs('#shopGeocodeStatus');
    var map = null;
    var marker = null;
    var initialized = false;

    function parseCoord(input, fallback) {
      var n = parseFloat(input && input.value ? input.value : '');
      return isFinite(n) ? n : fallback;
    }

    function getDefaultCenter() {
      return {
        lat: parseFloat(mapEl.getAttribute('data-default-lat')) || 10.313,
        lng: parseFloat(mapEl.getAttribute('data-default-lng')) || 123.9174
      };
    }

    function getDefaultZoom() {
      var z = parseInt(mapEl.getAttribute('data-default-zoom'), 10);
      return isFinite(z) ? z : 14;
    }

    function formatCoord(value) {
      return Number(value).toFixed(7);
    }

    function updateInputs(lat, lng, message) {
      if (latInput) latInput.value = formatCoord(lat);
      if (lngInput) lngInput.value = formatCoord(lng);
      if (status && message) status.textContent = message;
    }

    function setMarkerPosition(latLng) {
      if (!marker) {
        marker = new google.maps.Marker({
          map: map,
          position: latLng,
          draggable: true
        });
        marker.addListener('dragend', function () {
          var pos = marker.getPosition();
          updateInputs(pos.lat(), pos.lng(), '지도에서 위치를 선택했습니다.');
        });
      } else {
        marker.setPosition(latLng);
        marker.setMap(map);
      }
    }

    function initMap() {
      if (initialized || !window.google || !google.maps) return;
      initialized = true;

      var fallback = getDefaultCenter();
      var lat = parseCoord(latInput, fallback.lat);
      var lng = parseCoord(lngInput, fallback.lng);
      var hasCoords = latInput && latInput.value.trim() !== '' && lngInput && lngInput.value.trim() !== '';
      var center = hasCoords ? { lat: lat, lng: lng } : fallback;

      map = new google.maps.Map(mapEl, {
        center: center,
        zoom: hasCoords ? 16 : getDefaultZoom(),
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl: true
      });

      if (hasCoords) {
        setMarkerPosition(center);
      }

      map.addListener('click', function (event) {
        setMarkerPosition(event.latLng);
        updateInputs(event.latLng.lat(), event.latLng.lng(), '지도에서 위치를 선택했습니다.');
      });
    }

    function refreshMapSize() {
      if (!map) return;
      google.maps.event.trigger(map, 'resize');
      if (marker && marker.getPosition()) {
        map.panTo(marker.getPosition());
      } else {
        map.panTo(map.getCenter());
      }
    }

    function ensureMap() {
      if (window.google && google.maps) {
        initMap();
        window.setTimeout(refreshMapSize, 120);
      }
    }

    function syncFromInputs() {
      if (!map || !initialized) return;
      var lat = parseCoord(latInput, NaN);
      var lng = parseCoord(lngInput, NaN);
      if (!isFinite(lat) || !isFinite(lng)) return;
      var pos = { lat: lat, lng: lng };
      setMarkerPosition(pos);
      map.setCenter(pos);
      map.setZoom(16);
    }

    details.addEventListener('toggle', function () {
      if (details.open) {
        ensureMap();
      }
    });

    document.addEventListener('eottae:geocoder-ready', function () {
      if (details.open) ensureMap();
    });

    document.addEventListener('eottae:shop-coords-updated', function (event) {
      if (!event.detail || event.detail.lat == null || event.detail.lng == null) return;
      if (details.open) ensureMap();
      if (!map || !initialized) return;
      var pos = { lat: Number(event.detail.lat), lng: Number(event.detail.lng) };
      if (!isFinite(pos.lat) || !isFinite(pos.lng)) return;
      setMarkerPosition(pos);
      map.setCenter(pos);
      map.setZoom(16);
    });

    if (latInput) latInput.addEventListener('change', syncFromInputs);
    if (lngInput) lngInput.addEventListener('change', syncFromInputs);
  }

  function initAdCarousel() {
    var root = qs('[data-ad-carousel]');
    if (!root) return;

    var slides = qsa('[data-ad-slide]', root);
    if (slides.length < 2) return;

    var dots = qsa('[data-ad-dot]', root);
    var current = 0;
    var timer = null;
    var delay = 5500;

    function show(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach(function (slide, i) {
        slide.classList.toggle('is-active', i === current);
      });
      dots.forEach(function (dot, i) {
        dot.classList.toggle('is-active', i === current);
        dot.setAttribute('aria-selected', i === current ? 'true' : 'false');
      });
    }

    function next() {
      show(current + 1);
    }

    function start() {
      stop();
      timer = window.setInterval(next, delay);
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var idx = parseInt(dot.getAttribute('data-ad-dot'), 10);
        if (!isNaN(idx)) {
          show(idx);
          start();
        }
      });
    });

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    root.addEventListener('focusin', stop);
    root.addEventListener('focusout', start);

    show(0);
    start();
  }

  function initBusinessWriteSnippets() {
    qsa('[data-business-snippets]').forEach(function (root) {
      bindBusinessSnippetsPanel(root, {
        subjectInput: qs('#wr_subject'),
        contentInput: qs('#wr_content'),
      });
    });
  }

  function bindBusinessSnippetsPanel(root, options) {
    if (!root || !window.fetch) return;

    options = options || {};
    var toggle = qs('[data-snippets-toggle]', root);
    var panel = qs('[data-snippets-panel]', root);
    var listEl = qs('[data-snippets-list]', root);
    var emptyEl = qs('[data-snippets-empty]', root);
    var statusEl = qs('[data-snippets-status]', root);
    var subjectInput = options.subjectInput || qs('#wr_subject');
    var contentInput = options.contentInput || qs('#wr_content');
    var isDesktop = window.matchMedia('(min-width: 768px)').matches;

    function setStatus(msg, isError) {
      if (!statusEl) return;
      statusEl.textContent = msg || '';
      statusEl.classList.toggle('is-error', !!isError);
    }

    function escapeHtml(str) {
      return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function truncate(text, len) {
      text = String(text || '').replace(/\s+/g, ' ').trim();
      if (text.length <= len) return text;
      return text.slice(0, len) + '…';
    }

    function applySnippet(snippet, skipConfirm) {
      if (!snippet) return;
      var hasExisting = (subjectInput && subjectInput.value.trim()) || (contentInput && contentInput.value.trim());
      if (!skipConfirm && hasExisting && !window.confirm('현재 작성 중인 제목·내용을 불러온 문구로 바꿀까요?')) {
        return;
      }
      if (subjectInput) subjectInput.value = snippet.wr_subject || '';
      if (contentInput) contentInput.value = snippet.wr_content || '';
      setStatus('문구를 불러왔습니다.', false);
      if (panel && toggle && !isDesktop) {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
      }
    }

    function renderList(items) {
      if (!listEl) return;
      listEl.innerHTML = '';
      var hasItems = items && items.length;
      if (emptyEl) emptyEl.hidden = !!hasItems;
      if (!hasItems) return;

      items.forEach(function (item) {
        var li = document.createElement('li');
        li.className = 'business-snippets__item';
        li.innerHTML =
          '<button type="button" class="business-snippets__apply" data-snippets-apply>' +
          '<span class="business-snippets__label">' + escapeHtml(item.label || '홍보 문구') + '</span>' +
          '<span class="business-snippets__preview">' + escapeHtml(truncate(item.wr_content, isDesktop ? 120 : 60)) + '</span>' +
          '</button>' +
          '<button type="button" class="business-snippets__delete" data-snippets-delete>삭제</button>';
        li.querySelector('[data-snippets-apply]').addEventListener('click', function () {
          applySnippet(item, false);
        });
        li.querySelector('[data-snippets-delete]').addEventListener('click', function (e) {
          e.stopPropagation();
          if (!window.confirm('이 홍보 문구를 삭제할까요?')) return;
          deleteSnippet(item.snippet_id);
        });
        listEl.appendChild(li);
      });
    }

    function loadList() {
      return fetch('/proc/eottae-business-snippets.php?action=list', { credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '문구 목록을 불러오지 못했습니다.');
          }
          renderList(json.data || []);
        })
        .catch(function (err) {
          setStatus(err.message, true);
        });
    }

    function saveSnippet(label, subject, content, snippetId) {
      var fd = new FormData();
      fd.append('action', 'save');
      if (snippetId) fd.append('snippet_id', String(snippetId));
      fd.append('label', label || '');
      fd.append('wr_subject', subject || '');
      fd.append('wr_content', content || '');
      return fetch('/proc/eottae-business-snippets.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '저장에 실패했습니다.');
          }
          setStatus('자주 쓰는 문구로 저장했습니다.', false);
          return loadList();
        });
    }

    function deleteSnippet(id) {
      var fd = new FormData();
      fd.append('action', 'delete');
      fd.append('snippet_id', String(id));
      fetch('/proc/eottae-business-snippets.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '삭제에 실패했습니다.');
          }
          setStatus('문구를 삭제했습니다.', false);
          loadList();
        })
        .catch(function (err) {
          setStatus(err.message, true);
        });
    }

    function runAiGenerate(button) {
      var topic = window.prompt('홍보 주제 (선택, 예: 주말 할인, 신메뉴)', '') || '';
      button.disabled = true;
      setStatus('AI가 홍보 문구를 작성 중입니다...', false);
      var fd = new FormData();
      fd.append('topic', topic);
      return fetch('/proc/eottae-business-snippet-ai.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || 'AI 생성에 실패했습니다.');
          }
          return json.data || {};
        })
        .finally(function () {
          button.disabled = false;
        });
    }

    if (toggle && panel) {
      toggle.addEventListener('click', function () {
        var open = panel.hidden;
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) loadList();
      });

      if (isDesktop) {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        root.classList.add('is-desktop-open');
        loadList();
      }
    }

    var aiBtn = qs('[data-snippets-ai-generate]', root);
    if (aiBtn) {
      aiBtn.addEventListener('click', function () {
        runAiGenerate(aiBtn)
          .then(function (data) {
            if (window.confirm('생성된 문구를 글에 적용할까요?')) {
              applySnippet(data, true);
            }
            if (window.confirm('이 문구를 자주 쓰는 목록에 저장할까요?')) {
              return saveSnippet(data.label, data.wr_subject, data.wr_content);
            }
            setStatus('AI 문구를 생성했습니다.', false);
          })
          .catch(function (err) {
            setStatus(err.message, true);
          });
      });
    }

    var saveBtn = qs('[data-snippets-save-current]', root);
    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        var content = contentInput ? contentInput.value.trim() : '';
        if (!content) {
          alert('저장할 내용을 먼저 입력해 주세요.');
          if (contentInput) contentInput.focus();
          return;
        }
        var subject = subjectInput ? subjectInput.value.trim() : '';
        var label = window.prompt('문구 이름 (목록에 표시)', subject || '홍보 문구');
        if (label === null) return;
        saveBtn.disabled = true;
        saveSnippet(label, subject, content)
          .catch(function (err) { setStatus(err.message, true); })
          .finally(function () { saveBtn.disabled = false; });
      });
    }
  }

  function initBusinessSnippetsManager() {
    var root = qs('[data-business-snippets-manager]');
    if (!root || !window.fetch) return;

    var idInput = qs('#business_snippet_id', root);
    var labelInput = qs('#business_snippet_label', root);
    var subjectInput = qs('#business_snippet_subject', root);
    var contentInput = qs('#business_snippet_content', root);
    var statusEl = qs('[data-manager-status]', root);
    var listEl = qs('[data-manager-list]', root);
    var emptyEl = qs('[data-manager-empty]', root);
    var writeLink = qs('[data-manager-write-link]', root);
    var communityWriteBase = writeLink ? writeLink.getAttribute('href') : '';

    function setStatus(msg, isError) {
      if (!statusEl) return;
      statusEl.textContent = msg || '';
      statusEl.classList.toggle('is-error', !!isError);
    }

    function escapeHtml(str) {
      return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function resetForm() {
      if (idInput) idInput.value = '0';
      if (labelInput) labelInput.value = '';
      if (subjectInput) subjectInput.value = '';
      if (contentInput) contentInput.value = '';
      if (writeLink) writeLink.setAttribute('href', communityWriteBase);
    }

    function fillForm(item) {
      if (idInput) idInput.value = String(item.snippet_id || 0);
      if (labelInput) labelInput.value = item.label || '';
      if (subjectInput) subjectInput.value = item.wr_subject || '';
      if (contentInput) contentInput.value = item.wr_content || '';
      if (writeLink && item.snippet_id) {
        writeLink.setAttribute('href', communityWriteBase + (communityWriteBase.indexOf('?') >= 0 ? '&' : '?') + 'snippet_id=' + item.snippet_id);
      }
    }

    function renderList(items) {
      if (!listEl) return;
      listEl.innerHTML = '';
      var hasItems = items && items.length;
      if (emptyEl) emptyEl.hidden = !!hasItems;
      if (!hasItems) return;

      items.forEach(function (item) {
        var li = document.createElement('li');
        li.className = 'business-snippets-manager__item';
        li.innerHTML =
          '<button type="button" class="business-snippets-manager__pick" data-manager-pick>' +
          '<strong>' + escapeHtml(item.label || '홍보 문구') + '</strong>' +
          '<span>' + escapeHtml(item.wr_subject || '') + '</span>' +
          '</button>' +
          '<div class="business-snippets-manager__item-actions">' +
          '<a href="' + escapeHtml(communityWriteBase + (communityWriteBase.indexOf('?') >= 0 ? '&' : '?') + 'snippet_id=' + item.snippet_id) + '" class="business-snippets__btn business-snippets__btn--link">글쓰기</a>' +
          '<button type="button" class="business-snippets__delete" data-manager-delete>삭제</button>' +
          '</div>';
        li.querySelector('[data-manager-pick]').addEventListener('click', function () {
          fillForm(item);
          setStatus('문구를 불러왔습니다. 수정 후 저장할 수 있습니다.', false);
        });
        li.querySelector('[data-manager-delete]').addEventListener('click', function () {
          if (!window.confirm('이 홍보 문구를 삭제할까요?')) return;
          deleteSnippet(item.snippet_id);
        });
        listEl.appendChild(li);
      });
    }

    function loadList() {
      return fetch('/proc/eottae-business-snippets.php?action=list', { credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '문구 목록을 불러오지 못했습니다.');
          }
          renderList(json.data || []);
        })
        .catch(function (err) {
          setStatus(err.message, true);
        });
    }

    function saveCurrent() {
      var content = contentInput ? contentInput.value.trim() : '';
      if (!content) {
        alert('내용을 입력해 주세요.');
        if (contentInput) contentInput.focus();
        return Promise.resolve();
      }
      var fd = new FormData();
      fd.append('action', 'save');
      fd.append('snippet_id', idInput ? idInput.value : '0');
      fd.append('label', labelInput ? labelInput.value : '');
      fd.append('wr_subject', subjectInput ? subjectInput.value : '');
      fd.append('wr_content', content);
      return fetch('/proc/eottae-business-snippets.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '저장에 실패했습니다.');
          }
          if (json.data) fillForm(json.data);
          setStatus('홍보 문구를 저장했습니다.', false);
          return loadList();
        });
    }

    function deleteSnippet(id) {
      var fd = new FormData();
      fd.append('action', 'delete');
      fd.append('snippet_id', String(id));
      fetch('/proc/eottae-business-snippets.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '삭제에 실패했습니다.');
          }
          if (idInput && String(idInput.value) === String(id)) resetForm();
          setStatus('문구를 삭제했습니다.', false);
          loadList();
        })
        .catch(function (err) {
          setStatus(err.message, true);
        });
    }

    var aiBtn = qs('[data-manager-ai-generate]', root);
    if (aiBtn) {
      aiBtn.addEventListener('click', function () {
        var topic = window.prompt('홍보 주제 (선택, 예: 주말 할인, 신메뉴)', '') || '';
        aiBtn.disabled = true;
        setStatus('AI가 홍보 문구를 작성 중입니다...', false);
        var fd = new FormData();
        fd.append('topic', topic);
        fetch('/proc/eottae-business-snippet-ai.php', { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (res) { return parseJsonResponse(res); })
          .then(function (json) {
            if (!json || !json.success) {
              throw new Error((json && json.message) || 'AI 생성에 실패했습니다.');
            }
            var data = json.data || {};
            fillForm(data);
            if (idInput) idInput.value = '0';
            setStatus('AI 문구를 생성했습니다. 확인 후 저장해 주세요.', false);
          })
          .catch(function (err) {
            setStatus(err.message, true);
          })
          .finally(function () {
            aiBtn.disabled = false;
          });
      });
    }

    var saveBtn = qs('[data-manager-save]', root);
    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        saveBtn.disabled = true;
        saveCurrent()
          .catch(function (err) { setStatus(err.message, true); })
          .finally(function () { saveBtn.disabled = false; });
      });
    }

    var resetBtn = qs('[data-manager-reset]', root);
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        resetForm();
        setStatus('새 문구 작성을 시작합니다.', false);
      });
    }

    loadList();
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('eottae-page');
    initShopRegisterWizard();
    initShopGeocode();
    initShopCoordinatePicker();
    initMemberType();
    initReviewModal();
    initReviewReply();
    initShopSave();
    initShopDetailGallery();
    initPhotoPreview();
    initAdCarousel();
    initBusinessWriteSnippets();
    initBusinessSnippetsManager();
  });

  function initReviewModal() {
    var modal = qs('#eottaeReviewModal');
    if (!modal) return;

    var form = qs('#eottaeReviewForm', modal);
    var ratingInput = qs('#eottaeReviewRating', modal);
    var starBtns = qsa('.review-write-form__star', modal);

    function setRating(value) {
      if (ratingInput) ratingInput.value = String(value);
      starBtns.forEach(function (btn) {
        var star = parseInt(btn.getAttribute('data-star'), 10);
        btn.classList.toggle('is-active', star <= value);
      });
    }

    setRating(ratingInput ? parseInt(ratingInput.value, 10) || 5 : 5);

    starBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setRating(parseInt(btn.getAttribute('data-star'), 10));
      });
    });

    document.addEventListener('click', function (e) {
      if (e.target.closest('[data-review-open]')) {
        e.preventDefault();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
      }
      if (e.target.closest('[data-review-close]') || e.target === modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
      }
    });

    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(form);
        var submitBtn = qs('.review-write-form__submit', form);
        if (submitBtn) submitBtn.disabled = true;

        fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (res) { return parseJsonResponse(res); })
          .then(function (data) {
            if (data.success) {
              if (data.redirect_url) {
                window.location.href = data.redirect_url;
              } else {
                window.location.reload();
              }
              return;
            }
            alert(data.message || '리뷰 등록에 실패했습니다.');
            if (submitBtn) submitBtn.disabled = false;
          })
          .catch(function () {
            alert('네트워크 오류가 발생했습니다.');
            if (submitBtn) submitBtn.disabled = false;
          });
      });
    }
  }

  function initReviewReply() {
    document.addEventListener('submit', function (e) {
      var form = e.target.closest('[data-review-reply-form]');
      if (!form) return;
      e.preventDefault();

      var submitBtn = qs('.review-card__reply-submit', form);
      if (submitBtn) submitBtn.disabled = true;

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
      })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (data) {
          if (data.success) {
            if (data.redirect_url) {
              window.location.href = data.redirect_url;
            } else {
              window.location.reload();
            }
            return;
          }
          alert(data.message || '답변 등록에 실패했습니다.');
          if (submitBtn) submitBtn.disabled = false;
        })
        .catch(function () {
          alert('네트워크 오류가 발생했습니다.');
          if (submitBtn) submitBtn.disabled = false;
        });
    });
  }

  function initShopSave() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-shop-save]');
      if (!btn) return;
      e.preventDefault();

      var shopId = btn.getAttribute('data-shop-id');
      var token = btn.getAttribute('data-save-token') || '';
      var fd = new FormData();
      fd.append('shop_wr_id', shopId);
      fd.append('eottae_shop_save_token', token);

      btn.disabled = true;
      fetch('/proc/eottae-shop-save.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (data) {
          btn.disabled = false;
          if (!data.success) {
            var msg = data.message || '처리에 실패했습니다.';
            if (msg.indexOf('로그인') !== -1) {
              window.location.href = btn.getAttribute('data-login-url') || '/bbs/login.php';
              return;
            }
            alert(msg);
            return;
          }
          var saved = !!data.saved;
          btn.setAttribute('data-saved', saved ? '1' : '0');
          btn.textContent = saved ? '찜 해제' : '찜하기';
          btn.classList.toggle('is-saved', saved);
        })
        .catch(function () {
          btn.disabled = false;
          alert('네트워크 오류가 발생했습니다.');
        });
    });
  }
})();
