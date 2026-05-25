/**
 * 홈(빌더) — React 첫 렌더 완료 후 DOM 확장 스크립트 실행
 */
(function (global) {
  'use strict';

  var queue = [];
  var ready = false;
  var polling = false;

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

    return false;
  }

  function flush() {
    ready = true;
    var fns = queue.slice();
    queue = [];

    fns.forEach(function (fn) {
      try {
        fn();
      } catch (err) {
        if (global.console && global.console.error) {
          global.console.error(err);
        }
      }
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
