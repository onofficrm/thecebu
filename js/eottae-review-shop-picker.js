(function (global) {
  'use strict';

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function initReviewShopPicker() {
    var root = qs('[data-review-shop-picker]');
    if (!root || root.getAttribute('data-review-shop-bound') === '1') {
      return;
    }
    root.setAttribute('data-review-shop-bound', '1');

    var searchUrl = root.getAttribute('data-search-url') || '/proc/eottae-review-shop-search.php';
    var searchInput = qs('#reviewShopPickerSearch', root);
    var resultsEl = qs('#reviewShopPickerResults', root);
    var emptyEl = qs('#reviewShopPickerEmpty', root);
    var hintEl = qs('#reviewShopPickerHint', root);
    var selectedWrap = qs('#reviewShopPickerSelected', root);
    var selectedName = qs('#reviewShopPickerSelectedName', root);
    var selectedMeta = qs('#reviewShopPickerSelectedMeta', root);
    var searchWrap = qs('#reviewShopPickerSearchWrap', root);
    var clearBtn = qs('#reviewShopPickerClear', root);
    var wrIdInput = qs('#eottae_review_shop_wr_id', root);
    var boInput = qs('#eottae_review_shop_bo_table', root);
    var nameInput = qs('#eottae_review_shop_name', root);
    var wr1Input = qs('input[name="wr_1"]', root);
    var wr3Input = qs('#wr_3', root);
    var wr6Input = qs('input[name="wr_6"]', root);
    var ratingInput = qs('#wr_2', root);
    var starsWrap = qs('[data-review-board-stars]', root);

    var debounceTimer = null;
    var activeController = null;

    function setRating(value) {
      var rating = parseInt(value, 10) || 0;
      if (ratingInput) {
        ratingInput.value = String(rating);
      }
      if (!starsWrap) {
        return;
      }
      starsWrap.querySelectorAll('[data-star]').forEach(function (btn) {
        var star = parseInt(btn.getAttribute('data-star'), 10) || 0;
        btn.classList.toggle('is-active', rating >= star);
      });
    }

    function setSelected(shop) {
      var hasShop = !!(shop && shop.wr_id);
      var wrId = hasShop ? parseInt(shop.wr_id, 10) : 0;
      var bo = hasShop ? String(shop.bo_table || '') : '';
      var name = hasShop ? String(shop.name || '') : '';
      var meta = [];
      if (hasShop && shop.board_label) {
        meta.push(shop.board_label);
      }
      if (hasShop && shop.region) {
        meta.push(shop.region);
      }

      if (wrIdInput) {
        wrIdInput.value = wrId > 0 ? String(wrId) : '';
      }
      if (boInput) {
        boInput.value = bo;
      }
      if (nameInput) {
        nameInput.value = name;
      }
      if (wr1Input) {
        wr1Input.value = wrId > 0 ? String(wrId) : '';
      }
      if (wr6Input) {
        wr6Input.value = bo;
      }
      if (wr3Input) {
        wr3Input.value = name;
      }

      if (selectedWrap) {
        selectedWrap.hidden = !hasShop;
        selectedWrap.classList.toggle('is-empty', !hasShop);
      }
      if (searchWrap) {
        searchWrap.hidden = hasShop;
      }
      if (selectedName) {
        selectedName.textContent = name;
      }
      if (selectedMeta) {
        selectedMeta.textContent = meta.join(' · ');
      }
      if (resultsEl) {
        resultsEl.hidden = true;
        resultsEl.innerHTML = '';
      }
      if (emptyEl) {
        emptyEl.hidden = true;
      }
      if (searchInput && hasShop) {
        searchInput.value = '';
      }
    }

    function renderResults(items) {
      if (!resultsEl) {
        return;
      }

      if (!items || !items.length) {
        resultsEl.hidden = true;
        resultsEl.innerHTML = '';
        if (emptyEl) {
          emptyEl.hidden = false;
        }
        if (hintEl) {
          hintEl.textContent = '검색 결과가 없습니다. 업체 연결 없이 리뷰를 작성할 수 있습니다.';
        }
        return;
      }

      if (emptyEl) {
        emptyEl.hidden = true;
      }
      if (hintEl) {
        hintEl.textContent = items.length + '개 업체';
      }

      resultsEl.innerHTML = items.map(function (shop) {
        var meta = [];
        if (shop.board_label) {
          meta.push(shop.board_label);
        }
        if (shop.region) {
          meta.push(shop.region);
        }
        var thumb = shop.thumb_url
          ? '<img src="' + esc(shop.thumb_url) + '" alt="" class="review-shop-picker__result-thumb" loading="lazy" decoding="async">'
          : '<span class="review-shop-picker__result-thumb review-shop-picker__result-thumb--empty" aria-hidden="true"></span>';

        return ''
          + '<button type="button" class="review-shop-picker__result" role="option"'
          + ' data-bo-table="' + esc(shop.bo_table || '') + '"'
          + ' data-wr-id="' + esc(shop.wr_id || '') + '"'
          + ' data-name="' + esc(shop.name || '') + '"'
          + ' data-region="' + esc(shop.region || '') + '"'
          + ' data-board-label="' + esc(shop.board_label || '') + '">'
          + thumb
          + '<span class="review-shop-picker__result-body">'
          + '<strong class="review-shop-picker__result-name">' + esc(shop.name || '') + '</strong>'
          + (meta.length ? '<span class="review-shop-picker__result-meta">' + esc(meta.join(' · ')) + '</span>' : '')
          + (shop.address ? '<span class="review-shop-picker__result-addr">' + esc(shop.address) + '</span>' : '')
          + '</span>'
          + '</button>';
      }).join('');

      resultsEl.hidden = false;
    }

    function fetchShops(keyword) {
      if (activeController && typeof activeController.abort === 'function') {
        activeController.abort();
      }
      activeController = typeof AbortController !== 'undefined' ? new AbortController() : null;

      var url = searchUrl + '?limit=30';
      if (keyword) {
        url += '&q=' + encodeURIComponent(keyword);
      }

      var opts = { credentials: 'same-origin' };
      if (activeController) {
        opts.signal = activeController.signal;
      }

      return fetch(url, opts)
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          if (!data || !data.success) {
            return [];
          }
          return data.items || [];
        })
        .catch(function () {
          return [];
        });
    }

    function scheduleSearch() {
      if (!searchInput) {
        return;
      }
      clearTimeout(debounceTimer);
      debounceTimer = global.setTimeout(function () {
        var keyword = (searchInput.value || '').trim();
        if (keyword.length < 1) {
          if (resultsEl) {
            resultsEl.hidden = true;
            resultsEl.innerHTML = '';
          }
          if (emptyEl) {
            emptyEl.hidden = true;
          }
          if (hintEl) {
            hintEl.textContent = '검색어를 입력하면 등록된 업체가 표시됩니다.';
          }
          return;
        }
        fetchShops(keyword).then(renderResults);
      }, 220);
    }

    if (searchInput) {
      searchInput.addEventListener('input', scheduleSearch);
      searchInput.addEventListener('focus', function () {
        var keyword = (searchInput.value || '').trim();
        if (keyword.length > 0) {
          scheduleSearch();
        }
      });
    }

    if (resultsEl) {
      resultsEl.addEventListener('click', function (event) {
        var btn = event.target.closest('.review-shop-picker__result');
        if (!btn) {
          return;
        }
        setSelected({
          wr_id: btn.getAttribute('data-wr-id'),
          bo_table: btn.getAttribute('data-bo-table'),
          name: btn.getAttribute('data-name'),
          region: btn.getAttribute('data-region'),
          board_label: btn.getAttribute('data-board-label'),
        });
      });
    }

    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        setSelected(null);
        if (searchInput) {
          searchInput.focus();
        }
      });
    }

    if (starsWrap) {
      starsWrap.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-star]');
        if (!btn) {
          return;
        }
        event.preventDefault();
        var star = parseInt(btn.getAttribute('data-star'), 10) || 0;
        var current = parseInt(ratingInput ? ratingInput.value : '0', 10) || 0;
        setRating(current === star ? 0 : star);
      });
    }

    document.addEventListener('click', function (event) {
      if (!root.contains(event.target)) {
        if (resultsEl) {
          resultsEl.hidden = true;
        }
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReviewShopPicker);
  } else {
    initReviewShopPicker();
  }

  global.initEottaeReviewShopPicker = initReviewShopPicker;
}(window));
