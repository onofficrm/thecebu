(function (global) {
  'use strict';

  function getPeriodMode(root) {
    var checked = root.querySelector('input[name="wr_4"]:checked');
    return checked ? checked.value : 'period';
  }

  function syncPeriodUi(root) {
    var mode = getPeriodMode(root);
    var dates = root.querySelector('#sebuEventDates');
    var endWrap = root.querySelector('#sebuEventEndWrap');
    var endInput = root.querySelector('#wr_6');
    var reqEnd = root.querySelector('.sebu-event-template__req--end');

    if (dates) {
      dates.setAttribute('data-period-mode', mode);
    }

    var isPeriod = mode === 'period';
    if (endWrap) {
      endWrap.hidden = !isPeriod;
    }
    if (endInput) {
      endInput.required = isPeriod;
      if (!isPeriod) {
        endInput.value = '';
      }
    }
    if (reqEnd) {
      reqEnd.hidden = !isPeriod;
    }
  }

  function fillNameFromShop(root) {
    var shopSelect = root.querySelector('#wr_2');
    var nameInput = root.querySelector('#wr_3');
    if (!shopSelect || !nameInput || shopSelect.value === '') {
      return;
    }
    var opt = shopSelect.options[shopSelect.selectedIndex];
    if (!opt || !opt.textContent) {
      return;
    }
    var label = opt.textContent.split('(')[0].split('·')[0].trim();
    if (label && nameInput.value.trim() === '') {
      nameInput.value = label;
    }
  }

  function validateForm(form) {
    var modeInput = form.querySelector('input[name="wr_4"]:checked');
    var mode = modeInput ? modeInput.value : 'period';
    var start = form.querySelector('#wr_5');
    var end = form.querySelector('#wr_6');

    if (mode === 'period') {
      if (!end || !end.value) {
        global.alert('기간 있음 선택 시 종료일을 입력해 주세요.');
        if (end) {
          end.focus();
        }
        return false;
      }
      if (start && start.value && end.value < start.value) {
        global.alert('종료일은 시작일보다 빠를 수 없습니다.');
        end.focus();
        return false;
      }
    }

    return true;
  }

  function initTemplate(root) {
    if (!root || root.dataset.bound === '1') {
      return;
    }
    root.dataset.bound = '1';

    var radios = root.querySelectorAll('input[name="wr_4"]');
    var i;
    for (i = 0; i < radios.length; i += 1) {
      radios[i].addEventListener('change', function () {
        syncPeriodUi(root);
      });
    }

    var shopSelect = root.querySelector('#wr_2');
    if (shopSelect) {
      shopSelect.addEventListener('change', function () {
        fillNameFromShop(root);
      });
    }

    syncPeriodUi(root);

    var form = root.closest('form');
    if (form) {
      form.addEventListener('submit', function (event) {
        if (!validateForm(form)) {
          event.preventDefault();
          return;
        }
        syncEditorBeforeSubmit();
      });
    }
  }

  function syncEditorBeforeSubmit() {
    if (typeof oEditors !== 'undefined' && oEditors.getById && oEditors.getById.wr_content) {
      try {
        oEditors.getById.wr_content.exec('UPDATE_CONTENTS_FIELD', []);
      } catch (e) {
        /* ignore */
      }
    }
  }

  global.sebuEventTemplateSyncEditor = syncEditorBeforeSubmit;

  function scan() {
    var roots = document.querySelectorAll('#sebuEventTemplate');
    var i;
    for (i = 0; i < roots.length; i += 1) {
      initTemplate(roots[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scan);
  } else {
    scan();
  }
}(typeof window !== 'undefined' ? window : this));
