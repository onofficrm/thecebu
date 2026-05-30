(function (window, document) {
  'use strict';

  var originalMap = new WeakMap();

  function qs(selector, context) {
    return (context || document).querySelector(selector);
  }

  function qsa(selector, context) {
    return Array.prototype.slice.call((context || document).querySelectorAll(selector));
  }

  function t(key, fallback) {
    if (window.EottaeI18N && typeof window.EottaeI18N.t === 'function') {
      return window.EottaeI18N.t(key) || fallback;
    }

    return fallback;
  }

  function currentLanguage() {
    if (window.EottaeI18N && typeof window.EottaeI18N.getLanguage === 'function') {
      return window.EottaeI18N.getLanguage();
    }

    return document.documentElement.getAttribute('data-eottae-language') || 'ko';
  }

  function findArticle(panel) {
    return panel.closest('article') || panel.closest('#bo_v') || document;
  }

  function findTranslationScope(panel) {
    return panel.closest('.community-view-page__layout')
      || panel.closest('#bo_v')
      || findArticle(panel);
  }

  function applyScopeLabels(scope, language) {
    if (!scope || !window.EottaeI18N || typeof window.EottaeI18N.applyScope !== 'function') {
      return Promise.resolve();
    }

    return window.EottaeI18N.applyScope(scope, language);
  }

  function restoreScopeLabels(scope) {
    var siteLanguage = currentLanguage();
    return applyScopeLabels(scope, siteLanguage);
  }

  function rememberOriginal(panel) {
    var scope = findTranslationScope(panel);
    var article = findArticle(panel);

    if (originalMap.has(scope)) {
      return originalMap.get(scope);
    }

    var title = qs('[data-translation-title]', article);
    var content = qs('[data-translation-content]', article);
    var original = {
      titleText: title ? title.textContent : '',
      contentHtml: content ? content.innerHTML : '',
      extras: {}
    };

    qsa('[data-translation-extra]', scope).forEach(function (el) {
      var key = el.getAttribute('data-translation-extra');
      if (key) {
        original.extras[key] = el.textContent;
      }
    });

    originalMap.set(scope, original);
    return original;
  }

  function setStatus(panel, message, isError) {
    var status = qs('[data-translation-status]', panel);
    if (!status) {
      return;
    }

    if (!message) {
      status.hidden = true;
      status.textContent = '';
      status.classList.remove('is-error');
      return;
    }

    status.hidden = false;
    status.textContent = message;
    status.classList.toggle('is-error', !!isError);
  }

  function setActive(panel, language) {
    qsa('.post-translation__btn', panel).forEach(function (button) {
      var target = button.getAttribute('data-translation-target') || 'original';
      button.classList.toggle('is-active', target === language);
    });
  }

  function setLoading(panel, loading) {
    panel.classList.toggle('is-loading', !!loading);
    qsa('button', panel).forEach(function (button) {
      button.disabled = !!loading;
    });
  }

  function showNotice(panel, show) {
    var notice = qs('[data-translation-notice]', panel);
    if (notice) {
      notice.hidden = !show;
    }
  }

  function applyExtras(scope, extras) {
    if (!extras || typeof extras !== 'object') {
      return;
    }

    Object.keys(extras).forEach(function (key) {
      qsa('[data-translation-extra="' + key + '"]', scope).forEach(function (el) {
        el.textContent = extras[key];
      });
    });
  }

  function restoreExtras(scope, original) {
    if (!original || !original.extras) {
      return;
    }

    Object.keys(original.extras).forEach(function (key) {
      qsa('[data-translation-extra="' + key + '"]', scope).forEach(function (el) {
        el.textContent = original.extras[key];
      });
    });
  }

  function applyTranslation(panel, article, payload) {
    var title = qs('[data-translation-title]', article);
    var content = qs('[data-translation-content]', article);
    var scope = findTranslationScope(panel);

    if (title && payload.translatedTitle) {
      title.textContent = payload.translatedTitle;
    }
    if (content && payload.translatedContent) {
      content.innerHTML = payload.translatedContent;
      if (window.jQuery && typeof window.jQuery.fn.viewimageresize === 'function') {
        window.jQuery(content).viewimageresize();
      }
    }

    applyExtras(scope, payload.translatedExtras || null);
    applyScopeLabels(scope, payload.targetLanguage || '');

    showNotice(panel, true);
    setActive(panel, payload.targetLanguage || '');
  }

  function restoreOriginal(panel, article) {
    var scope = findTranslationScope(panel);
    var original = rememberOriginal(panel);
    var title = qs('[data-translation-title]', article);
    var content = qs('[data-translation-content]', article);

    if (title) {
      title.textContent = original.titleText;
    }
    if (content) {
      content.innerHTML = original.contentHtml;
      if (window.jQuery && typeof window.jQuery.fn.viewimageresize === 'function') {
        window.jQuery(content).viewimageresize();
      }
    }

    restoreExtras(scope, original);
    restoreScopeLabels(scope);

    showNotice(panel, false);
    setStatus(panel, '');
    setActive(panel, 'original');
  }

  function translate(panel, language) {
    var article = findArticle(panel);
    rememberOriginal(panel);

    var body = new window.FormData();
    body.append('bo_table', panel.getAttribute('data-bo-table') || '');
    body.append('wr_id', panel.getAttribute('data-wr-id') || '');
    body.append('source_language', panel.getAttribute('data-source-language') || 'ko');
    body.append('target_language', language);
    body.append('token', panel.getAttribute('data-token') || '');

    setLoading(panel, true);
    setStatus(panel, t('translation.loading', '번역 중입니다.'));
    showNotice(panel, false);

    window.fetch(panel.getAttribute('data-endpoint'), {
      method: 'POST',
      body: body,
      credentials: 'same-origin'
    })
      .then(function (response) {
        return response.json().then(function (payload) {
          if (!response.ok || !payload || !payload.success) {
            throw new Error((payload && payload.message) || 'translation_failed');
          }
          return payload;
        });
      })
      .then(function (payload) {
        applyTranslation(panel, article, payload);
        setStatus(panel, '');
      })
      .catch(function () {
        setStatus(panel, t('translation.error', '자동 번역을 불러오지 못했습니다. 잠시 후 다시 시도해주세요.'), true);
      })
      .then(function () {
        setLoading(panel, false);
      });
  }

  function applyCurrentLanguage(panel) {
    var language = currentLanguage();
    if (!language || language === (panel.getAttribute('data-source-language') || 'ko')) {
      restoreOriginal(panel, findArticle(panel));
      return;
    }

    if (qs('[data-translation-target="' + language + '"]', panel)) {
      translate(panel, language);
    }
  }

  function initPanel(panel) {
    if (panel.getAttribute('data-translation-ready') === '1') {
      return;
    }
    panel.setAttribute('data-translation-ready', '1');

    var language = currentLanguage();
    var preferred = qs('[data-translation-target="' + language + '"]', panel);
    if (preferred) {
      preferred.classList.add('is-preferred');
    }

    if (preferred && language !== (panel.getAttribute('data-source-language') || 'ko')) {
      window.setTimeout(function () {
        applyCurrentLanguage(panel);
      }, 80);
    }

    panel.addEventListener('click', function (event) {
      var button = event.target.closest('button');
      if (!button || !panel.contains(button)) {
        return;
      }

      event.preventDefault();
      var article = findArticle(panel);
      if (button.hasAttribute('data-translation-original')) {
        restoreOriginal(panel, article);
        return;
      }

      var target = button.getAttribute('data-translation-target');
      if (target) {
        translate(panel, target);
      }
    });
  }

  function init() {
    qsa('[data-post-translation]').forEach(initPanel);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.addEventListener('eottae:languagechange', function () {
    qsa('[data-post-translation]').forEach(function (panel) {
      qsa('[data-translation-target]', panel).forEach(function (button) {
        button.classList.toggle('is-preferred', button.getAttribute('data-translation-target') === currentLanguage());
      });
      applyCurrentLanguage(panel);
    });
  });
})(window, document);
