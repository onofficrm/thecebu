(function () {
  'use strict';

  function initReportWriteForm() {
    var root = document.getElementById('sebuReportTemplate');
    if (!root) {
      return;
    }

    var contactToggle = root.querySelector('[data-report-contact-toggle]');
    var contactWrap = root.querySelector('[data-report-contact-wrap]');
    if (contactToggle && contactWrap) {
      function syncContact() {
        var show = contactToggle.checked;
        contactWrap.hidden = !show;
        var input = contactWrap.querySelector('input, textarea');
        if (input) {
          input.required = show;
          if (!show) {
            input.value = '';
          }
        }
      }
      contactToggle.addEventListener('change', syncContact);
      syncContact();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReportWriteForm);
  } else {
    initReportWriteForm();
  }
})();
