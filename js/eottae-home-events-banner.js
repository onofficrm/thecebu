/**
 * 홈(빌더) — 업체 이벤트/기획전 배너 (댓글 많은 글 블록)
 */
(function (global) {
  'use strict';

  var AUTOPLAY_MS = 3000;
  var SWIPE_MIN = 40;

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
    var d = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) {
      return '';
    }
    return (d.getMonth() + 1) + '.' + d.getDate();
  }

  function findCommentsBlockCard() {
    var headings = document.querySelectorAll('h3');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      if ((headings[i].textContent || '').indexOf('댓글 많은 글') !== -1) {
        var card = headings[i].closest('div');
        while (card && card.parentElement) {
          if ((card.className || '').indexOf('rounded-[20px]') !== -1) {
            return card;
          }
          card = card.parentElement;
        }
      }
    }
    return null;
  }

  function buildBanner(events, listUrl) {
    var slides = '';
    var dots = '';
    var i;

    for (i = 0; i < events.length; i += 1) {
      var event = events[i];
      slides += ''
        + '<article class="home-events-banner__slide" data-slide-index="' + i + '">'
        + '<a href="' + esc(event.url || listUrl) + '" class="home-events-banner__link">'
        + '<span class="home-events-banner__category">' + esc(event.category || '이벤트') + '</span>'
        + '<strong class="home-events-banner__subject">' + esc(event.subject || '이벤트') + '</strong>'
        + (event.content ? '<p class="home-events-banner__desc">' + esc(event.content) + '</p>' : '')
        + (event.datetime ? '<span class="home-events-banner__date">' + esc(formatDate(event.datetime)) + '</span>' : '')
        + '</a>'
        + '</article>';
      dots += ''
        + '<button type="button" class="home-events-banner__dot' + (i === 0 ? ' is-active' : '') + '"'
        + ' data-banner-dot="' + i + '" aria-label="' + esc((i + 1) + '번 슬라이드') + '"></button>';
    }

    var root = document.createElement('div');
    root.className = 'home-events-banner';
    root.setAttribute('data-eottae-events-banner', '1');
    root.innerHTML = ''
      + '<div class="home-events-banner__viewport">'
      + '<div class="home-events-banner__track">' + slides + '</div>'
      + '</div>'
      + (events.length > 1
        ? '<div class="home-events-banner__dots" aria-hidden="true">' + dots + '</div>'
        : '');

  return root;
}

function bindCarousel(root) {
    if (!root || root.dataset.bound === '1') {
      return;
    }
    root.dataset.bound = '1';

    var track = root.querySelector('.home-events-banner__track');
    var dots = root.querySelectorAll('[data-banner-dot]');
    var slides = root.querySelectorAll('.home-events-banner__slide');
    if (!track || !slides.length) {
      return;
    }

    var active = 0;
    var timer = null;
    var startX = 0;
    var startY = 0;

    function goTo(index) {
      active = (index + slides.length) % slides.length;
      track.style.transform = 'translate3d(-' + (active * 100) + '%, 0, 0)';
      dots.forEach(function (dot, idx) {
        dot.classList.toggle('is-active', idx === active);
      });
    }

    function next() {
      goTo(active + 1);
    }

    function restartTimer() {
      if (timer) {
        clearInterval(timer);
      }
      if (slides.length <= 1) {
        return;
      }
      timer = setInterval(next, AUTOPLAY_MS);
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        goTo(Number(dot.getAttribute('data-banner-dot')) || 0);
        restartTimer();
      });
    });

    root.addEventListener('touchstart', function (e) {
      if (!e.changedTouches || !e.changedTouches.length) {
        return;
      }
      startX = e.changedTouches[0].clientX;
      startY = e.changedTouches[0].clientY;
    }, { passive: true });

    root.addEventListener('touchend', function (e) {
      if (!e.changedTouches || !e.changedTouches.length || slides.length <= 1) {
        return;
      }
      var dx = e.changedTouches[0].clientX - startX;
      var dy = e.changedTouches[0].clientY - startY;
      if (Math.abs(dx) < SWIPE_MIN || Math.abs(dx) < Math.abs(dy)) {
        return;
      }
      goTo(dx < 0 ? active + 1 : active - 1);
      restartTimer();
    }, { passive: true });

    goTo(0);
    restartTimer();
  }

  function mount() {
    var data = cfg();
    if (!data || !data.events || !data.events.length) {
      return;
    }

    var card = findCommentsBlockCard();
    if (!card || card.querySelector('[data-eottae-events-banner]')) {
      return;
    }

    var headingWrap = card.querySelector('.flex.items-center.justify-between');
    if (headingWrap) {
      var title = headingWrap.querySelector('h3');
      if (title) {
        title.textContent = '업체 이벤트 / 기획전';
      }
      var more = headingWrap.querySelector('a');
      if (more && data.list_url) {
        more.setAttribute('href', data.list_url);
      }
    }

    var listContainer = card.querySelector('.flex.flex-col.gap-3\\.5');
    if (!listContainer) {
      listContainer = card.querySelector('.flex.flex-col');
    }
    if (!listContainer) {
      return;
    }

    listContainer.innerHTML = '';
    var banner = buildBanner(data.events, data.list_url || '/page/eottae-events.php');
    listContainer.appendChild(banner);
    bindCarousel(banner);
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

  global.initEottaeHomeEventsBanner = init;
}(window));
