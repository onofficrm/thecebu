/**
 * 홈(빌더) — 오늘의 세부 브리핑 티저
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

  function renderStats(stats) {
    var html = '';
    var i;

    for (i = 0; i < (stats || []).length; i += 1) {
      html += ''
        + '<li class="sebu-briefing-teaser__stat">'
        + '<span class="sebu-briefing-teaser__stat-value">' + esc(String(stats[i].value == null ? 0 : stats[i].value)) + '</span>'
        + '<span class="sebu-briefing-teaser__stat-label">' + esc(stats[i].label || '') + '</span>'
        + '</li>';
    }

    return html;
  }

  function renderTeaser(data) {
    return ''
      + '<section class="sebu-briefing-teaser sebu-briefing-teaser--home-mount" aria-labelledby="sebu-briefing-teaser-home-title">'
      + '<div class="sebu-briefing-teaser__inner">'
      + '<div class="sebu-briefing-teaser__content">'
      + '<p class="sebu-briefing-teaser__eyebrow">Daily Briefing</p>'
      + '<h2 class="sebu-briefing-teaser__title" id="sebu-briefing-teaser-home-title">' + esc(data.title || '오늘의 세부 체크') + '</h2>'
      + (data.summary ? '<p class="sebu-briefing-teaser__summary">' + esc(data.summary) + '</p>' : '')
      + (data.line ? '<p class="sebu-briefing-teaser__line">' + esc(data.line) + '</p>' : '')
      + (data.stats && data.stats.length
        ? '<ul class="sebu-briefing-teaser__stats">' + renderStats(data.stats) + '</ul>'
        : '')
      + '</div>'
      + '<a href="' + esc(data.briefing_url || '/briefing/') + '" class="sebu-briefing-teaser__cta">'
      + esc(data.cta || '브리핑 보기') + '</a>'
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
    wrap.innerHTML = renderTeaser(data);
    target.appendChild(wrap);

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
