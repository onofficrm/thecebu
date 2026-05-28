/**
 * 홈(빌더) — React 첫 렌더 완료 후 DOM 확장 스크립트 실행
 */
(function (global) {
  'use strict';

  var queue = [];
  var ready = false;
  var polling = false;

  function normalizeText(value) {
    return String(value || '').replace(/\s+/g, '').trim();
  }

  function isReady() {
    var root = document.getElementById('root');
    if (!root || (root.innerHTML || '').length < 500) {
      return false;
    }

    var headings = root.querySelectorAll('h1');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      if ((headings[i].textContent || '').indexOf('세부어때') !== -1) {
        return true;
      }
    }

    var header = root.querySelector('header');
    if (header) {
      var navLinks = header.querySelectorAll('a[href]');
      var hasHome = false;
      var hasPrimary = false;

      for (i = 0; i < navLinks.length; i += 1) {
        var label = normalizeText(navLinks[i].textContent);
        if (label === '홈') {
          hasHome = true;
        }
        if (
          label.indexOf('내주변') !== -1
          || label.indexOf('커뮤니티') !== -1
          || label.indexOf('생활지도') !== -1
          || label === 'MY'
        ) {
          hasPrimary = true;
        }
      }

      if (hasHome && hasPrimary) {
        return true;
      }
    }

    var h2s = root.querySelectorAll('h2, h3');
    for (i = 0; i < h2s.length; i += 1) {
      var text = normalizeText(h2s[i].textContent);
      if (
        text.indexOf('커뮤니티') !== -1
        || text.indexOf('실시간') !== -1
        || text.indexOf('인기글') !== -1
        || text.indexOf('세부생활') !== -1
        || text.indexOf('세부최신') !== -1
      ) {
        return true;
      }
    }

    return false;
  }

  function flush() {
    ready = true;
    var fns = queue.slice();
    queue = [];

    global.requestAnimationFrame(function () {
      global.requestAnimationFrame(function () {
        fns.forEach(function (fn) {
          try {
            fn();
          } catch (err) {
            if (global.console && global.console.error) {
              global.console.error(err);
            }
          }
        });
      });
    });
  }

  function poll() {
    if (ready || polling) {
      return;
    }

    polling = true;
    var tries = 0;

    (function tick() {
      if (ready) {
        polling = false;
        return;
      }

      if (isReady()) {
        polling = false;
        flush();
        return;
      }

      tries += 1;
      if (tries >= 100) {
        polling = false;
        if (queue.length) {
          flush();
        }
        return;
      }

      global.setTimeout(tick, 200);
    })();
  }

  global.eottaeHomeAfterReactReady = function (fn) {
    if (typeof fn !== 'function') {
      return;
    }

    if (ready) {
      fn();
      return;
    }

    queue.push(fn);
    poll();
  };

  global.eottaeHomeIsReactReady = isReady;
}(window));
