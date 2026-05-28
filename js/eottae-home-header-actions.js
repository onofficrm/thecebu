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
          + '<span>' + esc(item.label) + '</span>'
          + '</summary>'
          + '<div class="eottae-gnb-header__mobile-children">';
        for (j = 0; j < item.children.length; j += 1) {
          var child = item.children[j];
          if (!child || !child.label) {
            continue;
          }
          html += ''
            + '<a href="' + esc(child.href || '#') + '" class="eottae-gnb-header__mobile-child-link">'
            + esc(child.label)
            + '</a>';
        }
        html += '</div></details>';
      } else {
        html += ''
          + '<a href="' + esc(item.href || '#') + '" class="eottae-gnb-header__mobile-link">'
          + esc(item.label)
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
        + '<a href="' + esc(data.mypage_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost">MY</a>'
        + '<a href="' + esc(data.logout_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost">로그아웃</a>';
    } else {
      authHtml = ''
        + '<a href="' + esc(data.login_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost">로그인</a>'
        + '<a href="' + esc(data.register_url || '#') + '" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost">회원가입</a>';
    }

    return ''
      + '<div id="siteMobileNav" class="eottae-home-mobile-nav site-header__mobile-nav eottae-gnb-header__mobile" data-eottae-home-mobile-nav="1" aria-hidden="true">'
      + '<div class="eottae-home-mobile-nav__head">'
      + '<strong class="eottae-home-mobile-nav__title">' + esc(data.title || '전체메뉴') + '</strong>'
      + '<button type="button" class="site-header__mobile-close eottae-home-mobile-nav__close" aria-label="메뉴 닫기">'
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

  function initHomeMobileMenuToggle(openBtn, menu, overlay, closeBtn) {
    if (!openBtn || !menu || openBtn.getAttribute('data-eottae-menu-toggle-bound') === '1') {
      return;
    }

    openBtn.setAttribute('data-eottae-menu-toggle-bound', '1');

    function setOpen(open) {
      var on = !!open;
      menu.classList.toggle('is-open', on);
      if (overlay) {
        overlay.classList.toggle('is-open', on);
      }
      openBtn.setAttribute('aria-expanded', on ? 'true' : 'false');
      openBtn.setAttribute('aria-label', on ? '메뉴 닫기' : '메뉴 열기');
      menu.setAttribute('aria-hidden', on ? 'false' : 'true');
      if (overlay) {
        overlay.setAttribute('aria-hidden', on ? 'false' : 'true');
      }
      document.body.style.overflow = on ? 'hidden' : '';
    }

    openBtn.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      setOpen(!menu.classList.contains('is-open'));
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function (event) {
        event.preventDefault();
        setOpen(false);
      });
    }

    if (overlay) {
      overlay.addEventListener('click', function () {
        setOpen(false);
      });
    }

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && menu.classList.contains('is-open')) {
        setOpen(false);
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
    if (!actions) {
      return;
    }

    var openBtn = header.querySelector('[data-eottae-home-menu-btn="1"]');
    if (!openBtn) {
      openBtn = document.createElement('button');
      openBtn.type = 'button';
      openBtn.className = 'eottae-gnb-header__icon-btn eottae-gnb-header__menu-btn site-header__menu-btn';
      openBtn.setAttribute('data-eottae-home-menu-btn', '1');
      openBtn.setAttribute('aria-controls', 'siteMobileNav');
      openBtn.setAttribute('aria-expanded', 'false');
      openBtn.setAttribute('aria-label', '메뉴 열기');
      openBtn.innerHTML = ''
        + '<svg class="eottae-gnb-header__icon eottae-gnb-header__icon--menu" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">'
        + '<path d="M4 6h16M4 12h16M4 18h16"/>'
        + '</svg>';

      var shopWrite = findShopWriteLink(header);
      if (shopWrite && shopWrite.parentNode === actions) {
        actions.insertBefore(openBtn, shopWrite);
      } else {
        actions.appendChild(openBtn);
      }
    }

    var menu = document.getElementById('siteMobileNav');
    var overlay = document.querySelector('[data-eottae-home-mobile-overlay="1"]');

    if (!menu) {
      var wrap = document.createElement('div');
      wrap.setAttribute('data-eottae-home-mobile-shell', '1');
      wrap.innerHTML = renderMobileMenuShell(data.mobile_menu);
      document.body.appendChild(wrap);
      menu = document.getElementById('siteMobileNav');
      overlay = document.querySelector('[data-eottae-home-mobile-overlay="1"]');
    }

    if (!menu) {
      return;
    }

    var closeBtn = menu.querySelector('.eottae-home-mobile-nav__close');
    initHomeMobileMenuToggle(openBtn, menu, overlay, closeBtn);
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

  function buildColumnNavLink(data, className, attrName) {
    var link = document.createElement('a');
    link.href = data.column_url;
    link.className = className || 'eottae-gnb-header__nav-link';
    link.setAttribute(attrName, '1');
    link.textContent = data.column_label || '컬럼';
    return link;
  }

  function buildAdroomNavLink(data, className, attrName) {
    var link = document.createElement('a');
    link.href = data.adroom_url;
    link.className = className || 'eottae-gnb-header__nav-link';
    link.setAttribute(attrName, '1');
    link.textContent = data.adroom_label || '광고방';
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
    link.textContent = data.free_label || '자유게시판';
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
          link.textContent = data.golf_join_label || '골프조인';
          link.href = data.golf_join_url;
          link.setAttribute('data-eottae-home-golf-nav', '1');
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
  }

  function init() {
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
