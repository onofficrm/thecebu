/**
 * 홈(빌더) — 헤더 액션 버튼(세부톡·세부일정)을 페이지 GNB와 동일하게 맞춤
 */
(function (global) {
  'use strict';

  function cfg() {
    return global.__EOTTae_HOME_HEADER_ACTIONS__ || null;
  }

  function findShopWriteLink(scope) {
    if (!scope) {
      return null;
    }

    var links = scope.querySelectorAll('a');
    var i;
    for (i = 0; i < links.length; i += 1) {
      if ((links[i].textContent || '').trim() === '업소등록') {
        return links[i];
      }
    }

    return null;
  }

  function buildActionButton(data, extraClass, attrName, href, label) {
    var btn = document.createElement('a');
    btn.href = href;
    btn.className = 'eottae-gnb-header__btn ' + (extraClass || '');
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

  function mountDesktop(data) {
    var header = document.querySelector('header');
    if (!header) {
      return;
    }

    var shopWrite = findShopWriteLink(header);
    if (!shopWrite || !shopWrite.parentNode) {
      return;
    }

    if (!header.querySelector('[data-eottae-home-calendar-btn="1"]') && data.calendar_url) {
      var calendarBtn = buildCalendarButton(data, 'eottae-gnb-header__btn--desktop hidden sm:inline-flex');
      shopWrite.parentNode.insertBefore(calendarBtn, shopWrite);
    }

    if (!header.querySelector('[data-eottae-home-talk-btn="1"]') && data.talk_url) {
      var talkBtn = buildTalkButton(data, 'eottae-gnb-header__btn--desktop hidden sm:inline-flex');
      shopWrite.parentNode.insertBefore(talkBtn, shopWrite);
    }
  }

  function mountMobile(data) {
    var header = document.querySelector('header');
    if (!header) {
      return;
    }

    var shopWrite = null;
    var links = header.querySelectorAll('a');
    var i;
    for (i = 0; i < links.length; i += 1) {
      var link = links[i];
      if ((link.textContent || '').trim() !== '업소등록') {
        continue;
      }
      if ((link.className || '').indexOf('col-span-2') !== -1) {
        shopWrite = link;
        break;
      }
    }

    if (!shopWrite || !shopWrite.parentNode) {
      return;
    }

    if (!header.querySelector('[data-eottae-home-calendar-btn="mobile"]') && data.calendar_url) {
      var calendarBtn = buildCalendarButton(
        data,
        'eottae-home-header-calendar-btn--mobile col-span-2 rounded-xl py-3 text-center text-sm font-bold'
      );
      calendarBtn.setAttribute('data-eottae-home-calendar-btn', 'mobile');
      shopWrite.parentNode.insertBefore(calendarBtn, shopWrite);
    }

    if (!header.querySelector('[data-eottae-home-talk-btn="mobile"]') && data.talk_url) {
      var talkBtn = buildTalkButton(
        data,
        'eottae-home-header-talk-btn--mobile col-span-2 rounded-xl py-3 text-center text-sm font-bold'
      );
      talkBtn.setAttribute('data-eottae-home-talk-btn', 'mobile');
      shopWrite.parentNode.insertBefore(talkBtn, shopWrite);
    }
  }

  function hideRentcarMenuLinks() {
    var links = document.querySelectorAll('a[href*="bo_table=rentcar"], a[href*="bo_table%3Drentcar"]');
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
      if ((node.textContent || '').replace(/\s+/g, '') === '렌트카') {
        var item = node.closest('li') || node.closest('nav') || node.parentElement;
        if (item && item.parentNode && item !== document.body) {
          item.parentNode.removeChild(item);
        } else if (node.parentNode) {
          node.parentNode.removeChild(node);
        }
      }
    }
  }

  function mount() {
    hideRentcarMenuLinks();

    var data = cfg();
    if (!data) {
      return;
    }

    mountDesktop(data);
    mountMobile(data);
    hideRentcarMenuLinks();
  }

  function init() {
    var run = function () {
      mount();
    };

    if (typeof global.eottaeHomeAfterReactReady === 'function') {
      global.eottaeHomeAfterReactReady(run);
      return;
    }

    global.setTimeout(run, 1500);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeHomeHeaderActions = init;
}(window));
