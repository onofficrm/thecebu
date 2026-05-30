/**
 * 홈(빌더) — 히어로 3열 사이드바: 비로그인 로그인 박스 높이·하단 정렬
 */
(function (global) {
  'use strict';

  var MOUNTED_ATTR = 'data-eottae-hero-sidebar-mounted';

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function findHeroSidebar() {
    if (typeof global.findEottaeHeroSidebarColumn === 'function') {
      return global.findEottaeHeroSidebarColumn();
    }
    return null;
  }

  function cfg() {
    return global.__EOTTae_HOME_HERO_SIDEBAR__ || {};
  }

  function findMemberLoginTarget(sidebar) {
    if (!sidebar) {
      return null;
    }

    return sidebar.querySelector('.community-sidebar__login')
      || sidebar.querySelector('section.community-login-box')
      || sidebar.querySelector('.community-login-box--member')
      || sidebar.querySelector('.community-login-box:not(.community-login-box--guest)');
  }

  function upgradeMemberLoginBox(sidebar) {
    var templateRoot = document.getElementById('eottae-home-login-box-template');
    if (!templateRoot || !sidebar) {
      return false;
    }

    var source = templateRoot.querySelector('.community-sidebar__login');
    if (!source || !source.querySelector('.community-login-box--member')) {
      return false;
    }

    var target = findMemberLoginTarget(sidebar);
    if (!target || target.getAttribute('data-eottae-login-upgraded') === '1') {
      return false;
    }

    var clone = source.cloneNode(true);
    clone.setAttribute('data-eottae-login-upgraded', '1');
    target.replaceWith(clone);
    return true;
  }

  function enrichGuestLoginBox(guestBox) {
    if (!guestBox || guestBox.getAttribute('data-eottae-guest-enriched') === '1') {
      return;
    }
    if (guestBox.querySelector('.community-login-box__stats--guest')) {
      guestBox.setAttribute('data-eottae-guest-enriched', '1');
      return;
    }

    var data = cfg();
    var cta = guestBox.querySelector('.community-login-box__cta');
    var links = guestBox.querySelector('.community-login-box__links');
    if (!cta) {
      return;
    }

    var stats = document.createElement('div');
    stats.className = 'community-login-box__stats community-login-box__stats--guest';
    stats.setAttribute('aria-hidden', 'true');
    stats.innerHTML = ''
      + '<div class="community-login-box__stat community-login-box__stat--guest">'
      + '<span class="community-login-box__stat-label">포인트</span>'
      + '<strong class="community-login-box__stat-value">활동·리뷰 적립</strong>'
      + '</div>'
      + '<div class="community-login-box__stat community-login-box__stat--guest">'
      + '<span class="community-login-box__stat-label">쿠폰</span>'
      + '<strong class="community-login-box__stat-value">할인·이벤트</strong>'
      + '</div>';

    guestBox.insertBefore(stats, cta);

    guestBox.setAttribute('data-eottae-guest-enriched', '1');
  }

  function patchGuestLoginUrls(guestBox) {
    var data = cfg();
    var loginUrl = data.login_url;
    var registerUrl = data.register_url;
    var passwordUrl = data.password_url;
    if (!guestBox) {
      return;
    }

    var loginCta = guestBox.querySelector('.community-login-box__cta');
    if (loginCta && loginUrl) {
      loginCta.setAttribute('href', loginUrl);
    }

    var linkEls = guestBox.querySelectorAll('.community-login-box__links a');
    if (linkEls.length > 0 && registerUrl) {
      linkEls[0].setAttribute('href', registerUrl);
    }
    if (linkEls.length > 1 && passwordUrl) {
      linkEls[1].setAttribute('href', passwordUrl);
    }
  }

  function layoutSidebar(sidebar) {
    if (!sidebar) {
      return false;
    }

    sidebar.classList.add('home-hero-sidebar-column');

    if (upgradeMemberLoginBox(sidebar)) {
      if (typeof global.scheduleEottaeHeroColumnHeights === 'function') {
        global.scheduleEottaeHeroColumnHeights(40);
        global.scheduleEottaeHeroColumnHeights(200);
      }
    }

    var guestBox = sidebar.querySelector('.community-login-box--guest');
    if (guestBox) {
      patchGuestLoginUrls(guestBox);
      enrichGuestLoginBox(guestBox);
    }

    var eventsWrap = sidebar.querySelector('.home-hero-sidebar-events');
    if (eventsWrap) {
      eventsWrap.classList.remove('home-hero-sidebar-events--fill');
    }

    var legacyEvents = sidebar.querySelector('section.flex-1');
    if (legacyEvents && eventsWrap && legacyEvents !== eventsWrap && !legacyEvents.contains(eventsWrap)) {
      legacyEvents.style.display = 'none';
    }

    sidebar.setAttribute(MOUNTED_ATTR, '1');

    if (typeof global.scheduleEottaeHeroColumnHeights === 'function') {
      global.scheduleEottaeHeroColumnHeights(40);
      global.scheduleEottaeHeroColumnHeights(200);
      global.scheduleEottaeHeroColumnHeights(600);
    } else if (typeof global.syncEottaeHeroColumnHeights === 'function') {
      global.syncEottaeHeroColumnHeights();
    }

    return true;
  }

  function mount() {
    return layoutSidebar(findHeroSidebar());
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
      global.setTimeout(schedule, 4000);
    } else {
      schedule();
    }

    if (typeof MutationObserver === 'undefined') {
      return;
    }

    var root = document.getElementById('root');
    if (!root) {
      return;
    }

    var scheduled = false;
    var observer = new MutationObserver(function () {
      if (scheduled) {
        return;
      }
      scheduled = true;
      global.requestAnimationFrame(function () {
        scheduled = false;
        var sidebar = findHeroSidebar();
        if (!sidebar || sidebar.getAttribute(MOUNTED_ATTR) === '1') {
          if (sidebar) {
            upgradeMemberLoginBox(sidebar);
            if (sidebar.querySelector('.community-login-box--guest:not([data-eottae-guest-enriched])')) {
              layoutSidebar(sidebar);
            }
          }
          return;
        }
        layoutSidebar(sidebar);
      });
    });

    observer.observe(root, {
      childList: true,
      subtree: true,
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.mountEottaeHomeHeroSidebar = mount;
  global.initEottaeHomeHeroSidebar = init;
}(window));
