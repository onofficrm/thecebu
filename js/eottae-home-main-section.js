/**
 * 홈(빌더) — 오늘·내일·모레 일정 요약 + 인기글/급상승/댓글많은글
 */
(function (global) {
  'use strict';

  function cfg() {
    return global.__EOTTae_HOME_MAIN_SECTION__ || null;
  }

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function normalizeText(value) {
    return String(value || '').replace(/\s+/g, '').trim();
  }

  function findPopularSection() {
    var headings = document.querySelectorAll('h2, h3');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      var text = normalizeText(headings[i].textContent);
      if (text.indexOf('실시간인기글') !== -1 || text.indexOf('전체실시간') !== -1) {
        var section = headings[i].closest('section');
        if (section) {
          return section;
        }
        return headings[i].closest('div');
      }
    }
    return null;
  }

  function renderEventItem(event) {
    if (!event || !event.title) {
      return '';
    }

    var meta = [];
    if (event.time_label) {
      meta.push(event.time_label);
    }
    if (event.location) {
      meta.push(event.location);
    }
    if (event.writer_display) {
      meta.push(event.writer_display);
    }
    if (event.is_google && event.source_label) {
      meta.push(event.source_label);
    }

    var href = event.detail_href || '#';

    return ''
      + '<li class="sebu-cal-summary-event">'
      + '<a href="' + esc(href) + '" class="sebu-cal-summary-event__link">'
      + '<div class="sebu-cal-summary-event__head">'
      + '<span class="sebu-cal-summary-event__category ' + esc(event.category_class || '') + '">' + esc(event.category_label || '') + '</span>'
      + (event.badge_label ? '<span class="sebu-cal-summary-event__badge ' + esc(event.badge_class || '') + '">' + esc(event.badge_label) + '</span>' : '')
      + (event.is_google ? '<span class="sebu-cal-summary-event__source">Google</span>' : '')
      + '</div>'
      + '<strong class="sebu-cal-summary-event__title">' + esc(event.title) + '</strong>'
      + (meta.length ? '<p class="sebu-cal-summary-event__meta">' + esc(meta.join(' · ')) + '</p>' : '')
      + '</a>'
      + '</li>';
  }

  function renderDayCard(day) {
    var eventsHtml = '';
    var j;
    if (day.events && day.events.length) {
      for (j = 0; j < day.events.length; j += 1) {
        eventsHtml += renderEventItem(day.events[j]);
      }
    } else {
      eventsHtml = ''
        + '<div class="sebu-cal-summary-day__empty">'
        + '<p>등록된 일정이 없습니다.</p>'
        + '<p class="sebu-cal-summary-day__empty-sub">새로운 일정을 등록해보세요.</p>'
        + '</div>';
    }

    return ''
      + '<article class="sebu-cal-summary-day">'
      + '<header class="sebu-cal-summary-day__head">'
      + '<h3 class="sebu-cal-summary-day__label">' + esc(day.label || '') + '</h3>'
      + '<p class="sebu-cal-summary-day__date">' + esc(day.date || '') + (day.weekday ? ' (' + esc(day.weekday) + ')' : '') + '</p>'
      + '<span class="sebu-cal-summary-day__count">' + esc(String(day.count || 0)) + '건</span>'
      + '</header>'
      + (day.events && day.events.length
        ? '<ul class="sebu-cal-summary-day__list">' + eventsHtml + '</ul>'
        : eventsHtml)
      + '</article>';
  }

  function renderTalkEvents(talkEvents) {
    if (!talkEvents || !talkEvents.length) {
      return '';
    }

    var html = '';
    var i;
    for (i = 0; i < talkEvents.length; i += 1) {
      var event = talkEvents[i];
      html += ''
        + '<li class="sebu-cal-summary-talk__item">'
        + '<a href="' + esc(event.detail_href || '#') + '" class="sebu-cal-summary-talk__link">'
        + '<span class="sebu-cal-summary-talk__day">' + esc(event.day_label || '') + '</span>'
        + '<strong class="sebu-cal-summary-talk__name">' + esc(event.title || '') + '</strong>'
        + (event.time_label ? '<span class="sebu-cal-summary-talk__time">' + esc(event.time_label) + '</span>' : '')
        + '</a>'
        + '</li>';
    }

    return ''
      + '<section class="sebu-cal-summary-talk" aria-label="세부톡 일정">'
      + '<header class="sebu-cal-summary-talk__head">'
      + '<h3 class="sebu-cal-summary-talk__title">세부톡 일정</h3>'
      + '</header>'
      + '<ul class="sebu-cal-summary-talk__list">' + html + '</ul>'
      + '</section>';
  }

  function renderCalendarBlock(calendar) {
    var daysHtml = '';
    var i;
    for (i = 0; i < (calendar.days || []).length; i += 1) {
      daysHtml += renderDayCard(calendar.days[i]);
    }

    var createHref = calendar.is_member ? calendar.create_url : calendar.login_url;

    return ''
      + '<section class="sebu-cal-summary" data-eottae-home-calendar="1">'
      + '<header class="sebu-cal-summary__head">'
      + '<div>'
      + '<h2 class="sebu-cal-summary__title">' + esc(calendar.title || '이번 3일 세부 일정') + '</h2>'
      + '<p class="sebu-cal-summary__desc">오늘·내일·모레 세부 지역 일정을 한눈에 확인하세요.</p>'
      + '</div>'
      + '<div class="sebu-cal-summary__actions">'
      + '<a href="' + esc(calendar.calendar_url || '/calendar/') + '" class="sebu-cal-summary__btn">캘린더 전체보기</a>'
      + '<a href="' + esc(createHref || '/page/eottae-calendar-create.php') + '" class="sebu-cal-summary__btn sebu-cal-summary__btn--primary">일정 등록하기</a>'
      + '</div>'
      + '</header>'
      + '<div class="sebu-cal-summary__grid">' + daysHtml + '</div>'
      + renderTalkEvents(calendar.talk_events || [])
      + '</section>';
  }

  function renderPopularPost(post) {
    if (!post || !post.title) {
      return '';
    }

    return ''
      + '<li class="sebu-popular-item">'
      + '<a href="' + esc(post.url || '#') + '" class="sebu-popular-item__link">'
      + (post.board ? '<span class="sebu-popular-item__board">' + esc(post.board) + '</span>' : '')
      + '<strong class="sebu-popular-item__title">' + esc(post.title) + '</strong>'
      + '<span class="sebu-popular-item__meta">'
      + '조회 ' + esc(String(post.views || 0))
      + ' · 댓글 ' + esc(String(post.comments || 0))
      + (post.time ? ' · ' + esc(post.time) : '')
      + '</span>'
      + '</a>'
      + '</li>';
  }

  function renderPopularList(posts) {
    if (!posts || !posts.length) {
      return '<p class="sebu-popular-panel__empty">표시할 글이 없습니다.</p>';
    }

    var html = '';
    var i;
    for (i = 0; i < posts.length; i += 1) {
      html += renderPopularPost(posts[i]);
    }

    return '<ul class="sebu-popular-panel__list">' + html + '</ul>';
  }

  function renderPopularBlock(popular) {
    var tabs = [
      { key: 'latest', label: '실시간 인기글', posts: popular.latest || [] },
      { key: 'hit', label: '조회수 급상승', posts: popular.hit || [] },
      { key: 'comment', label: '댓글 많은 글', posts: popular.comment || [] },
    ];

    var tabsHtml = '';
    var panelsHtml = '';
    var i;

    for (i = 0; i < tabs.length; i += 1) {
      var tab = tabs[i];
      var active = i === 0 ? ' is-active' : '';
      tabsHtml += '<button type="button" class="sebu-popular__tab' + active + '" data-sebu-popular-tab="' + esc(tab.key) + '">' + esc(tab.label) + '</button>';
      panelsHtml += ''
        + '<div class="sebu-popular-panel' + active + '" data-sebu-popular-panel="' + esc(tab.key) + '">'
        + '<h3 class="sebu-popular-panel__title">' + esc(tab.label) + '</h3>'
        + renderPopularList(tab.posts)
        + '</div>';
    }

    var desktopCols = '';
    for (i = 0; i < tabs.length; i += 1) {
      desktopCols += ''
        + '<article class="sebu-popular-col">'
        + '<h3 class="sebu-popular-col__title">' + esc(tabs[i].label) + '</h3>'
        + renderPopularList(tabs[i].posts)
        + '</article>';
    }

    return ''
      + '<section class="sebu-popular" data-eottae-home-popular="1" aria-label="커뮤니티 인기글">'
      + '<header class="sebu-popular__head">'
      + '<h2 class="sebu-popular__title">커뮤니티 인기글</h2>'
      + '</header>'
      + '<div class="sebu-popular__tabs" role="tablist">' + tabsHtml + '</div>'
      + '<div class="sebu-popular__mobile-panels">' + panelsHtml + '</div>'
      + '<div class="sebu-popular__desktop-grid">' + desktopCols + '</div>'
      + '</section>';
  }

  function bindPopularTabs(root) {
    var tabs = root.querySelectorAll('[data-sebu-popular-tab]');
    var panels = root.querySelectorAll('[data-sebu-popular-panel]');
    var i;

    function activate(key) {
      for (i = 0; i < tabs.length; i += 1) {
        tabs[i].classList.toggle('is-active', tabs[i].getAttribute('data-sebu-popular-tab') === key);
      }
      for (i = 0; i < panels.length; i += 1) {
        panels[i].classList.toggle('is-active', panels[i].getAttribute('data-sebu-popular-panel') === key);
      }
    }

    for (i = 0; i < tabs.length; i += 1) {
      tabs[i].addEventListener('click', function () {
        activate(this.getAttribute('data-sebu-popular-tab'));
      });
    }
  }

  function mount() {
    var data = cfg();
    if (!data || !data.calendar) {
      return false;
    }

    var section = findPopularSection();
    if (!section || section.querySelector('[data-eottae-home-main-mounted]')) {
      return !!section;
    }

    var mountRoot = document.createElement('div');
    mountRoot.className = 'sebu-home-main-section';
    mountRoot.setAttribute('data-eottae-home-main-mounted', '1');
    mountRoot.innerHTML = renderCalendarBlock(data.calendar) + renderPopularBlock(data.popular || {});

    while (section.firstChild) {
      section.removeChild(section.firstChild);
    }
    section.appendChild(mountRoot);

    var popular = mountRoot.querySelector('[data-eottae-home-popular]');
    if (popular) {
      bindPopularTabs(popular);
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
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  global.initEottaeHomeMainSection = init;
}(window));
