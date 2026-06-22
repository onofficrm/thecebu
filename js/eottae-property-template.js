(function () {
  'use strict';

  var root = document.getElementById('sebuPropertyTemplate');
  if (!root) {
    return;
  }

  var LABELS = {
    property_type: {
      condo: '콘도',
      house: '하우스',
      villa: '빌라',
      office: '오피스',
      commercial: '상가',
      land: '토지',
      other: '기타'
    },
    deal_type: {
      month: '월세',
      sale: '매매',
      short: '단기임대',
      long: '장기임대'
    },
    furnishing: {
      full: '풀퍼니처',
      semi: '세미퍼니처',
      unfurnished: '비가구',
      nego: '협의'
    }
  };

  var REQUIRED = ['property_type', 'deal_type', 'region', 'price', 'description', 'contact'];

  function fieldEls() {
    return root.querySelectorAll('[data-property-field]');
  }

  function getData() {
    var data = {};
    fieldEls().forEach(function (el) {
      var key = el.getAttribute('data-property-field');
      if (!key) {
        return;
      }
      data[key] = (el.value || '').trim();
    });
    return data;
  }

  function labelOf(group, key) {
    if (!key || !LABELS[group] || !LABELS[group][key]) {
      return '';
    }
    return LABELS[group][key];
  }

  function isEmpty(val) {
    return !val || String(val).trim() === '';
  }

  function validateRequired(data) {
    var i;
    for (i = 0; i < REQUIRED.length; i += 1) {
      if (isEmpty(data[REQUIRED[i]])) {
        return false;
      }
    }
    return true;
  }

  function buildTitle(data) {
    var region = data.region;
    var dealLabel = labelOf('deal_type', data.deal_type);
    var typeLabel = labelOf('property_type', data.property_type);
    var price = data.price;
    var middle = dealLabel + ' ' + typeLabel;

    if (!isEmpty(data.building_name)) {
      middle = data.building_name + ' ' + middle;
    }

    return '[' + region + '] ' + middle + ' / ' + price;
  }

  function line(label, value) {
    if (isEmpty(value)) {
      return '';
    }
    return label + ' ' + value;
  }

  function block(title, lines) {
    var filtered = lines.filter(function (row) {
      return row !== '';
    });
    if (!filtered.length) {
      return '';
    }
    return title + '\n\n' + filtered.join('\n') + '\n';
  }

  function buildBody(data) {
    var parts = [];

    var infoLines = [
      line('매물종류:', labelOf('property_type', data.property_type)),
      line('거래유형:', labelOf('deal_type', data.deal_type)),
      line('지역:', data.region),
      line('매물명/건물명:', data.building_name),
      line('가격:', data.price)
    ];
    parts.push(block('[부동산 매물정보]', infoLines));

    var detailLines = [
      line('방 개수:', data.rooms),
      line('화장실 개수:', data.bathrooms),
      line('가구 여부:', labelOf('furnishing', data.furnishing)),
      line('입주 가능일:', data.move_in)
    ];
    var detailBlock = block('[매물 상세정보]', detailLines);
    if (detailBlock) {
      parts.push(detailBlock);
    }

    if (!isEmpty(data.description)) {
      parts.push('[매물 설명]\n' + data.description + '\n');
    }
    if (!isEmpty(data.nearby)) {
      parts.push('[주변 정보]\n' + data.nearby + '\n');
    }

    var contactLines = [
      line('연락처:', data.contact),
      line('카카오톡 ID:', data.kakao_id)
    ];
    parts.push(block('[연락정보]', contactLines));

    if (!isEmpty(data.extra)) {
      parts.push('[기타 안내사항]\n' + data.extra + '\n');
    }

    return parts.join('\n').trim();
  }

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function plainToEditorHtml(text) {
    return text.split('\n').map(function (row) {
      if (row === '') {
        return '<p><br></p>';
      }
      return '<p>' + escapeHtml(row) + '</p>';
    }).join('');
  }

  function getEditorPlain(fieldId) {
    var el = document.getElementById(fieldId);
    if (!el) {
      return '';
    }
    if (typeof oEditors !== 'undefined' && oEditors.getById && oEditors.getById[fieldId]) {
      try {
        var ir = oEditors.getById[fieldId].getIR() || '';
        var tmp = document.createElement('div');
        tmp.innerHTML = ir;
        return (tmp.textContent || tmp.innerText || '').replace(/\u00a0/g, ' ').trim();
      } catch (e) {
        return (el.value || '').trim();
      }
    }
    return (el.value || '').trim();
  }

  function setEditorContent(fieldId, text) {
    var el = document.getElementById(fieldId);
    if (!el) {
      return;
    }
    var html = plainToEditorHtml(text);
    if (typeof oEditors !== 'undefined' && oEditors.getById && oEditors.getById[fieldId]) {
      try {
        oEditors.getById[fieldId].exec('SET_CONTENTS', [html]);
        oEditors.getById[fieldId].exec('UPDATE_CONTENTS_FIELD', []);
        return;
      } catch (e) {
        /* fallback */
      }
    }
    el.value = text;
    el.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function showError(show) {
    var err = document.getElementById('sebuPropertyTemplateError');
    if (!err) {
      return;
    }
    if (show) {
      err.hidden = false;
      err.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
      err.hidden = true;
    }
  }

  function syncEstateMetaFields(data) {
    var wr1 = document.getElementById('wr_1');
    var wr2 = document.getElementById('wr_2');
    var completed = document.getElementById('estate_deal_completed_checkbox');
    var addressInput = root.querySelector('[name="wr_4"]');
    var latInput = root.querySelector('[name="wr_5"]');
    var lngInput = root.querySelector('[name="wr_6"]');
    if (wr1) {
      wr1.value = (data && data.region) ? data.region : '';
    }
    if (wr2) {
      var status = (data && data.estate_deal_status) ? data.estate_deal_status : 'trading';
      wr2.value = status === 'completed' ? 'completed' : 'trading';
      if (completed) {
        completed.checked = wr2.value === 'completed';
      }
    }
    if (addressInput && data && typeof data.address !== 'undefined') {
      addressInput.value = data.address || '';
    }
    if (latInput && data && typeof data.lat !== 'undefined') {
      latInput.value = data.lat || '';
    }
    if (lngInput && data && typeof data.lng !== 'undefined') {
      lngInput.value = data.lng || '';
    }
    syncEstateTemplateJson(data);
  }

  function mergeLocationData(data) {
    data = data || {};
    var addressInput = root.querySelector('[name="wr_4"]');
    var latInput = root.querySelector('[name="wr_5"]');
    var lngInput = root.querySelector('[name="wr_6"]');

    if (addressInput) {
      data.address = (addressInput.value || '').trim();
    }
    if (latInput) {
      data.lat = (latInput.value || '').trim();
    }
    if (lngInput) {
      data.lng = (lngInput.value || '').trim();
    }

    return data;
  }

  function syncEstateTemplateJson(data) {
    var hidden = document.getElementById('estate_template_json');
    if (!hidden) {
      return;
    }
    try {
      hidden.value = JSON.stringify(data || {});
    } catch (e) {
      hidden.value = '';
    }
  }

  function setFieldValue(el, value) {
    if (!el) {
      return;
    }
    var val = value == null ? '' : String(value);
    if (el.tagName === 'SELECT') {
      el.value = val;
      return;
    }
    el.value = val;
  }

  function applyDataToFields(data) {
    if (!data || typeof data !== 'object') {
      return;
    }
    fieldEls().forEach(function (el) {
      var key = el.getAttribute('data-property-field');
      if (!key || typeof data[key] === 'undefined') {
        return;
      }
      setFieldValue(el, data[key]);
    });
    syncEstateMetaFields(getData());
  }

  function applyTemplateToEditor(options) {
    options = options || {};
    var data = mergeLocationData(getData());
    if (!validateRequired(data)) {
      if (!options.silent) {
        showError(true);
      }
      return false;
    }
    if (!options.silent) {
      showError(false);
    }

    syncEstateMetaFields(data);

    var subjectEl = document.getElementById('wr_subject');
    var contentId = 'wr_content';
    var title = buildTitle(data);
    var body = buildBody(data);

    if (subjectEl) {
      subjectEl.value = title;
      subjectEl.dispatchEvent(new Event('input', { bubbles: true }));
    }

    var existing = getEditorPlain(contentId);
    if (options.force || !existing || existing.indexOf('[부동산 매물정보]') === -1) {
      setEditorContent(contentId, body);
      return true;
    }
    if (!options.silent && options.confirmReplace !== false) {
      if (window.confirm('기존 본문 내용이 있습니다. 템플릿 내용으로 교체하시겠습니까?')) {
        setEditorContent(contentId, body);
      }
      return true;
    }
    return true;
  }

  function resetTemplateFields() {
    fieldEls().forEach(function (el) {
      if (el.tagName === 'SELECT') {
        el.selectedIndex = 0;
      } else {
        el.value = '';
      }
    });
    syncEstateMetaFields({ region: '', estate_deal_status: 'trading' });
    showError(false);
  }

  fieldEls().forEach(function (el) {
    el.addEventListener('change', function () {
      syncEstateMetaFields(getData());
    });
    el.addEventListener('input', function () {
      syncEstateMetaFields(getData());
    });
  });

  var completedCheckbox = document.getElementById('estate_deal_completed_checkbox');
  var dealSelect = document.getElementById('estate_deal_status');
  if (completedCheckbox && dealSelect) {
    completedCheckbox.addEventListener('change', function () {
      dealSelect.value = completedCheckbox.checked ? 'completed' : 'trading';
      syncEstateMetaFields(mergeLocationData(getData()));
    });
    dealSelect.addEventListener('change', function () {
      completedCheckbox.checked = dealSelect.value === 'completed';
      syncEstateMetaFields(mergeLocationData(getData()));
    });
  }

  ['wr_4', 'wr_5', 'wr_6'].forEach(function (name) {
    var el = root.querySelector('[name="' + name + '"]');
    if (!el) return;
    el.addEventListener('input', function () {
      syncEstateTemplateJson(mergeLocationData(getData()));
    });
    el.addEventListener('change', function () {
      syncEstateTemplateJson(mergeLocationData(getData()));
    });
  });

  if (window.__SEBU_ESTATE_TEMPLATE_INITIAL__) {
    applyDataToFields(window.__SEBU_ESTATE_TEMPLATE_INITIAL__);
  } else {
    var jsonEl = document.getElementById('estate_template_json');
    if (jsonEl && jsonEl.value) {
      try {
        applyDataToFields(JSON.parse(jsonEl.value));
      } catch (e) {
        /* ignore */
      }
    }
  }

  syncEstateMetaFields(mergeLocationData(getData()));

  document.getElementById('sebuPropertyTemplateApply').addEventListener('click', function () {
    if (!applyTemplateToEditor({ confirmReplace: true })) {
      return;
    }
    var subjectEl = document.getElementById('wr_subject');
    if (subjectEl && window.matchMedia && window.matchMedia('(max-width: 767px)').matches) {
      subjectEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });

  document.getElementById('sebuPropertyTemplateReset').addEventListener('click', function () {
    if (window.confirm('템플릿 입력 내용을 모두 지울까요?')) {
      resetTemplateFields();
    }
  });

  var writeForm = root.closest('form');
  if (writeForm) {
    writeForm.addEventListener('submit', function () {
      syncEstateMetaFields(mergeLocationData(getData()));
      applyTemplateToEditor({ silent: true, force: false, confirmReplace: false });
      if (typeof oEditors !== 'undefined' && oEditors.getById && oEditors.getById.wr_content) {
        try {
          oEditors.getById.wr_content.exec('UPDATE_CONTENTS_FIELD', []);
        } catch (e) {
          /* ignore */
        }
      }
    });
  }

  window.sebuPropertyTemplateApplyBeforeSubmit = function () {
    syncEstateMetaFields(mergeLocationData(getData()));
    return applyTemplateToEditor({ silent: true, force: false, confirmReplace: false });
  };
})();
