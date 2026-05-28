(function () {
  'use strict';

  function initAdroomListWriteGuide() {
    var root = document.querySelector('.board-wrap--eottae-adroom#bo_list');
    if (!root) {
      return;
    }

    var guide = document.getElementById('adroom-write-guide');
    if (!guide) {
      return;
    }

    root.querySelectorAll('[data-adroom-show-write-guide]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        guide.hidden = false;
        guide.classList.add('is-flash');
        guide.scrollIntoView({ behavior: 'smooth', block: 'start' });
        try {
          guide.focus({ preventScroll: true });
        } catch (e) {
          guide.focus();
        }

        window.setTimeout(function () {
          guide.classList.remove('is-flash');
        }, 2600);
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdroomListWriteGuide);
  } else {
    initAdroomListWriteGuide();
  }
})();
