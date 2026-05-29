(function (global) {
  'use strict';

  function initGalSliders() {
    var roots = document.querySelectorAll('[data-gal-slider]');
    if (!roots.length) {
      return;
    }

    roots.forEach(function (root) {
      if (root.getAttribute('data-gal-slider-bound') === '1') {
        return;
      }
      root.setAttribute('data-gal-slider-bound', '1');

      var slides = root.querySelectorAll('[data-gal-slide]');
      if (!slides.length) {
        return;
      }

      var index = 0;
      var autoplayMs = parseInt(root.getAttribute('data-autoplay'), 10);
      if (isNaN(autoplayMs) || autoplayMs < 1) {
        autoplayMs = 3000;
      }

      var timer = null;
      var prevBtn = root.querySelector('[data-gal-slider-prev]');
      var nextBtn = root.querySelector('[data-gal-slider-next]');
      var counter = root.querySelector('[data-gal-slider-counter]');
      var thumbs = root.querySelectorAll('[data-gal-thumb]');

      function show(nextIndex) {
        index = (nextIndex + slides.length) % slides.length;

        slides.forEach(function (slide, idx) {
          var active = idx === index;
          slide.classList.toggle('is-active', active);
          slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });

        thumbs.forEach(function (thumb, idx) {
          thumb.classList.toggle('is-active', idx === index);
        });

        if (counter) {
          counter.textContent = (index + 1) + ' / ' + slides.length;
        }
      }

      function next() {
        show(index + 1);
      }

      function prev() {
        show(index - 1);
      }

      function startAutoplay() {
        stopAutoplay();
        if (slides.length < 2) {
          return;
        }
        timer = global.setInterval(next, autoplayMs);
      }

      function stopAutoplay() {
        if (timer) {
          global.clearInterval(timer);
          timer = null;
        }
      }

      function restartAutoplay() {
        stopAutoplay();
        startAutoplay();
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          prev();
          restartAutoplay();
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          next();
          restartAutoplay();
        });
      }

      thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
          var target = parseInt(thumb.getAttribute('data-gal-thumb'), 10);
          if (isNaN(target)) {
            return;
          }
          show(target);
          restartAutoplay();
        });
      });

      root.addEventListener('mouseenter', stopAutoplay);
      root.addEventListener('mouseleave', startAutoplay);
      root.addEventListener('focusin', stopAutoplay);
      root.addEventListener('focusout', startAutoplay);

      var touchStartX = 0;
      root.addEventListener('touchstart', function (event) {
        if (!event.changedTouches || !event.changedTouches.length) {
          return;
        }
        touchStartX = event.changedTouches[0].clientX;
        stopAutoplay();
      }, { passive: true });

      root.addEventListener('touchend', function (event) {
        if (!event.changedTouches || !event.changedTouches.length) {
          return;
        }
        var deltaX = event.changedTouches[0].clientX - touchStartX;
        if (Math.abs(deltaX) > 40) {
          if (deltaX < 0) {
            next();
          } else {
            prev();
          }
        }
        startAutoplay();
      }, { passive: true });

      show(0);
      startAutoplay();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGalSliders);
  } else {
    initGalSliders();
  }
}(window));
