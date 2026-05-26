/**
 * 홈(빌더) — 오늘의 세부 브리핑
 */
(function (global) {
  'use strict';

  function cfg() {
    return global.__EOTTae_HOME_BRIEFING__ || null;
  }

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function renderCard(card) {
    return ''
      + '<a href="' + esc(card.url || '#') + '" class="sebu-briefing-card sebu-briefing-card--' + esc(card.icon || 'default') + '">'
      + '<span class="sebu-briefing-card__icon" aria-hidden="true"></span>'
      + '<span class="sebu-briefing-card__value">' + esc(String(card.value == null ? 0 : card.value)) + '</span>'
      + '<span class="sebu-briefing-card__label">' + esc(card.label || '') + '</span>'
      + '</a>';
  }

  function renderPopular(post) {
    if (!post || !post.title) {
      return '';
    }

    return ''
      + '<li class="sebu-briefing-popular-list__item">'
      + '<a href="' + esc(post.url || '#') + '" class="sebu-briefing-popular-list__link">'
      + (post.board ? '<span class="sebu-briefing-popular-list__board">' + esc(post.board) + '</span>' : '')
      + '<span class="sebu-briefing-popular-list__title">' + esc(post.title) + '</span>'
      + '<span class="sebu-briefing-popular-list__meta">조회 ' + esc(String(post.views || 0)) + '</span>'
      + '</a>'
      + '</li>';
  }

  function renderBriefing(data) {
    var cardsHtml = '';
    var linesHtml = '';
    var popularHtml = '';
    var i;

    for (i = 0; i < (data.cards || []).length; i += 1) {
      cardsHtml += renderCard(data.cards[i]);
    }

    for (i = 0; i < (data.lines || []).length; i += 1) {
      if (data.lines[i]) {
        linesHtml += '<p class="sebu-briefing__line">' + esc(data.lines[i]) + '</p>';
      }
    }

    for (i = 0; i < (data.popular || []).length; i += 1) {
      popularHtml += renderPopular(data.popular[i]);
    }

    return ''
      + '<section class="sebu-briefing sebu-briefing--today sebu-briefing--home-mount" aria-labelledby="sebu-briefing-home-title">'
      + '<div class="sebu-briefing__inner">'
      + '<header class="sebu-briefing__head">'
      + '<div>'
      + '<p class="sebu-briefing__eyebrow">Daily Briefing</p>'
      + '<h2 class="sebu-briefing__title" id="sebu-briefing-home-title">' + esc(data.title || '오늘의 세부 브리핑') + '</h2>'
      + '<p class="sebu-briefing__subtitle">' + esc(data.subtitle || '') + '</p>'
      + '</div>'
      + '</header>'
      + (data.admin_notice
        ? '<div class="sebu-briefing__notice" role="note"><strong class="sebu-briefing__notice-label">오늘의 안내</strong><p>'
          + esc(data.admin_notice) + '</p></div>'
        : '')
      + (cardsHtml ? '<div class="sebu-briefing__cards">' + cardsHtml + '</div>' : '')
      + (linesHtml ? '<div class="sebu-briefing__body">' + linesHtml + '</div>' : '')
      + (data.summary ? '<p class="sebu-briefing__summary">' + esc(data.summary) + '</p>' : '')
      + (popularHtml
        ? '<div class="sebu-briefing__popular"><h3 class="sebu-briefing__popular-title">인기글</h3>'
          + '<ul class="sebu-briefing-popular-list">' + popularHtml + '</ul></div>'
        : '')
      + '</div>'
      + '</section>';
  }

  function findMountTarget() {
    var root = document.getElementById('root');
    if (!root) {
      return null;
    }

    var main = root.querySelector('main');
    if (main) {
      return main;
    }

    var sections = root.querySelectorAll('section');
    if (sections.length) {
      return sections[0].parentElement || root;
    }

    return root;
  }

  function mount() {
    var data = cfg();
    if (!data) {
      return false;
    }

    var target = findMountTarget();
    if (!target) {
      return false;
    }

    if (document.getElementById('eottae-sebu-briefing-home')) {
      return true;
    }

    var wrap = document.createElement('div');
    wrap.id = 'eottae-sebu-briefing-home';
    wrap.innerHTML = renderBriefing(data);

    if (target.firstChild) {
      target.insertBefore(wrap, target.firstChild);
    } else {
      target.appendChild(wrap);
    }

    return true;
  }

  function init() {
    if (mount()) {
      return;
    }

    if (typeof MutationObserver === 'undefined') {
      return;
    }

    var root = document.getElementById('root');
    if (!root) {
      return;
    }

    var observer = new MutationObserver(function () {
      if (mount()) {
        observer.disconnect();
      }
    });

    observer.observe(root, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}(window));
