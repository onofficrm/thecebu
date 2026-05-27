(function () {
  'use strict';

  var form = document.getElementById('golf-join-filter-form');
  var searchOpen = document.getElementById('golf-join-search-open');
  var searchPanel = document.getElementById('golf-join-search-panel');

  function submitFilters() {
    if (!form) {
      return;
    }
    var datePreset = form.querySelector('input[name="date_preset"]:checked');
    var dateInput = form.querySelector('input[name="date"]');
    if (datePreset && datePreset.value === 'custom' && dateInput && !dateInput.value) {
      datePreset = form.querySelector('input[name="date_preset"][value=""]');
      if (datePreset) {
        datePreset.checked = true;
      }
    }
    form.submit();
  }

  if (form) {
    form.addEventListener('change', function (e) {
      var target = e.target;
      if (!target || !target.name) {
        return;
      }
      if (target.name === 'date' && target.type === 'date') {
        var custom = form.querySelector('input[name="date_preset"][value="custom"]');
        if (custom) {
          custom.checked = true;
        }
      }
      if (target.name === 'q') {
        return;
      }
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

  document.querySelectorAll('.golf-join-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
      var row = chip.closest('.golf-join-chips');
      if (!row) {
        return;
      }
      row.querySelectorAll('.golf-join-chip').forEach(function (c) {
        c.classList.remove('is-active');
      });
      chip.classList.add('is-active');
    });
  });
})();
