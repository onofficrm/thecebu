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

  function communityUrl() {
    var data = cfg();
    if (data && data.community_url) {
      return data.community_url;
    }
    return '/bbs/board.php?bo_table=community';
  }

  function findPopularSection() {
    var root = document.getElementById('root') || document;
    var headings = root.querySelectorAll('h2, h3');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      var text = normalizeText(headings[i].textContent);
      if (
        text.indexOf('실시간인기글') !== -1
        || text.indexOf('전체실시간') !== -1
        || text.indexOf('커뮤니티인기글') !== -1
      ) {
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

    var eventId = event.event_id ? String(event.event_id) : '';
    var href = eventId ? '#' : (event.detail_href || '#');

    return ''
      + '<li class="sebu-cal-summary-event">'
      + '<a href="' + esc(href) + '" class="sebu-cal-summary-event__link"'
      + (eventId ? ' data-sebu-cal-event="' + esc(eventId) + '" role="button"' : '')
      + '>'
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

    var dateLabel = esc(day.date || '');
    if (day.weekday) {
      dateLabel += ' (' + esc(day.weekday) + ')';
    }

    return ''
      + '<article class="sebu-cal-summary-day">'
      + '<header class="sebu-cal-summary-day__head">'
      + '<h3 class="sebu-cal-summary-day__label">' + esc(day.label || '') + ' ' + dateLabel + '</h3>'
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
      var talkEventId = event.event_id ? String(event.event_id) : '';
      var talkHref = talkEventId ? '#' : (event.detail_href || '#');
      html += ''
        + '<li class="sebu-cal-summary-talk__item">'
        + '<a href="' + esc(talkHref) + '" class="sebu-cal-summary-talk__link"'
        + (talkEventId ? ' data-sebu-cal-event="' + esc(talkEventId) + '" role="button"' : '')
        + '>'
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

    var comments = Number(post.comments || 0);
    var views = Number(post.views || 0);

    return ''
      + '<li class="sebu-community-item">'
      + '<a href="' + esc(post.url || '#') + '" class="sebu-community-item__link">'
      + '<div class="sebu-community-item__main">'
      + '<span class="sebu-community-item__title-wrap">'
      + (post.board ? '<span class="sebu-community-item__board">' + esc(post.board) + '</span>' : '')
      + '<span class="sebu-community-item__title">' + esc(post.title) + '</span>'
      + '</span>'
      + (post.is_new ? '<span class="sebu-community-item__badge sebu-community-item__badge--new">N</span>' : '')
      + (post.is_hot ? '<span class="sebu-community-item__badge sebu-community-item__badge--hot">HOT</span>' : '')
      + '</div>'
      + '<div class="sebu-community-item__meta">'
      + '<span class="sebu-community-item__comments">' + (comments > 0 ? '[' + esc(String(comments)) + ']' : '') + '</span>'
      + '<span class="sebu-community-item__views">조회 ' + esc(String(views)) + '</span>'
      + '<span class="sebu-community-item__meta-sep" aria-hidden="true">|</span>'
      + '<span class="sebu-community-item__time">' + esc(post.time || '') + '</span>'
      + '</div>'
      + '</a>'
      + '</li>';
  }

  function renderPopularList(posts) {
    if (!posts || !posts.length) {
      return '<p class="sebu-community-col__empty">표시할 글이 없습니다.</p>';
    }

    var html = '';
    var i;
    for (i = 0; i < posts.length; i += 1) {
      html += renderPopularPost(posts[i]);
    }

    return '<ul class="sebu-community-col__list">' + html + '</ul>';
  }

  function renderPopularColumn(column) {
    return ''
      + '<article class="sebu-community-col">'
      + '<header class="sebu-community-col__head">'
      + '<h3 class="sebu-community-col__title">'
      + '<span class="sebu-community-col__accent ' + esc(column.accentClass || '') + '" aria-hidden="true"></span>'
      + esc(column.label || '')
      + '</h3>'
      + '<a href="' + esc(column.moreUrl || communityUrl()) + '" class="sebu-community-col__more">더보기 +</a>'
      + '</header>'
      + renderPopularList(column.posts || [])
      + '</article>';
  }

  function renderPopularBlock(popular) {
    var columns = [
      {
        key: 'latest',
        label: '실시간 인기글',
        accentClass: 'sebu-community-col__accent--orange',
        posts: popular.latest || [],
      },
      {
        key: 'hit',
        label: '조회수 급상승',
        accentClass: 'sebu-community-col__accent--rose',
        posts: popular.hit || [],
      },
      {
        key: 'comment',
        label: '댓글 많은 글',
        accentClass: 'sebu-community-col__accent--pink',
        posts: popular.comment || [],
      },
    ];

    var colsHtml = '';
    var i;
    var moreUrl = communityUrl();

    for (i = 0; i < columns.length; i += 1) {
      colsHtml += renderPopularColumn({
        label: columns[i].label,
        accentClass: columns[i].accentClass,
        posts: columns[i].posts,
        moreUrl: moreUrl,
      });
    }

    return ''
      + '<section class="sebu-community-popular" data-eottae-home-popular="1" aria-label="커뮤니티 인기글">'
      + '<h2 class="sebu-community-popular__title">커뮤니티 인기글</h2>'
      + '<div class="sebu-community-popular__grid">' + colsHtml + '</div>'
      + '</section>';
  }

  var mountScheduled = false;
  var mountDone = false;

  function mount() {
    if (mountDone) {
      return true;
    }

    var data = cfg();
    if (!data || !data.calendar) {
      return false;
    }

    var section = findPopularSection();
    if (!section) {
      return false;
    }

    if (section.querySelector('[data-eottae-home-main-mounted]')) {
      mountDone = true;
      return true;
    }

    var mountRoot = document.createElement('div');
    mountRoot.className = 'sebu-home-main-section';
    mountRoot.setAttribute('data-eottae-home-main-mounted', '1');
    mountRoot.innerHTML = renderCalendarBlock(data.calendar) + renderPopularBlock(data.popular || {});

    while (section.firstChild) {
      section.removeChild(section.firstChild);
    }
    section.appendChild(mountRoot);
    mountDone = true;

    if (typeof global.eottaeCalendarInitEventModal === 'function') {
      global.eottaeCalendarInitEventModal();
    }

    return true;
  }

  function scheduleMount(retryCount) {
    if (mountDone || mountScheduled) {
      return;
    }

    mountScheduled = true;
    global.requestAnimationFrame(function () {
      mountScheduled = false;
      if (mount()) {
        return;
      }

      var tries = typeof retryCount === 'number' ? retryCount : 0;
      if (tries < 8) {
        global.setTimeout(function () {
          scheduleMount(tries + 1);
        }, 250);
      }
    });
  }

  function init() {
    var run = function () {
      scheduleMount(0);
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
