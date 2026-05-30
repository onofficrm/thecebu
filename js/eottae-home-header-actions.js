/**
 * 홈(빌더) — 헤더 액션 버튼(세부톡·세부일정)을 페이지 GNB와 동일하게 맞춤
 */
(function (global) {
  'use strict';

  function cfg() {
    return global.__EOTTae_HOME_HEADER_ACTIONS__ || null;
  }

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function i18nKeyForText(text) {
    if (global.EottaeI18N && typeof global.EottaeI18N.keyForText === 'function') {
      return global.EottaeI18N.keyForText(text);
    }

    return '';
  }

  function i18nAttrForText(text) {
    var key = i18nKeyForText(text);
    return key ? ' data-i18n="' + esc(key) + '"' : '';
  }

  function t(key, fallback) {
    if (global.EottaeI18N && typeof global.EottaeI18N.t === 'function') {
      var value = global.EottaeI18N.t(key);
      if (value) {
        return value;
      }
    }

    return fallback;
  }

  function navLabel(key, fallback) {
    return t(key, fallback);
  }

  function applyHomeI18n() {
    if (!global.EottaeI18N) {
      return;
    }

    var apply = function () {
      if (typeof global.EottaeI18N.apply === 'function') {
        global.EottaeI18N.apply();
      }
    };

    if (typeof global.EottaeI18N.ready === 'function') {
      global.EottaeI18N.ready().then(apply).catch(apply);
      return;
    }

    apply();
  }

  function renderLanguageSelect(extraClass) {
    return ''
      + '<div class="eottae-language ' + esc(extraClass || '') + '" data-eottae-language-control="1">'
      + '<div class="eottae-language__select-wrap">'
      + '<select class="eottae-language__select" data-eottae-language-select aria-label="언어 선택" data-i18n-aria-label="language.select_label">'
      + '<option value="ko">🇰🇷 한국어</option>'
      + '<option value="ja">🇯🇵 日本語</option>'
      + '<option value="zh">🇨🇳 中文</option>'
      + '<option value="en">🇺🇸 English</option>'
      + '</select>'
      + '</div>'
      + '</div>';
  }

  function findHeaderActionsRow(header) {
    var shopWrite = findShopWriteLink(header);
    if (shopWrite && shopWrite.parentNode) {
      return shopWrite.parentNode;
    }

    var links = header.querySelectorAll('a[href]');
    var i;
    for (i = 0; i < links.length; i += 1) {
      var label = (links[i].textContent || '').replace(/\s+/g, '');
      if (label === 'MY' || label === '로그인' || label === '로그아웃') {
        var row = links[i].parentElement;
        if (row) {
          return row;
        }
      }
    }

    return header;
  }

  function renderMobileMenuItems(items) {
    if (!items || !items.length) {
      return '';
    }

    var html = '';
    var i;
    var j;

    for (i = 0; i < items.length; i += 1) {
      var item = items[i];
      if (!item || !item.label) {
        continue;
      }

      if (item.children && item.children.length) {
        html += ''
          + '<details class="eottae-gnb-header__mobile-group">'
          + '<summary class="eottae-gnb-header__mobile-link eottae-gnb-header__mobile-summary">'
          + '<span' + i18nAttrForText(item.label) + '>' + esc(item.label) + '</span>'
          + '</summary>'
          + '<div class="eottae-gnb-header__mobile-children">';
        for (j = 0; j < item.children.length; j += 1) {
          var child = item.children[j];
          if (!child || !child.label) {
            continue;
          }
          html += ''
            + '<a href="' + esc(child.href || '#') + '" class="eottae-gnb-header__mobile-child-link">'
            + '<span' + i18nAttrForText(child.label) + '>' + esc(child.label) + '</span>'
            + '</a>';
        }
        html += '</div></details>';
      } else {
        html += ''
          + '<a href="' + esc(item.href || '#') + '" class="eottae-gnb-header__mobile-link">'
          + '<span' + i18nAttrForText(item.label) + '>' + esc(item.label) + '</span>'
          + '</a>';
      }
    }

    return html;
  }

  function renderMobileMenuShell(menuData) {
    var data = menuData || {};
    var authHtml = '';

    if (data.is_member) {
      authHtml = ''
        + '<a href="' + esc(data.logout_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost" data-i18n="button.logout">로그아웃</a>';
    } else {
      authHtml = ''
        + '<a href="' + esc(data.login_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost" data-i18n="button.login">로그인</a>'
        + '<a href="' + esc(data.register_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost" data-i18n="button.register">회원가입</a>';
    }

    authHtml += renderLanguageSelect('eottae-language--mobile-header');

    return ''
      + '<div id="siteMobileNav" class="eottae-home-mobile-nav site-header__mobile-nav eottae-gnb-header__mobile" data-eottae-home-mobile-nav="1" aria-hidden="true">'
      + '<div class="eottae-home-mobile-nav__head">'
      + '<strong class="eottae-home-mobile-nav__title" data-i18n="common.all_menu">' + esc(data.title || '전체메뉴') + '</strong>'
      + '<button type="button" class="site-header__mobile-close eottae-home-mobile-nav__close" aria-label="메뉴 닫기" data-i18n-aria-label="common.close_menu">'
      + '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>'
      + '</button>'
      + '</div>'
      + '<nav class="eottae-gnb-header__mobile-nav" aria-label="전체메뉴">'
      + renderMobileMenuItems(data.items || [])
      + '</nav>'
      + '<div class="eottae-gnb-header__mobile-auth">' + authHtml + '</div>'
      + '</div>'
      + '<div class="site-header__overlay eottae-gnb-header__overlay eottae-home-mobile-overlay" data-eottae-home-mobile-overlay="1" aria-hidden="true"></div>';
  }

  function renderHomeGnbShell(data) {
    var menu = data && data.mobile_menu ? data.mobile_menu : {};
    var authHtml = '';
    var logoUrl = menu.logo_url || '';

    if (menu.is_member) {
      authHtml = ''
        + '<a href="' + esc(menu.logout_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop" data-i18n="button.logout">로그아웃</a>';
    } else {
      authHtml = ''
        + '<a href="' + esc(menu.login_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop" data-i18n="button.login">로그인</a>'
        + '<a href="' + esc(menu.register_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop" data-i18n="button.register">회원가입</a>';
    }

    return ''
      + '<div class="eottae-gnb-header__shell" data-eottae-gnb-shell="1" data-eottae-home-gnb-shell="1">'
      + '<div class="eottae-gnb-header__desktop-head" data-eottae-gnb-desktop-head="1">'
      + '<div class="eottae-gnb-header__inner">'
      + '<div class="eottae-gnb-header__left">'
      + '<a href="' + esc(menu.home_url || '/') + '" class="eottae-gnb-header__logo">'
      + (logoUrl
        ? '<img src="' + esc(logoUrl) + '" alt="세부어때" class="eottae-gnb-header__logo-img">'
        : '<span class="eottae-gnb-header__logo-text">세부어때</span>')
      + '</a>'
      + '<nav class="eottae-gnb-header__nav" aria-label="메인메뉴" data-eottae-gnb-nav="1" data-eottae-home-gnb-nav="1">'
      + renderDesktopNavLinks(menu.items || [])
      + '</nav>'
      + '</div>'
      + '<div class="eottae-gnb-header__actions">'
      + authHtml
      + renderLanguageSelect('eottae-language--desktop')
      + (data && data.talk_url ? '<a href="' + esc(data.talk_url) + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--talk eottae-gnb-header__btn--desktop" data-eottae-home-talk-btn="1">' + esc(navLabel('home.talk', data.talk_label || '세부톡')) + '</a>' : '')
      + (data && data.calendar_url ? '<a href="' + esc(data.calendar_url) + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--calendar eottae-gnb-header__btn--desktop" data-eottae-home-calendar-btn="1">' + esc(navLabel('home.calendar', data.calendar_label || '세부일정')) + '</a>' : '')
      + (menu.shop_write_url ? '<a href="' + esc(menu.shop_write_url) + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--register eottae-gnb-header__btn--desktop" data-i18n="button.shop_register">업소등록</a>' : '')
      + '<button type="button" class="eottae-gnb-header__icon-btn eottae-gnb-header__menu-btn site-header__menu-btn" data-eottae-home-menu-btn="1" aria-controls="siteMobileNav" aria-expanded="false" aria-label="메뉴 열기" data-i18n-aria-label="common.open_menu">'
      + '<svg class="eottae-gnb-header__icon eottae-gnb-header__icon--menu" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>'
      + '</button>'
      + '</div>'
      + '</div>'
      + renderDesktopMegaPanel(menu.items || [])
      + '</div>'
      + '</div>';
  }

  function renderDesktopMegaPanel(items) {
    if (!items || !items.length) {
      return '';
    }

    var cols = '';
    var i;
    var j;
    var item;
    var child;

    for (i = 0; i < items.length; i += 1) {
      item = items[i];
      if (!item || !item.children || !item.children.length) {
        continue;
      }

      cols += ''
        + '<div class="eottae-gnb-header__mega-col"'
        + (item.key ? ' data-mega-key="' + esc(item.key) + '"' : '')
        + '>'
        + '<a href="' + esc(item.href || '#') + '" class="eottae-gnb-header__mega-col-title"' + i18nAttrForText(item.label || '') + '>'
        + esc(item.label || '')
        + '</a>'
        + '<ul class="eottae-gnb-header__mega-list">';

      for (j = 0; j < item.children.length; j += 1) {
        child = item.children[j];
        if (!child || !child.label) {
          continue;
        }
        cols += ''
          + '<li><a href="' + esc(child.href || '#') + '" class="eottae-gnb-header__mega-link"' + i18nAttrForText(child.label) + '>'
          + esc(child.label)
          + '</a></li>';
      }

      cols += '</ul></div>';
    }

    if (!cols) {
      return '';
    }

    return ''
      + '<div class="eottae-gnb-header__mega-panel" id="eottaeGnbMegaPanel" data-eottae-gnb-mega data-eottae-home-mega="1"'
      + ' aria-label="전체 서브메뉴" aria-hidden="true">'
      + '<div class="eottae-gnb-header__mega-inner">' + cols + '</div>'
      + '</div>';
  }

  function ensureHomeGnbShell(header) {
    header.classList.add('eottae-gnb-header');

    var shell = header.querySelector('[data-eottae-gnb-shell]');
    if (shell) {
      return shell;
    }

    shell = document.createElement('div');
    shell.className = 'eottae-gnb-header__shell';
    shell.setAttribute('data-eottae-gnb-shell', '1');

    var desktopHead = document.createElement('div');
    desktopHead.className = 'eottae-gnb-header__desktop-head';
    desktopHead.setAttribute('data-eottae-gnb-desktop-head', '1');

    while (header.firstChild) {
      desktopHead.appendChild(header.firstChild);
    }

    shell.appendChild(desktopHead);
    header.appendChild(shell);

    return shell;
  }

  function homeHeaderNeedsFullRebuild(header) {
    if (!header) {
      return false;
    }

    if (header.getAttribute('data-eottae-home-header-rebuilt') !== '1') {
      return true;
    }

    if (!header.querySelector('[data-eottae-home-gnb-shell="1"]')) {
      return true;
    }

    if (!header.querySelector('[data-eottae-home-menu-btn="1"]')) {
      return true;
    }

    return false;
  }

  function rebuildHomeHeader(data) {
    if (!data || !data.mobile_menu || !data.mobile_menu.items || !data.mobile_menu.items.length) {
      return false;
    }

    var header = document.querySelector('header');
    if (!header) {
      return false;
    }

    if (homeHeaderNeedsFullRebuild(header)) {
      header.innerHTML = renderHomeGnbShell(data);
      header.setAttribute('data-eottae-home-header-rebuilt', '1');
      mountHomeMobileMenu(data);
    } else {
      var nav = header.querySelector('[data-eottae-home-gnb-nav="1"]');
      if (nav) {
        nav.innerHTML = renderDesktopNavLinks(data.mobile_menu.items);
      }
    }

    header.classList.add('eottae-gnb-header');
    header.setAttribute('data-eottae-home-gnb-injected', '1');
    applyHomeI18n();
    return true;
  }

  function tagHomeNavMegaKeys(header, items) {
    if (!header || !items || !items.length) {
      return;
    }

    var labelToKey = {};
    var i;

    for (i = 0; i < items.length; i += 1) {
      if (!items[i] || !items[i].key) {
        continue;
      }
      labelToKey[normalizeNavLabel(items[i].label)] = items[i].key;
    }

    var links = header.querySelectorAll('a[href]');
    for (i = 0; i < links.length; i += 1) {
      var link = links[i];
      var key = labelToKey[normalizeNavLabel(link.textContent)];
      if (!key) {
        continue;
      }

      link.setAttribute('data-mega-key', key);
      var hasChildren = false;
      var k;
      for (k = 0; k < items.length; k += 1) {
        if (items[k] && items[k].key === key && items[k].children && items[k].children.length) {
          hasChildren = true;
          break;
        }
      }
      if (hasChildren) {
        link.classList.add('eottae-gnb-header__nav-link--parent');
        link.setAttribute('aria-haspopup', 'true');
      }
    }
  }

  function findLegacyBuilderNavContainer(header) {
    if (!header) {
      return null;
    }

    var containers = header.querySelectorAll('nav, div');
    var i;
    var j;

    for (i = 0; i < containers.length; i += 1) {
      var container = containers[i];
      if (container.getAttribute('data-eottae-home-gnb-nav') === '1') {
        continue;
      }

      var links = container.querySelectorAll('a[href]');
      var hasLodging = false;
      var hasFood = false;
      var legacyCount = 0;
      var primaryCount = 0;

      for (j = 0; j < links.length; j += 1) {
        var label = normalizeNavLabel(links[j].textContent);
        if (label === '숙소') {
          hasLodging = true;
        }
        if (label === '맛집') {
          hasFood = true;
        }
        if (
          label === '숙소'
          || label === '맛집'
          || label === '마사지'
          || label === '가이드'
          || label === '골프'
          || label === '자유'
          || label === '생활정보'
          || label === '이벤트'
          || label === '구인구직'
          || label === '부동산'
          || label === '광고방'
        ) {
          legacyCount += 1;
        }
        if (
          label === '홈'
          || label.indexOf('내주변') !== -1
          || label.indexOf('커뮤니티') !== -1
          || label.indexOf('생활지도') !== -1
          || label === '골프조인'
          || label === '컬럼'
        ) {
          primaryCount += 1;
        }
      }

      if (hasLodging && hasFood && links.length >= 5) {
        return container;
      }

      if (hasLodging && hasFood && primaryCount === 0) {
        return container;
      }

      if (hasFood && legacyCount >= 3) {
        return container;
      }

      if (legacyCount >= 4 && primaryCount <= 2) {
        return container;
      }
    }

    return null;
  }

  function hideLegacyBuilderNavs(header) {
    var guard = 0;
    var legacyNav = findLegacyBuilderNavContainer(header);

    while (legacyNav && guard < 8) {
      legacyNav.setAttribute('data-eottae-home-legacy-nav-hidden', '1');
      legacyNav.style.setProperty('display', 'none', 'important');
      guard += 1;
      legacyNav = findLegacyBuilderNavContainer(header);
    }
  }

  function renderNavLinkInner(label, hasChildren) {
    return ''
      + (hasChildren ? '<span class="eottae-gnb-header__nav-caret" aria-hidden="true"></span>' : '')
      + '<span class="eottae-gnb-header__nav-label"' + i18nAttrForText(label) + '>' + esc(label) + '</span>';
  }

  function renderDesktopNavLinks(items) {
    if (!items || !items.length) {
      return '';
    }

    var html = '';
    var i;
    var item;
    var hasChildren;

    for (i = 0; i < items.length; i += 1) {
      item = items[i];
      if (!item || !item.label) {
        continue;
      }

      hasChildren = !!(item.children && item.children.length);
      html += ''
        + '<a href="' + esc(item.href || '#') + '"'
        + ' class="eottae-gnb-header__nav-link'
        + (hasChildren ? ' eottae-gnb-header__nav-link--parent' : '')
        + '"'
        + (item.key ? ' data-mega-key="' + esc(item.key) + '"' : '')
        + (hasChildren ? ' aria-haspopup="true"' : '')
        + '>'
        + renderNavLinkInner(item.label, hasChildren);
      html += '</a>';
    }

    return html;
  }

  function replaceHomePrimaryNav(data) {
    if (!data || !data.mobile_menu || !data.mobile_menu.items || !data.mobile_menu.items.length) {
      return;
    }

    if (rebuildHomeHeader(data)) {
      return;
    }

    var header = document.querySelector('header');
    if (!header) {
      return;
    }

    var shell = ensureHomeGnbShell(header);
    var desktopHead = shell.querySelector('.eottae-gnb-header__desktop-head') || shell;
    var legacyNav = findLegacyBuilderNavContainer(header);
    var nav = header.querySelector('[data-eottae-home-gnb-nav="1"]');

    if (!nav) {
      nav = document.createElement('nav');
      nav.className = 'eottae-gnb-header__nav';
      nav.setAttribute('aria-label', '메인메뉴');
      nav.setAttribute('data-eottae-gnb-nav', '1');
      nav.setAttribute('data-eottae-home-gnb-nav', '1');
      nav.innerHTML = renderDesktopNavLinks(data.mobile_menu.items);

      if (legacyNav && legacyNav.parentNode) {
        legacyNav.parentNode.insertBefore(nav, legacyNav);
      } else {
        var logo = header.querySelector('a[href="/"]');
        if (logo && logo.parentNode) {
          logo.parentNode.appendChild(nav);
        } else {
          desktopHead.insertBefore(nav, desktopHead.firstChild);
        }
      }
    } else {
      nav.innerHTML = renderDesktopNavLinks(data.mobile_menu.items);
    }

    hideLegacyBuilderNavs(header);

    header.setAttribute('data-eottae-home-gnb-injected', '1');
    tagHomeNavMegaKeys(header, data.mobile_menu.items);
    mountDesktopMegaPanel(data);
    applyHomeI18n();
  }

  function mountDesktopMegaPanel(data) {
    if (!data || !data.mobile_menu || !data.mobile_menu.items || !data.mobile_menu.items.length) {
      return;
    }

    var header = document.querySelector('header');
    if (!header || header.querySelector('[data-eottae-home-mega="1"]')) {
      return;
    }

    var items = data.mobile_menu.items;
    var shell = ensureHomeGnbShell(header);
    tagHomeNavMegaKeys(header, items);

    var megaHtml = renderDesktopMegaPanel(items);
    if (!megaHtml) {
      return;
    }

    shell.insertAdjacentHTML('beforeend', megaHtml);
  }

  function findHeaderMenuButtonHost(header, actions) {
    if (actions && actions !== header) {
      return actions;
    }

    var shopWrite = findShopWriteLink(header);
    if (shopWrite && shopWrite.parentNode) {
      return shopWrite.parentNode;
    }

    var candidates = header.querySelectorAll('div, nav');
    var i;
    for (i = 0; i < candidates.length; i += 1) {
      if (candidates[i].querySelector('a[href]')) {
        return candidates[i];
      }
    }

    return header;
  }

  function getHomeMobileMenuNodes() {
    return {
      menu: document.querySelector('[data-eottae-home-mobile-nav="1"]'),
      overlay: document.querySelector('[data-eottae-home-mobile-overlay="1"]'),
    };
  }

  function setHomeMobileMenuOpen(open) {
    var nodes = getHomeMobileMenuNodes();
    var menu = nodes.menu;
    if (!menu) {
      return;
    }

    var overlay = nodes.overlay;
    var on = !!open;

    menu.classList.toggle('is-open', on);
    if (overlay) {
      overlay.classList.toggle('is-open', on);
    }

    menu.setAttribute('aria-hidden', on ? 'false' : 'true');
    if (overlay) {
      overlay.setAttribute('aria-hidden', on ? 'false' : 'true');
    }

    document.body.style.overflow = on ? 'hidden' : '';

    document.querySelectorAll('[data-eottae-home-menu-btn="1"]').forEach(function (btn) {
      btn.setAttribute('aria-expanded', on ? 'true' : 'false');
      btn.setAttribute('aria-label', global.EottaeI18N ? global.EottaeI18N.t(on ? 'common.close_menu' : 'common.open_menu') || (on ? '메뉴 닫기' : '메뉴 열기') : (on ? '메뉴 닫기' : '메뉴 열기'));
    });
  }

  function findHomeMenuOpenButton(target) {
    if (!(target instanceof Element)) {
      return null;
    }

    return target.closest(
      '[data-eottae-home-menu-btn="1"], '
      + '#root header.eottae-gnb-header .eottae-gnb-header__menu-btn, '
      + '#root header.eottae-gnb-header .site-header__menu-btn'
    );
  }

  function isHomeHeaderMenuButton(btn) {
    if (!btn) {
      return false;
    }

    var header = btn.closest('header');
    if (!header) {
      return false;
    }

    if (header.getAttribute('data-eottae-home-header-rebuilt') === '1') {
      return true;
    }

    var root = document.getElementById('root');
    return !!(root && root.contains(header));
  }

  function ensureHomeMobileMenuControls() {
    if (document.documentElement.getAttribute('data-eottae-home-menu-controls') === '1') {
      return;
    }

    document.documentElement.setAttribute('data-eottae-home-menu-controls', '1');

    document.addEventListener('click', function (event) {
      var target = event.target;
      if (!(target instanceof Element)) {
        return;
      }

      var openBtn = findHomeMenuOpenButton(target);
      if (openBtn) {
        if (!isHomeHeaderMenuButton(openBtn)) {
          return;
        }

        var menu = document.querySelector('[data-eottae-home-mobile-nav="1"]');
        if (!menu) {
          return;
        }

        event.preventDefault();
        event.stopPropagation();
        setHomeMobileMenuOpen(!menu.classList.contains('is-open'));
        return;
      }

      if (target.closest('.eottae-home-mobile-nav__close, .site-header__mobile-close')) {
        event.preventDefault();
        setHomeMobileMenuOpen(false);
        return;
      }

      if (target.closest('[data-eottae-home-mobile-overlay="1"]')) {
        setHomeMobileMenuOpen(false);
      }
    }, true);

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') {
        return;
      }

      var menu = document.querySelector('[data-eottae-home-mobile-nav="1"]');
      if (menu && menu.classList.contains('is-open')) {
        setHomeMobileMenuOpen(false);
      }
    });
  }

  function mountHomeMobileMenu(data) {
    if (!data || !data.mobile_menu || !data.mobile_menu.items || !data.mobile_menu.items.length) {
      return;
    }

    var header = document.querySelector('header');
    if (!header) {
      return;
    }

    var actions = findHeaderActionsRow(header);
    var buttonHost = findHeaderMenuButtonHost(header, actions);

    var openBtn = header.querySelector('[data-eottae-home-menu-btn="1"]');
    if (!openBtn) {
      openBtn = document.createElement('button');
      openBtn.type = 'button';
      openBtn.className = 'eottae-gnb-header__icon-btn eottae-gnb-header__menu-btn site-header__menu-btn';
      openBtn.setAttribute('data-eottae-home-menu-btn', '1');
      openBtn.setAttribute('aria-controls', 'siteMobileNav');
      openBtn.setAttribute('aria-expanded', 'false');
      openBtn.setAttribute('aria-label', '메뉴 열기');
      openBtn.setAttribute('data-i18n-aria-label', 'common.open_menu');
      openBtn.innerHTML = ''
        + '<svg class="eottae-gnb-header__icon eottae-gnb-header__icon--menu" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">'
        + '<path d="M4 6h16M4 12h16M4 18h16"/>'
        + '</svg>';

      var shopWrite = findShopWriteLink(header);
      if (shopWrite && shopWrite.parentNode === buttonHost) {
        buttonHost.insertBefore(openBtn, shopWrite);
      } else {
        buttonHost.appendChild(openBtn);
      }
    }

    var menu = document.querySelector('[data-eottae-home-mobile-nav="1"]');
    if (!menu) {
      menu = document.getElementById('siteMobileNav');
    }

    if (!menu) {
      var wrap = document.createElement('div');
      wrap.setAttribute('data-eottae-home-mobile-shell', '1');
      wrap.innerHTML = renderMobileMenuShell(data.mobile_menu);
      document.body.appendChild(wrap);
      menu = document.querySelector('[data-eottae-home-mobile-nav="1"]');
    }

    if (!menu) {
      return;
    }

    ensureHomeMobileMenuControls();
    applyHomeI18n();
  }

  function isShopWriteLabel(text) {
    var label = (text || '').replace(/\s+/g, '');
    return label === '업소등록' || label === '업체등록';
  }

  function isShopWriteHref(href) {
    if (!href) {
      return false;
    }
    if (href.indexOf('안내') !== -1) {
      return false;
    }
    return (href.indexOf('bo_table=shop') !== -1 || href.indexOf('bo_table%3Dshop') !== -1)
      && href.indexOf('write') !== -1;
  }

  function findShopWriteLink(scope) {
    if (!scope) {
      return null;
    }

    var links = scope.querySelectorAll('a');
    var i;
    var hrefMatch = null;
    for (i = 0; i < links.length; i += 1) {
      var link = links[i];
      if (isShopWriteLabel(link.textContent)) {
        return link;
      }
      if (!hrefMatch && isShopWriteHref(link.getAttribute('href') || '')) {
        hrefMatch = link;
      }
    }

    return hrefMatch;
  }

  function buildActionButton(data, extraClass, attrName, href, label) {
    var btn = document.createElement('a');
    btn.href = href;
    btn.className = 'eottae-gnb-header__btn eottae-home-header-pill ' + (extraClass || '');
    btn.setAttribute(attrName, '1');
    btn.textContent = label;
    return btn;
  }

  function buildTalkButton(data, extraClass) {
    return buildActionButton(
      data,
      'eottae-gnb-header__btn--talk ' + (extraClass || ''),
      'data-eottae-home-talk-btn',
      data.talk_url,
      data.talk_label || '세부톡'
    );
  }

  function buildCalendarButton(data, extraClass) {
    return buildActionButton(
      data,
      'eottae-gnb-header__btn--calendar ' + (extraClass || ''),
      'data-eottae-home-calendar-btn',
      data.calendar_url,
      data.calendar_label || '세부일정'
    );
  }

  function normalizeShopWriteLink(link, isMobile) {
    if (!link) {
      return;
    }

    /* React Tailwind(bg-primary-500·text-white 등)이 GNB pill 색상을 덮어쓰지 않도록 클래스·인라인 스타일 제거 */
    link.removeAttribute('style');
    link.className = 'eottae-gnb-header__btn eottae-gnb-header__btn--register eottae-home-header-pill'
      + (isMobile
        ? ' eottae-gnb-header__btn--mobile-action col-span-2'
        : ' eottae-gnb-header__btn--desktop hidden sm:inline-flex');
  }

  function mountDesktop(data) {
    var header = document.querySelector('header');
    if (!header) {
      return;
    }

    var shopWrite = findShopWriteLink(header);
    if (!shopWrite || !shopWrite.parentNode) {
      return;
    }

    normalizeShopWriteLink(shopWrite, false);

    if (!header.querySelector('[data-eottae-home-calendar-btn="1"]') && data.calendar_url) {
      var calendarBtn = buildCalendarButton(data, 'eottae-gnb-header__btn--desktop hidden sm:inline-flex');
      shopWrite.parentNode.insertBefore(calendarBtn, shopWrite);
    }

    if (!header.querySelector('[data-eottae-home-talk-btn="1"]') && data.talk_url) {
      var talkBtn = buildTalkButton(data, 'eottae-gnb-header__btn--desktop hidden sm:inline-flex');
      shopWrite.parentNode.insertBefore(talkBtn, shopWrite);
    }
  }

  function findMobileMenuNav() {
    var siteNav = document.querySelector('#siteMobileNav .eottae-gnb-header__mobile-nav');
    if (siteNav) {
      return siteNav;
    }

    var mobileNav = document.querySelector('.eottae-gnb-header__mobile-nav');
    if (mobileNav) {
      return mobileNav;
    }

    var header = document.querySelector('header');
    if (!header) {
      return null;
    }

    var links = header.querySelectorAll('a[href]');
    var i;
    for (i = 0; i < links.length; i += 1) {
      var label = (links[i].textContent || '').replace(/\s+/g, '');
      if (label === '홈' || label === '내주변') {
        var nav = links[i].closest('nav');
        if (nav) {
          return nav;
        }
      }
    }

    return null;
  }

  function removeMobileHeaderCalendarButtons(header) {
    if (!header) {
      return;
    }

    header.querySelectorAll('[data-eottae-home-calendar-btn]').forEach(function (node) {
      if (node.parentNode) {
        node.parentNode.removeChild(node);
      }
    });
  }

  function mountCalendarInMobileMenu(data) {
    if (!data.calendar_url) {
      return;
    }

    var menu = findMobileMenuNav();
    if (!menu) {
      return;
    }

    if (menu.querySelector('[data-eottae-home-calendar-menu="1"]')) {
      return;
    }

    var link = document.createElement('a');
    link.href = data.calendar_url;
    link.className = 'eottae-gnb-header__mobile-link eottae-gnb-header__mobile-link--accent';
    link.setAttribute('data-eottae-home-calendar-menu', '1');
    link.textContent = data.calendar_label || '세부일정';

    var talkMenu = menu.querySelector('[href*="/talk"]');
    if (talkMenu && talkMenu.parentNode === menu) {
      menu.insertBefore(link, talkMenu.nextSibling);
    } else {
      menu.appendChild(link);
    }
  }

  function mountMobile(data) {
    var header = document.querySelector('header');
    if (!header) {
      return;
    }

    removeMobileHeaderCalendarButtons(header);

    var shopWrite = null;
    var links = header.querySelectorAll('a');
    var i;
    for (i = 0; i < links.length; i += 1) {
      var link = links[i];
      if (!isShopWriteLabel(link.textContent) && !isShopWriteHref(link.getAttribute('href') || '')) {
        continue;
      }
      if ((link.className || '').indexOf('col-span-2') !== -1) {
        shopWrite = link;
        break;
      }
    }

    if (shopWrite && shopWrite.parentNode) {
      normalizeShopWriteLink(shopWrite, true);

      if (!header.querySelector('[data-eottae-home-talk-btn="mobile"]') && data.talk_url) {
        var talkBtn = buildTalkButton(data, 'eottae-gnb-header__btn--mobile-action');
        talkBtn.setAttribute('data-eottae-home-talk-btn', 'mobile');
        shopWrite.parentNode.insertBefore(talkBtn, shopWrite);
      }
    }

    mountCalendarInMobileMenu(data);
  }

  function normalizeNavLabel(text) {
    return (text || '').replace(/\s+/g, '');
  }

  function columnNavExists(scope) {
    if (!scope) {
      return false;
    }

    var links = scope.querySelectorAll('a[href]');
    var i;
    for (i = 0; i < links.length; i += 1) {
      var href = links[i].getAttribute('href') || '';
      var label = normalizeNavLabel(links[i].textContent);
      if (label.indexOf('생활정보컬럼') !== -1 || label === '컬럼' || href.indexOf('/column') !== -1) {
        return true;
      }
    }

    return false;
  }

  function adroomNavExists(scope) {
    if (!scope) {
      return false;
    }

    var links = scope.querySelectorAll('a[href]');
    var i;
    for (i = 0; i < links.length; i += 1) {
      var href = links[i].getAttribute('href') || '';
      var label = normalizeNavLabel(links[i].textContent);
      if (label === '광고방' || href.indexOf('/ad-room') !== -1 || href.indexOf('bo_table=adroom') !== -1 || href.indexOf('bo_table%3Dadroom') !== -1) {
        return true;
      }
    }

    return false;
  }

  function findNavLinkByLabel(scope, labelText) {
    if (!scope) {
      return null;
    }

    var target = normalizeNavLabel(labelText);
    var links = scope.querySelectorAll('a[href]');
    var i;
    for (i = 0; i < links.length; i += 1) {
      if (normalizeNavLabel(links[i].textContent) === target) {
        return links[i];
      }
    }

    return null;
  }

  function applyDesktopNavLinkLabel(link, label, hasChildren) {
    if (!link) {
      return;
    }

    var showCaret = typeof hasChildren === 'boolean'
      ? hasChildren
      : link.classList.contains('eottae-gnb-header__nav-link--parent');
    link.innerHTML = renderNavLinkInner(label || '', showCaret);
  }

  function buildColumnNavLink(data, className, attrName) {
    var link = document.createElement('a');
    link.href = data.column_url;
    link.className = className || 'eottae-gnb-header__nav-link';
    link.setAttribute(attrName, '1');
    applyDesktopNavLinkLabel(link, data.column_label || '컬럼', true);
    return link;
  }

  function buildAdroomNavLink(data, className, attrName) {
    var link = document.createElement('a');
    link.href = data.adroom_url;
    link.className = className || 'eottae-gnb-header__nav-link';
    link.setAttribute(attrName, '1');
    applyDesktopNavLinkLabel(link, data.adroom_label || '광고방', false);
    return link;
  }

  function freeNavExists(scope) {
    if (!scope) {
      return false;
    }

    var links = scope.querySelectorAll('a[href]');
    var i;
    for (i = 0; i < links.length; i += 1) {
      var href = links[i].getAttribute('href') || '';
      var label = normalizeNavLabel(links[i].textContent);
      if (
        label === '자유게시판'
        || href.indexOf('/free') !== -1
        || href.indexOf('bo_table=free') !== -1
        || href.indexOf('bo_table%3Dfree') !== -1
      ) {
        return true;
      }
    }

    return false;
  }

  function buildFreeNavLink(data, className, attrName) {
    var link = document.createElement('a');
    link.href = data.free_url;
    link.className = className || 'eottae-gnb-header__nav-link';
    link.setAttribute(attrName, '1');
    if (className && className.indexOf('eottae-gnb-header__nav-link') !== -1) {
      applyDesktopNavLinkLabel(link, data.free_label || '자유게시판', false);
    } else {
      link.textContent = data.free_label || '자유게시판';
    }
    return link;
  }

  function findFreeInsertAfter(scope) {
    var column = scope.querySelector('[data-eottae-home-column-nav="1"], [data-eottae-home-column-menu="1"]');
    if (column) {
      return column;
    }
    return findNavLinkByLabel(scope, '컬럼');
  }

  function findAdroomInsertAfter(scope) {
    return findFreeInsertAfter(scope) || findNavLinkByLabel(scope, '커뮤니티');
  }

  function mountAdroomNav(data) {
    if (!data.adroom_url) {
      return;
    }

    var header = document.querySelector('header');
    if (!header || adroomNavExists(header)) {
      return;
    }

    var anchor = findAdroomInsertAfter(header);
    if (!anchor || !anchor.parentNode) {
      return;
    }

    var className = anchor.className || 'eottae-gnb-header__nav-link';
    if (className.indexOf('eottae-gnb-header__nav-link') === -1) {
      className = 'eottae-gnb-header__nav-link';
    }

    var adroomLink = buildAdroomNavLink(data, className, 'data-eottae-home-adroom-nav');
    anchor.parentNode.insertBefore(adroomLink, anchor.nextSibling);
  }

  function mountColumnNav(data) {
    if (!data.column_url) {
      return;
    }

    var header = document.querySelector('header');
    if (!header || columnNavExists(header)) {
      return;
    }

    var community = findNavLinkByLabel(header, '커뮤니티');
    if (!community || !community.parentNode) {
      return;
    }

    var columnLink = buildColumnNavLink(
      data,
      community.className || 'eottae-gnb-header__nav-link',
      'data-eottae-home-column-nav'
    );
    community.parentNode.insertBefore(columnLink, community.nextSibling);
  }

  function mountFreeNav(data) {
    if (!data.free_url) {
      return;
    }

    var header = document.querySelector('header');
    if (!header || freeNavExists(header)) {
      return;
    }

    var anchor = findFreeInsertAfter(header);
    if (!anchor || !anchor.parentNode) {
      return;
    }

    var className = anchor.className || 'eottae-gnb-header__nav-link';
    if (className.indexOf('eottae-gnb-header__nav-link') === -1) {
      className = 'eottae-gnb-header__nav-link';
    }

    var freeLink = buildFreeNavLink(data, className, 'data-eottae-home-free-nav');
    anchor.parentNode.insertBefore(freeLink, anchor.nextSibling);
  }

  function mountFreeInMobileMenu(data) {
    if (!data.free_url) {
      return;
    }

    var menu = findMobileMenuNav();
    if (!menu || menu.querySelector('[data-eottae-home-free-menu="1"]')) {
      return;
    }

    if (freeNavExists(menu)) {
      return;
    }

    var anchor = findFreeInsertAfter(menu);
    var link = buildFreeNavLink(
      data,
      'eottae-gnb-header__mobile-link',
      'data-eottae-home-free-menu'
    );
    link.setAttribute('data-eottae-home-free-menu', '1');

    if (anchor && anchor.parentNode === menu) {
      menu.insertBefore(link, anchor.nextSibling);
      return;
    }

    menu.appendChild(link);
  }

  function mountColumnInMobileMenu(data) {
    if (!data.column_url) {
      return;
    }

    var menu = findMobileMenuNav();
    if (!menu || menu.querySelector('[data-eottae-home-column-menu="1"]')) {
      return;
    }

    if (columnNavExists(menu)) {
      return;
    }

    var community = findNavLinkByLabel(menu, '커뮤니티');
    var link = buildColumnNavLink(
      data,
      'eottae-gnb-header__mobile-link',
      'data-eottae-home-column-menu'
    );
    link.setAttribute('data-eottae-home-column-menu', '1');

    if (community && community.parentNode === menu) {
      menu.insertBefore(link, community.nextSibling);
      return;
    }

    menu.appendChild(link);
  }

  function mountAdroomInMobileMenu(data) {
    if (!data.adroom_url) {
      return;
    }

    var menu = findMobileMenuNav();
    if (!menu || menu.querySelector('[data-eottae-home-adroom-menu="1"]')) {
      return;
    }

    if (adroomNavExists(menu)) {
      return;
    }

    var anchor = findAdroomInsertAfter(menu);
    var link = buildAdroomNavLink(
      data,
      'eottae-gnb-header__mobile-link',
      'data-eottae-home-adroom-menu'
    );
    link.setAttribute('data-eottae-home-adroom-menu', '1');

    if (anchor && anchor.parentNode === menu) {
      menu.insertBefore(link, anchor.nextSibling);
      return;
    }

    menu.appendChild(link);
  }

  function replaceTourNavWithGolfJoin(data) {
    if (!data || !data.golf_join_url) {
      return;
    }

    var scopes = [document.querySelector('header'), findMobileMenuNav()];
    var s;
    for (s = 0; s < scopes.length; s += 1) {
      var scope = scopes[s];
      if (!scope) {
        continue;
      }
      var links = scope.querySelectorAll('a[href]');
      var i;
      for (i = 0; i < links.length; i += 1) {
        var link = links[i];
        var label = normalizeNavLabel(link.textContent);
        var href = link.getAttribute('href') || '';
        if (label === '투어' || href.indexOf('bo_table=tour') !== -1 || href.indexOf('bo_table%3Dtour') !== -1) {
          link.href = data.golf_join_url;
          link.setAttribute('data-eottae-home-golf-nav', '1');
          if (link.classList.contains('eottae-gnb-header__nav-link')) {
            applyDesktopNavLinkLabel(link, data.golf_join_label || '골프조인', true);
          } else {
            link.textContent = data.golf_join_label || '골프조인';
          }
        }
      }
    }
  }

  function hideBoardMenuLinks(boardKeys, labels) {
    var pattern = boardKeys
      .map(function (key) {
        return 'a[href*="bo_table=' + key + '"], a[href*="bo_table%3D' + key + '"]';
      })
      .join(', ');
    var links = pattern ? document.querySelectorAll(pattern) : [];
    var i;
    for (i = 0; i < links.length; i += 1) {
      var link = links[i];
      if (link.parentNode) {
        link.parentNode.removeChild(link);
      }
    }

    var textLinks = document.querySelectorAll('a, button');
    for (i = 0; i < textLinks.length; i += 1) {
      var node = textLinks[i];
      var normalized = (node.textContent || '').replace(/\s+/g, '');
      if (labels.indexOf(normalized) === -1) {
        continue;
      }
      var item = node.closest('li') || node.closest('nav') || node.parentElement;
      if (item && item.parentNode && item !== document.body) {
        item.parentNode.removeChild(item);
      } else if (node.parentNode) {
        node.parentNode.removeChild(node);
      }
    }
  }

  function hideRentcarMenuLinks() {
    hideBoardMenuLinks(['rentcar'], ['렌트카']);
  }

  function hideMassageMenuLinks() {
    hideBoardMenuLinks(['massage'], ['마사지', '마사지·스파']);
  }

  function hideCommunityHubMenuLinks() {
    hideBoardMenuLinks(['free', 'people'], ['자유게시판', '사람찾기']);
  }

  function mount() {
    hideRentcarMenuLinks();
    hideMassageMenuLinks();
    hideCommunityHubMenuLinks();

    var data = cfg();
    if (!data) {
      return;
    }

    replaceTourNavWithGolfJoin(data);
    replaceHomePrimaryNav(data);
    mountHomeMobileMenu(data);
    mountDesktop(data);
    mountMobile(data);
    mountCalendarInMobileMenu(data);
    mountColumnNav(data);
    mountColumnInMobileMenu(data);
    mountAdroomNav(data);
    mountAdroomInMobileMenu(data);
    hideRentcarMenuLinks();
    hideMassageMenuLinks();
    hideCommunityHubMenuLinks();
    replaceTourNavWithGolfJoin(data);
    applyHomeI18n();
  }

  function init() {
    ensureHomeMobileMenuControls();

    var run = function () {
      mount();
    };

    var schedule = function () {
      run();
      global.setTimeout(run, 400);
      global.setTimeout(run, 1200);
      global.setTimeout(run, 2800);
    };

    if (typeof global.eottaeHomeAfterReactReady === 'function') {
      global.eottaeHomeAfterReactReady(schedule);
      global.setTimeout(schedule, 4000);
      return;
    }

    schedule();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeHomeHeaderActions = init;
}(window));
