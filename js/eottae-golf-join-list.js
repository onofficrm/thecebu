(function () {
  'use strict';

  var form = document.getElementById('golf-join-filter-form');
  var searchOpen = document.getElementById('golf-join-search-open');
  var searchPanel = document.getElementById('golf-join-search-panel');
  var dateField = document.getElementById('golf-join-date-field');
  var dateInput = document.getElementById('golf-join-date-input');

  function syncChipActiveStates() {
    if (!form) {
      return;
    }
    form.querySelectorAll('.golf-join-chip').forEach(function (chip) {
      var radio = chip.querySelector('input[type="radio"]');
      chip.classList.toggle('is-active', !!(radio && radio.checked));
    });
  }

  function syncDateField(openPicker) {
    if (!form || !dateField) {
      return;
    }

    var custom = form.querySelector('input[name="date_preset"][value="custom"]');
    var isCustom = !!(custom && custom.checked);

    if (isCustom) {
      dateField.hidden = false;
      dateField.classList.add('is-open');
      if (openPicker && dateInput) {
        if (typeof dateInput.showPicker === 'function') {
          try {
            dateInput.showPicker();
          } catch (e) {
            dateInput.focus();
          }
        } else {
          dateInput.focus();
        }
      }
    } else {
      dateField.hidden = true;
      dateField.classList.remove('is-open');
      if (dateInput) {
        dateInput.value = '';
      }
    }
  }

  function submitFilters() {
    if (!form) {
      return;
    }

    var datePreset = form.querySelector('input[name="date_preset"]:checked');
    if (datePreset && datePreset.value === 'custom') {
      if (!dateInput || !dateInput.value) {
        syncDateField(true);
        return;
      }
    }

    form.submit();
  }

  if (form) {
    syncChipActiveStates();
    syncDateField(false);

    form.addEventListener('change', function (e) {
      var target = e.target;
      if (!target || !target.name) {
        return;
      }

      if (target.name === 'date_preset') {
        syncChipActiveStates();
        if (target.value === 'custom') {
          syncDateField(true);
          return;
        }
        syncDateField(false);
        submitFilters();
        return;
      }

      if (target.name === 'date' && target.type === 'date') {
        var custom = form.querySelector('input[name="date_preset"][value="custom"]');
        if (custom) {
          custom.checked = true;
        }
        syncChipActiveStates();
        syncDateField(false);
        if (target.value) {
          submitFilters();
        }
        return;
      }

      if (target.name === 'q') {
        return;
      }

      syncChipActiveStates();
      submitFilters();
    });
  }

  if (searchOpen && searchPanel) {
    searchOpen.addEventListener('click', function () {
      var open = searchPanel.hasAttribute('hidden');
      if (open) {
        searchPanel.removeAttribute('hidden');
        searchOpen.setAttribute('aria-expanded', 'true');
        var input = searchPanel.querySelector('input[type="search"]');
        if (input) {
          input.focus();
        }
      } else {
        searchPanel.setAttribute('hidden', '');
        searchOpen.setAttribute('aria-expanded', 'false');
      }
    });
  }
})();
