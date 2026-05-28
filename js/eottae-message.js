(function () {
  'use strict';

  var root = document.querySelector('[data-eottae-message-page]');
  if (!root) {
    return;
  }

  root.querySelectorAll('[data-message-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var confirmMessage = form.getAttribute('data-message-confirm');
      if (confirmMessage && !window.confirm(confirmMessage)) {
        return;
      }

      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      var submit = form.querySelector('[type="submit"]');
      if (submit) {
        submit.disabled = true;
      }

      fetch(form.getAttribute('action'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
        body: new FormData(form),
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (data && data.success) {
            window.location.href = data.redirect || window.location.href;
            return;
          }
          window.alert((data && data.message) || '처리 중 오류가 발생했습니다.');
          if (data && data.redirect) {
            window.location.href = data.redirect;
          }
        })
        .catch(function () {
          window.alert('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.');
        })
        .finally(function () {
          if (submit) {
            submit.disabled = false;
          }
        });
    });
  });

  var bubbles = root.querySelector('.eottae-message-bubbles');
  if (bubbles) {
    bubbles.scrollTop = bubbles.scrollHeight;
  }
})();
