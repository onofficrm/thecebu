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

  function resetTemplateFields() {
    fieldEls().forEach(function (el) {
      if (el.tagName === 'SELECT') {
        el.selectedIndex = 0;
      } else {
        el.value = '';
      }
    });
    showError(false);
  }

  document.getElementById('sebuPropertyTemplateApply').addEventListener('click', function () {
    var data = getData();
    if (!validateRequired(data)) {
      showError(true);
      return;
    }
    showError(false);

    var subjectEl = document.getElementById('wr_subject');
    var contentId = 'wr_content';
    var title = buildTitle(data);
    var body = buildBody(data);

    if (subjectEl) {
      subjectEl.value = title;
      subjectEl.dispatchEvent(new Event('input', { bubbles: true }));
    }

    var existing = getEditorPlain(contentId);
    var applyBody = function () {
      setEditorContent(contentId, body);
    };

    if (existing) {
      if (window.confirm('기존 본문 내용이 있습니다. 템플릿 내용으로 교체하시겠습니까?')) {
        applyBody();
      }
    } else {
      applyBody();
    }
  });

  document.getElementById('sebuPropertyTemplateReset').addEventListener('click', function () {
    if (window.confirm('템플릿 입력 내용을 모두 지울까요?')) {
      resetTemplateFields();
    }
  });
})();
