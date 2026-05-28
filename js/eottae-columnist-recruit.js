(function () {
  'use strict';

  var root = document.querySelector('[data-sebu-columnist-recruit]');
  if (!root) {
    return;
  }

  function qs(sel, ctx) {
    return (ctx || document).querySelector(sel);
  }

  function scrollToEl(el) {
    if (!el) {
      return;
    }
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  root.querySelectorAll('[data-scroll-to]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      var targetId = btn.getAttribute('data-scroll-to');
      if (!targetId) {
        return;
      }
      var target = document.getElementById(targetId);
      if (target) {
        e.preventDefault();
        scrollToEl(target);
      }
    });
  });

  var form = qs('[data-columnist-recruit-form]', root);
  if (!form) {
    return;
  }

  var phoneInput = qs('[name="contact_phone"]', form);
  var kakaoInput = qs('[name="contact_kakao"]', form);

  function validateContact() {
    var phone = phoneInput ? phoneInput.value.trim() : '';
    var kakao = kakaoInput ? kakaoInput.value.trim() : '';
    return phone !== '' || kakao !== '';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    if (!validateContact()) {
      window.alert('연락처 또는 카카오톡 ID 중 하나는 필수입니다.');
      if (phoneInput) {
        phoneInput.focus();
      }
      return;
    }

    var submitBtn = qs('[type="submit"]', form);
    if (submitBtn) {
      submitBtn.disabled = true;
    }

    var body = new FormData(form);
    fetch(form.getAttribute('action') || '', {
      method: 'POST',
      body: body,
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (data && data.success) {
          if (data.redirect) {
            window.location.href = data.redirect;
            return;
          }
          window.location.reload();
          return;
        }
        window.alert((data && data.message) || '신청 접수에 실패했습니다.');
      })
      .catch(function () {
        window.alert('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.');
      })
      .finally(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
        }
      });
  });
})();
