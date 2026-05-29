(function () {
  'use strict';

  var attempts = 0;
  var maxAttempts = 30;

  function normalize(text) {
    return String(text || '').replace(/\s+/g, '');
  }

  function closestBlock(node) {
    while (node && node !== document.body) {
      if (
        node.matches &&
        node.matches('section, [data-eottae-home-main-host], .sebu-home-main-host-wrap, .sebu-home-main-section')
      ) {
        return node;
      }
      node = node.parentElement;
    }
    return null;
  }

  function findHeadingAnchor() {
    var markers = [
      '전체실시간인기글',
      '실시간인기글',
      '커뮤니티인기글',
      '오늘의세부커뮤니티',
      '세부최신소식'
    ];
    var headings = document.querySelectorAll('h2, h3');
    var i;
    var m;
    var text;

    for (m = 0; m < markers.length; m += 1) {
      for (i = 0; i < headings.length; i += 1) {
        text = normalize(headings[i].textContent);
        if (text.indexOf(markers[m]) !== -1) {
          return closestBlock(headings[i]);
        }
      }
    }

    return null;
  }

  function findAnchor() {
    var hot = document.querySelector('[data-eottae-home-popular-hot-mounted="1"]');
    if (hot) {
      return hot.closest('[data-eottae-home-main-host]') || closestBlock(hot) || hot;
    }

    var community = document.querySelector('[data-eottae-home-main-mounted="1"]');
    if (community) {
      return community.closest('[data-eottae-home-main-host]') || closestBlock(community) || community;
    }

    return findHeadingAnchor();
  }

  function mountColumn() {
    var column = document.getElementById('eottae-home-column');
    if (!column) {
      return true;
    }

    var anchor = findAnchor();
    if (!anchor || !anchor.parentNode || anchor === column || column.contains(anchor)) {
      return false;
    }

    var next = anchor.nextSibling;
    if (next !== column) {
      anchor.parentNode.insertBefore(column, next);
    }

    column.setAttribute('data-eottae-home-column-mounted', '1');
    return true;
  }

  function scheduleMount() {
    if (mountColumn()) {
      return;
    }

    attempts += 1;
    if (attempts >= maxAttempts) {
      return;
    }

    window.setTimeout(scheduleMount, 250);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scheduleMount);
  } else {
    scheduleMount();
  }

  window.addEventListener('load', scheduleMount);
})();
