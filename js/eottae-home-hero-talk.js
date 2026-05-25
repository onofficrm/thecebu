/**
 * 홈(빌더) — 히어로 3열: 중간(세부광장 톡방) · 우측(세부톡)
 */
(function (global) {
  'use strict';

  var VARIANTS = {
    plaza: {
      marker: 'data-eottae-home-plaza-hero',
      ariaLabel: '세부광장 톡방',
      title: '세부광장',
      desc: '광장 이야기를 이어갈 톡방',
      newLabel: '광장 연결 톡방',
      hotLabel: '지금 뜨는 톡방',
      moreLabel: '톡방 더보기',
      panelClass: 'home-hero-talk-rooms home-hero-talk-rooms--plaza lg:col-span-4',
    },
    talk: {
      marker: 'data-eottae-home-talk-sidebar',
      ariaLabel: '세부톡 추천',
      title: '세부톡',
      desc: '지금 활발한 톡방을 만나보세요',
      newLabel: '신규 톡방',
      hotLabel: '화제의 톡방',
      moreLabel: '톡방 더보기',
      panelClass: 'home-hero-talk-rooms home-hero-talk-rooms--sidebar',
    },
  };

  function cfg(key) {
    if (key === 'plaza') {
      return global.__EOTTae_HOME_HERO_PLAZA__ || null;
    }
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

  function hideSidebarEvents(sidebar) {
    if (!sidebar || sidebar.dataset.eottaeEventsHidden === '1') {
      return null;
    }

    var sections = sidebar.querySelectorAll('section');
    var i;
    for (i = 0; i < sections.length; i += 1) {
      var h2 = sections[i].querySelector('h2');
      if (h2 && (h2.textContent || '').indexOf('업체 이벤트') !== -1) {
        sections[i].style.display = 'none';
        sections[i].setAttribute('data-eottae-hidden-events', '1');
        sidebar.dataset.eottaeEventsHidden = '1';
        return sections[i];
      }
    }

    return null;
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

  function buildPanel(data, variantKey) {
    var variant = VARIANTS[variantKey];
    var newHtml = renderSection(variant.newLabel, 'home-hero-talk-rooms__badge--new', data.new);
    var hotHtml = renderSection(variant.hotLabel, 'home-hero-talk-rooms__badge--hot', data.hot);
    var body = newHtml + hotHtml;
    var listUrl = data.list_url || (variantKey === 'plaza' ? '/talk' : '/talk');
    var createUrl = data.create_url || '/page/eottae-talk-create.php';

    if (!body) {
      body = ''
        + '<div class="home-hero-talk-rooms__empty">'
        + '<p>아직 표시할 톡방이 없습니다.</p>'
        + '<a href="' + esc(createUrl) + '">톡방 만들기</a>'
        + '</div>';
    }

    var panel = document.createElement('aside');
    panel.className = variant.panelClass;
    panel.setAttribute(variant.marker, '1');
    panel.setAttribute('aria-label', variant.ariaLabel);
    panel.innerHTML = ''
      + '<div class="home-hero-talk-rooms__card">'
      + '<header class="home-hero-talk-rooms__head">'
      + '<h2 class="home-hero-talk-rooms__title">' + esc(variant.title) + '</h2>'
      + '<p class="home-hero-talk-rooms__desc">' + esc(variant.desc) + '</p>'
      + '</header>'
      + body
      + '<footer class="home-hero-talk-rooms__footer">'
      + '<a href="' + esc(listUrl) + '" class="home-hero-talk-rooms__more">' + esc(variant.moreLabel) + '</a>'
      + (variantKey === 'plaza' && data.plaza_url
        ? '<a href="' + esc(data.plaza_url) + '" class="home-hero-talk-rooms__more home-hero-talk-rooms__more--ghost">세부광장으로</a>'
        : '')
      + '</footer>'
      + '</div>';

    return panel;
  }

  function mountPlaza() {
    var data = cfg('plaza');
    if (!data) {
      return;
    }

    var grid = findHeroGrid();
    if (!grid || grid.querySelector('[data-eottae-home-plaza-hero]')) {
      return;
    }

    var sidebar = findSidebarColumn(grid);
    var panel = buildPanel(data, 'plaza');
    grid.classList.add('eottae-home-hero-grid--3col');

    if (sidebar) {
      grid.insertBefore(panel, sidebar);
    } else {
      grid.appendChild(panel);
    }
  }

  function mountTalkSidebar() {
    var data = cfg('talk');
    if (!data) {
      return;
    }

    var grid = findHeroGrid();
    if (!grid) {
      return;
    }

    var sidebar = findSidebarColumn(grid);
    if (!sidebar || sidebar.querySelector('[data-eottae-home-talk-sidebar]')) {
      return;
    }

    var hiddenEvents = hideSidebarEvents(sidebar);
    var panel = buildPanel(data, 'talk');
    grid.classList.add('eottae-home-hero-grid--3col');

    if (hiddenEvents && hiddenEvents.parentNode) {
      hiddenEvents.parentNode.insertBefore(panel, hiddenEvents);
    } else {
      sidebar.appendChild(panel);
    }
  }

  function mount() {
    mountPlaza();
    mountTalkSidebar();
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
