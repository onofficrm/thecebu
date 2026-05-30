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

  function rememberOriginal(article) {
    if (originalMap.has(article)) {
      return originalMap.get(article);
    }

    var title = qs('[data-translation-title]', article);
    var content = qs('[data-translation-content]', article);
    var original = {
      titleText: title ? title.textContent : '',
      contentHtml: content ? content.innerHTML : ''
    };

    originalMap.set(article, original);
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

  function applyTranslation(panel, article, payload) {
    var title = qs('[data-translation-title]', article);
    var content = qs('[data-translation-content]', article);

    if (title && payload.translatedTitle) {
      title.textContent = payload.translatedTitle;
    }
    if (content && payload.translatedContent) {
      content.innerHTML = payload.translatedContent;
      if (window.jQuery && typeof window.jQuery.fn.viewimageresize === 'function') {
        window.jQuery(content).viewimageresize();
      }
    }

    showNotice(panel, true);
    setActive(panel, payload.targetLanguage || '');
  }

  function restoreOriginal(panel, article) {
    var original = rememberOriginal(article);
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

    showNotice(panel, false);
    setStatus(panel, '');
    setActive(panel, 'original');
  }

  function translate(panel, language) {
    var article = findArticle(panel);
    rememberOriginal(article);

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
    });
  });
})(window, document);
