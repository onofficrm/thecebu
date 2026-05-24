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
        if (current === 0) {
          var subjectInput = qs('#wr_subject', root);
          if (subjectInput && !subjectInput.value.trim()) {
            alert('업체명을 입력해 주세요.');
            subjectInput.focus();
            return;
          }
        }
        if (current < panels.length - 1) {
          current++;
          render();
          if (current === panels.length - 1) {
            updateShopRegisterSummary(root);
          }
        }
      });
    }

    var caSelect = qs('#ca_name', root);
    var wr1Input = qs('#wr_1', root);
    if (caSelect && wr1Input) {
      caSelect.addEventListener('change', function () {
        wr1Input.value = caSelect.value;
      });
    }

    render();
  }

  function updateShopRegisterSummary(root) {
    var box = qs('#shopRegisterSummary', root);
    if (!box) return;

    var fields = [
      ['업체명', qs('#wr_subject', root)],
      ['카테고리', qs('#ca_name', root)],
      ['지역', qs('#wr_2', root)],
      ['주소', qs('#wr_3', root)],
      ['전화', qs('#wr_4', root)],
      ['영업시간', qs('#wr_6', root)],
      ['영업상태', qs('#wr_8', root)]
    ];

    var html = '<dl>';
    var hasValue = false;
    fields.forEach(function (item) {
      var el = item[1];
      if (!el) return;
      var val = (el.value || '').trim();
      if (val === '') return;
      hasValue = true;
      html += '<dt>' + item[0] + '</dt><dd>' + val.replace(/</g, '&lt;') + '</dd>';
    });
    html += '</dl>';

    box.innerHTML = hasValue ? html : '<p class="shop-register-page__summary-empty">입력 내용을 확인해 주세요.</p>';
  }

  function initShopDetailGallery() {
    var hero = qs('#shopDetailHeroImg');
    if (!hero) return;

    qsa('.shop-detail-page__thumb').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var src = btn.getAttribute('data-gallery-src');
        if (src) hero.src = src;
        qsa('.shop-detail-page__thumb').forEach(function (b) {
          b.classList.toggle('is-active', b === btn);
        });
      });
    });
  }

  function initPhotoPreview() {
    document.addEventListener('change', function (e) {
      var input = e.target;
      if (!input.matches('[data-photo-preview], [data-photo-input]')) return;

      var slot = input.closest('.community-write-page__photo-slot, .shop-register-page__photo');
      if (!slot) return;

      var preview = qs('.community-write-page__photo-preview', slot);
      if (!preview || !input.files || !input.files[0]) return;

      var reader = new FileReader();
      reader.onload = function (ev) {
        preview.src = ev.target.result;
        preview.hidden = false;
        var placeholder = qs('.community-write-page__photo-placeholder', slot);
        if (placeholder) placeholder.style.display = 'none';
      };
      reader.readAsDataURL(input.files[0]);
    });
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

  /* Shop register geocode */
  function initShopGeocode() {
    var btn = qs('#shopGeocodeBtn');
    if (!btn) return;

    var addressInput = qs('#wr_3');
    var latInput = qs('#wr_9');
    var lngInput = qs('#wr_10');
    var status = qs('#shopGeocodeStatus');

    btn.addEventListener('click', function () {
      var address = addressInput ? addressInput.value.trim() : '';
      if (!address) {
        alert('주소를 입력해 주세요.');
        return;
      }

      btn.disabled = true;
      if (status) status.textContent = '좌표를 찾는 중…';

      var body = new FormData();
      body.append('address', address);

      fetch('/proc/eottae-geocode.php', { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          btn.disabled = false;
          if (!data.ok) {
            if (data.status === 'REQUEST_DENIED') {
              if (status) {
                status.textContent = 'Geocoding API가 비활성화되어 있습니다. Google Cloud Console에서 Geocoding API를 켜 주세요.';
              }
              return;
            }
            if (status) status.textContent = '좌표를 찾지 못했습니다. 주소를 확인해 주세요.';
            return;
          }
          if (latInput) latInput.value = data.lat;
          if (lngInput) lngInput.value = data.lng;
          if (status) status.textContent = '좌표가 설정되었습니다.';
        })
        .catch(function () {
          btn.disabled = false;
          if (status) status.textContent = '요청에 실패했습니다.';
        });
    });
  }

  function initAdCarousel() {
    var root = qs('[data-ad-carousel]');
    if (!root) return;

    var slides = qsa('[data-ad-slide]', root);
    if (slides.length < 2) return;

    var dots = qsa('[data-ad-dot]', root);
    var current = 0;
    var timer = null;
    var delay = 5500;

    function show(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach(function (slide, i) {
        slide.classList.toggle('is-active', i === current);
      });
      dots.forEach(function (dot, i) {
        dot.classList.toggle('is-active', i === current);
        dot.setAttribute('aria-selected', i === current ? 'true' : 'false');
      });
    }

    function next() {
      show(current + 1);
    }

    function start() {
      stop();
      timer = window.setInterval(next, delay);
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var idx = parseInt(dot.getAttribute('data-ad-dot'), 10);
        if (!isNaN(idx)) {
          show(idx);
          start();
        }
      });
    });

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    root.addEventListener('focusin', stop);
    root.addEventListener('focusout', start);

    show(0);
    start();
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('eottae-page');
    initShopRegisterWizard();
    initShopGeocode();
    initMemberType();
    initReviewModal();
    initReviewReply();
    initShopSave();
    initShopDetailGallery();
    initPhotoPreview();
    initAdCarousel();
  });

  function initReviewModal() {
    var modal = qs('#eottaeReviewModal');
    if (!modal) return;

    var form = qs('#eottaeReviewForm', modal);
    var ratingInput = qs('#eottaeReviewRating', modal);
    var starBtns = qsa('.review-write-form__star', modal);

    function setRating(value) {
      if (ratingInput) ratingInput.value = String(value);
      starBtns.forEach(function (btn) {
        var star = parseInt(btn.getAttribute('data-star'), 10);
        btn.classList.toggle('is-active', star <= value);
      });
    }

    setRating(ratingInput ? parseInt(ratingInput.value, 10) || 5 : 5);

    starBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setRating(parseInt(btn.getAttribute('data-star'), 10));
      });
    });

    document.addEventListener('click', function (e) {
      if (e.target.closest('[data-review-open]')) {
        e.preventDefault();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
      }
      if (e.target.closest('[data-review-close]') || e.target === modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
      }
    });

    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(form);
        var submitBtn = qs('.review-write-form__submit', form);
        if (submitBtn) submitBtn.disabled = true;

        fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (res) { return res.json(); })
          .then(function (data) {
            if (data.success) {
              if (data.redirect_url) {
                window.location.href = data.redirect_url;
              } else {
                window.location.reload();
              }
              return;
            }
            alert(data.message || '리뷰 등록에 실패했습니다.');
            if (submitBtn) submitBtn.disabled = false;
          })
          .catch(function () {
            alert('네트워크 오류가 발생했습니다.');
            if (submitBtn) submitBtn.disabled = false;
          });
      });
    }
  }

  function initReviewReply() {
    document.addEventListener('submit', function (e) {
      var form = e.target.closest('[data-review-reply-form]');
      if (!form) return;
      e.preventDefault();

      var submitBtn = qs('.review-card__reply-submit', form);
      if (submitBtn) submitBtn.disabled = true;

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success) {
            if (data.redirect_url) {
              window.location.href = data.redirect_url;
            } else {
              window.location.reload();
            }
            return;
          }
          alert(data.message || '답변 등록에 실패했습니다.');
          if (submitBtn) submitBtn.disabled = false;
        })
        .catch(function () {
          alert('네트워크 오류가 발생했습니다.');
          if (submitBtn) submitBtn.disabled = false;
        });
    });
  }

  function initShopSave() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-shop-save]');
      if (!btn) return;
      e.preventDefault();

      var shopId = btn.getAttribute('data-shop-id');
      var token = btn.getAttribute('data-save-token') || '';
      var fd = new FormData();
      fd.append('shop_wr_id', shopId);
      fd.append('eottae_shop_save_token', token);

      btn.disabled = true;
      fetch('/proc/eottae-shop-save.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          btn.disabled = false;
          if (!data.success) {
            var msg = data.message || '처리에 실패했습니다.';
            if (msg.indexOf('로그인') !== -1) {
              window.location.href = btn.getAttribute('data-login-url') || '/bbs/login.php';
              return;
            }
            alert(msg);
            return;
          }
          var saved = !!data.saved;
          btn.setAttribute('data-saved', saved ? '1' : '0');
          btn.textContent = saved ? '찜 해제' : '찜하기';
          btn.classList.toggle('is-saved', saved);
        })
        .catch(function () {
          btn.disabled = false;
          alert('네트워크 오류가 발생했습니다.');
        });
    });
  }
})();
