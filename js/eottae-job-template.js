(function () {
  'use strict';

  var root = document.getElementById('sebuJobTemplate');
  if (!root) {
    return;
  }

  var LABELS = {
    work_type: {
      fulltime: '정규직',
      contract: '계약직',
      parttime: '파트타임',
      part: '아르바이트',
      freelance: '프리랜서',
      other: '기타'
    },
    pay_type: {
      month: '월급',
      week: '주급',
      day: '일급',
      hour: '시급',
      nego: '협의'
    },
    gender: {
      any: '무관',
      male: '남성',
      female: '여성'
    },
    career: {
      any: '무관',
      new: '신입',
      prefer: '경력자 우대',
      required: '경력 필수'
    },
    language: {
      any: '무관',
      ko: '한국어',
      en: '영어',
      ceb: '세부아노',
      tl: '타갈로그어',
      ko_en: '한국어+영어',
      other: '기타'
    }
  };

  var REQUIRED = ['company', 'job_type', 'headcount', 'region', 'salary', 'work_desc', 'apply_method', 'contact'];

  function fieldEls() {
    return root.querySelectorAll('[data-job-field]');
  }

  function getData() {
    var data = {};
    fieldEls().forEach(function (el) {
      var key = el.getAttribute('data-job-field');
      if (!key) {
        return;
      }
      data[key] = (el.value || '').trim();
    });
    return data;
  }

  function labelOf(group, key) {
    if (!key || key === 'any' || !LABELS[group] || !LABELS[group][key]) {
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
    var jobType = data.job_type;
    var salaryPart = data.salary;

    if (data.pay_type === 'nego' && salaryPart.toLowerCase().indexOf('협의') === -1) {
      salaryPart = '급여 협의';
    } else if (data.pay_type && data.pay_type !== 'nego') {
      var payLabel = labelOf('pay_type', data.pay_type);
      if (payLabel && salaryPart.indexOf(payLabel) === -1) {
        salaryPart = payLabel + ' ' + salaryPart;
      }
    }

    return '[' + region + '] ' + jobType + ' 모집 / ' + salaryPart;
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
      line('업체명:', data.company),
      line('모집직종:', data.job_type),
      line('모집인원:', data.headcount),
      line('근무지역:', data.region),
      line('근무형태:', labelOf('work_type', data.work_type)),
      line('근무시간:', data.work_hours),
      line('급여:', data.salary),
      line('급여형태:', labelOf('pay_type', data.pay_type))
    ];
    parts.push(block('[구인정보]', infoLines));

    if (!isEmpty(data.work_desc)) {
      parts.push('[업무내용]\n' + data.work_desc + '\n');
    }

    var qualLines = [
      line('나이:', data.age),
      line('성별:', labelOf('gender', data.gender)),
      line('경력:', labelOf('career', data.career)),
      line('언어조건:', labelOf('language', data.language))
    ];
    if (!isEmpty(data.qualification)) {
      qualLines.push('');
      qualLines.push(data.qualification);
    }
    var qualBlock = block('[지원자격]', qualLines);
    if (qualBlock) {
      parts.push(qualBlock);
    }

    if (!isEmpty(data.benefits)) {
      parts.push('[복리후생]\n' + data.benefits + '\n');
    }
    if (!isEmpty(data.preferred)) {
      parts.push('[우대사항]\n' + data.preferred + '\n');
    }

    var applyLines = [
      line('지원방법:', data.apply_method),
      line('연락처:', data.contact),
      line('카카오톡 ID:', data.kakao_id),
      line('이메일:', data.email)
    ];
    parts.push(block('[지원방법]', applyLines));

    if (!isEmpty(data.deadline)) {
      parts.push('[마감일]\n' + data.deadline + '\n');
    }
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
    var err = document.getElementById('sebuJobTemplateError');
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

  function showAiStatus(message, type) {
    var status = document.getElementById('sebuJobTemplateAiStatus');
    if (!status) {
      return;
    }
    status.hidden = !message;
    status.textContent = message || '';
    status.classList.toggle('is-error', type === 'error');
    status.classList.toggle('is-success', type === 'success');
  }

  function syncJobMetaFields(data) {
    var wr1 = document.getElementById('wr_1');
    var wr2 = document.getElementById('wr_2');
    if (wr1) {
      wr1.value = (data && data.region) ? data.region : '';
    }
    if (wr2) {
      var status = (data && data.job_recruit_status) ? data.job_recruit_status : 'recruiting';
      wr2.value = status === 'completed' ? 'completed' : 'recruiting';
    }
    syncJobTemplateJson(data);
  }

  function syncJobTemplateJson(data) {
    var hidden = document.getElementById('job_template_json');
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
      if (el.value !== val && val !== '') {
        var opt = document.createElement('option');
        opt.value = val;
        opt.textContent = val;
        el.appendChild(opt);
        el.value = val;
      }
      return;
    }
    el.value = val;
  }

  function applyDataToFields(data) {
    if (!data || typeof data !== 'object') {
      return;
    }
    fieldEls().forEach(function (el) {
      var key = el.getAttribute('data-job-field');
      if (!key || typeof data[key] === 'undefined') {
        return;
      }
      setFieldValue(el, data[key]);
    });
    syncJobMetaFields(getData());
  }

  function initJobShopPicker() {
    var picker = root.querySelector('[data-job-shop-picker]');
    if (!picker) {
      return;
    }

    var searchUrl = picker.getAttribute('data-search-url') || '/proc/eottae-review-shop-search.php';
    var searchInput = document.getElementById('jobShopPickerSearch');
    var resultsEl = document.getElementById('jobShopPickerResults');
    var emptyEl = document.getElementById('jobShopPickerEmpty');
    var hintEl = document.getElementById('jobShopPickerHint');
    var selectedWrap = document.getElementById('jobShopPickerSelected');
    var selectedName = document.getElementById('jobShopPickerSelectedName');
    var selectedMeta = document.getElementById('jobShopPickerSelectedMeta');
    var searchWrap = document.getElementById('jobShopPickerSearchWrap');
    var clearBtn = document.getElementById('jobShopPickerClear');
    var wrIdInput = document.getElementById('eottae_job_shop_wr_id');
    var boInput = document.getElementById('eottae_job_shop_bo_table');
    var timer = null;

    function setSelected(shop) {
      var hasShop = !!(shop && shop.wr_id);
      var meta = [];
      if (hasShop && shop.board_label) meta.push(shop.board_label);
      if (hasShop && shop.region) meta.push(shop.region);

      if (wrIdInput) wrIdInput.value = hasShop ? String(parseInt(shop.wr_id, 10) || '') : '';
      if (boInput) boInput.value = hasShop ? String(shop.bo_table || '') : '';
      if (selectedWrap) {
        selectedWrap.hidden = !hasShop;
        selectedWrap.classList.toggle('is-empty', !hasShop);
      }
      if (searchWrap) searchWrap.hidden = hasShop;
      if (selectedName) selectedName.textContent = hasShop ? String(shop.name || '') : '';
      if (selectedMeta) selectedMeta.textContent = meta.join(' · ');
      if (resultsEl) {
        resultsEl.hidden = true;
        resultsEl.innerHTML = '';
      }
      if (emptyEl) emptyEl.hidden = true;
      if (searchInput && hasShop) searchInput.value = '';

      var companyEl = root.querySelector('[data-job-field="company"]');
      if (hasShop && companyEl && !companyEl.value.trim()) {
        companyEl.value = String(shop.name || '');
        syncJobMetaFields(getData());
      }
    }

    function renderResults(items) {
      if (!resultsEl) return;
      if (!items || !items.length) {
        resultsEl.hidden = true;
        resultsEl.innerHTML = '';
        if (emptyEl) emptyEl.hidden = false;
        if (hintEl) hintEl.textContent = '검색 결과가 없습니다. 업체 연결 없이 작성할 수 있습니다.';
        return;
      }

      if (emptyEl) emptyEl.hidden = true;
      if (hintEl) hintEl.textContent = items.length + '개 업체';
      resultsEl.innerHTML = items.map(function (shop) {
        var meta = [];
        if (shop.board_label) meta.push(shop.board_label);
        if (shop.region) meta.push(shop.region);
        var thumb = shop.thumb_url
          ? '<img src="' + escapeHtml(shop.thumb_url) + '" alt="" class="sebu-job-shop-picker__result-thumb" loading="lazy" decoding="async">'
          : '<span class="sebu-job-shop-picker__result-thumb sebu-job-shop-picker__result-thumb--empty" aria-hidden="true"></span>';
        return ''
          + '<button type="button" class="sebu-job-shop-picker__result" role="option"'
          + ' data-bo-table="' + escapeHtml(shop.bo_table || '') + '"'
          + ' data-wr-id="' + escapeHtml(shop.wr_id || '') + '"'
          + ' data-name="' + escapeHtml(shop.name || '') + '"'
          + ' data-region="' + escapeHtml(shop.region || '') + '"'
          + ' data-board-label="' + escapeHtml(shop.board_label || '') + '">'
          + thumb
          + '<span class="sebu-job-shop-picker__result-body">'
          + '<strong>' + escapeHtml(shop.name || '') + '</strong>'
          + (meta.length ? '<span>' + escapeHtml(meta.join(' · ')) + '</span>' : '')
          + (shop.address ? '<em>' + escapeHtml(shop.address) + '</em>' : '')
          + '</span>'
          + '</button>';
      }).join('');
      resultsEl.hidden = false;
    }

    function fetchShops(keyword) {
      var url = searchUrl + '?limit=30&q=' + encodeURIComponent(keyword);
      return fetch(url, { credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          return data && data.success ? (data.items || []) : [];
        })
        .catch(function () { return []; });
    }

    function scheduleSearch() {
      if (!searchInput) return;
      clearTimeout(timer);
      timer = window.setTimeout(function () {
        var keyword = (searchInput.value || '').trim();
        if (keyword.length < 1) {
          if (resultsEl) {
            resultsEl.hidden = true;
            resultsEl.innerHTML = '';
          }
          if (emptyEl) emptyEl.hidden = true;
          if (hintEl) hintEl.textContent = '업체명을 검색해 공고와 연결할 수 있습니다.';
          return;
        }
        fetchShops(keyword).then(renderResults);
      }, 220);
    }

    if (searchInput) {
      searchInput.addEventListener('input', scheduleSearch);
    }
    if (resultsEl) {
      resultsEl.addEventListener('click', function (event) {
        var btn = event.target.closest('.sebu-job-shop-picker__result');
        if (!btn) return;
        setSelected({
          wr_id: btn.getAttribute('data-wr-id'),
          bo_table: btn.getAttribute('data-bo-table'),
          name: btn.getAttribute('data-name'),
          region: btn.getAttribute('data-region'),
          board_label: btn.getAttribute('data-board-label')
        });
      });
    }
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        setSelected(null);
        if (searchInput) searchInput.focus();
      });
    }
  }

  function applyTemplateToEditor(options) {
    options = options || {};
    var data = getData();
    if (!validateRequired(data)) {
      if (!options.silent) {
        showError(true);
      }
      return false;
    }
    if (!options.silent) {
      showError(false);
    }

    syncJobMetaFields(data);

    var subjectEl = document.getElementById('wr_subject');
    var contentId = 'wr_content';
    var title = buildTitle(data);
    var body = buildBody(data);

    if (subjectEl) {
      subjectEl.value = title;
      subjectEl.dispatchEvent(new Event('input', { bubbles: true }));
    }

    var existing = getEditorPlain(contentId);
    if (options.force || !existing || existing.indexOf('[구인정보]') === -1) {
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
    syncJobMetaFields({ region: '', job_recruit_status: 'recruiting' });
    showError(false);
    showAiStatus('', '');
  }

  function requestAiDraft() {
    var aiUrl = root.getAttribute('data-job-ai-url') || '';
    var aiBtn = document.getElementById('sebuJobTemplateAi');
    var data = getData();
    var body = new FormData();

    if (!aiUrl || !window.fetch) {
      showAiStatus('AI 초안 작성 기능을 사용할 수 없습니다.', 'error');
      return;
    }
    if (isEmpty(data.company) && isEmpty(data.job_type) && isEmpty(data.region)) {
      showAiStatus('업체명, 직종, 근무지역 중 하나 이상을 입력하면 더 정확한 초안을 만들 수 있습니다.', 'error');
      return;
    }

    Object.keys(data).forEach(function (key) {
      body.append(key, data[key] || '');
    });

    showAiStatus('AI가 구인글 초안을 작성 중입니다...', '');
    if (aiBtn) {
      aiBtn.disabled = true;
      aiBtn.classList.add('is-loading');
    }

    fetch(aiUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: body,
      headers: { Accept: 'application/json' }
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || !json.success) {
          throw new Error((json && json.message) || 'AI 초안 작성에 실패했습니다.');
        }
        applyDataToFields(json.data || {});
        if (!applyTemplateToEditor({ force: true, silent: true })) {
          throw new Error('필수 항목을 채우지 못했습니다. 내용을 확인해 주세요.');
        }
        showAiStatus(json.source === 'api' ? 'AI 초안을 작성했습니다. 내용과 연락처를 확인해 주세요.' : '기본 템플릿으로 초안을 작성했습니다. 내용과 연락처를 확인해 주세요.', 'success');
      })
      .catch(function (err) {
        showAiStatus(err && err.message ? err.message : 'AI 초안 작성에 실패했습니다.', 'error');
      })
      .then(function () {
        if (aiBtn) {
          aiBtn.disabled = false;
          aiBtn.classList.remove('is-loading');
        }
      });
  }

  fieldEls().forEach(function (el) {
    el.addEventListener('change', function () {
      syncJobMetaFields(getData());
    });
    el.addEventListener('input', function () {
      syncJobMetaFields(getData());
    });
  });

  if (window.__SEBU_JOB_TEMPLATE_INITIAL__) {
    applyDataToFields(window.__SEBU_JOB_TEMPLATE_INITIAL__);
  } else {
    var jsonEl = document.getElementById('job_template_json');
    if (jsonEl && jsonEl.value) {
      try {
        applyDataToFields(JSON.parse(jsonEl.value));
      } catch (e) {
        /* ignore */
      }
    }
  }

  syncJobMetaFields(getData());
  initJobShopPicker();

  var writeForm = root.closest('form');
  if (writeForm) {
    writeForm.addEventListener('submit', function () {
      syncJobMetaFields(getData());
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

  window.sebuJobTemplateApplyBeforeSubmit = function () {
    syncJobMetaFields(getData());
    return applyTemplateToEditor({ silent: true, force: false, confirmReplace: false });
  };

  document.getElementById('sebuJobTemplateApply').addEventListener('click', function () {
    if (!applyTemplateToEditor({ confirmReplace: true })) {
      return;
    }
    var subjectEl = document.getElementById('wr_subject');
    if (subjectEl && window.matchMedia && window.matchMedia('(max-width: 767px)').matches) {
      subjectEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });

  var aiDraftBtn = document.getElementById('sebuJobTemplateAi');
  if (aiDraftBtn) {
    aiDraftBtn.addEventListener('click', requestAiDraft);
  }

  document.getElementById('sebuJobTemplateReset').addEventListener('click', function () {
    if (window.confirm('템플릿 입력 내용을 모두 지울까요?')) {
      resetTemplateFields();
    }
  });
})();
