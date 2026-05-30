(function (window, document) {
  'use strict';

  var endpoint = (window.__EOTTae__ && window.__EOTTae__.procBase)
    ? window.__EOTTae__.procBase + '/eottae-translation-batch.php'
    : '/proc/eottae-translation-batch.php';
  var pending = null;

  function currentLanguage() {
    if (window.EottaeI18N && typeof window.EottaeI18N.getLanguage === 'function') {
      return window.EottaeI18N.getLanguage();
    }

    return document.documentElement.getAttribute('data-eottae-language') || 'ko';
  }

  function qsa(selector, context) {
    return Array.prototype.slice.call((context || document).querySelectorAll(selector));
  }

  function itemKey(item) {
    return String(item.getAttribute('data-bo-table') || '') + ':' + String(item.getAttribute('data-wr-id') || '');
  }

  function rememberOriginal(el, attr) {
    if (!el || el.getAttribute(attr)) {
      return;
    }
    el.setAttribute(attr, el.textContent || '');
  }

  function restoreOriginals(items) {
    items.forEach(function (item) {
      qsa('[data-translation-list-title]', item).forEach(function (el) {
        var original = el.getAttribute('data-translation-original-title');
        if (original !== null) {
          el.textContent = original;
        }
      });
      qsa('[data-translation-list-snippet]', item).forEach(function (el) {
        var original = el.getAttribute('data-translation-original-snippet');
        if (original !== null) {
          el.textContent = original;
        }
      });
      qsa('[data-translation-extra]', item).forEach(function (el) {
        var original = el.getAttribute('data-translation-original-extra');
        if (original !== null) {
          el.textContent = original;
        }
      });
    });
  }

  function collectItems() {
    return qsa('[data-list-translation-item]').filter(function (item) {
      return item.getAttribute('data-bo-table') && item.getAttribute('data-wr-id');
    });
  }

  function applyPayload(item, payload) {
    if (!payload || !payload.translatedTitle) {
      return;
    }

    qsa('[data-translation-list-title]', item).forEach(function (el) {
      rememberOriginal(el, 'data-translation-original-title');
      el.textContent = payload.translatedTitle;
    });

    if (payload.translatedSnippet) {
      qsa('[data-translation-list-snippet]', item).forEach(function (el) {
        rememberOriginal(el, 'data-translation-original-snippet');
        el.textContent = payload.translatedSnippet;
      });
    }

    if (payload.translatedExtras && typeof payload.translatedExtras === 'object') {
      Object.keys(payload.translatedExtras).forEach(function (key) {
        qsa('[data-translation-extra="' + key + '"]', item).forEach(function (el) {
          rememberOriginal(el, 'data-translation-original-extra');
          el.textContent = payload.translatedExtras[key];
        });
      });
    }
  }

  function applyListTranslations() {
    var language = currentLanguage();
    var items = collectItems();

    if (!items.length) {
      return Promise.resolve();
    }

    if (!language || language === 'ko') {
      restoreOriginals(items);
      return Promise.resolve();
    }

    var requestItems = items.map(function (item) {
      return {
        bo_table: item.getAttribute('data-bo-table'),
        wr_id: parseInt(item.getAttribute('data-wr-id'), 10) || 0
      };
    });

    var body = new window.FormData();
    body.append('target_language', language);
    body.append('items', JSON.stringify(requestItems));

    if (pending) {
      return pending;
    }

    pending = window.fetch(endpoint, {
      method: 'POST',
      body: body,
      credentials: 'same-origin'
    })
      .then(function (response) {
        return response.json().then(function (payload) {
          if (!response.ok || !payload || !payload.success) {
            throw new Error((payload && payload.message) || 'batch_failed');
          }
          return payload;
        });
      })
      .then(function (payload) {
        var map = payload.translations || {};
        items.forEach(function (item) {
          applyPayload(item, map[itemKey(item)] || null);
        });
      })
      .catch(function () {
        /* 목록 번역 실패 시 원문 유지 */
      })
      .then(function () {
        pending = null;
      });

    return pending;
  }

  function scheduleApply() {
    if (window.EottaeI18N && typeof window.EottaeI18N.ready === 'function') {
      window.EottaeI18N.ready().then(applyListTranslations).catch(applyListTranslations);
      return;
    }
    applyListTranslations();
  }

  function init() {
    scheduleApply();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.addEventListener('eottae:languagechange', scheduleApply);
  window.addEventListener('eottae:shop-list-appended', scheduleApply);
})(window, document);
