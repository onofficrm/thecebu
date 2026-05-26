/**
 * 세부어때 캘린더 — 폼·하루종일 토글
 */
(function () {
  'use strict';

  function initAllDayToggle(form) {
    var checkbox = form.querySelector('[data-sebu-cal-all-day]');
    var timeFields = form.querySelector('[data-sebu-cal-time-fields]');
    if (!checkbox || !timeFields) {
      return;
    }

    function sync() {
      if (checkbox.checked) {
        timeFields.setAttribute('hidden', 'hidden');
      } else {
        timeFields.removeAttribute('hidden');
      }
    }

    checkbox.addEventListener('change', sync);
    sync();
  }

  function initStartEndDate(form) {
    var start = form.querySelector('#sebu_cal_start_date');
    var end = form.querySelector('#sebu_cal_end_date');
    if (!start || !end) {
      return;
    }

    start.addEventListener('change', function () {
      if (!end.value || end.value < start.value) {
        end.value = start.value;
      }
      if (end.min !== undefined) {
        end.min = start.value;
      }
    });
  }

  document.querySelectorAll('[data-sebu-cal-form]').forEach(function (form) {
    initAllDayToggle(form);
    initStartEndDate(form);
  });
})();
