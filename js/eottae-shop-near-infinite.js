/**
 * 내주변 업소 — 목록 무한 스크롤 (20 → 30 → 40…)
 */
(function (global) {
  'use strict';

  function batchLimit(offset) {
    var o = parseInt(offset, 10) || 0;
    if (o < 1) return 20;
    if (o < 21) return 30;
    return 40;
  }

  function listFiltersFromUrl() {
    var u = new URL(global.location.href);
    var keys = ['sca', 'sfl', 'stx', 'sst', 'sod', 'eottae_lat', 'eottae_lng'];
    var params = new URLSearchParams();
    keys.forEach(function (key) {
      var val = u.searchParams.get(key);
      if (val !== null && val !== '') {
        params.set(key, val);
      }
    });
    return params;
  }

  function ShopInfiniteList(root) {
    this.root = root;
    this.api = root.getAttribute('data-shop-api') || '';
    this.boTable = root.getAttribute('data-bo-table') || 'shop';
    this.nextOffset = parseInt(root.getAttribute('data-next-offset'), 10) || 0;
    this.hasMore = root.getAttribute('data-has-more') === '1';
    this.loading = false;
    this.statusEl = document.querySelector('[data-shop-infinite-status]');
    this.sentinel = null;
  }

  ShopInfiniteList.prototype.setStatus = function (text, visible) {
    if (!this.statusEl) return;
    this.statusEl.textContent = text || '';
    if (visible) {
      this.statusEl.removeAttribute('hidden');
    } else {
      this.statusEl.setAttribute('hidden', 'hidden');
    }
  };

  ShopInfiniteList.prototype.buildRequestUrl = function () {
    var params = listFiltersFromUrl();
    params.set('action', 'list_cards');
    params.set('bo_table', this.boTable);
    params.set('offset', String(this.nextOffset));
    params.set('limit', String(batchLimit(this.nextOffset)));
    return this.api + '?' + params.toString();
  };

  ShopInfiniteList.prototype.loadMore = function () {
    var self = this;
    if (!self.hasMore || self.loading || !self.api) {
      return;
    }

    self.loading = true;
    self.setStatus('더 불러오는 중…', true);

    fetch(self.buildRequestUrl(), { credentials: 'same-origin' })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (!data || !data.success) {
          throw new Error((data && data.message) || '목록을 불러오지 못했습니다.');
        }

        var empty = self.root.querySelector('[data-shop-empty-state]');
        if (empty) {
          empty.remove();
        }

        if (data.html) {
          var wrap = document.createElement('div');
          wrap.innerHTML = data.html;
          while (wrap.firstChild) {
            self.root.appendChild(wrap.firstChild);
          }
        }

        self.nextOffset = parseInt(data.next_offset, 10) || self.nextOffset;
        self.hasMore = !!data.has_more;
        self.root.setAttribute('data-next-offset', String(self.nextOffset));
        self.root.setAttribute('data-has-more', self.hasMore ? '1' : '0');

        if (typeof data.total === 'number') {
          self.root.setAttribute('data-total', String(data.total));
          var title = document.querySelector('.shop-near-results__title strong');
          if (title) {
            title.textContent = String(data.total);
          }
        }

        if (self.hasMore) {
          self.setStatus('', false);
        } else {
          self.setStatus('모든 업체를 불러왔습니다.', true);
        }

        global.document.dispatchEvent(new CustomEvent('eottae:shop-list-appended'));

        if (!self.hasMore && self.sentinel && self.sentinel.parentNode) {
          self.sentinel.parentNode.removeChild(self.sentinel);
        }
      })
      .catch(function (err) {
        self.setStatus(err.message || '목록을 불러오지 못했습니다.', true);
      })
      .finally(function () {
        self.loading = false;
      });
  };

  ShopInfiniteList.prototype.bindScroll = function () {
    var self = this;
    if (!self.hasMore) {
      return;
    }

    self.sentinel = document.createElement('div');
    self.sentinel.className = 'shop-near-infinite__sentinel';
    self.sentinel.setAttribute('aria-hidden', 'true');
    self.root.parentNode.appendChild(self.sentinel);

    if (!('IntersectionObserver' in global)) {
      var panel = self.root.closest('.shop-near-page__panel') || self.root;
      panel.addEventListener('scroll', function () {
        if (!self.hasMore || self.loading) return;
        var rect = self.root.getBoundingClientRect();
        if (rect.bottom < global.innerHeight + 200) {
          self.loadMore();
        }
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            self.loadMore();
          }
        });
      },
      { root: null, rootMargin: '240px 0px', threshold: 0 }
    );

    observer.observe(self.sentinel);
  };

  function init() {
    var root = document.querySelector('[data-shop-infinite-list]');
    if (!root) {
      return;
    }
    var list = new ShopInfiniteList(root);
    list.bindScroll();
  }

  if (global.document.readyState === 'loading') {
    global.document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(typeof window !== 'undefined' ? window : this);
