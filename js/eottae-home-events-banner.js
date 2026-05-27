/**
 * 홈(빌더) — 업체 이벤트/기획전 2행 카드 (히어로 3열 사이드바)
 */
(function (global) {
  'use strict';

  var DISPLAY_COUNT = 2;

  function cfg() {
    return global.__EOTTae_HOME_EVENTS_BANNER__ || null;
  }

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function formatDate(value) {
    if (!value) {
      return '';
    }
    var raw = String(value).trim();
    if (/^\d{4}-\d{2}-\d{2}/.test(raw)) {
      return raw.slice(0, 10);
    }
    var d = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) {
      return '';
    }
    var month = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return d.getFullYear() + '-' + month + '-' + day;
  }

  function normalizeText(value) {
    return String(value || '').replace(/\s+/g, ' ').trim();
  }

  function isEventsHeading(text) {
    return text.indexOf('업체 이벤트') !== -1
      || (text.indexOf('기획전') !== -1 && text.indexOf('업체') !== -1);
  }

  function isTalkHeading(text) {
    return text === '세부톡' || text.indexOf('세부톡') === 0;
  }

  function sidebarChildFromNode(sidebar, node) {
    if (!sidebar || !node) {
      return null;
    }

    var current = node;
    while (current && current.parentElement && current.parentElement !== sidebar) {
      current = current.parentElement;
    }

    return current && current.parentElement === sidebar ? current : null;
  }

  function findHeroSidebar() {
    if (typeof global.findEottaeHeroSidebarColumn === 'function') {
      return global.findEottaeHeroSidebarColumn();
    }

    var headings = document.querySelectorAll('h1');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      if ((headings[i].textContent || '').indexOf('세부어때') !== -1) {
        var section = headings[i].closest('section');
        var grid = section && section.parentElement;
        if (!grid) {
          continue;
        }
        var nodes = grid.children;
        var j;
        for (j = 0; j < nodes.length; j += 1) {
          if ((nodes[j].className || '').indexOf('lg:col-span-4') !== -1) {
            return nodes[j];
          }
        }
        return nodes.length > 1 ? nodes[nodes.length - 1] : null;
      }
    }
    return null;
  }

  function findSidebarLegacyEventsBlock(sidebar) {
    if (!sidebar) {
      return null;
    }

    var headings = sidebar.querySelectorAll('h2, h3');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      if (!isEventsHeading(normalizeText(headings[i].textContent))) {
        continue;
      }
      var block = sidebarChildFromNode(sidebar, headings[i]);
      if (block && !block.querySelector('[data-eottae-events-stack]')) {
        return block;
      }
    }

    return null;
  }

  function findSidebarTalkBlock(sidebar) {
    if (!sidebar) {
      return null;
    }

    var headings = sidebar.querySelectorAll('h2, h3, h4');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      if (!isTalkHeading(normalizeText(headings[i].textContent))) {
        continue;
      }
      return sidebarChildFromNode(sidebar, headings[i]);
    }

    return null;
  }

  function removeDuplicateLegacyEvents(sidebar, keepNode) {
    if (!sidebar) {
      return;
    }

    var children = Array.prototype.slice.call(sidebar.children);
    var i;

    for (i = 0; i < children.length; i += 1) {
      var child = children[i];
      if (!child || child === keepNode) {
        continue;
      }
      if (child.classList && child.classList.contains('home-hero-sidebar-events')) {
        continue;
      }

      var heading = child.querySelector('h2, h3');
      if (!heading) {
        continue;
      }

      if (!isEventsHeading(normalizeText(heading.textContent))) {
        continue;
      }

      if (child.parentNode) {
        child.parentNode.removeChild(child);
      }
    }
  }

  function buildEventsStack(events, listUrl) {
    var cards = '';
    var i;

    for (i = 0; i < events.length && i < DISPLAY_COUNT; i += 1) {
      var event = events[i];
      cards += ''
        + '<article class="home-events-stack__item">'
        + '<a href="' + esc(event.url || listUrl) + '" class="home-events-stack__link">'
        + '<div class="home-events-stack__meta">'
        + '<span class="home-events-stack__badge">' + esc(event.category || '진행중') + '</span>'
        + (event.datetime ? '<span class="home-events-stack__date">' + esc(formatDate(event.datetime)) + '</span>' : '')
        + '</div>'
        + '<strong class="home-events-stack__subject">' + esc(event.subject || '이벤트') + '</strong>'
        + (event.content ? '<p class="home-events-stack__desc">' + esc(event.content) + '</p>' : '')
        + '</a>'
        + '</article>';
    }

    var root = document.createElement('div');
    root.className = 'home-events-stack';
    root.setAttribute('data-eottae-events-stack', '1');
    root.innerHTML = ''
      + '<div class="home-events-stack__head">'
      + '<span class="home-events-stack__mark" aria-hidden="true"></span>'
      + '<h2 class="home-events-stack__title">업체 이벤트 / 기획전</h2>'
      + '</div>'
      + '<div class="home-events-stack__list">' + cards + '</div>';

    return root;
  }

  function buildWrap(stack) {
    var wrap = document.createElement('div');
    wrap.className = 'home-hero-sidebar-events';
    wrap.setAttribute('aria-label', '업체 이벤트 / 기획전');
    wrap.setAttribute('data-eottae-events-banner-mounted', '1');
    wrap.appendChild(stack);
    return wrap;
  }

  function replaceCarouselWithStack(sidebar) {
    if (!sidebar) {
      return;
    }

    var carousel = sidebar.querySelector('[data-eottae-events-banner]');
    if (carousel && carousel.parentNode) {
      carousel.parentNode.removeChild(carousel);
    }
  }

  function mount() {
    var data = cfg();
    if (!data || !data.events || !data.events.length) {
      return false;
    }

    var sidebar = findHeroSidebar();
    if (!sidebar) {
      return false;
    }

    var events = data.events.slice(0, DISPLAY_COUNT);
    if (!events.length) {
      return false;
    }

    var existingWrap = sidebar.querySelector('.home-hero-sidebar-events[data-eottae-events-banner-mounted]');
    if (existingWrap && existingWrap.querySelector('[data-eottae-events-stack]')) {
      existingWrap.classList.remove('home-hero-sidebar-events--fill');
      removeDuplicateLegacyEvents(sidebar, existingWrap);
      replaceCarouselWithStack(sidebar);
      if (typeof global.mountEottaeHomeHeroSidebar === 'function') {
        global.mountEottaeHomeHeroSidebar();
      }
      if (typeof global.scheduleEottaeHeroColumnHeights === 'function') {
        global.scheduleEottaeHeroColumnHeights(80);
      }
      return true;
    }

    if (existingWrap) {
      existingWrap.parentNode.removeChild(existingWrap);
    }

    replaceCarouselWithStack(sidebar);

    var legacy = findSidebarLegacyEventsBlock(sidebar);
    var talkBlock = findSidebarTalkBlock(sidebar);
    var stack = buildEventsStack(events, data.list_url || '/page/eottae-events.php');
    var wrap = buildWrap(stack);

    if (legacy && legacy.parentNode) {
      legacy.parentNode.replaceChild(wrap, legacy);
    } else if (talkBlock && talkBlock.parentNode === sidebar) {
      sidebar.insertBefore(wrap, talkBlock);
    } else {
      sidebar.appendChild(wrap);
    }

    removeDuplicateLegacyEvents(sidebar, wrap);

    if (typeof global.mountEottaeHomeHeroSidebar === 'function') {
      global.mountEottaeHomeHeroSidebar();
    }

    if (typeof global.scheduleEottaeHeroColumnHeights === 'function') {
      global.scheduleEottaeHeroColumnHeights(80);
    }

    return true;
  }

  function init() {
    var run = function () {
      mount();
    };

    if (typeof global.eottaeHomeAfterReactReady === 'function') {
      global.eottaeHomeAfterReactReady(run);
    } else {
      global.setTimeout(run, 1500);
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
        if (!sidebar) {
          return;
        }
        if (!sidebar.querySelector('[data-eottae-events-stack]')) {
          mount();
        }
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

  global.initEottaeHomeEventsBanner = init;
}(window));
