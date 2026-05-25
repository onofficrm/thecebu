/**
 * 홈(빌더) — 히어로 3열: 신규·화제 톡방 위젯 삽입
 */
(function (global) {
  'use strict';

  function cfg() {
    return global.__EOTTae_HOME_HERO_TALK__ || null;
  }

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function formatCount(value) {
    var n = Number(value) || 0;
    return n.toLocaleString('ko-KR');
  }

  function findHeroGrid() {
    var headings = document.querySelectorAll('h1');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      if ((headings[i].textContent || '').indexOf('세부어때') !== -1) {
        var section = headings[i].closest('section');
        if (section && section.parentElement) {
          return section.parentElement;
        }
      }
    }
    return null;
  }

  function findSidebarColumn(grid) {
    if (!grid) {
      return null;
    }

    var nodes = grid.children;
    var i;
    for (i = 0; i < nodes.length; i += 1) {
      if ((nodes[i].className || '').indexOf('lg:col-span-4') !== -1) {
        return nodes[i];
      }
    }

    return nodes.length > 1 ? nodes[nodes.length - 1] : null;
  }

  function renderRoomItem(room) {
    if (!room || !room.enter_href) {
      return '';
    }

    var stats = [];
    if (room.member_count != null) {
      stats.push('참여 ' + formatCount(room.member_count));
    }
    if (room.post_count != null) {
      stats.push('글 ' + formatCount(room.post_count));
    }
    if (room.updated_label) {
      stats.push(room.updated_label);
    }

    return ''
      + '<li class="home-hero-talk-rooms__item">'
      + '<a href="' + esc(room.enter_href) + '" class="home-hero-talk-rooms__link">'
      + '<span class="home-hero-talk-rooms__emoji" aria-hidden="true">' + esc(room.emoji || '💬') + '</span>'
      + '<span class="home-hero-talk-rooms__body">'
      + '<strong class="home-hero-talk-rooms__name">' + esc(room.room_name || '톡방') + '</strong>'
      + (room.category ? '<span class="home-hero-talk-rooms__category">' + esc(room.category) + '</span>' : '')
      + (stats.length ? '<span class="home-hero-talk-rooms__meta">' + esc(stats.join(' · ')) + '</span>' : '')
      + '</span>'
      + '</a>'
      + '</li>';
  }

  function renderSection(title, badgeClass, rooms) {
    if (!rooms || !rooms.length) {
      return '';
    }

    var html = '';
    var i;
    for (i = 0; i < rooms.length; i += 1) {
      html += renderRoomItem(rooms[i]);
    }
    if (!html) {
      return '';
    }

    return ''
      + '<section class="home-hero-talk-rooms__block">'
      + '<header class="home-hero-talk-rooms__block-head">'
      + '<h3 class="home-hero-talk-rooms__block-title">'
      + '<span class="home-hero-talk-rooms__badge ' + badgeClass + '"></span>'
      + esc(title)
      + '</h3>'
      + '</header>'
      + '<ul class="home-hero-talk-rooms__list">' + html + '</ul>'
      + '</section>';
  }

  function buildPanel(data) {
    var newHtml = renderSection('신규 톡방', 'home-hero-talk-rooms__badge--new', data.new);
    var hotHtml = renderSection('화제의 톡방', 'home-hero-talk-rooms__badge--hot', data.hot);
    var body = newHtml + hotHtml;

    if (!body) {
      body = ''
        + '<div class="home-hero-talk-rooms__empty">'
        + '<p>아직 공개된 톡방이 없습니다.</p>'
        + '<a href="' + esc(data.create_url || '/page/eottae-talk-create.php') + '">톡방 만들기</a>'
        + '</div>';
    }

    var panel = document.createElement('aside');
    panel.className = 'home-hero-talk-rooms lg:col-span-4';
    panel.setAttribute('data-eottae-home-talk-hero', '1');
    panel.setAttribute('aria-label', '세부톡 추천');
    panel.innerHTML = ''
      + '<div class="home-hero-talk-rooms__card">'
      + '<header class="home-hero-talk-rooms__head">'
      + '<h2 class="home-hero-talk-rooms__title">세부톡</h2>'
      + '<p class="home-hero-talk-rooms__desc">지금 활발한 톡방을 만나보세요</p>'
      + '</header>'
      + body
      + '<footer class="home-hero-talk-rooms__footer">'
      + '<a href="' + esc(data.list_url || '/talk') + '" class="home-hero-talk-rooms__more">톡방 더보기</a>'
      + '</footer>'
      + '</div>';

    return panel;
  }

  function mount() {
    var data = cfg();
    if (!data) {
      return;
    }

    var grid = findHeroGrid();
    if (!grid || grid.querySelector('[data-eottae-home-talk-hero]')) {
      return;
    }

    var sidebar = findSidebarColumn(grid);
    var panel = buildPanel(data);
    grid.classList.add('eottae-home-hero-grid--3col');

    if (sidebar) {
      grid.insertBefore(panel, sidebar);
    } else {
      grid.appendChild(panel);
    }

    grid.dataset.eottaeHeroTalkMounted = '1';
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

  global.initEottaeHomeHeroTalk = init;
}(window));
