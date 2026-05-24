(function () {
  'use strict';

  function qs(sel, ctx) {
    return (ctx || document).querySelector(sel);
  }

  function qsa(sel, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
  }

  function ensureInquiryModal() {
    var modal = qs('#eottaeInquiryModal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'eottaeInquiryModal';
    modal.className = 'eottae-inquiry-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.innerHTML =
      '<div class="eottae-inquiry-modal__panel">' +
      '<button type="button" class="eottae-inquiry-modal__close" aria-label="닫기">&times;</button>' +
      '<h2 class="eottae-inquiry-modal__title">빠른 문의하기</h2>' +
      '<p class="eottae-inquiry-modal__desc">문의 내용을 남겨 주시면 빠르게 연락드리겠습니다.</p>' +
      '<form class="eottae-inquiry-modal__form" method="post" action="/proc/inquiry-submit.php">' +
      '<input type="hidden" name="inquiry_code" value="">' +
      '<div class="eottae-field"><label>이름</label><input type="text" name="name" required></div>' +
      '<div class="eottae-field"><label>연락처</label><input type="tel" name="phone" required></div>' +
      '<div class="eottae-field"><label>문의 내용</label><textarea name="message" required></textarea></div>' +
      '<button type="submit" class="inquiry-button__btn inquiry-button__btn--inquiry" style="width:100%">문의 보내기</button>' +
      '</form></div>';
    document.body.appendChild(modal);

    qs('.eottae-inquiry-modal__close', modal).addEventListener('click', function () {
      modal.classList.remove('is-open');
    });
    modal.addEventListener('click', function (e) {
      if (e.target === modal) modal.classList.remove('is-open');
    });

    return modal;
  }

  function openInquiryModal(code) {
    var modal = ensureInquiryModal();
    var input = qs('input[name="inquiry_code"]', modal);
    if (input) input.value = code || '';
    modal.classList.add('is-open');
    var nameInput = qs('input[name="name"]', modal);
    if (nameInput) nameInput.focus();
  }

  function handleShare(btn) {
    var url = btn.getAttribute('data-share-url') || window.location.href;
    if (navigator.share) {
      navigator.share({ title: document.title, url: url }).catch(function () {});
      return;
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(function () {
        alert('링크가 복사되었습니다.');
      });
      return;
    }
    prompt('링크를 복사하세요:', url);
  }

  document.addEventListener('click', function (e) {
    var inquiryBtn = e.target.closest('[data-inquiry-action="open"], .inquiry-button__btn--inquiry');
    if (inquiryBtn && inquiryBtn.tagName === 'BUTTON') {
      e.preventDefault();
      var code = inquiryBtn.getAttribute('data-inquiry-code') || '';
      openInquiryModal(code);
      return;
    }
    var shareBtn = e.target.closest('.inquiry-button__btn--share');
    if (shareBtn) {
      e.preventDefault();
      handleShare(shareBtn);
    }
  });

  /* Shop register 6-step wizard */
  function initShopRegisterWizard() {
    var root = qs('.shop-register-page');
    if (!root) return;

    var panels = qsa('.shop-register-page__panel', root);
    var steps = qsa('.shop-register-page__step', root);
    var btnPrev = qs('[data-wizard="prev"]', root);
    var btnNext = qs('[data-wizard="next"]', root);
    var btnSubmit = qs('[data-wizard="submit"]', root);
    var current = 0;

    function render() {
      panels.forEach(function (p, i) {
        p.classList.toggle('is-active', i === current);
      });
      steps.forEach(function (s, i) {
        s.classList.toggle('is-active', i === current);
        s.classList.toggle('is-done', i < current);
      });
      if (btnPrev) btnPrev.style.display = current === 0 ? 'none' : '';
      if (btnNext) btnNext.style.display = current >= panels.length - 1 ? 'none' : '';
      if (btnSubmit) btnSubmit.style.display = current >= panels.length - 1 ? '' : 'none';
    }

    if (btnPrev) {
      btnPrev.addEventListener('click', function () {
        if (current > 0) {
          current--;
          render();
        }
      });
    }
    if (btnNext) {
      btnNext.addEventListener('click', function () {
        if (current < panels.length - 1) {
          current++;
          render();
        }
      });
    }
    render();
  }

  /* Auth member type → mb_1 */
  function initMemberType() {
    var form = qs('#fregisterform');
    if (!form) return;
    var mb1 = qs('input[name="mb_1"]', form);
    if (!mb1) return;
    qsa('input[name="eottae_member_type"]', form).forEach(function (radio) {
      radio.addEventListener('change', function () {
        mb1.value = this.value === 'business' ? 'business' : 'member';
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('eottae-page');
    initShopRegisterWizard();
    initMemberType();
  });
})();
