/**
 * 홈(빌더) — 헤더 액션 버튼(세부톡)을 페이지 GNB와 동일하게 맞춤
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

  function buildTalkButton(data, extraClass) {
    var btn = document.createElement('a');
    btn.href = data.talk_url;
    btn.className = 'eottae-gnb-header__btn eottae-gnb-header__btn--talk ' + (extraClass || '');
    btn.setAttribute('data-eottae-home-talk-btn', '1');
    btn.textContent = data.talk_label || '세부톡';
    return btn;
  }

  function mountDesktop(data) {
    var header = document.querySelector('header');
    if (!header || header.querySelector('[data-eottae-home-talk-btn="1"]')) {
      return;
    }

    var shopWrite = findShopWriteLink(header);
    if (!shopWrite || !shopWrite.parentNode) {
      return;
    }

    var btn = buildTalkButton(data, 'eottae-gnb-header__btn--desktop hidden sm:inline-flex');
    shopWrite.parentNode.insertBefore(btn, shopWrite);
  }

  function mountMobile(data) {
    var header = document.querySelector('header');
    if (!header || header.querySelector('[data-eottae-home-talk-btn="mobile"]')) {
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

    var btn = buildTalkButton(data, 'eottae-home-header-talk-btn--mobile col-span-2 rounded-xl py-3 text-center text-sm font-bold');
    btn.setAttribute('data-eottae-home-talk-btn', 'mobile');
    shopWrite.parentNode.insertBefore(btn, shopWrite);
  }

  function mount() {
    var data = cfg();
    if (!data || !data.talk_url) {
      return;
    }

    mountDesktop(data);
    mountMobile(data);
  }

  function init() {
    mount();
    if (typeof MutationObserver === 'undefined') {
      return;
    }

    var root = document.getElementById('root');
    if (!root) {
      return;
    }

    new MutationObserver(mount).observe(root, {
      childList: true,
      subtree: true,
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeHomeHeaderActions = init;
}(window));
