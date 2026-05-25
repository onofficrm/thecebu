/**
 * 홈(빌더) — 인기글 행: 조회수급상승 오른쪽 세부톡방 추천
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

  function findPopularRowCard() {
    var headings = document.querySelectorAll('h3');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      var text = headings[i].textContent || '';
      if (text.indexOf('댓글 많은 글') !== -1) {
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
      + '<h4 class="home-hero-talk-rooms__block-title">'
      + '<span class="home-hero-talk-rooms__badge ' + badgeClass + '"></span>'
      + esc(title)
      + '</h4>'
      + '</header>'
      + '<ul class="home-hero-talk-rooms__list">' + html + '</ul>'
      + '</section>';
  }

  function buildPanel(data) {
    var newHtml = renderSection('신규 톡방', 'home-hero-talk-rooms__badge--new', data.new);
    var hotHtml = renderSection('화제의 톡방', 'home-hero-talk-rooms__badge--hot', data.hot);
    var body = newHtml + hotHtml;
    var listUrl = data.list_url || '/talk';
    var createUrl = data.create_url || '/page/eottae-talk-create.php';
    var isEmpty = body === '';

    if (isEmpty) {
      body = ''
        + '<div class="home-hero-talk-rooms__empty">'
        + '<span class="home-hero-talk-rooms__empty-icon" aria-hidden="true">💬</span>'
        + '<p class="home-hero-talk-rooms__empty-title">아직 표시할 톡방이 없습니다</p>'
        + '<p class="home-hero-talk-rooms__empty-desc">관심 주제의 톡방을 만들거나 세부톡방을 둘러보세요</p>'
        + '</div>';
    }

    var footerHtml = isEmpty
      ? '<a href="' + esc(createUrl) + '" class="home-hero-talk-rooms__cta home-hero-talk-rooms__cta--primary">톡방 만들기</a>'
        + '<a href="' + esc(listUrl) + '" class="home-hero-talk-rooms__cta home-hero-talk-rooms__cta--ghost">톡방 더보기</a>'
      : '<a href="' + esc(listUrl) + '" class="home-hero-talk-rooms__cta home-hero-talk-rooms__cta--primary">톡방 더보기</a>';

    var panel = document.createElement('aside');
    panel.className = 'home-hero-talk-rooms home-hero-talk-rooms--content';
    panel.setAttribute('data-eottae-home-talk-content', '1');
    panel.setAttribute('aria-label', '세부톡 추천');
    panel.innerHTML = ''
      + '<div class="home-hero-talk-rooms__card">'
      + body
      + '<footer class="home-hero-talk-rooms__footer">' + footerHtml + '</footer>'
      + '</div>';

    return panel;
  }

  function mountContentTalk() {
    var data = cfg();
    if (!data) {
      return;
    }

    var card = findPopularRowCard();
    if (!card || card.querySelector('[data-eottae-home-talk-content]')) {
      return;
    }

    card.classList.add('home-hero-talk-card');

    var headingWrap = card.querySelector('.flex.items-center.justify-between');
    if (headingWrap) {
      var title = headingWrap.querySelector('h3');
      if (title) {
        title.textContent = '세부톡';
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

    listContainer.classList.add('home-hero-talk-legacy-list');
    listContainer.style.display = 'none';
    card.appendChild(buildPanel(data));
  }

  function cleanupLegacyHeroTalk() {
    var legacy = document.querySelectorAll('[data-eottae-home-plaza-hero], [data-eottae-home-talk-sidebar]');
    var i;
    for (i = 0; i < legacy.length; i += 1) {
      if (legacy[i].parentNode) {
        legacy[i].parentNode.removeChild(legacy[i]);
      }
    }

    var feed = document.getElementById('eottae-home-talk-feed');
    if (feed && feed.parentNode) {
      feed.parentNode.removeChild(feed);
    }
  }

  function hidePlazaSectionThirdColumn() {
    var headings = document.querySelectorAll('h2, h3');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      var text = (headings[i].textContent || '').replace(/\s+/g, '');
      if (text.indexOf('세부광장') === -1) {
        continue;
      }
      var section = headings[i].closest('section');
      if (!section) {
        continue;
      }
      var grids = section.querySelectorAll('.grid');
      var g;
      for (g = 0; g < grids.length; g += 1) {
        if (grids[g].children.length >= 3) {
          grids[g].children[2].style.display = 'none';
        }
      }
    }
  }

  var mounted = false;

  function mount() {
    if (mounted) {
      return true;
    }

    cleanupLegacyHeroTalk();
    hidePlazaSectionThirdColumn();
    mountContentTalk();
    mounted = true;
    return true;
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

  global.initEottaeHomeHeroTalk = init;
}(window));
