(function (global) {
  'use strict';

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function initStatusPanel(panel) {
    if (!panel || panel.dataset.bound === '1') return;
    panel.dataset.bound = '1';
    var procUrl = panel.getAttribute('data-proc-url');
    var boTable = panel.getAttribute('data-bo-table');
    var wrId = panel.getAttribute('data-wr-id');
    panel.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-market-status]');
      if (!btn) return;
      var status = btn.getAttribute('data-market-status');
      var body = new FormData();
      body.append('bo_table', boTable);
      body.append('wr_id', wrId);
      body.append('status', status);
      btn.disabled = true;
      fetch(procUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
        headers: { Accept: 'application/json' },
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (!data || !data.success) throw new Error((data && data.message) || '변경에 실패했습니다.');
          global.location.reload();
        })
        .catch(function (err) {
          alert(err && err.message ? err.message : '변경에 실패했습니다.');
          btn.disabled = false;
        });
    });
  }

  function initPhotoPreviews() {
    document.querySelectorAll('[data-photo-preview]').forEach(function (input) {
      if (input.dataset.bound === '1') return;
      input.dataset.bound = '1';
      input.addEventListener('change', function () {
        var slot = input.closest('.market-photo-slot');
        var preview = slot ? slot.querySelector('.market-photo-slot__preview') : null;
        if (!preview || !input.files || !input.files[0]) return;
        preview.src = URL.createObjectURL(input.files[0]);
        preview.hidden = false;
      });
    });
  }

  function setMarketPriceMode(isFree) {
    var flag = qs('#market_free_giveaway');
    var priceFields = qs('[data-market-price-fields]');
    var priceInput = qs('#wr_1');
    var offerWrap = qs('[data-market-offer-wrap]');
    var offerInput = qs('#wr_8');
    var submitBtn = qs('#btn_submit');
    var modeWrap = qs('[data-market-price-mode]');

    if (flag) {
      flag.value = isFree ? '1' : '0';
    }
    if (priceFields) {
      priceFields.classList.toggle('is-hidden', isFree);
    }
    if (priceInput) {
      priceInput.required = !isFree;
      if (isFree) {
        priceInput.value = '';
      }
    }
    if (offerWrap) {
      offerWrap.hidden = isFree;
    }
    if (offerInput) {
      offerInput.disabled = isFree;
      if (isFree) {
        offerInput.checked = false;
      }
    }
    if (submitBtn && submitBtn.textContent.indexOf('수정') === -1) {
      submitBtn.textContent = isFree ? '무료나눔 등록' : '상품 등록';
    }
    if (modeWrap) {
      modeWrap.querySelectorAll('[data-market-price-mode]').forEach(function (btn) {
        if (btn.tagName !== 'BUTTON') {
          return;
        }
        var mode = btn.getAttribute('data-market-price-mode');
        var active = (mode === 'free') === isFree;
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    }
  }

  function formatPeso(value) {
    var num = parseInt(value, 10) || 0;
    if (num < 1) {
      return '-';
    }
    return '₱' + String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function renderPriceAiResult(target, json) {
    var data = json && json.data ? json.data : {};
    var stats = json && json.stats ? json.stats : {};
    var comps = json && json.comps ? json.comps : [];
    var range = formatPeso(data.price_min) + ' ~ ' + formatPeso(data.price_max);
    var html = ''
      + '<strong>추천가 ' + formatPeso(data.suggested_price) + '</strong>'
      + '<span>참고 범위 ' + range + '</span>'
      + '<p>' + (data.summary || '최근 유사 매물을 기준으로 참고 가격을 계산했습니다.') + '</p>'
      + '<small>' + (data.disclaimer || '참고용 가격입니다. 실제 거래가는 협의에 따라 달라질 수 있습니다.') + '</small>';
    if (stats.count) {
      html += '<em>비교 매물 ' + stats.count + '건 · 평균 ' + formatPeso(stats.avg) + '</em>';
    } else if (!comps.length) {
      html += '<em>유사 매물이 부족합니다.</em>';
    }
    target.innerHTML = html;
    target.hidden = false;
  }

  function initPriceAi() {
    var form = qs('#fwrite');
    var btn = qs('[data-market-price-ai-trigger]');
    var result = qs('[data-market-price-ai-result]');
    if (!form || !btn || !result || btn.dataset.bound === '1') {
      return;
    }
    btn.dataset.bound = '1';

    btn.addEventListener('click', function () {
      var url = form.getAttribute('data-market-price-ai-url') || '';
      var subject = qs('#wr_subject');
      var region = qs('#wr_3');
      var content = qs('#wr_content');
      var priceInput = qs('#wr_1');
      var body = new FormData();

      if (!url) {
        result.textContent = 'AI 가격 참고 기능을 사용할 수 없습니다.';
        result.hidden = false;
        return;
      }
      if (!subject || !subject.value.trim()) {
        result.textContent = '상품명을 먼저 입력해 주세요.';
        result.hidden = false;
        subject && subject.focus();
        return;
      }

      body.append('bo_table', (form.querySelector('[name="bo_table"]') || {}).value || 'market');
      body.append('subject', subject.value.trim());
      body.append('region', region ? region.value : '');
      body.append('content', content ? content.value : '');

      btn.disabled = true;
      btn.classList.add('is-loading');
      result.textContent = 'AI가 최근 유사 매물을 비교 중입니다...';
      result.hidden = false;

      fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
        headers: { Accept: 'application/json' }
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '가격 참고 요청에 실패했습니다.');
          }
          renderPriceAiResult(result, json);
          if (priceInput && (!priceInput.value || parseInt(priceInput.value, 10) < 1) && json.data && json.data.suggested_price) {
            priceInput.value = parseInt(json.data.suggested_price, 10) || '';
          }
        })
        .catch(function (err) {
          result.textContent = err && err.message ? err.message : '가격 참고 요청에 실패했습니다.';
          result.hidden = false;
        })
        .then(function () {
          btn.disabled = false;
          btn.classList.remove('is-loading');
        });
    });
  }

  function initPriceMode() {
    var wrap = qs('[data-market-price-mode]');
    if (!wrap || wrap.dataset.bound === '1') {
      return;
    }
    wrap.dataset.bound = '1';

    var flag = qs('#market_free_giveaway');
    setMarketPriceMode(flag && flag.value === '1');

    wrap.addEventListener('click', function (event) {
      var btn = event.target.closest('[data-market-price-mode]');
      if (!btn || btn.tagName !== 'BUTTON') {
        return;
      }
      setMarketPriceMode(btn.getAttribute('data-market-price-mode') === 'free');
    });
  }

  function init() {
    document.querySelectorAll('[data-market-status-panel]').forEach(initStatusPanel);
    initPhotoPreviews();
    initPriceMode();
    initPriceAi();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}(window));
