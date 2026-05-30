(function (window, document) {
  'use strict';

  var config = window.__EOTTaeI18N__ || {};
  var defaultLanguage = config.defaultLanguage || 'ko';
  var storageKey = config.storageKey || 'cebuatteLanguage';
  var supportedLanguages = config.supportedLanguages || ['ko', 'en', 'ja', 'zh'];
  var localesBaseUrl = config.localesBaseUrl || '/locales';
  var dictionaries = {};
  var currentLanguage = defaultLanguage;
  var staticKoDictionary = {
    'menu.home': '홈',
    'menu.nearby': '내주변',
    'menu.nearby_food': '내 주변 맛집',
    'menu.nearby_business': '내 주변 업체',
    'menu.nearby_hospital': '내 주변 병원',
    'menu.nearby_convenience': '내 주변 생활편의',
    'menu.community': '커뮤니티',
    'menu.life_info': '생활정보',
    'menu.free_board': '자유게시판',
    'menu.business_review': '업체리뷰',
    'menu.people_finder': '사람찾기',
    'menu.events_promotions': '이벤트/프로모션',
    'menu.report_box': '제보함',
    'menu.life_map': '생활지도',
    'menu.all_map': '전체지도',
    'menu.jobs': '구인구직',
    'menu.market': '중고장터',
    'menu.real_estate': '부동산',
    'menu.golf_join': '골프조인',
    'menu.join_recruit': '조인 모집',
    'menu.recruiting': '모집중',
    'menu.closed': '마감',
    'menu.golf_course_info': '골프장 정보',
    'menu.column': '컬럼',
    'menu.all_columns': '전체 컬럼',
    'menu.columnist_intro': '컬럼리스트 소개',
    'menu.columnist_apply': '컬럼리스트 신청',
    'menu.media': '미디어',
    'menu.gallery': '갤러리',
    'menu.youtube': '유튜브',
    'menu.my': 'MY',
    'menu.my_profile': '내 프로필',
    'menu.my_posts': '내가 쓴 글',
    'menu.saved_posts': '찜한 글',
    'menu.my_business': '내 업체',
    'menu.my_applications': '내 신청내역',
    'menu.messages': '쪽지',
    'button.login': '로그인',
    'button.logout': '로그아웃',
    'button.register': '회원가입',
    'button.shop_register': '업소등록',
    'button.business_register': '업체등록'
  };

  function isSupported(language) {
    return supportedLanguages.indexOf(language) !== -1;
  }

  function normalizeLanguage(language) {
    var value = String(language || '').toLowerCase();

    if (value.indexOf('ko') === 0) {
      return 'ko';
    }
    if (value.indexOf('en') === 0) {
      return 'en';
    }
    if (value.indexOf('ja') === 0) {
      return 'ja';
    }
    if (value === 'zh' || value.indexOf('zh-') === 0) {
      return 'zh';
    }

    return '';
  }

  function getStoredLanguage() {
    try {
      var stored = window.localStorage.getItem(storageKey);
      return isSupported(stored) ? stored : '';
    } catch (error) {
      return '';
    }
  }

  function detectBrowserLanguage() {
    var candidates = [];

    if (window.navigator.languages && window.navigator.languages.length) {
      candidates = Array.prototype.slice.call(window.navigator.languages);
    }
    if (window.navigator.language) {
      candidates.push(window.navigator.language);
    }

    for (var i = 0; i < candidates.length; i += 1) {
      var normalized = normalizeLanguage(candidates[i]);
      if (isSupported(normalized)) {
        return normalized;
      }
    }

    return '';
  }

  function resolveInitialLanguage() {
    return getStoredLanguage() || detectBrowserLanguage() || defaultLanguage;
  }

  function readNestedValue(source, path) {
    var parts = String(path || '').split('.');
    var value = source;

    for (var i = 0; i < parts.length; i += 1) {
      if (!value || typeof value !== 'object' || !(parts[i] in value)) {
        return '';
      }
      value = value[parts[i]];
    }

    return typeof value === 'string' ? value : '';
  }

  function translate(key) {
    var dictionary = dictionaries[currentLanguage] || {};
    var fallback = dictionaries[defaultLanguage] || {};

    return readNestedValue(dictionary, key) || dictionary[key] || readNestedValue(fallback, key) || fallback[key] || '';
  }

  function setText(element, value) {
    if (element.tagName === 'OPTION') {
      element.textContent = value;
      return;
    }

    element.textContent = value;
  }

  function applyTranslations() {
    document.documentElement.setAttribute('lang', currentLanguage);
    document.documentElement.setAttribute('data-eottae-language', currentLanguage);

    document.querySelectorAll('[data-i18n]').forEach(function (element) {
      var value = translate(element.getAttribute('data-i18n'));
      if (value) {
        setText(element, value);
      }
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (element) {
      var value = translate(element.getAttribute('data-i18n-placeholder'));
      if (value) {
        element.setAttribute('placeholder', value);
      }
    });

    document.querySelectorAll('[data-i18n-title]').forEach(function (element) {
      var value = translate(element.getAttribute('data-i18n-title'));
      if (value) {
        element.setAttribute('title', value);
      }
    });

    document.querySelectorAll('[data-i18n-aria-label]').forEach(function (element) {
      var value = translate(element.getAttribute('data-i18n-aria-label'));
      if (value) {
        element.setAttribute('aria-label', value);
      }
    });

    document.querySelectorAll('[data-eottae-language-select]').forEach(function (select) {
      select.value = currentLanguage;
    });

    if (typeof window.CustomEvent === 'function') {
      window.dispatchEvent(new CustomEvent('eottae:languagechange', {
        detail: {
          language: currentLanguage
        }
      }));
    }
  }

  function loadDictionary(language) {
    if (dictionaries[language]) {
      return Promise.resolve(dictionaries[language]);
    }

    return window.fetch(localesBaseUrl.replace(/\/$/, '') + '/' + language + '.json', {
      credentials: 'same-origin'
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Failed to load locale: ' + language);
        }
        return response.json();
      })
      .then(function (dictionary) {
        dictionaries[language] = dictionary || {};
        return dictionaries[language];
      });
  }

  function setLanguage(language, persist) {
    var nextLanguage = isSupported(language) ? language : defaultLanguage;

    currentLanguage = nextLanguage;
    if (persist) {
      try {
        window.localStorage.setItem(storageKey, nextLanguage);
      } catch (error) { /* ignore */ }
    }

    return Promise.all([
      loadDictionary(defaultLanguage),
      loadDictionary(nextLanguage)
    ]).then(function () {
      applyTranslations();
      return nextLanguage;
    }).catch(function () {
      if (nextLanguage !== defaultLanguage) {
        currentLanguage = defaultLanguage;
        return loadDictionary(defaultLanguage).then(function () {
          applyTranslations();
          return defaultLanguage;
        });
      }
      applyTranslations();
      return defaultLanguage;
    });
  }

  function initSelectors() {
    document.addEventListener('change', function (event) {
      var target = event.target;
      if (!(target instanceof Element) || !target.matches('[data-eottae-language-select]')) {
        return;
      }
      setLanguage(target.value, true);
    });
  }

  window.EottaeI18N = {
    getLanguage: function () {
      return currentLanguage;
    },
    setLanguage: function (language) {
      return setLanguage(language, true);
    },
    t: translate,
    apply: applyTranslations,
    keyForText: function (text) {
      var normalized = String(text || '').replace(/\s+/g, ' ').trim();
      var dictionary = dictionaries[defaultLanguage] || staticKoDictionary;
      var staticMap = {
        '홈': 'menu.home',
        '내주변': 'menu.nearby',
        '내 주변 맛집': 'menu.nearby_food',
        '내 주변 업체': 'menu.nearby_business',
        '내 주변 병원': 'menu.nearby_hospital',
        '내 주변 생활편의': 'menu.nearby_convenience',
        '커뮤니티': 'menu.community',
        '생활정보': 'menu.life_info',
        '자유게시판': 'menu.free_board',
        '업체리뷰': 'menu.business_review',
        '사람찾기': 'menu.people_finder',
        '이벤트/프로모션': 'menu.events_promotions',
        '제보함': 'menu.report_box',
        '생활지도': 'menu.life_map',
        '전체지도': 'menu.all_map',
        '구인구직': 'menu.jobs',
        '중고장터': 'menu.market',
        '부동산': 'menu.real_estate',
        '골프조인': 'menu.golf_join',
        '조인 모집': 'menu.join_recruit',
        '모집중': 'menu.recruiting',
        '마감': 'menu.closed',
        '골프장 정보': 'menu.golf_course_info',
        '컬럼': 'menu.column',
        '전체 컬럼': 'menu.all_columns',
        '컬럼리스트 소개': 'menu.columnist_intro',
        '컬럼리스트 신청': 'menu.columnist_apply',
        '미디어': 'menu.media',
        '갤러리': 'menu.gallery',
        '유튜브': 'menu.youtube',
        'MY': 'menu.my',
        '내 프로필': 'menu.my_profile',
        '내가 쓴 글': 'menu.my_posts',
        '찜한 글': 'menu.saved_posts',
        '내 업체': 'menu.my_business',
        '내 신청내역': 'menu.my_applications',
        '쪽지': 'menu.messages',
        '로그인': 'button.login',
        '로그아웃': 'button.logout',
        '회원가입': 'button.register',
        '업소등록': 'button.shop_register',
        '업체등록': 'button.business_register'
      };
      var key;

      if (staticMap[normalized]) {
        return staticMap[normalized];
      }

      for (key in dictionary) {
        if (Object.prototype.hasOwnProperty.call(dictionary, key) && dictionary[key] === normalized) {
          return key;
        }
      }

      return '';
    }
  };

  currentLanguage = resolveInitialLanguage();
  initSelectors();

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setLanguage(currentLanguage, false);
    });
  } else {
    setLanguage(currentLanguage, false);
  }
})(window, document);
