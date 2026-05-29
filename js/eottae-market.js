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
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}(window));
