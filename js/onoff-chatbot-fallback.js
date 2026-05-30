(function (window, document) {
  'use strict';

  function qs(selector, context) {
    return (context || document).querySelector(selector);
  }

  function qsa(selector, context) {
    return Array.prototype.slice.call((context || document).querySelectorAll(selector));
  }

  function lockBodyScroll(lock) {
    document.documentElement.classList.toggle('onoff-chatbot-modal-open', !!lock);
  }

  function openModal(modal) {
    if (!modal) {
      return;
    }
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    lockBodyScroll(true);
    var first = qs('input, textarea, select, button', modal);
    if (first) {
      window.setTimeout(function () {
        first.focus();
      }, 30);
    }
  }

  function closeModal(modal) {
    if (!modal) {
      return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    lockBodyScroll(false);
  }

  function initConsultModal() {
    qsa('.consult-modal').forEach(function (modal) {
      var overlay = qs('.consult-modal-overlay', modal);
      if (overlay) {
        overlay.addEventListener('click', function () {
          closeModal(modal);
        });
      }
      qsa('.consult-modal-close', modal).forEach(function (btn) {
        btn.addEventListener('click', function () {
          closeModal(modal);
        });
      });
    });

    qsa('.consult-modal-open').forEach(function (trigger) {
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        var target = trigger.getAttribute('data-target') || '#cmpConsultModal';
        openModal(qs(target));
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') {
        return;
      }
      qsa('.consult-modal.is-open').forEach(function (modal) {
        closeModal(modal);
      });
    });
  }

  function initConsultForm() {
    qsa('.cmp-consult-form').forEach(function (form) {
      var statusEl = qs('.cmp-consult-form__status', form);
      var submitBtn = qs('.cmp-consult-form__submit', form);

      function showStatus(message, isError) {
        if (!statusEl) {
          return;
        }
        statusEl.textContent = message;
        statusEl.hidden = false;
        statusEl.classList.toggle('is-error', !!isError);
        statusEl.classList.toggle('is-success', !isError);
      }

      form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        if (submitBtn) {
          submitBtn.disabled = true;
        }

        fetch(form.getAttribute('action') || '', {
          method: 'POST',
          body: new FormData(form),
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json'
          }
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            if (data && data.success) {
              if (data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
              }
              showStatus(data.message || '문의가 접수되었습니다.', false);
              form.reset();
              var modal = form.closest('.consult-modal');
              if (modal) {
                window.setTimeout(function () {
                  closeModal(modal);
                }, 2000);
              }
              return;
            }
            showStatus((data && data.message) || '접수에 실패했습니다. 다시 시도해 주세요.', true);
          })
          .catch(function () {
            showStatus('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.', true);
          })
          .finally(function () {
            if (submitBtn) {
              submitBtn.disabled = false;
            }
          });
      });
    });
  }

  function initFallbackLauncher() {
    var launcher = qs('.onoff-chatbot-fallback__launcher');
    if (!launcher) {
      return;
    }
    launcher.addEventListener('click', function () {
      openModal(qs('#cmpConsultModal'));
    });
  }

  function init() {
    initConsultModal();
    initConsultForm();
    initFallbackLauncher();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window, document);
