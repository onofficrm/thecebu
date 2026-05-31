/**
 * 홈(빌더) — 오늘·내일·모레 일정 요약 + 인기글/급상승/공개톡방
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

  function t(key, fallback) {
    if (global.EottaeI18N && typeof global.EottaeI18N.t === 'function') {
      var value = global.EottaeI18N.t(key);
      if (value) {
        return value;
      }
    }

    return fallback;
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

  function headingMatchesPopularSection(text) {
    return text.indexOf('전체실시간') !== -1
      || text.indexOf('실시간인기글') !== -1
      || text.indexOf('커뮤니티인기글') !== -1
      || text.indexOf('커뮤니티최신글') !== -1
      || text.indexOf('세부최신소식') !== -1
      || text.indexOf('오늘의세부커뮤니티') !== -1;
  }

  function nodeHasLegacyPopularGrid(node) {
    if (!node || !node.querySelector) {
      return false;
    }

    if (
      node.querySelector('[data-eottae-home-popular-hot="1"]')
      || node.querySelector('[data-eottae-home-popular-hot-mounted="1"]')
    ) {
      return false;
    }

    if (
      node.querySelector('[class*="grid-cols-3"]')
      || node.querySelector('[class*="lg:grid-cols-3"]')
    ) {
      return true;
    }

    var merged = '';
    var h3s = node.querySelectorAll('h3');
    var i;

    for (i = 0; i < h3s.length; i += 1) {
      merged += normalizeText(h3s[i].textContent);
    }

    return merged.indexOf('댓글많은글') !== -1
      && (merged.indexOf('추천글') !== -1 || merged.indexOf('커뮤니티최신글') !== -1);
  }

  function isUnsafeHomeRemovalTarget(node, heading) {
    var root = document.getElementById('root');

    if (!node || !node.parentNode || node === root || node === document.body) {
      return true;
    }

    if (heading && !node.contains(heading)) {
      return true;
    }

    if (node.querySelector('header') || node.closest('header')) {
      return true;
    }

    if (node.querySelector('[data-eottae-home-main-mounted="1"], [data-eottae-home-popular-hot="1"]')) {
      return false;
    }

    if (root && node === root.firstElementChild && node.children.length > 2) {
      return true;
    }

    if (node.querySelectorAll('h2').length > 4) {
      return true;
    }

    return false;
  }

  function resolveScopedSectionRoot(heading, matcher) {
    var root = document.getElementById('root') || document;
    var node = heading;
    var depth = 0;
    var match = null;

    while (node && node !== root && depth < 10) {
      if (matcher(node) && !isUnsafeHomeRemovalTarget(node, heading)) {
        match = node;
      }
      node = node.parentElement;
      depth += 1;
    }

    if (match) {
      return match;
    }

    var fallback = heading.parentElement;
    if (fallback && !isUnsafeHomeRemovalTarget(fallback, heading)) {
      return fallback;
    }

    return null;
  }

  function resolvePopularSectionRoot(heading) {
    return resolveScopedSectionRoot(heading, nodeHasLegacyPopularGrid)
      || heading.closest('section')
      || heading.parentElement;
  }

  function findPopularSectionHeading(root) {
    var headings = root.querySelectorAll('h2, h3');
    var i;
    var text;
    var heading = null;

    for (i = 0; i < headings.length; i += 1) {
      text = normalizeText(headings[i].textContent);
      if (text.indexOf('오늘의세부커뮤니티') !== -1) {
        return headings[i];
      }
    }

    for (i = 0; i < headings.length; i += 1) {
      text = normalizeText(headings[i].textContent);
      if (text.indexOf('전체실시간') !== -1 || text.indexOf('실시간인기글') !== -1) {
        return headings[i];
      }
    }

    for (i = 0; i < headings.length; i += 1) {
      text = normalizeText(headings[i].textContent);
      if (headingMatchesPopularSection(text)) {
        return headings[i];
      }
    }

    for (i = 0; i < headings.length; i += 1) {
      text = normalizeText(headings[i].textContent);
      if (text.indexOf('댓글많은글') !== -1 || text.indexOf('댓글많은순') !== -1) {
        return headings[i];
      }
    }

    return heading;
  }

  function findPopularSection() {
    var root = document.getElementById('root') || document;
    var mount = root.querySelector('[data-eottae-home-community-mount="1"]');
    if (mount) {
      return mount;
    }

    var heading = findPopularSectionHeading(root);
    if (!heading) {
      return null;
    }

    return resolvePopularSectionRoot(heading);
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
        + '<p>' + esc(t('home.calendar_empty', '등록된 일정이 없습니다.')) + '</p>'
        + '<p class="sebu-cal-summary-day__empty-sub">' + esc(t('home.calendar_empty_sub', '새로운 일정을 등록해보세요.')) + '</p>'
        + '</div>';
    }

    var dateLabel = esc(day.date || '');
    if (day.weekday) {
      dateLabel += ' (' + esc(day.weekday) + ')';
    }

    return ''
      + '<article class="sebu-cal-summary-day' + (day.events && day.events.length ? '' : ' is-empty') + '">'
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
      + '<h3 class="sebu-cal-summary-talk__title">' + esc(t('home.calendar_talk_title', '세부톡 일정')) + '</h3>'
      + '</header>'
      + '<ul class="sebu-cal-summary-talk__list">' + html + '</ul>'
      + '</section>';
  }

  function renderCalendarBlock(calendar) {
    var daysHtml = '';
    var i;
    var totalCount = 0;
    for (i = 0; i < (calendar.days || []).length; i += 1) {
      totalCount += Number(calendar.days[i].count || 0);
      daysHtml += renderDayCard(calendar.days[i]);
    }

    var createHref = calendar.is_member ? calendar.create_url : calendar.login_url;
    var hasTalkEvents = !!(calendar.talk_events && calendar.talk_events.length);
    var summaryHtml = '';

    if (totalCount < 1 && !hasTalkEvents) {
      for (i = 0; i < (calendar.days || []).length; i += 1) {
        summaryHtml += ''
          + '<span class="sebu-cal-compact__day">'
          + '<strong>' + esc(calendar.days[i].label || '') + '</strong>'
          + '<span>' + esc(t('home.calendar_no_schedule', '일정 없음')) + '</span>'
          + '</span>';
      }

      return ''
        + '<section class="sebu-cal-summary sebu-cal-summary--compact" data-eottae-home-calendar="1">'
        + '<div class="sebu-cal-compact">'
        + '<div class="sebu-cal-compact__copy">'
        + '<p class="sebu-cal-compact__eyebrow">' + esc(t('home.calendar_eyebrow', '세부 일정')) + '</p>'
        + '<h2 class="sebu-cal-summary__title">' + esc(calendar.title || t('home.calendar_title', '이번 3일 세부 일정')) + '</h2>'
        + '<p class="sebu-cal-summary__desc">' + esc(t('home.calendar_empty_sub', '오늘·내일·모레 등록된 일정이 아직 없습니다.')) + '</p>'
        + '</div>'
        + '<div class="sebu-cal-compact__days" aria-label="이번 3일 일정 요약">' + summaryHtml + '</div>'
        + '<div class="sebu-cal-summary__actions">'
        + '<a href="' + esc(calendar.calendar_url || '/calendar/') + '" class="sebu-cal-summary__btn">' + esc(t('home.calendar_view', '캘린더 보기')) + '</a>'
        + '<a href="' + esc(createHref || '/page/eottae-calendar-create.php') + '" class="sebu-cal-summary__btn sebu-cal-summary__btn--primary">' + esc(t('home.calendar_create', '일정 등록')) + '</a>'
        + '</div>'
        + '</div>'
        + '</section>';
    }

    return ''
      + '<section class="sebu-cal-summary" data-eottae-home-calendar="1">'
      + '<header class="sebu-cal-summary__head">'
      + '<div>'
      + '<h2 class="sebu-cal-summary__title">' + esc(calendar.title || t('home.calendar_title', '이번 3일 세부 일정')) + '</h2>'
      + '<p class="sebu-cal-summary__desc">' + esc(t('home.calendar_desc', '오늘·내일·모레 세부 지역 일정을 한눈에 확인하세요.')) + '</p>'
      + '</div>'
      + '<div class="sebu-cal-summary__actions">'
      + '<a href="' + esc(calendar.calendar_url || '/calendar/') + '" class="sebu-cal-summary__btn">' + esc(t('home.calendar_view', '캘린더 전체보기')) + '</a>'
      + '<a href="' + esc(createHref || '/page/eottae-calendar-create.php') + '" class="sebu-cal-summary__btn sebu-cal-summary__btn--primary">' + esc(t('home.calendar_create', '일정 등록하기')) + '</a>'
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

  function popularTripleColumns(popular) {
    var data = popular || {};

    return [
      {
        label: t('home.popular_recommended', '실시간 추천글'),
        posts: data.latest || [],
        accentClass: 'sebu-community-col__accent--orange',
      },
      {
        label: t('home.popular_rising', '조회수 급상승'),
        posts: data.hit || [],
        accentClass: 'sebu-community-col__accent--rose',
      },
      {
        label: t('home.popular_comments', '댓글 많은 글'),
        posts: data.comment || [],
        accentClass: 'sebu-community-col__accent--pink',
      },
    ];
  }

  function renderPopularTripleRow(popular) {
    var columns = popularTripleColumns(popular);
    var html = '';
    var i;

    for (i = 0; i < columns.length; i += 1) {
      html += renderPopularColumn(columns[i]);
    }

    return ''
      + '<div class="sebu-community-popular__triple" aria-label="실시간 인기글 3열">'
      + html
      + '</div>';
  }

  function renderPopularHotBlock(popular) {
    return ''
      + '<section class="sebu-community-popular-hot" data-eottae-home-popular-hot="1" aria-label="전체 실시간 인기글">'
      + '<h2 class="sebu-community-popular-hot__title"><span class="sebu-community-popular-hot__emoji" aria-hidden="true">🔥 </span>' + esc(t('home.popular_hot_title', '전체 실시간 인기글')) + '</h2>'
      + renderPopularTripleRow(popular)
      + '</section>';
  }

  function findLegacySectionContainer(heading) {
    var scoped = resolveScopedSectionRoot(heading, function (node) {
      return nodeHasLegacyBoardRow(node)
        || (nodeHasLegacyPopularGrid(node) && isLegacySectionTitle(normalizeText(heading.textContent), heading.tagName));
    });

    return scoped;
  }

  function nodeHasLegacyBoardRow(node) {
    if (!node || !node.querySelector) {
      return false;
    }

    var merged = '';
    var h3s = node.querySelectorAll('h3');
    var i;

    for (i = 0; i < h3s.length; i += 1) {
      merged += normalizeText(h3s[i].textContent);
    }

    return (merged.indexOf('자유게시판') !== -1
        && (merged.indexOf('질문답변') !== -1 || merged.indexOf('교민장터') !== -1))
      || (merged.indexOf('세부생활정보') !== -1 && merged.indexOf('구인구직') !== -1);
  }

  function isLegacySectionTitle(text, tagName) {
    var isH2 = String(tagName || '').toUpperCase() === 'H2';

    if (isH2 && (text === '커뮤니티최신글' || text === '세부생활정보')) {
      return true;
    }

    return (text.indexOf('이번') !== -1 && text.indexOf('세부일정') !== -1);
  }

  function shouldRemoveLegacyNode(node, host) {
    if (!node || !node.parentNode) {
      return false;
    }

    if (host && (node === host || host.contains(node))) {
      return false;
    }

    if (node.querySelector && (
      node.querySelector('[data-eottae-home-main-mounted="1"]')
      || node.querySelector('[data-eottae-home-popular-hot-mounted="1"]')
      || node.querySelector('[data-eottae-home-popular-hot="1"]')
    )) {
      return false;
    }

    return true;
  }

  function resolveCommunitySectionRoot(heading) {
    var root = document.getElementById('root') || document;
    var node = heading.parentElement;
    var depth = 0;
    var candidate = null;

    while (node && node !== root && depth < 12) {
      if (node.querySelector('header')) {
        break;
      }

      if (!isUnsafeHomeRemovalTarget(node, heading)) {
        candidate = node;
      }

      node = node.parentElement;
      depth += 1;
    }

    return candidate;
  }

  function findHeadingWithMarker(markers) {
    var root = document.getElementById('root') || document;
    var headings = root.querySelectorAll('h2, h3');
    var i;
    var m;
    var text;

    for (m = 0; m < markers.length; m += 1) {
      for (i = 0; i < headings.length; i += 1) {
        text = normalizeText(headings[i].textContent);
        if (text.indexOf(markers[m]) !== -1) {
          return headings[i];
        }
      }
    }

    return null;
  }

  function findLegacyBoardsBlock() {
    var root = document.getElementById('root') || document;
    var hCommunity = findHeadingWithMarker(['커뮤니티최신글']);
    var hLife = findHeadingWithMarker(['세부생활정보']);
    var anchor = hCommunity || hLife;

    if (!anchor) {
      return null;
    }

    var node = anchor.parentElement;
    var depth = 0;
    var match = null;

    while (node && node !== root && depth < 14) {
      if ((!hLife || node.contains(hLife)) && !isUnsafeHomeRemovalTarget(node, anchor)) {
        match = node;
      }
      node = node.parentElement;
      depth += 1;
    }

    return match || resolveCommunitySectionRoot(anchor);
  }

  function findCommunityInsertPoint() {
    var hotMounted = document.querySelector('[data-eottae-home-popular-hot-mounted="1"]');
    if (hotMounted) {
      var hotHost = hotMounted.closest('[data-eottae-home-main-host="popular-hot"]') || hotMounted.parentNode;
      if (hotHost && hotHost.parentNode) {
        return { parent: hotHost.parentNode, before: hotHost.nextSibling };
      }
    }

    var builderHot = findBuilderPopularHotSection();
    if (builderHot && builderHot.parentNode) {
      return { parent: builderHot.parentNode, before: builderHot.nextSibling };
    }

    var markers = [
      '갤러리최신글',
      '유튜브최신글',
      '지역별로찾아보세요',
      '내주변정보찾기',
      '주변인기업소',
      '세부생활지금시작하세요',
    ];
    var heading = findHeadingWithMarker(markers);
    if (heading) {
      var section = resolveCommunitySectionRoot(heading);
      if (section && section.parentNode) {
        return { parent: section.parentNode, before: section };
      }
    }

    return null;
  }

  function ensureCommunityMountHost() {
    var root = document.getElementById('root') || document;
    var existing = root.querySelector('[data-eottae-home-community-mount="1"]');
    if (existing) {
      return existing;
    }

    var mounted = root.querySelector('[data-eottae-home-main-mounted="1"]');
    if (mounted) {
      return mounted.closest('[data-eottae-home-main-host]') || mounted.parentElement;
    }

    var insertPoint = findCommunityInsertPoint();
    if (!insertPoint || !insertPoint.parent) {
      return null;
    }

    var host = document.createElement('div');
    host.className = 'sebu-home-main-host-wrap';
    host.setAttribute('data-eottae-home-community-mount', '1');

    if (insertPoint.before) {
      insertPoint.parent.insertBefore(host, insertPoint.before);
    } else {
      insertPoint.parent.appendChild(host);
    }

    return host;
  }

  function findCommunitySection() {
    var root = document.getElementById('root') || document;
    var mount = root.querySelector('[data-eottae-home-community-mount="1"]');
    if (mount) {
      return mount;
    }

    var mounted = root.querySelector('[data-eottae-home-main-mounted="1"]');
    if (mounted) {
      var mountHost = mounted.closest('[data-eottae-home-community-mount="1"]');
      if (mountHost) {
        return mountHost;
      }

      return mounted.parentElement;
    }

    return ensureCommunityMountHost();
  }

  function hideLegacyBuilderCommunitySections() {
    var root = document.getElementById('root') || document;
    var markers = [
      '전체실시간',
      '실시간인기글',
      '커뮤니티최신글',
      '세부생활정보',
      '자유게시판',
      '이번',
    ];
    var headings = root.querySelectorAll('h2, h3');
    var i;
    var m;
    var text;
    var section;

    for (i = 0; i < headings.length; i += 1) {
      if (isInsideHomeMainHost(headings[i])) {
        continue;
      }

      text = normalizeText(headings[i].textContent);

      for (m = 0; m < markers.length; m += 1) {
        if (text.indexOf(markers[m]) === -1) {
          continue;
        }

        if (markers[m] === '이번' && text.indexOf('세부일정') === -1) {
          continue;
        }

        section = findLegacySectionContainer(headings[i])
          || headings[i].closest('section')
          || resolveCommunitySectionRoot(headings[i]);

        if (!section || isInsideHomeMainHost(section)) {
          continue;
        }

        section.classList.add('sebu-home-legacy-hidden');
        break;
      }
    }
  }

  function findBuilderPopularHotSection() {
    var root = document.getElementById('root') || document;
    var headings = root.querySelectorAll('h2, h3');
    var i;
    var text;

    for (i = 0; i < headings.length; i += 1) {
      text = normalizeText(headings[i].textContent);
      if (
        text.indexOf('전체실시간') !== -1
        || text.indexOf('현재실시간인기글') !== -1
        || text.indexOf('실시간인기글') !== -1
        || text.indexOf('커뮤니티인기글') !== -1
        || text.indexOf('실시간추천글') !== -1
        || (text.indexOf('추천글') !== -1 && text.indexOf('댓글') !== -1)
      ) {
        var scoped = findLegacySectionContainer(headings[i]);
        if (scoped) {
          return scoped;
        }

        scoped = resolveCommunitySectionRoot(headings[i]);
        if (scoped && nodeHasLegacyPopularGrid(scoped)) {
          return scoped;
        }
      }
    }

    return null;
  }

  function mountPopularHot(popular, communitySection) {
    var html = renderPopularHotBlock(popular || {});
    var existing = document.querySelector('[data-eottae-home-popular-hot-mounted="1"]');
    if (existing) {
      existing.innerHTML = html;
      return existing;
    }

    var builderHot = findBuilderPopularHotSection();
    var hotWrap = document.createElement('div');
    hotWrap.className = 'sebu-home-main-section sebu-home-main-section--popular-hot';
    hotWrap.setAttribute('data-eottae-home-popular-hot-mounted', '1');
    hotWrap.innerHTML = html;

    if (builderHot && builderHot.parentNode) {
      builderHot.classList.add('sebu-home-main-host');
      builderHot.setAttribute('data-eottae-home-main-host', 'popular-hot');
      while (builderHot.firstChild) {
        builderHot.removeChild(builderHot.firstChild);
      }
      builderHot.appendChild(hotWrap);
      hideLegacyBuilderCommunitySections();
      return hotWrap;
    }

    if (communitySection && communitySection.parentNode) {
      communitySection.parentNode.insertBefore(hotWrap, communitySection);
      return hotWrap;
    }

    var root = document.getElementById('root');
    if (root) {
      root.appendChild(hotWrap);
    }

    return hotWrap;
  }

  function isInsideHomeMainHost(node) {
    if (!node) {
      return false;
    }

    var hosts = document.querySelectorAll('[data-eottae-home-main-host]');
    var i;

    for (i = 0; i < hosts.length; i += 1) {
      if (hosts[i] === node || hosts[i].contains(node)) {
        return true;
      }
    }

    return !!node.closest('[data-eottae-home-popular-hot-mounted="1"], [data-eottae-home-main-mounted="1"]');
  }

  function removeLegacyHomeSections() {
    var root = document.getElementById('root') || document;
    var removed = {};
    var headings = root.querySelectorAll('h2, h3');
    var i;
    var text;
    var sectionRoot;
    var shouldRemove;
    var host;

    for (i = 0; i < headings.length; i += 1) {
      if (isInsideHomeMainHost(headings[i])) {
        continue;
      }

      text = normalizeText(headings[i].textContent);
      sectionRoot = findLegacySectionContainer(headings[i]);
      shouldRemove = isLegacySectionTitle(text, headings[i].tagName)
        || (text === '자유게시판' && nodeHasLegacyBoardRow(sectionRoot));

      if (!shouldRemove || !sectionRoot || removed[sectionRoot] || isUnsafeHomeRemovalTarget(sectionRoot, headings[i])) {
        continue;
      }

      host = root.querySelector('[data-eottae-home-main-host]');
      if (!shouldRemoveLegacyNode(sectionRoot, host)) {
        continue;
      }

      removed[sectionRoot] = true;
      sectionRoot.classList.add('sebu-home-legacy-hidden');
    }
  }

  function watchLegacyHomeSectionsRemoval() {
    removeLegacyHomeSections();
    hideLegacyBuilderCommunitySections();
  }

  function renderTodayOverview(latestNews) {
    var today = latestNews && latestNews.today ? latestNews.today : {};
    var metrics = [
      { label: '오늘 새 글', value: today.posts || 0, tone: 'sky' },
      { label: '댓글', value: today.comments || 0, tone: 'violet' },
      { label: '새 소식', value: today.news || 0, tone: 'orange' },
    ];
    var html = '';
    var i;

    for (i = 0; i < metrics.length; i += 1) {
      html += ''
        + '<span class="sebu-news-today__item sebu-news-today__item--' + esc(metrics[i].tone) + '">'
        + '<span class="sebu-news-today__label">' + esc(metrics[i].label) + '</span>'
        + '<strong class="sebu-news-today__value">' + esc(formatCount(metrics[i].value)) + '</strong>'
        + '</span>';
    }

    return ''
      + '<div class="sebu-news-today" aria-label="오늘 세부 한눈에">'
      + '<span class="sebu-news-today__eyebrow">' + esc(t('home.news_eyebrow', '오늘 세부 한눈에')) + '</span>'
      + '<div class="sebu-news-today__items">' + html + '</div>'
      + '</div>';
  }

  function renderNewsThumb(post) {
    var thumb = post && (post.thumb || post.thumb_url) ? String(post.thumb || post.thumb_url) : '';
    var initial = post && post.initial ? String(post.initial) : '';
    if (!initial && post && post.title) {
      initial = String(post.title).charAt(0) || '?';
    }
    if (!initial) {
      initial = '?';
    }

    if (thumb) {
      return ''
        + '<span class="sebu-news-item__thumb sebu-news-item__thumb--image">'
        + '<img src="' + esc(thumb) + '" alt="" loading="lazy" decoding="async">'
        + '</span>';
    }

    return ''
      + '<span class="sebu-news-item__thumb sebu-news-item__thumb--initial" aria-hidden="true">'
      + esc(initial)
      + '</span>';
  }

  function renderNewsPost(post) {
    if (!post || !post.title) {
      return '';
    }

    var comments = Number(post.comments || 0);
    var views = Number(post.views || 0);
    var stats = [];
    if (comments > 0) {
      stats.push('댓글 ' + formatCount(comments));
    }
    if (views > 0) {
      stats.push('조회 ' + formatCount(views));
    }
    if (post.time) {
      stats.push(post.time);
    }

    return ''
      + '<li class="sebu-news-item">'
      + '<a href="' + esc(post.url || '#') + '" class="sebu-news-item__link">'
      + renderNewsThumb(post)
      + '<span class="sebu-news-item__main">'
      + '<span class="sebu-news-item__head">'
      + (post.board ? '<span class="sebu-news-item__board">' + esc(post.board) + '</span>' : '')
      + (post.is_new ? '<span class="sebu-news-item__badge sebu-news-item__badge--new">NEW</span>' : '')
      + (post.is_hot ? '<span class="sebu-news-item__badge sebu-news-item__badge--hot">HOT</span>' : '')
      + '</span>'
      + '<strong class="sebu-news-item__title">' + esc(post.title) + '</strong>'
      + '</span>'
      + (stats.length ? '<span class="sebu-news-item__stats">' + esc(stats.join(' · ')) + '</span>' : '')
      + '<span class="sebu-news-item__arrow" aria-hidden="true">›</span>'
      + '</a>'
      + '</li>';
  }

  function renderNewsFeaturePost(post, index) {
    if (!post || !post.title) {
      return '';
    }

    var comments = Number(post.comments || 0);
    var views = Number(post.views || 0);
    var stats = [];
    if (views > 0) {
      stats.push('조회 ' + formatCount(views));
    }
    if (comments > 0) {
      stats.push('댓글 ' + formatCount(comments));
    }
    if (post.time) {
      stats.push(post.time);
    }

    return ''
      + '<a href="' + esc(post.url || '#') + '" class="sebu-news-feature sebu-news-feature--' + esc(String(index + 1)) + '">'
      + '<span class="sebu-news-feature__media">' + renderNewsThumb(post) + '</span>'
      + '<span class="sebu-news-feature__body">'
      + '<span class="sebu-news-feature__head">'
      + (post.board ? '<span class="sebu-news-item__board">' + esc(post.board) + '</span>' : '')
      + (post.is_new ? '<span class="sebu-news-item__badge sebu-news-item__badge--new">NEW</span>' : '')
      + (post.is_hot ? '<span class="sebu-news-item__badge sebu-news-item__badge--hot">HOT</span>' : '')
      + '</span>'
      + '<strong class="sebu-news-feature__title">' + esc(post.title) + '</strong>'
      + (post.excerpt ? '<span class="sebu-news-feature__excerpt">' + esc(post.excerpt) + '</span>' : '')
      + (stats.length ? '<span class="sebu-news-feature__stats">' + esc(stats.join(' · ')) + '</span>' : '')
      + '</span>'
      + '</a>';
  }

  function renderNewsPanel(tab, index) {
    var posts = tab && tab.items ? tab.items.slice(0, 5) : [];
    var featureCount = Math.min(posts.length, 3);
    var featureHtml = '';
    var listHtml = '';
    var html = '';
    var i;

    for (i = 0; i < featureCount; i += 1) {
      featureHtml += renderNewsFeaturePost(posts[i], i);
    }
    for (i = featureCount; i < posts.length; i += 1) {
      listHtml += renderNewsPost(posts[i]);
    }

    if (!featureHtml && !listHtml) {
      var cta = tab && tab.emptyCta ? tab.emptyCta : {};
      html = ''
        + '<div class="sebu-news-empty">'
        + '<p class="sebu-news-empty__eyebrow">첫 소식을 기다리는 중</p>'
        + '<strong class="sebu-news-empty__title">' + esc(cta.title || t('home.news_empty_title', '아직 표시할 글이 없습니다.')) + '</strong>'
        + '<p class="sebu-news-empty__desc">' + esc(cta.desc || t('home.news_empty_desc', '새 글이 올라오면 홈 최신소식에 바로 노출됩니다.')) + '</p>'
        + '<div class="sebu-news-empty__actions">'
        + '<a href="' + esc(cta.primaryUrl || tab.url || communityUrl()) + '" class="sebu-news-empty__btn sebu-news-empty__btn--primary">' + esc(cta.primaryText || t('home.news_write', '글쓰기')) + '</a>'
        + '<a href="' + esc(cta.listUrl || tab.url || communityUrl()) + '" class="sebu-news-empty__btn">' + esc(cta.listText || t('home.news_list', '목록 보기')) + '</a>'
        + '</div>'
        + '</div>';
    } else {
      html = ''
        + (featureHtml ? '<div class="sebu-news-panel__features">' + featureHtml + '</div>' : '')
        + (listHtml ? '<ul class="sebu-news-panel__list">' + listHtml + '</ul>' : '');
    }

    return ''
      + '<div class="sebu-news-panel' + (index === 0 ? ' is-active' : '') + '"'
      + ' data-news-panel="' + esc(tab.key || '') + '"'
      + ' role="tabpanel"'
      + ' aria-hidden="' + (index === 0 ? 'false' : 'true') + '">'
      + html
      + '<a href="' + esc(tab.url || communityUrl()) + '" class="sebu-news-panel__more">' + esc(t('home.news_more', '더보기')) + '</a>'
      + '</div>';
  }

  function renderContributionBanner(banner) {
    if (!banner || !banner.actions || !banner.actions.length) {
      return '';
    }

    var actionsHtml = '';
    var i;
    for (i = 0; i < banner.actions.length; i += 1) {
      actionsHtml += ''
        + '<a href="' + esc(banner.actions[i].url || '#') + '" class="sebu-contribute-banner__btn sebu-contribute-banner__btn--' + esc(banner.actions[i].tone || 'default') + '">'
        + esc(banner.actions[i].label || '등록하기')
        + '</a>';
    }

    return ''
      + '<aside class="sebu-contribute-banner" aria-label="생활정보 등록 안내">'
      + '<div class="sebu-contribute-banner__copy">'
      + '<p class="sebu-contribute-banner__eyebrow">' + esc(banner.eyebrow || '함께 채우는 세부 생활지도') + '</p>'
      + '<strong class="sebu-contribute-banner__title">' + esc(banner.title || '') + '</strong>'
      + '<p class="sebu-contribute-banner__desc">' + esc(banner.desc || '') + '</p>'
      + '</div>'
      + '<div class="sebu-contribute-banner__actions">' + actionsHtml + '</div>'
      + '</aside>';
  }

  function renderLatestNewsColumn(latestNews) {
    latestNews = latestNews || {};
    var tabs = latestNews.tabs || [];
    var tabsHtml = '';
    var panelsHtml = '';
    var i;

    for (i = 0; i < tabs.length; i += 1) {
      tabsHtml += ''
        + '<button type="button" class="sebu-news-tabs__item' + (i === 0 ? ' is-active' : '') + '"'
        + ' data-news-tab="' + esc(tabs[i].key || '') + '"'
        + ' aria-pressed="' + (i === 0 ? 'true' : 'false') + '">'
        + '<span>' + esc(tabs[i].label || '') + '</span>'
        + '<em>' + esc(formatCount(tabs[i].count || 0)) + '</em>'
        + '</button>';
      panelsHtml += renderNewsPanel(tabs[i], i);
    }

    return ''
      + '<article class="sebu-community-col sebu-community-col--news" data-eottae-news-tabs="1">'
      + '<header class="sebu-community-col__head sebu-community-col__head--news">'
      + '<div>'
      + '<p class="sebu-news-kicker">' + esc(t('home.news_kicker', '세부 커뮤니티')) + '</p>'
      + '<h3 class="sebu-community-col__title">'
      + '<span class="sebu-community-col__accent sebu-community-col__accent--sky" aria-hidden="true"></span>'
      + esc(latestNews.title || t('home.news_title', '세부 최신소식'))
      + '</h3>'
      + '<p class="sebu-news-desc">' + esc(latestNews.desc || t('home.news_desc', '세부의 새 글을 빠르게 확인하세요.')) + '</p>'
      + '</div>'
      + '</header>'
      + renderTodayOverview(latestNews)
      + '<nav class="sebu-news-tabs" role="tablist" aria-label="최신소식 분류">' + tabsHtml + '</nav>'
      + '<div class="sebu-news-panels">' + panelsHtml + '</div>'
      + '</article>';
  }

  function bindLatestNewsTabs(root) {
    if (!root || root.dataset.newsTabsBound === '1') {
      return;
    }

    var buttons = root.querySelectorAll('[data-news-tab]');
    var panels = root.querySelectorAll('[data-news-panel]');
    if (!buttons.length || !panels.length) {
      return;
    }

    root.dataset.newsTabsBound = '1';
    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var key = button.getAttribute('data-news-tab') || '';
        buttons.forEach(function (item) {
          var active = item === button;
          item.classList.toggle('is-active', active);
          item.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        panels.forEach(function (panel) {
          var active = (panel.getAttribute('data-news-panel') || '') === key;
          panel.classList.toggle('is-active', active);
          panel.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
      });
    });
  }

  function formatCount(value) {
    var n = Number(value) || 0;
    return n.toLocaleString('ko-KR');
  }

  function talkListUrl() {
    var data = cfg();
    if (data && data.talk_rooms && data.talk_rooms.list_url) {
      return data.talk_rooms.list_url;
    }
    return '/talk';
  }

  function renderTalkRoomSlide(room, index) {
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

    var desc = room.room_description || '관심 주제별 공개 톡방에 참여해 보세요.';
    if (desc.length > 52) {
      desc = desc.slice(0, 52) + '…';
    }

    return ''
      + '<article class="sebu-talkrooms-col__slide" data-slide-index="' + esc(String(index)) + '" aria-hidden="' + (index === 0 ? 'false' : 'true') + '">'
      + '<a href="' + esc(room.enter_href) + '" class="sebu-talkrooms-col__link">'
      + '<span class="sebu-talkrooms-col__emoji" aria-hidden="true">' + esc(room.emoji || '💬') + '</span>'
      + '<span class="sebu-talkrooms-col__body">'
      + '<span class="sebu-talkrooms-col__meta">'
      + (room.category ? '<span class="sebu-talkrooms-col__category">' + esc(room.category) + '</span>' : '')
      + (room.updated_label ? '<span class="sebu-talkrooms-col__time">' + esc(room.updated_label) + '</span>' : '')
      + '</span>'
      + '<strong class="sebu-talkrooms-col__name">' + esc(room.room_name || '세부톡방') + '</strong>'
      + '<span class="sebu-talkrooms-col__desc">' + esc(desc) + '</span>'
      + (stats.length ? '<span class="sebu-talkrooms-col__stats">' + esc(stats.join(' · ')) + '</span>' : '')
      + '</span>'
      + '<span class="sebu-talkrooms-col__enter">입장</span>'
      + '</a>'
      + '</article>';
  }

  function renderTalkRoomsMobileCarousel(rooms) {
    var slidesHtml = '';
    var dotsHtml = '';
    var i;

    for (i = 0; i < rooms.length; i += 1) {
      slidesHtml += renderTalkRoomSlide(rooms[i], i);
      dotsHtml += ''
        + '<button type="button" class="sebu-talkrooms-col__dot' + (i === 0 ? ' is-active' : '') + '"'
        + ' data-slide-to="' + esc(String(i)) + '"'
        + ' aria-label="' + esc(String(i + 1)) + '번 톡방"'
        + (i === 0 ? ' aria-current="true"' : '')
        + '></button>';
    }

    return ''
      + '<div class="sebu-talkrooms-col__mobile" data-eottae-talkrooms-carousel="1">'
      + '<div class="sebu-talkrooms-col__viewport">'
      + '<div class="sebu-talkrooms-col__track" data-slide-count="' + esc(String(rooms.length)) + '">' + slidesHtml + '</div>'
      + '</div>'
      + '<div class="sebu-talkrooms-col__controls">'
      + '<button type="button" class="sebu-talkrooms-col__nav sebu-talkrooms-col__nav--prev" aria-label="이전 톡방">‹</button>'
      + '<div class="sebu-talkrooms-col__dots" role="tablist" aria-label="톡방 슬라이드">' + dotsHtml + '</div>'
      + '<button type="button" class="sebu-talkrooms-col__nav sebu-talkrooms-col__nav--next" aria-label="다음 톡방">›</button>'
      + '</div>'
      + '</div>';
  }

  function renderTalkRoomItem(room) {
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

    var meta = stats.length ? stats.join(' · ') : '';
    if (room.updated_label) {
      meta = meta ? room.updated_label + ' · ' + meta : room.updated_label;
    }

    return ''
      + '<li class="sebu-talkrooms-col__item">'
      + '<a href="' + esc(room.enter_href) + '" class="sebu-talkrooms-col__link">'
      + '<span class="sebu-talkrooms-col__emoji" aria-hidden="true">' + esc(room.emoji || '💬') + '</span>'
      + '<span class="sebu-talkrooms-col__body">'
      + '<span class="sebu-talkrooms-col__head">'
      + (room.category ? '<span class="sebu-talkrooms-col__category">' + esc(room.category) + '</span>' : '')
      + '<strong class="sebu-talkrooms-col__name">' + esc(room.room_name || '세부톡방') + '</strong>'
      + '</span>'
      + (meta ? '<span class="sebu-talkrooms-col__meta">' + esc(meta) + '</span>' : '')
      + '</span>'
      + '<span class="sebu-talkrooms-col__enter">입장</span>'
      + '</a>'
      + '</li>';
  }

  function talkRoomsVisibleCount(talkRooms) {
    var count = talkRooms && talkRooms.visible_count != null ? Number(talkRooms.visible_count) : 6;
    if (!count || count < 1) {
      count = 6;
    }
    return Math.min(8, count);
  }

  function renderTalkRoomsDesktopList(rooms, visibleCount, enableVertical) {
    var listHtml = '';
    var i;

    for (i = 0; i < rooms.length; i += 1) {
      listHtml += renderTalkRoomItem(rooms[i]);
    }

    if (enableVertical) {
      return ''
        + '<div class="sebu-talkrooms-col__desktop sebu-talkrooms-col__desktop--vertical"'
        + ' data-eottae-talkrooms-vertical="1"'
        + ' data-visible-count="' + esc(String(visibleCount)) + '">'
        + '<div class="sebu-talkrooms-col__v-viewport">'
        + '<ul class="sebu-talkrooms-col__list sebu-talkrooms-col__v-track">' + listHtml + '</ul>'
        + '</div>'
        + '</div>';
    }

    return ''
      + '<div class="sebu-talkrooms-col__desktop">'
      + '<ul class="sebu-talkrooms-col__list">' + listHtml + '</ul>'
      + '</div>';
  }

  function renderTalkRoomsAiBanner(banner) {
    banner = banner || {};
    var url = banner.url || (global.__EOTTae__ && global.__EOTTae__.talkAiUrl) || '/talk/ai.php';
    var eyebrow = banner.eyebrow || t('home.talk_ai_eyebrow', 'AI 톡방 도우미');
    var title = banner.title || t('home.talk_ai_title', '조용한 톡방, AI가 먼저 말을 걸어요');
    var desc = banner.desc || t('home.talk_ai_desc', '오늘의 질문·모임 제안·신규회원 환영까지. 세부어때 AI가 우리 톡방 분위기를 살려줍니다.');
    var cta = banner.cta || t('home.talk_ai_cta', '자세히 보기');

    return ''
      + '<a href="' + esc(url) + '" class="sebu-talkrooms-ai-banner" aria-label="' + esc(title) + '">'
      + '<span class="sebu-talkrooms-ai-banner__icon" aria-hidden="true">🤖</span>'
      + '<span class="sebu-talkrooms-ai-banner__copy">'
      + '<span class="sebu-talkrooms-ai-banner__eyebrow">' + esc(eyebrow) + '</span>'
      + '<strong class="sebu-talkrooms-ai-banner__title">' + esc(title) + '</strong>'
      + '<span class="sebu-talkrooms-ai-banner__desc">' + esc(desc) + '</span>'
      + '</span>'
      + '<span class="sebu-talkrooms-ai-banner__cta">' + esc(cta) + '<span aria-hidden="true">→</span></span>'
      + '</a>';
  }

  function renderTalkRoomsColumn(talkRooms) {
    var listUrl = (talkRooms && talkRooms.list_url) ? talkRooms.list_url : talkListUrl();
    var rooms = talkRooms && talkRooms.rooms ? talkRooms.rooms : [];
    var visibleCount = talkRoomsVisibleCount(talkRooms);
    var staticRooms = rooms.slice(0, visibleCount);
    var enableVertical = rooms.length > visibleCount;
    var bodyHtml = '';

    if (rooms.length) {
      bodyHtml = ''
        + renderTalkRoomsDesktopList(enableVertical ? rooms : staticRooms, visibleCount, enableVertical)
        + renderTalkRoomsMobileCarousel(rooms);
    } else {
      bodyHtml = '<p class="sebu-community-col__empty">표시할 공개 톡방이 없습니다.</p>';
    }

    return ''
      + '<article class="sebu-community-col sebu-community-col--talkrooms" aria-label="공개 세부톡방">'
      + '<header class="sebu-community-col__head">'
      + '<h3 class="sebu-community-col__title">'
      + '<span class="sebu-community-col__accent sebu-community-col__accent--sky" aria-hidden="true"></span>'
      + '공개 세부톡방'
      + '</h3>'
      + '<a href="' + esc(listUrl) + '" class="sebu-community-col__more">전체보기 +</a>'
      + '</header>'
      + '<div class="sebu-talkrooms-col__stack">'
      + bodyHtml
      + renderTalkRoomsAiBanner(talkRooms && talkRooms.ai_banner ? talkRooms.ai_banner : null)
      + '</div>'
      + '</article>';
  }

  function bindTalkRoomsCarousel(root) {
    if (!root || root.dataset.carouselBound === '1') {
      return;
    }

    var track = root.querySelector('.sebu-talkrooms-col__track');
    var slides = track ? track.querySelectorAll('.sebu-talkrooms-col__slide') : [];
    var dots = root.querySelectorAll('.sebu-talkrooms-col__dot');
    if (!track || !slides.length) {
      return;
    }

    root.dataset.carouselBound = '1';

    var active = 0;
    var timer = null;
    var intervalMs = 3000;
    var touchStartX = 0;
    var touchStartY = 0;

    function setActive(index) {
      var count = slides.length;
      if (count < 1) {
        return;
      }

      active = ((index % count) + count) % count;

      var i;
      for (i = 0; i < slides.length; i += 1) {
        var on = i === active;
        slides[i].classList.toggle('is-active', on);
        slides[i].setAttribute('aria-hidden', on ? 'false' : 'true');
      }

      dots.forEach(function (dot, dotIndex) {
        var current = dotIndex === active;
        dot.classList.toggle('is-active', current);
        if (current) {
          dot.setAttribute('aria-current', 'true');
        } else {
          dot.removeAttribute('aria-current');
        }
      });

      track.style.transform = 'translate3d(-' + (active * 100) + '%, 0, 0)';
    }

    function nextSlide(step) {
      setActive(active + (typeof step === 'number' ? step : 1));
    }

    function stopAuto() {
      if (timer) {
        global.clearInterval(timer);
        timer = null;
      }
    }

    function startAuto() {
      stopAuto();
      if (slides.length < 2) {
        return;
      }
      timer = global.setInterval(function () {
        nextSlide(1);
      }, intervalMs);
    }

    function restartAuto() {
      stopAuto();
      startAuto();
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var target = parseInt(dot.getAttribute('data-slide-to') || '0', 10);
        setActive(target);
        restartAuto();
      });
    });

    var prevBtn = root.querySelector('.sebu-talkrooms-col__nav--prev');
    var nextBtn = root.querySelector('.sebu-talkrooms-col__nav--next');
    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        nextSlide(-1);
        restartAuto();
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        nextSlide(1);
        restartAuto();
      });
    }

    root.addEventListener('mouseenter', stopAuto);
    root.addEventListener('mouseleave', startAuto);
    root.addEventListener('focusin', stopAuto);
    root.addEventListener('focusout', startAuto);

    root.addEventListener('touchstart', function (event) {
      if (!event.changedTouches || !event.changedTouches.length) {
        return;
      }
      touchStartX = event.changedTouches[0].clientX;
      touchStartY = event.changedTouches[0].clientY;
      stopAuto();
    }, { passive: true });

    root.addEventListener('touchend', function (event) {
      if (!event.changedTouches || !event.changedTouches.length) {
        return;
      }
      var dx = event.changedTouches[0].clientX - touchStartX;
      var dy = event.changedTouches[0].clientY - touchStartY;
      if (Math.abs(dx) >= 40 && Math.abs(dx) > Math.abs(dy)) {
        nextSlide(dx < 0 ? 1 : -1);
      }
      restartAuto();
    }, { passive: true });

    setActive(0);
    startAuto();
  }

  function bindTalkRoomsVerticalCarousel(root) {
    if (!root || root.dataset.verticalBound === '1') {
      return;
    }

    var viewport = root.querySelector('.sebu-talkrooms-col__v-viewport');
    var track = root.querySelector('.sebu-talkrooms-col__v-track');
    var items = track ? track.querySelectorAll('.sebu-talkrooms-col__item') : [];
    var visibleCount = parseInt(root.getAttribute('data-visible-count') || '6', 10);
    if (!viewport || !track || !items.length || items.length <= visibleCount) {
      return;
    }

    root.dataset.verticalBound = '1';

    var active = 0;
    var timer = null;
    var intervalMs = 3000;
    var stepPx = 0;

    function measureStep() {
      if (!items[0]) {
        return 0;
      }
      var style = global.getComputedStyle(track);
      var gap = parseFloat(style.rowGap || style.gap || '0') || 0;
      stepPx = items[0].offsetHeight + gap;
      return stepPx;
    }

    function applyTransform() {
      if (!stepPx) {
        measureStep();
      }
      track.style.transform = 'translate3d(0, -' + (active * stepPx) + 'px, 0)';
    }

    function nextSlide() {
      active += 1;
      if (active > items.length - visibleCount) {
        active = 0;
        track.style.transition = 'none';
        applyTransform();
        global.requestAnimationFrame(function () {
          global.requestAnimationFrame(function () {
            track.style.transition = '';
          });
        });
        return;
      }
      applyTransform();
    }

    function stopAuto() {
      if (timer) {
        global.clearInterval(timer);
        timer = null;
      }
    }

    function startAuto() {
      stopAuto();
      if (items.length <= visibleCount) {
        return;
      }
      timer = global.setInterval(nextSlide, intervalMs);
    }

    measureStep();
    viewport.style.setProperty('--sebu-talkrooms-visible', String(visibleCount));
    applyTransform();
    startAuto();

    root.addEventListener('mouseenter', stopAuto);
    root.addEventListener('mouseleave', startAuto);
    root.addEventListener('focusin', stopAuto);
    root.addEventListener('focusout', startAuto);

    global.addEventListener('resize', function () {
      measureStep();
      applyTransform();
    }, { passive: true });
  }

  function initTalkRoomsCarousels(scope) {
    var host = scope || document;
    host.querySelectorAll('[data-eottae-talkrooms-carousel="1"]').forEach(bindTalkRoomsCarousel);
    host.querySelectorAll('[data-eottae-talkrooms-vertical="1"]').forEach(bindTalkRoomsVerticalCarousel);
  }

  function renderCommunityBlock(talkRooms) {
    var data = cfg() || {};
    var colsHtml = renderLatestNewsColumn(data.latest_news || {});
    colsHtml += renderTalkRoomsColumn(talkRooms || {});

    return ''
      + '<section class="sebu-community-popular" data-eottae-home-popular="1" aria-label="오늘의 세부 커뮤니티">'
      + '<h2 class="sebu-community-popular__title">오늘의 세부 커뮤니티</h2>'
      + renderContributionBanner(data.contribution_banner || {})
      + '<div class="sebu-community-popular__grid">' + colsHtml + '</div>'
      + '</section>';
  }

  var mountDone = false;

  function needsRemount() {
    return mountDone && !document.querySelector('[data-eottae-home-main-mounted="1"]');
  }

  function mount() {
    if (mountDone && !needsRemount()) {
      return true;
    }

    if (needsRemount()) {
      mountDone = false;
    }

    var data = cfg();
    if (!data || (!data.popular && !data.latest_news && !data.talk_rooms)) {
      return false;
    }

    var section = findCommunitySection();
    if (!section) {
      return false;
    }

    if (section.querySelector('[data-eottae-home-main-mounted]')) {
      mountPopularHot(data.popular || {}, section);
      mountDone = true;
      return true;
    }

    mountPopularHot(data.popular || {}, section);

    var mountRoot = document.createElement('div');
    mountRoot.className = 'sebu-home-main-section';
    mountRoot.setAttribute('data-eottae-home-main-mounted', '1');
    mountRoot.innerHTML = renderCommunityBlock(data.talk_rooms || {});

    if (!section.hasAttribute('data-eottae-home-community-mount')) {
      section.classList.add('sebu-home-main-host');
      section.setAttribute('data-eottae-home-main-host', 'community');
    }

    while (section.firstChild) {
      section.removeChild(section.firstChild);
    }
    section.appendChild(mountRoot);
    mountDone = true;

    hideLegacyBuilderCommunitySections();
    initTalkRoomsCarousels(mountRoot);
    bindLatestNewsTabs(mountRoot.querySelector('[data-eottae-news-tabs="1"]'));

    if (typeof global.eottaeCalendarInitEventModal === 'function') {
      global.eottaeCalendarInitEventModal();
    }

    return true;
  }

  var popularMountObserver = null;

  function scheduleMount(retryCount) {
    if (mountDone && !needsRemount()) {
      return;
    }

    var data = cfg();
    if (data && data.popular) {
      mountPopularHot(data.popular || {}, findCommunitySection());
    }

    if (mount()) {
      watchLegacyHomeSectionsRemoval();
      global.setTimeout(watchLegacyHomeSectionsRemoval, 600);
      if (popularMountObserver) {
        popularMountObserver.disconnect();
        popularMountObserver = null;
      }
      return;
    }

    watchLegacyHomeSectionsRemoval();

    var tries = typeof retryCount === 'number' ? retryCount : 0;
    if (tries < 40) {
      global.setTimeout(function () {
        scheduleMount(tries + 1);
      }, 300);
    }
  }

  function watchPopularSectionMount() {
    var root = document.getElementById('root');
    scheduleMount(0);

    if (!root || mountDone || popularMountObserver) {
      return;
    }

    popularMountObserver = new MutationObserver(function () {
      if (mount()) {
        popularMountObserver.disconnect();
        popularMountObserver = null;
      }
    });

    popularMountObserver.observe(root, { childList: true, subtree: true });
    global.setTimeout(function () {
      if (popularMountObserver) {
        popularMountObserver.disconnect();
        popularMountObserver = null;
      }
    }, 60000);
  }

  function init() {
    var run = function () {
      watchPopularSectionMount();
    };

    if (typeof global.eottaeHomeAfterReactReady === 'function') {
      global.eottaeHomeAfterReactReady(run);
      global.setTimeout(run, 4000);
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

  global.addEventListener('eottae:languagechange', function () {
    mountDone = false;
    var mainMounted = document.querySelector('[data-eottae-home-main-mounted="1"]');
    if (mainMounted && mainMounted.parentNode) {
      mainMounted.parentNode.removeChild(mainMounted);
    }
    var hotMounted = document.querySelector('[data-eottae-home-popular-hot-mounted="1"]');
    if (hotMounted && hotMounted.parentNode) {
      hotMounted.parentNode.removeChild(hotMounted);
    }
    mount();
  });
}(window));
