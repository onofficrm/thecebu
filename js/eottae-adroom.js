(function () {
  'use strict';

  function normalizeSearch(value) {
    return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
  }

  function initAdroomShopPicker() {
    var root = document.getElementById('adroom-shop-picker');
    if (!root) {
      return;
    }

    var boInput = document.getElementById('eottae_adroom_shop_bo_table');
    var wrInput = document.getElementById('eottae_adroom_shop_wr_id');
    var wr1 = root.querySelector('input[name="wr_1"]');
    var wr2 = root.querySelector('input[name="wr_2"]');
    var searchInput = document.getElementById('adroom-shop-picker-search');
    var countEl = document.getElementById('adroom-shop-picker-count');
    var noMatchEl = document.getElementById('adroom-shop-picker-no-match');
    var items = Array.prototype.slice.call(root.querySelectorAll('.adroom-shop-picker__item'));

    function selectItem(item) {
      if (!item || !boInput || !wrInput) {
        return;
      }

      var bo = item.getAttribute('data-bo-table') || '';
      var wrId = item.getAttribute('data-wr-id') || '0';

      boInput.value = bo;
      wrInput.value = wrId;
      if (wr1) {
        wr1.value = bo;
      }
      if (wr2) {
        wr2.value = wrId;
      }

      items.forEach(function (el) {
        var on = el === item;
        el.classList.toggle('is-selected', on);
        el.setAttribute('aria-selected', on ? 'true' : 'false');
      });
    }

    function filterItems() {
      var query = searchInput ? normalizeSearch(searchInput.value) : '';
      var visible = 0;

      items.forEach(function (item) {
        var hay = normalizeSearch(item.getAttribute('data-search') || item.textContent || '');
        var show = query === '' || hay.indexOf(query) !== -1;
        item.hidden = !show;
        item.style.display = show ? '' : 'none';
        if (show) {
          visible += 1;
        }
      });

      if (countEl) {
        countEl.textContent = visible + '개 업체';
      }
      if (noMatchEl) {
        noMatchEl.hidden = visible > 0;
      }
    }

    items.forEach(function (item) {
      item.addEventListener('click', function () {
        selectItem(item);
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', filterItems);
      searchInput.addEventListener('search', filterItems);
    }

    filterItems();
  }

  function initAdroomCouponPicker() {
    var root = document.getElementById('adroom-coupon-picker');
    if (!root) {
      return;
    }

    var cpInput = document.getElementById('eottae_adroom_cp_id');
    var wr4 = root.querySelector('input[name="wr_4"]');
    var radios = root.querySelectorAll('input[name="adroom_coupon_pick"]');

    function syncCpId() {
      var value = '';
      radios.forEach(function (radio) {
        if (radio.checked) {
          value = radio.value || '';
        }
      });
      if (cpInput) {
        cpInput.value = value;
      }
      if (wr4) {
        wr4.value = value;
      }
    }

    radios.forEach(function (radio) {
      radio.addEventListener('change', syncCpId);
    });
    syncCpId();
  }

  function initAdroomCouponClaim() {
    var btn = document.querySelector('[data-adroom-coupon-claim]');
    if (!btn) {
      return;
    }

    var statusEl = document.getElementById('adroom-coupon-claim-status');
    var procUrl = btn.getAttribute('data-proc-url') || '';
    var wrId = btn.getAttribute('data-wr-id') || '0';

    btn.addEventListener('click', function () {
      if (!procUrl || btn.disabled) {
        return;
      }

      btn.disabled = true;
      if (statusEl) {
        statusEl.textContent = '발급 중…';
      }

      var body = new FormData();
      body.append('action', 'claim');
      body.append('wr_id', wrId);

      fetch(procUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
        headers: { Accept: 'application/json' },
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.message) || '쿠폰 발급에 실패했습니다.');
          }

          if (statusEl) {
            statusEl.textContent = data.message || '쿠폰이 발급되었습니다.';
          }

          if (data.coupons_url && window.confirm((data.message || '쿠폰이 발급되었습니다.') + '\n\n쿠폰함으로 이동할까요?')) {
            window.location.href = data.coupons_url;
            return;
          }

          btn.textContent = '내 쿠폰함에서 보기';
          btn.removeAttribute('data-adroom-coupon-claim');
          if (data.coupons_url) {
            btn.onclick = function () {
              window.location.href = data.coupons_url;
            };
          }
        })
        .catch(function (err) {
          btn.disabled = false;
          if (statusEl) {
            statusEl.textContent = '';
          }
          window.alert(err && err.message ? err.message : '쿠폰 발급에 실패했습니다.');
        });
    });
  }

  function init() {
    initAdroomShopPicker();
    initAdroomCouponPicker();
    initAdroomCouponClaim();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
