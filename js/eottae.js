(function (global) {
  'use strict';

  function qs(sel, ctx) {
    return (ctx || document).querySelector(sel);
  }

  function qsa(sel, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
  }

  function parseJsonResponse(res) {
    return res.text().then(function (text) {
      var trimmed = (text || '').trim();
      if (!trimmed) {
        throw new Error('서버 응답이 비어 있습니다.');
      }
      try {
        return JSON.parse(trimmed);
      } catch (e) {
        throw new Error('서버 응답 오류입니다. 잠시 후 다시 시도해 주세요.');
      }
    });
  }

  function fetchWithTimeout(url, options, timeoutMs) {
    var ms = timeoutMs || 50000;
    var opts = options || {};

    return new Promise(function (resolve, reject) {
      var done = false;
      var controller = global.AbortController ? new AbortController() : null;
      var timer = setTimeout(function () {
        if (done) return;
        done = true;
        if (controller) {
          controller.abort();
        }
        var timeoutError = new Error('AI 요청 시간이 초과되었습니다. 잠시 후 다시 시도해 주세요.');
        timeoutError.name = 'AbortError';
        reject(timeoutError);
      }, ms);

      if (controller) {
        opts.signal = controller.signal;
      }

      fetch(url, opts).then(function (res) {
        if (done) return;
        done = true;
        clearTimeout(timer);
        resolve(res);
      }, function (err) {
        if (done) return;
        done = true;
        clearTimeout(timer);
        reject(err);
      });
    });
  }

  function aiFetchJson(url, options, timeoutMs) {
    return fetchWithTimeout(url, options, timeoutMs || 50000)
      .then(function (res) {
        if (!res.ok) {
          throw new Error('서버 오류입니다. (HTTP ' + res.status + ')');
        }
        return parseJsonResponse(res);
      })
      .then(function (json) {
        if (!json || !json.success) {
          throw new Error((json && json.message) || 'AI 요청에 실패했습니다.');
        }
        return json;
      });
  }

  function aiAbortErrorMessage(err) {
    if (err && err.name === 'AbortError') {
      return 'AI 요청 시간이 초과되었습니다. 잠시 후 다시 시도해 주세요.';
    }
    return (err && err.message) || 'AI 요청에 실패했습니다.';
  }

  function rememberAiBtnLabels(buttons) {
    buttons.forEach(function (b) {
      if (!b.getAttribute('data-ai-original-label')) {
        b.setAttribute('data-ai-original-label', getAiBtnDefaultLabel(b));
      }
    });
  }

  function resetAiButtons(buttons) {
    buttons.forEach(function (b) {
      setAiBtnLoading(b, false);
      var original = b.getAttribute('data-ai-original-label');
      if (b.classList.contains('eottae-ai-btn')) {
        setAiBtnLabel(b, original || getAiBtnDefaultLabel(b));
      } else if (original) {
        b.textContent = original;
      }
    });
  }

  function eottaeProcPath(file) {
    var base = global.__EOTTae__ && global.__EOTTae__.procBase ? String(global.__EOTTae__.procBase) : '';
    if (base) {
      return base.replace(/\/$/, '') + '/' + String(file || '').replace(/^\//, '');
    }
    return '/proc/' + String(file || '').replace(/^\//, '');
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

  function isDesktopViewport() {
    return window.matchMedia('(min-width: 1025px)').matches;
  }

  function lifeMapMessageUrl(context, wrId, boTable) {
    var cfg = global.__EOTTae__ || {};
    var base = cfg.messageUrl || '/page/eottae-messages.php';
    if (context === 'market' && wrId) {
      return base + (base.indexOf('?') >= 0 ? '&' : '?') + 'market=' + encodeURIComponent(String(wrId))
        + (boTable ? '&bo_table=' + encodeURIComponent(boTable) : '') + '#message-compose';
    }
    return base + '#message-compose';
  }

  function lifeMapMessageDefaultBody(context, title) {
    title = (title || '').trim();
    if (context === 'market') {
      return title !== ''
        ? '안녕하세요. ' + title + ' 구매 문의드립니다.\n\n'
        : '안녕하세요. 중고물품 구매 문의드립니다.\n\n';
    }
    if (context === 'job') {
      return title !== ''
        ? '안녕하세요. ' + title + ' 구인 문의드립니다.\n\n'
        : '안녕하세요. 구인공고 문의드립니다.\n\n';
    }
    if (context === 'estate') {
      return title !== ''
        ? '안녕하세요. ' + title + ' 부동산 문의드립니다.\n\n'
        : '안녕하세요. 부동산 매물 문의드립니다.\n\n';
    }
    return '';
  }

  function lifeMapMessageModalTitle(context) {
    if (context === 'market') return '중고물품 문의하기';
    if (context === 'job') return '구인공고 문의하기';
    if (context === 'estate') return '부동산 문의하기';
    return '문의하기';
  }

  function ensureLifeMapMessageModal() {
    var modal = qs('#eottaeLifeMapMessageModal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'eottaeLifeMapMessageModal';
    modal.className = 'eottae-inquiry-modal eottae-life-map-message-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.innerHTML =
      '<div class="eottae-inquiry-modal__panel">' +
      '<button type="button" class="eottae-inquiry-modal__close" aria-label="닫기">&times;</button>' +
      '<h2 class="eottae-inquiry-modal__title" data-life-map-message-title>문의하기</h2>' +
      '<p class="eottae-life-map-message-modal__desc">작성자에게 쪽지로 문의를 보냅니다.</p>' +
      '<form class="eottae-life-map-message-modal__form" method="post">' +
      '<input type="hidden" name="eottae_message_token" value="">' +
      '<input type="hidden" name="action" value="">' +
      '<input type="hidden" name="wr_id" value="">' +
      '<input type="hidden" name="bo_table" value="">' +
      '<input type="hidden" name="receiver" value="">' +
      '<div class="eottae-field"><label for="eottaeLifeMapMessageBody">문의 내용</label>' +
      '<textarea id="eottaeLifeMapMessageBody" name="body" rows="5" maxlength="3000" required></textarea></div>' +
      '<button type="submit" class="inquiry-button__btn inquiry-button__btn--inquiry" style="width:100%">보내기</button>' +
      '</form></div>';
    document.body.appendChild(modal);

    qs('.eottae-inquiry-modal__close', modal).addEventListener('click', function () {
      modal.classList.remove('is-open');
    });
    modal.addEventListener('click', function (e) {
      if (e.target === modal) modal.classList.remove('is-open');
    });

    qs('.eottae-life-map-message-modal__form', modal).addEventListener('submit', function (e) {
      e.preventDefault();
      var form = e.currentTarget;
      var cfg = global.__EOTTae__ || {};
      var procUrl = cfg.messageProcUrl || eottaeProcPath('eottae-message.php');
      var submit = qs('[type="submit"]', form);
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      if (submit) submit.disabled = true;
      fetch(procUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
        body: new FormData(form)
      })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (data && data.success) {
            modal.classList.remove('is-open');
            showEottaeToast(data.message || '문의를 보냈습니다.');
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
          if (submit) submit.disabled = false;
        });
    });

    return modal;
  }

  function openLifeMapMessageModal(options) {
    options = options || {};
    var cfg = global.__EOTTae__ || {};
    if (!cfg.isMember) {
      window.location.href = loginUrlFor(window.location.href);
      return;
    }
    if (!cfg.messageToken) {
      window.location.href = lifeMapMessageUrl(options.context, options.wrId, options.boTable);
      return;
    }

    var modal = ensureLifeMapMessageModal();
    var context = options.context || '';
    var titleEl = qs('[data-life-map-message-title]', modal);
    if (titleEl) titleEl.textContent = lifeMapMessageModalTitle(context);

    var tokenInput = qs('input[name="eottae_message_token"]', modal);
    var actionInput = qs('input[name="action"]', modal);
    var wrInput = qs('input[name="wr_id"]', modal);
    var boInput = qs('input[name="bo_table"]', modal);
    var receiverInput = qs('input[name="receiver"]', modal);
    var bodyInput = qs('textarea[name="body"]', modal);

    if (tokenInput) tokenInput.value = cfg.messageToken;
    if (actionInput) actionInput.value = context === 'market' ? 'market_inquiry' : 'send';
    if (wrInput) wrInput.value = context === 'market' ? String(options.wrId || '') : '';
    if (boInput) boInput.value = context === 'market' ? String(options.boTable || '') : '';
    if (receiverInput) receiverInput.value = context === 'market' ? '' : String(options.ownerId || '');
    if (bodyInput) {
      bodyInput.value = lifeMapMessageDefaultBody(context, options.title || '');
      bodyInput.focus();
      bodyInput.setSelectionRange(bodyInput.value.length, bodyInput.value.length);
    }

    modal.classList.add('is-open');
  }

  function shouldUseLifeMapMessagePopup(btn) {
    if (!isDesktopViewport()) return false;
    if (!btn.closest('[data-cebu-map-page]')) return false;
    var context = btn.getAttribute('data-inquiry-context') || '';
    return context === 'market' || context === 'job' || context === 'estate';
  }

  function showEottaeToast(message, durationMs) {
    var text = (message || '').trim();
    if (!text) {
      return;
    }

    durationMs = durationMs || 2200;
    var toast = document.getElementById('eottaeToast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'eottaeToast';
      toast.className = 'eottae-toast';
      toast.setAttribute('role', 'status');
      toast.setAttribute('aria-live', 'polite');
      document.body.appendChild(toast);
    }

    toast.textContent = text;
    toast.classList.add('is-visible');

    if (showEottaeToast._timer) {
      clearTimeout(showEottaeToast._timer);
    }
    showEottaeToast._timer = setTimeout(function () {
      toast.classList.remove('is-visible');
    }, durationMs);
  }

  function handleShare(btn) {
    var url = btn.getAttribute('data-share-url') || window.location.href;
    if (navigator.share) {
      navigator.share({ title: document.title, url: url })
        .then(function () {
          showEottaeToast('공유했어요');
        })
        .catch(function () {});
      return;
    }
    copyTextToClipboard(url, '링크가 복사됐어요');
  }

  function copyTextToClipboard(text, successMessage) {
    var value = (text || '').trim();
    if (!value) return Promise.reject();

    successMessage = successMessage || '링크가 복사됐어요';

    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(value).then(function () {
        showEottaeToast(successMessage);
      });
    }

    return new Promise(function (resolve, reject) {
      var textarea = document.createElement('textarea');
      textarea.value = value;
      textarea.setAttribute('readonly', 'readonly');
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();
      try {
        document.execCommand('copy');
        showEottaeToast(successMessage);
        resolve();
      } catch (err) {
        prompt('링크를 복사하세요:', value);
        reject(err);
      } finally {
        document.body.removeChild(textarea);
      }
    });
  }

  document.addEventListener('click', function (e) {
    var copyBtn = e.target.closest('[data-copy-text], [data-talk-invite-copy]');
    if (copyBtn) {
      e.preventDefault();
      var text = copyBtn.getAttribute('data-copy-text') || '';
      var targetSelector = copyBtn.getAttribute('data-copy-target');
      if (targetSelector) {
        var target = document.querySelector(targetSelector);
        if (target && target.value) {
          text = target.value;
        }
      }
      copyTextToClipboard(text, copyBtn.getAttribute('data-copy-message') || '초대 링크가 복사되었습니다.');
      return;
    }
  });

  function messageComposeUrl(shopCode) {
    var cfg = global.__EOTTae__ || {};
    var base = cfg.messageUrl || '/page/eottae-messages.php';
    if (!shopCode) {
      return base + '#message-compose';
    }
    return base + '?shop=' + encodeURIComponent(shopCode) + '#message-compose';
  }

  function loginUrlFor(returnUrl) {
    var cfg = global.__EOTTae__ || {};
    var login = cfg.loginUrl || '/bbs/login.php';
    if (!returnUrl) {
      return login;
    }
    return login + (login.indexOf('?') >= 0 ? '&' : '?') + 'url=' + encodeURIComponent(returnUrl);
  }

  document.addEventListener('click', function (e) {
    var inquiryBtn = e.target.closest('[data-inquiry-action="open"], .inquiry-button__btn--inquiry');
    if (inquiryBtn && inquiryBtn.tagName === 'BUTTON') {
      e.preventDefault();
      var code = inquiryBtn.getAttribute('data-inquiry-code') || '';
      var ownerId = inquiryBtn.getAttribute('data-message-owner') || '';
      var inquiryContext = inquiryBtn.getAttribute('data-inquiry-context') || '';
      var cfg = global.__EOTTae__ || {};

      if (shouldUseLifeMapMessagePopup(inquiryBtn)) {
        openLifeMapMessageModal({
          context: inquiryContext,
          ownerId: ownerId,
          wrId: inquiryBtn.getAttribute('data-inquiry-wr-id') || '',
          boTable: inquiryBtn.getAttribute('data-inquiry-bo-table') || '',
          title: inquiryBtn.getAttribute('data-shop-name') || ''
        });
        return;
      }

      if (inquiryBtn.closest('[data-cebu-map-page]') && inquiryContext) {
        var targetUrl = lifeMapMessageUrl(
          inquiryContext,
          inquiryBtn.getAttribute('data-inquiry-wr-id') || '',
          inquiryBtn.getAttribute('data-inquiry-bo-table') || ''
        );
        if (cfg.isMember) {
          window.location.href = targetUrl;
          return;
        }
        window.location.href = loginUrlFor(targetUrl);
        return;
      }

      if (ownerId) {
        if (cfg.isMember) {
          window.location.href = messageComposeUrl(code);
          return;
        }
        window.location.href = loginUrlFor(messageComposeUrl(code));
        return;
      }
      openInquiryModal(code);
      return;
    }
    var shareBtn = e.target.closest('.inquiry-button__btn--share');
    if (shareBtn) {
      e.preventDefault();
      handleShare(shareBtn);
    }
  });

  function syncShopBusinessFields(root) {
    var preset = qs('#wr_6_preset', root);
    var hoursHidden = qs('#wr_6', root);
    var hoursCustom = qs('#wr_6_custom', root);
    if (preset && hoursHidden) {
      if (preset.value === '__custom__') {
        hoursHidden.value = hoursCustom ? hoursCustom.value.trim() : '';
      } else {
        hoursHidden.value = preset.value.trim();
      }
    }

    var specialHidden = qs('#eottae_closed_special', root);
    var closedHidden = qs('#wr_7', root);
    var customInput = qs('#eottae_closed_custom', root);
    var special = '';
    qsa('[data-closed-special]', root).forEach(function (el) {
      if (el.checked) special = el.getAttribute('data-closed-special') || '';
    });
    if (specialHidden) specialHidden.value = special;

    var weekdays = [];
    qsa('[data-closed-weekday]:checked', root).forEach(function (el) {
      weekdays.push(el.value);
    });

    var parts = [];
    if (special === '연중무휴') {
      parts.push('연중무휴');
    } else if (special === '비정기') {
      parts.push('비정기 휴무');
    } else {
      weekdays.forEach(function (day) {
        parts.push('매주 ' + day + '요일');
      });
    }
    var custom = customInput ? customInput.value.trim() : '';
    if (custom) {
      parts.push(custom);
    }
    if (closedHidden) {
      closedHidden.value = parts.join(' · ');
    }
  }

  function syncShopSnsFields(root) {
    var scope = root && root.querySelector ? root : document;
    var form = root && root.tagName === 'FORM' ? root : qs('.shop-register-page', scope) || qs('#fwrite', scope);
    if (!form) return;

    var hidden = qs('#wr_link2', form);
    if (!hidden) return;

    var keys = ['youtube', 'instagram', 'tiktok', 'facebook', 'naver_blog'];
    var payload = {};
    keys.forEach(function (key) {
      var el = qs('#eottae_sns_' + key, form);
      if (!el) return;
      var val = (el.value || '').trim();
      if (val !== '') payload[key] = val;
    });
    hidden.value = Object.keys(payload).length ? JSON.stringify(payload) : '';
  }

  function initShopRegisterBusinessInfo(root) {
    var preset = qs('#wr_6_preset', root);
    var hoursCustom = qs('#wr_6_custom', root);
    if (preset && hoursCustom) {
      var toggleHoursCustom = function () {
        var isCustom = preset.value === '__custom__';
        hoursCustom.hidden = !isCustom;
        if (isCustom) hoursCustom.focus();
        syncShopBusinessFields(root);
      };
      preset.addEventListener('change', toggleHoursCustom);
      hoursCustom.addEventListener('input', function () { syncShopBusinessFields(root); });
      toggleHoursCustom();
    }

    var weekdayInputs = qsa('[data-closed-weekday]', root);
    var specialInputs = qsa('[data-closed-special]', root);

    function refreshClosedState(changed) {
      var activeSpecial = '';
      specialInputs.forEach(function (el) {
        if (el === changed && el.checked) {
          activeSpecial = el.getAttribute('data-closed-special') || '';
          specialInputs.forEach(function (other) {
            if (other !== el) other.checked = false;
          });
        } else if (el !== changed && el.checked) {
          activeSpecial = el.getAttribute('data-closed-special') || '';
        }
      });

      if (changed && changed.hasAttribute('data-closed-weekday') && changed.checked) {
        specialInputs.forEach(function (el) { el.checked = false; });
        activeSpecial = '';
      }

      var disableWeekdays = activeSpecial === '연중무휴' || activeSpecial === '비정기';
      weekdayInputs.forEach(function (el) {
        el.disabled = disableWeekdays;
        if (disableWeekdays) el.checked = false;
      });

      syncShopBusinessFields(root);
    }

    weekdayInputs.forEach(function (el) {
      el.addEventListener('change', function () { refreshClosedState(el); });
    });
    specialInputs.forEach(function (el) {
      el.addEventListener('change', function () { refreshClosedState(el); });
    });
    var customInput = qs('#eottae_closed_custom', root);
    if (customInput) {
      customInput.addEventListener('input', function () { syncShopBusinessFields(root); });
    }
    syncShopBusinessFields(root);
  }

  /* Shop register 7-step wizard */
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
            syncShopBusinessFields(root);
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

    initShopAiGenerator(root);
    initShopMapThumbAi(root);
    initShopRegisterBusinessInfo(root);
    render();
  }

  function shopSetAiStatus(root, message, isError) {
    qsa('[data-shop-ai-status]', root).forEach(function (el) {
      el.textContent = message || '';
      el.classList.toggle('is-error', !!isError);
    });
  }

  function setAiBtnLabel(btn, text) {
    if (!btn) return;
    var label = btn.querySelector('.eottae-ai-btn__label');
    if (label) {
      label.textContent = text;
      return;
    }
    btn.textContent = text;
  }

  function setAiBtnLoading(btn, loading) {
    if (!btn) return;
    btn.classList.toggle('is-loading', !!loading);
    btn.disabled = !!loading;
  }

  function getAiBtnDefaultLabel(btn) {
    if (!btn) return '';
    return btn.getAttribute('data-default-label') || (btn.querySelector('.eottae-ai-btn__label') ? btn.querySelector('.eottae-ai-btn__label').textContent : btn.textContent) || '';
  }

  function shopAiValue(root, selector) {
    var el = qs(selector, root);
    return el ? (el.value || '').trim() : '';
  }

  function shopAiEnabled(root) {
    if (root && root.getAttribute('data-ai-enabled') === '1') {
      return true;
    }
    if (global.__EOTTae__ && global.__EOTTae__.aiEnabled) {
      return true;
    }
    return false;
  }

  function shopSyncEditorField(root, fieldId) {
    if (!fieldId || typeof oEditors === 'undefined' || !oEditors.getById || !oEditors.getById[fieldId]) {
      return;
    }
    oEditors.getById[fieldId].exec('UPDATE_CONTENTS_FIELD', []);
  }

  function eottaeSetEditorContent(fieldId, value, root) {
    if (!fieldId || value == null || value === '') return;
    var el = root ? qs('#' + fieldId, root) : document.getElementById(fieldId);
    if (!el) return;
    var html = String(value);
    if (typeof oEditors !== 'undefined' && oEditors.getById && oEditors.getById[fieldId]) {
      try {
        oEditors.getById[fieldId].exec('SET_CONTENTS', [html]);
        oEditors.getById[fieldId].exec('UPDATE_CONTENTS_FIELD', []);
      } catch (e) {
        el.value = html;
        el.dispatchEvent(new Event('input', { bubbles: true }));
      }
      return;
    }
    el.value = html;
    el.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function shopFillAiValue(root, selector, value, overwrite) {
    var el = qs(selector, root);
    if (!el || !value) return;
    if (!overwrite && (el.value || '').trim() !== '') return;
    if (selector === '#wr_content' || el.id === 'wr_content') {
      eottaeSetEditorContent('wr_content', value, root);
      return;
    }
    el.value = value;
    el.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function initShopAiGenerator(root) {
    var form = qs('#fwrite', root) || root;
    if (!form || !window.fetch) return;

  function runShopAiGenerate(btn) {
      if (!shopAiEnabled(root)) {
        var disabledMsg = 'AI 자동생성은 서버에 OpenAI API 키가 설정된 후 이용할 수 있습니다.';
        shopSetAiStatus(root, disabledMsg, true);
        alert(disabledMsg);
        return;
      }

      var subject = shopAiValue(root, '#wr_subject');
      if (!subject) {
        alert('1단계에서 업체명을 먼저 입력해 주세요.');
        var subjectInput = qs('#wr_subject', root);
        var firstPanel = qs('.shop-register-page__panel[data-step="0"]', root);
        if (firstPanel) {
          qsa('.shop-register-page__panel', root).forEach(function (panel, index) {
            panel.classList.toggle('is-active', panel === firstPanel);
          });
          qsa('.shop-register-page__step', root).forEach(function (step, index) {
            step.classList.toggle('is-active', index === 0);
            step.classList.toggle('is-done', false);
          });
          var btnPrev = qs('[data-wizard="prev"]', root);
          var btnNext = qs('[data-wizard="next"]', root);
          var btnSubmit = qs('[data-wizard="submit"]', root);
          if (btnPrev) btnPrev.style.display = 'none';
          if (btnNext) btnNext.style.display = '';
          if (btnSubmit) btnSubmit.style.display = 'none';
        }
        if (subjectInput) subjectInput.focus();
        return;
      }

      var mode = btn.getAttribute('data-shop-ai-generate') || 'all';
      var buttons = qsa('[data-shop-ai-generate]', form);
      shopSyncEditorField(root, 'wr_content');

      var payload = new FormData();
      payload.append('bo_table', shopAiValue(root, 'input[name="bo_table"]') || 'shop');
      payload.append('mode', mode);
      payload.append('name', subject);
      payload.append('category', shopAiValue(root, '#ca_name'));
      payload.append('region', shopAiValue(root, '#wr_2'));
      payload.append('address', shopAiValue(root, '#wr_3'));
      payload.append('phone', shopAiValue(root, '#wr_4'));
      payload.append('hours', shopAiValue(root, '#wr_6'));
      payload.append('closed', shopAiValue(root, '#wr_7'));
      payload.append('website', shopAiValue(root, '#wr_link1'));
      payload.append('instagram', shopAiValue(root, '#eottae_sns_instagram'));
      payload.append('tiktok', shopAiValue(root, '#eottae_sns_tiktok'));
      payload.append('facebook', shopAiValue(root, '#eottae_sns_facebook'));
      payload.append('naver_blog', shopAiValue(root, '#eottae_sns_naver_blog'));
      payload.append('youtube', shopAiValue(root, '#eottae_sns_youtube'));
      payload.append('intro', shopAiValue(root, '#wr_content'));

      buttons.forEach(function (b) {
        if (!b.getAttribute('data-ai-original-label')) {
          b.setAttribute('data-ai-original-label', getAiBtnDefaultLabel(b));
        }
        setAiBtnLoading(b, true);
        setAiBtnLabel(b, 'AI 생성 중…');
      });
      shopSetAiStatus(
        root,
        mode === 'seo'
          ? 'AI가 SEO 문구를 작성 중입니다...'
          : 'AI가 업체 소개와 SEO 문구를 작성 중입니다...',
        false
      );

      function resetShopAiButtons() {
        buttons.forEach(function (b) {
          setAiBtnLoading(b, false);
          setAiBtnLabel(b, b.getAttribute('data-ai-original-label') || getAiBtnDefaultLabel(b));
        });
      }

      function applyShopAiData(data) {
        shopFillAiValue(root, '#eottae_seo_title', data.seo_title, true);
        shopFillAiValue(root, '#eottae_seo_intro', data.seo_intro, true);
        shopFillAiValue(root, '#eottae_seo_description', data.meta_description, true);
        shopFillAiValue(root, '#eottae_seo_keyword', data.focus_keyword, true);
        shopSetAiStatus(root, 'AI 문구를 입력했습니다. 등록 전 내용이 맞는지 한 번 확인해 주세요.', false);
        if (mode !== 'seo' && data.intro) {
          setTimeout(function () {
            shopFillAiValue(root, '#wr_content', data.intro, true);
          }, 0);
        }
      }

      aiFetchJson(eottaeProcPath('eottae-shop-ai-generate.php'), {
        method: 'POST',
        credentials: 'same-origin',
        body: payload
      }, 50000)
        .then(function (json) {
          resetShopAiButtons();
          try {
            applyShopAiData(json.data || {});
          } catch (fillErr) {
            var fillMessage = (fillErr && fillErr.message) || '생성된 문구를 화면에 넣지 못했습니다.';
            shopSetAiStatus(root, fillMessage, true);
            alert(fillMessage);
          }
        }, function (err) {
          var message = aiAbortErrorMessage(err);
          shopSetAiStatus(root, message, true);
          alert(message);
          resetShopAiButtons();
        });
    }

    form.addEventListener('click', function (event) {
      var btn = event.target.closest('[data-shop-ai-generate]');
      if (!btn || !form.contains(btn)) return;
      event.preventDefault();
      runShopAiGenerate(btn);
    });
  }

  function talkApplyValue(form, selector) {
    var el = qs(selector, form);
    return el ? (el.value || '').trim() : '';
  }

  function talkApplyFill(form, selector, value, overwrite) {
    var el = qs(selector, form);
    if (!el || !value) return;
    if (!overwrite && (el.value || '').trim() !== '') return;
    el.value = value;
    el.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function talkApplySetAiStatus(form, message, isError) {
    var status = qs('[data-talk-apply-ai-status]', form);
    if (!status) return;
    if (!message) {
      status.hidden = true;
      status.textContent = '';
      status.classList.remove('is-error');
      return;
    }
    status.hidden = false;
    status.textContent = message;
    status.classList.toggle('is-error', !!isError);
  }

  function talkApplySelectEmoji(form, emoji) {
    if (!emoji) return;
    var input = qs('[data-talk-emoji-input]', form);
    var preview = qs('[data-talk-emoji-preview]', form);
    if (input) {
      input.value = emoji;
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (preview) {
      preview.textContent = emoji;
    }
    qsa('[data-talk-emoji-option]', form).forEach(function (btn) {
      btn.classList.toggle('is-selected', btn.getAttribute('data-talk-emoji-option') === emoji);
    });
  }

  function talkApplyApplyAiData(form, data, mode) {
    if (!data) return;
    var overwrite = mode === 'all';
    if (data.room_name) {
      talkApplyFill(form, '#talk_room_name', data.room_name, overwrite || mode === 'room_name');
    }
    if (data.room_description) {
      talkApplyFill(form, '#talk_room_description', data.room_description, overwrite || mode === 'room_name' || mode === 'room_description');
    }
    if (data.room_detail) {
      talkApplyFill(form, '#talk_room_detail', data.room_detail, overwrite || mode === 'room_detail');
    }
    if (data.rules) {
      talkApplyFill(form, '#talk_rules', data.rules, overwrite || mode === 'rules');
    }
    if (data.apply_reason) {
      talkApplyFill(form, '#talk_apply_reason', data.apply_reason, overwrite || mode === 'apply_reason');
    }
    if (data.category) {
      var category = qs('#talk_category', form);
      if (category && (overwrite || mode === 'all' || mode === 'emoji' || !category.value)) {
        category.value = data.category;
        category.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }
    if (data.emoji) {
      talkApplySelectEmoji(form, data.emoji);
    }
  }

  function initTalkEmojiPicker(form) {
    var picker = qs('[data-talk-emoji-picker]', form);
    if (!picker) return;

    var input = qs('[data-talk-emoji-input]', form);
    if (input) {
      input.addEventListener('input', function () {
        var value = (input.value || '').trim();
        var preview = qs('[data-talk-emoji-preview]', form);
        if (preview && value) {
          preview.textContent = value;
        }
        qsa('[data-talk-emoji-option]', form).forEach(function (btn) {
          btn.classList.toggle('is-selected', btn.getAttribute('data-talk-emoji-option') === value);
        });
      });
    }

    qsa('[data-talk-emoji-option]', form).forEach(function (btn) {
      btn.addEventListener('click', function () {
        talkApplySelectEmoji(form, btn.getAttribute('data-talk-emoji-option'));
      });
    });
  }

  function initTalkApplyAi(form) {
    var buttons = qsa('[data-talk-apply-ai]', form);
    if (!buttons.length || !window.fetch) return;

    function buildFormData(mode) {
      var fd = new FormData();
      fd.append('mode', mode || 'all');
      fd.append('topic_hint', talkApplyValue(form, '#talk_topic_hint'));
      fd.append('room_name', talkApplyValue(form, '#talk_room_name'));
      fd.append('room_description', talkApplyValue(form, '#talk_room_description'));
      fd.append('room_detail', talkApplyValue(form, '#talk_room_detail'));
      fd.append('category', talkApplyValue(form, '#talk_category'));
      fd.append('rules', talkApplyValue(form, '#talk_rules'));
      fd.append('apply_reason', talkApplyValue(form, '#talk_apply_reason'));
      fd.append('emoji', talkApplyValue(form, '#talk_emoji'));
      return fd;
    }

    function validateBeforeAi(mode) {
      var hasTopic = talkApplyValue(form, '#talk_topic_hint') !== '';
      var hasName = talkApplyValue(form, '#talk_room_name') !== '';
      var hasCategory = talkApplyValue(form, '#talk_category') !== '';
      if (hasTopic || hasName || hasCategory) {
        return true;
      }
      alert('톡방 주제, 이름, 카테고리 중 하나 이상을 입력해 주세요.');
      var hint = qs('#talk_topic_hint', form);
      if (hint) hint.focus();
      return false;
    }

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var mode = btn.getAttribute('data-talk-apply-ai') || 'all';
        if (!validateBeforeAi(mode)) {
          return;
        }

        var loadingLabel = mode === 'all' ? 'AI 작성 중…' : '작성 중…';
        rememberAiBtnLabels(buttons);
        buttons.forEach(function (b) {
          setAiBtnLoading(b, true);
          if (b.classList.contains('eottae-ai-btn')) {
            setAiBtnLabel(b, loadingLabel);
          } else {
            b.textContent = loadingLabel;
          }
        });
        talkApplySetAiStatus(form, 'AI가 신청서 내용을 작성 중입니다...', false);

        aiFetchJson(eottaeProcPath('eottae-talkroom-apply-ai.php'), {
          method: 'POST',
          credentials: 'same-origin',
          body: buildFormData(mode)
        }, 50000)
          .then(function (json) {
            talkApplyApplyAiData(form, json.data || {}, mode);
            var sourceLabel = json.source === 'api' ? 'AI' : '기본 템플릿';
            talkApplySetAiStatus(form, sourceLabel + '로 내용을 입력했습니다. 제출 전 내용을 확인해 주세요.', false);
          })
          .catch(function (err) {
            talkApplySetAiStatus(form, aiAbortErrorMessage(err), true);
          })
          .finally(function () {
            resetAiButtons(buttons);
          });
      });
    });
  }

  function initTalkApplyForm() {
    var form = qs('[data-talk-apply-form]');
    if (!form) return;
    initTalkEmojiPicker(form);
    initTalkApplyAi(form);
  }

  function initShopMapThumbAi(root) {
    var btn = qs('[data-map-thumb-ai-generate]', root);
    if (!btn || !window.fetch) return;

    btn.addEventListener('click', function () {
      var subject = shopAiValue(root, '#wr_subject');
      if (!subject) {
        alert('업체명을 먼저 입력해 주세요.');
        var subjectInput = qs('#wr_subject', root);
        if (subjectInput) subjectInput.focus();
        return;
      }

      var status = qs('[data-map-thumb-ai-status]', root);
      var tmpInput = qs('#eottae_map_thumb_ai_tmp', root);
      var previewImg = qs('[data-map-thumb-preview-img]', root);
      var previewSlot = previewImg ? previewImg.closest('.shop-register-page__photo-slot') : null;
      var fileInput = qs('#eottae_map_thumb', root);
      var form = new FormData();
      form.append('bo_table', shopAiValue(root, 'input[name="bo_table"]') || 'shop');
      form.append('name', subject);
      form.append('category', shopAiValue(root, '#ca_name'));
      form.append('region', shopAiValue(root, '#wr_2'));
      form.append('address', shopAiValue(root, '#wr_3'));
      form.append('intro', shopAiValue(root, '#wr_content'));

      setAiBtnLoading(btn, true);
      setAiBtnLabel(btn, 'AI 썸네일 생성 중…');
      if (status) {
        status.textContent = '지도 마커용 정사각형 썸네일을 생성하고 있습니다.';
        status.classList.remove('is-error');
      }

      aiFetchJson(eottaeProcPath('eottae-shop-map-thumb-ai.php'), {
        method: 'POST',
        credentials: 'same-origin',
        body: form
      }, 70000)
        .then(function (json) {
          var data = json.data || {};
          if (tmpInput) tmpInput.value = data.tmp || '';
          if (fileInput) fileInput.value = '';
          if (previewImg && data.url) {
            previewImg.src = data.url + '?v=' + Date.now();
            previewImg.hidden = false;
            if (previewSlot) previewSlot.classList.add('has-preview');
          }
          if (status) status.textContent = 'AI 썸네일을 생성했습니다. 다시 생성 버튼을 누르면 새 이미지로 교체됩니다.';
        })
        .catch(function (err) {
          if (status) {
            status.textContent = aiAbortErrorMessage(err);
            status.classList.add('is-error');
          }
        })
        .finally(function () {
          setAiBtnLoading(btn, false);
          setAiBtnLabel(btn, previewImg && previewImg.src && !previewImg.hidden ? 'AI 지도 썸네일 다시 생성' : getAiBtnDefaultLabel(btn));
        });
    });

    var manualInput = qs('#eottae_map_thumb', root);
    if (manualInput) {
      manualInput.addEventListener('change', function () {
        var tmpInput = qs('#eottae_map_thumb_ai_tmp', root);
        if (tmpInput) tmpInput.value = '';
        setAiBtnLabel(btn, getAiBtnDefaultLabel(btn));
      });
    }
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
      ['인스타그램', qs('#eottae_sns_instagram', root)],
      ['틱톡', qs('#eottae_sns_tiktok', root)],
      ['페이스북', qs('#eottae_sns_facebook', root)],
      ['네이버블로그', qs('#eottae_sns_naver_blog', root)],
      ['유튜브', qs('#eottae_sns_youtube', root)],
      ['영업시간', qs('#wr_6', root)],
      ['휴무일', qs('#wr_7', root)],
      ['영업상태', qs('#wr_8', root)],
      ['SEO 타이틀', qs('#eottae_seo_title', root)],
      ['SEO 소개', qs('#eottae_seo_intro', root)],
      ['메타 디스크립션', qs('#eottae_seo_description', root)],
      ['포커스 키워드', qs('#eottae_seo_keyword', root)]
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

  function initShopDetailContentEditor() {
    var root = qs('[data-shop-content-edit]');
    if (!root) return;

    var view = qs('#shopContentView', root);
    var edit = qs('#shopContentEdit', root);
    var body = qs('#shopContentBody', root);
    var textarea = qs('#shop_wr_content', root);
    var statusEl = qs('[data-shop-content-status]', root);
    var openBtns = qsa('[data-shop-content-edit-open]', root);
    var saveBtn = qs('[data-shop-content-save]', root);
    var cancelBtn = qs('[data-shop-content-cancel]', root);
    var useEditor = root.getAttribute('data-shop-content-use-editor') === '1';
    var editorReady = false;
    var saving = false;

    function setStatus(message, type) {
      if (!statusEl) return;
      statusEl.textContent = message || '';
      statusEl.classList.remove('is-error', 'is-success');
      if (type) statusEl.classList.add('is-' + type);
    }

    function shopEditorInstance() {
      return typeof oEditors !== 'undefined' && oEditors.getById && oEditors.getById.shop_wr_content
        ? oEditors.getById.shop_wr_content
        : null;
    }

    function activateShopEditorWysiwyg() {
      var ed = shopEditorInstance();
      if (!ed) return;
      try {
        ed.exec('CHANGE_EDITING_MODE', ['WYSIWYG', true]);
        ed.exec('LOAD_CONTENTS_FIELD', [false]);
      } catch (e) { /* ignore */ }
    }

    function ensureEditor() {
      if (!useEditor || editorReady || !textarea) return;
      if (typeof nhn === 'undefined' || !nhn.husky || !nhn.husky.EZCreator || typeof g5_editor_url === 'undefined') {
        return;
      }

      nhn.husky.EZCreator.createInIFrame({
        oAppRef: oEditors,
        elPlaceHolder: 'shop_wr_content',
        sSkinURI: g5_editor_url + '/SmartEditor2Skin.html',
        htParams: {
          bUseToolbar: true,
          bUseVerticalResizer: true,
          bUseModeChanger: true,
          bSkipXssFilter: true,
          sDefaultEditingMode: 'WYSIWYG',
          fOnBeforeUnload: function () {}
        },
        fOnAppLoad: function () {
          activateShopEditorWysiwyg();
        },
        fCreator: 'createSEditor2'
      });

      editorReady = true;
    }

    function syncEditorToField() {
      var ed = shopEditorInstance();
      if (!useEditor || !editorReady || !ed) {
        return;
      }
      ed.exec('UPDATE_CONTENTS_FIELD', []);
    }

    function setOpenButtonsVisible(visible) {
      openBtns.forEach(function (btn) {
        btn.hidden = !visible;
      });
    }

    function showEdit() {
      if (!edit || !view) return;
      view.hidden = true;
      edit.hidden = false;
      root.classList.add('is-editing');
      setStatus('', '');
      setOpenButtonsVisible(false);

      window.requestAnimationFrame(function () {
        ensureEditor();
        if (editorReady) {
          activateShopEditorWysiwyg();
        }
      });
    }

    function showView() {
      if (!edit || !view) return;
      view.hidden = false;
      edit.hidden = true;
      root.classList.remove('is-editing');
      setStatus('', '');
      setOpenButtonsVisible(true);
    }

    function getContentValue() {
      if (!textarea) return '';
      if (useEditor) {
        syncEditorToField();
      }
      return textarea.value.trim();
    }

    openBtns.forEach(function (btn) {
      btn.addEventListener('click', showEdit);
    });

    if (cancelBtn) {
      cancelBtn.addEventListener('click', showView);
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        if (saving) return;

        var content = getContentValue();
        if (!content) {
          setStatus('본문 내용을 입력해 주세요.', 'error');
          return;
        }

        var fd = new FormData();
        fd.append('eottae_shop_content_token', root.getAttribute('data-shop-content-token') || '');
        fd.append('bo_table', root.getAttribute('data-shop-content-bo-table') || '');
        fd.append('wr_id', root.getAttribute('data-shop-content-wr-id') || '');
        fd.append('wr_content', content);

        saving = true;
        saveBtn.disabled = true;
        setStatus('저장 중…', '');

        fetch(root.getAttribute('data-shop-content-action') || '', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        })
          .then(function (res) { return res.json(); })
          .then(function (data) {
            if (!data || !data.success) {
              throw new Error((data && data.message) ? data.message : '저장에 실패했습니다.');
            }
            if (body && data.content_html) {
              body.innerHTML = data.content_html;
            }
            if (data.token) {
              root.setAttribute('data-shop-content-token', data.token);
            }
            setStatus(data.message || '저장했습니다.', 'success');
            showView();
          })
          .catch(function (err) {
            setStatus(err.message || '저장에 실패했습니다.', 'error');
          })
          .finally(function () {
            saving = false;
            saveBtn.disabled = false;
          });
      });
    }
  }

  function initPhotoPreview() {
    function applyPhotoPreview(input) {
      var slot = input.closest('.community-write-page__photo-slot, .shop-register-page__photo-slot');
      if (!slot) return;

      var preview = qs('.community-write-page__photo-preview, .shop-register-page__photo-preview', slot);
      if (!preview) return;

      if (!input.files || !input.files[0]) {
        return;
      }

      var reader = new FileReader();
      reader.onload = function (ev) {
        preview.src = ev.target.result;
        preview.hidden = false;
        slot.classList.add('has-preview');
      };
      reader.readAsDataURL(input.files[0]);
    }

    document.addEventListener('change', function (e) {
      var input = e.target;
      if (!input.matches('[data-photo-preview], [data-photo-input]')) return;
      applyPhotoPreview(input);
    });
  }

  /* Auth member type → mb_1 / mb_2 */
  function initMemberType() {
    var forms = [qs('#fregisterform'), qs('#fregister')].filter(Boolean);
    if (!forms.length) return;

    forms.forEach(function (form) {
      var mb1 = qs('input[name="mb_1"]', form);
      var mb2 = qs('input[name="mb_2"]', form);
      var roleWrap = qs('[data-member-role-wrap]', form);
      if (!mb1 || !mb2) return;

      function syncRoleVisibility(audience) {
        if (!roleWrap) return;
        var show = audience === 'expat' || audience === 'both';
        roleWrap.classList.toggle('is-hidden', !show);
        if (audience === 'tourist') {
          mb1.value = 'member';
          var memberRadio = qs('input[name="eottae_member_type"][value="member"]', form);
          if (memberRadio) memberRadio.checked = true;
        }
      }

      function syncFromAudience(radio) {
        mb2.value = radio.value;
        syncRoleVisibility(radio.value);
      }

      function syncFromRole(radio) {
        mb1.value = radio.value === 'business' ? 'business' : 'member';
      }

      qsa('input[name="eottae_audience_type"]', form).forEach(function (radio) {
        radio.addEventListener('change', function () {
          syncFromAudience(this);
        });
      });

      qsa('input[name="eottae_member_type"]', form).forEach(function (radio) {
        radio.addEventListener('change', function () {
          syncFromRole(this);
        });
      });

      var checkedAudience = qs('input[name="eottae_audience_type"]:checked', form);
      if (checkedAudience) {
        syncFromAudience(checkedAudience);
      } else {
        syncRoleVisibility(mb2.value || '');
      }

      var checkedRole = qs('input[name="eottae_member_type"]:checked', form);
      if (checkedRole) {
        syncFromRole(checkedRole);
      }
    });
  }

  /* Shop register geocode (browser Geocoder — HTTP referrer 제한 API 키 대응) */
  var shopRegionRules = [
    { region: 'IT Park', keywords: ['it park', 'i.t. park', 'cebu it park', 'lahug'] },
    { region: '막탄', keywords: ['mactan', '막탄', 'mactan island'] },
    { region: '아얄라', keywords: ['ayala', 'cebu business park', '아얄라'] },
    { region: '만다우에', keywords: ['mandaue', '만다우에'] },
    { region: '라푸라푸', keywords: ['lapu-lapu', 'lapu lapu', 'lapulapu', '라푸라푸'] }
  ];

  function shopNormalizeAddress(address) {
    var query = (address || '').trim();
    if (!query) return '';
    if (!/cebu|세부/i.test(query)) {
      query += ', Cebu, Philippines';
    }
    return query;
  }

  function shopDetectRegionFromText(text) {
    var hay = (text || '').toLowerCase();
    if (!hay) return '';
    var i;
    for (i = 0; i < shopRegionRules.length; i++) {
      var rule = shopRegionRules[i];
      var k;
      for (k = 0; k < rule.keywords.length; k++) {
        if (hay.indexOf(rule.keywords[k]) !== -1) {
          return rule.region;
        }
      }
    }
    if (/cebu city|세부시티|세부|talamban|banilad|sm seaside|sugbu/.test(hay)) {
      return '세부시티';
    }
    return '';
  }

  window.shopDetectRegionFromText = shopDetectRegionFromText;
  window.eottaeSetEditorContent = eottaeSetEditorContent;

  function shopApplyRegionFromAddress(address, regionInput, regionDisplay, status) {
    var region = shopDetectRegionFromText(address);
    if (!region) {
      return false;
    }
    if (regionInput) {
      regionInput.value = region;
    }
    if (regionDisplay) {
      regionDisplay.textContent = '대표 지역: ' + region;
    }
    if (status) {
      status.textContent = '대표 지역이 설정되었습니다.';
    }
    return true;
  }

  function shopDetectRegionFromGeocodeResult(result) {
    if (!result) return '';
    var parts = [result.formatted_address || ''];
    if (result.address_components) {
      result.address_components.forEach(function (component) {
        if (component.long_name) parts.push(component.long_name);
        if (component.short_name) parts.push(component.short_name);
      });
    }
    return shopDetectRegionFromText(parts.join(' '));
  }

  function shopDetectRegionFromCoords(lat, lng) {
    lat = parseFloat(lat);
    lng = parseFloat(lng);
    if (!isFinite(lat) || !isFinite(lng)) return '';
    if (lat >= 10.25 && lat <= 10.38 && lng >= 123.92 && lng <= 124.05) return '막탄';
    if (lat >= 10.25 && lat <= 10.35 && lng >= 123.93 && lng <= 124.04) return '라푸라푸';
    if (lat >= 10.30 && lat <= 10.40 && lng >= 123.90 && lng <= 123.97) return '만다우에';
    if (lat >= 10.24 && lat <= 10.36 && lng >= 123.78 && lng <= 123.90) return '세부시티';
    return '';
  }

  function shopReverseGeocode(lat, lng) {
    return new Promise(function (resolve) {
      if (!window.google || !google.maps || !google.maps.Geocoder) {
        resolve({ ok: false, status: 'MAPS_NOT_READY' });
        return;
      }
      var geocoder = new google.maps.Geocoder();
      geocoder.geocode({ location: { lat: lat, lng: lng } }, function (results, status) {
        if (status !== 'OK' || !results || !results[0] || !results[0].geometry) {
          resolve({ ok: false, status: status || 'ZERO_RESULTS' });
          return;
        }
        var loc = results[0].geometry.location;
        resolve({
          ok: true,
          lat: loc.lat(),
          lng: loc.lng(),
          address: results[0].formatted_address || '',
          region: shopDetectRegionFromGeocodeResult(results[0])
        });
      });
    });
  }

  function shopGeocodeErrorMessage(data) {
    var status = data && data.status ? data.status : '';
    if (status === 'REQUEST_DENIED') {
      return '좌표 API 권한을 확인하지 못했습니다. 대표 지역은 주소로 자동 설정되며, 좌표는 필요 시 직접 입력할 수 있습니다.';
    }
    if (status === 'OVER_QUERY_LIMIT') {
      return '좌표 API 사용량이 많습니다. 대표 지역은 주소로 자동 설정되며, 좌표는 필요 시 직접 입력할 수 있습니다.';
    }
    if (data && data.message) {
      return data.message;
    }
    return '좌표를 찾지 못했습니다. 주소를 확인해 주세요.';
  }

  function shopApplyGeocodeResult(data, latInput, lngInput, regionInput, regionDisplay, status) {
    if (latInput) latInput.value = data.lat;
    if (lngInput) lngInput.value = data.lng;
    if (regionInput && data.region) {
      regionInput.value = data.region;
      if (regionDisplay) {
        regionDisplay.textContent = '대표 지역: ' + data.region;
      }
    } else if (regionDisplay && regionInput && !regionInput.value) {
      regionDisplay.textContent = '대표 지역을 자동 분류하지 못했습니다. 주소에 Cebu City·Mactan 등 지역명을 포함해 주세요.';
    }
    if (status) {
      status.textContent = data.region
        ? '좌표와 대표 지역이 설정되었습니다.'
        : '좌표가 설정되었습니다.';
    }
    document.dispatchEvent(new CustomEvent('eottae:shop-coords-updated', {
      detail: { lat: data.lat, lng: data.lng, source: 'geocode' }
    }));
  }

  function shopGeocodeWithGoogle(address) {
    return new Promise(function (resolve) {
      if (!window.google || !google.maps || !google.maps.Geocoder) {
        resolve({ ok: false, status: 'MAPS_NOT_READY' });
        return;
      }
      var geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: shopNormalizeAddress(address) }, function (results, status) {
        if (status !== 'OK' || !results || !results[0] || !results[0].geometry) {
          resolve({ ok: false, status: status || 'ZERO_RESULTS' });
          return;
        }
        var loc = results[0].geometry.location;
        resolve({
          ok: true,
          lat: loc.lat(),
          lng: loc.lng(),
          address: results[0].formatted_address || address,
          region: shopDetectRegionFromGeocodeResult(results[0])
        });
      });
    });
  }

  function shopRunGeocode(address) {
    return shopGeocodeWithGoogle(address).then(function (data) {
      if (data.ok || data.status !== 'MAPS_NOT_READY') {
        return data;
      }
      return { ok: false, status: 'MAPS_NOT_READY' };
    });
  }

  function initShopGeocode() {
    var btn = qs('#shopGeocodeBtn');
    var currentBtn = qs('#shopCurrentLocationBtn');
    if (!btn && !currentBtn) return;

    var addressInput = qs('#wr_3');
    var latInput = qs('#wr_9');
    var lngInput = qs('#wr_10');
    var regionInput = qs('#wr_2');
    var regionDisplay = qs('#shopRegionDisplay');
    var status = qs('#shopGeocodeStatus');
    var geocodeTimer = null;
    var lastGeocodedAddress = '';

    function openCoordDetails() {
      var details = qs('.shop-register-page__advanced');
      if (details) details.open = true;
    }

    function applyCurrentLocation(lat, lng, address, region) {
      var data = {
        lat: lat,
        lng: lng,
        region: region || shopDetectRegionFromText(address || '') || shopDetectRegionFromCoords(lat, lng)
      };
      if (addressInput && address) {
        addressInput.value = address;
        lastGeocodedAddress = address;
      }
      shopApplyGeocodeResult(data, latInput, lngInput, regionInput, regionDisplay, status);
      openCoordDetails();
    }

    function runGeocode(trigger) {
      var address = addressInput ? addressInput.value.trim() : '';
      if (!address) {
        if (trigger === 'manual') {
          alert('주소를 입력해 주세요.');
        }
        return;
      }
      if (trigger === 'auto' && address === lastGeocodedAddress) {
        return;
      }

      if (btn) btn.disabled = true;
      if (currentBtn) currentBtn.disabled = true;
      if (status) status.textContent = '주소를 확인하는 중…';

      shopRunGeocode(address)
        .then(function (data) {
          if (btn) btn.disabled = false;
          if (currentBtn) currentBtn.disabled = false;
          if (!data.ok) {
            if (shopApplyRegionFromAddress(address, regionInput, regionDisplay, status)) {
              if (latInput && !latInput.value) latInput.value = '';
              if (lngInput && !lngInput.value) lngInput.value = '';
              return;
            }
            if (status) status.textContent = shopGeocodeErrorMessage(data);
            return;
          }
          lastGeocodedAddress = address;
          shopApplyGeocodeResult(data, latInput, lngInput, regionInput, regionDisplay, status);
        })
        .catch(function () {
          if (btn) btn.disabled = false;
          if (currentBtn) currentBtn.disabled = false;
          if (status) status.textContent = '요청에 실패했습니다.';
        });
    }

    if (btn) {
      btn.addEventListener('click', function () {
        runGeocode('manual');
      });
    }

    if (currentBtn) {
      currentBtn.addEventListener('click', function () {
        if (!navigator.geolocation) {
          alert('이 브라우저에서는 현재위치를 사용할 수 없습니다.');
          return;
        }
        currentBtn.disabled = true;
        if (btn) btn.disabled = true;
        if (status) status.textContent = '현재위치를 확인하는 중…';

        navigator.geolocation.getCurrentPosition(function (pos) {
          var lat = pos.coords.latitude;
          var lng = pos.coords.longitude;

          function finish() {
            currentBtn.disabled = false;
            if (btn) btn.disabled = false;
          }

          shopReverseGeocode(lat, lng).then(function (data) {
            if (data.ok) {
              applyCurrentLocation(data.lat, data.lng, data.address, data.region);
              finish();
              return;
            }
            applyCurrentLocation(lat, lng, '', shopDetectRegionFromCoords(lat, lng));
            if (status) {
              status.textContent = data.status === 'MAPS_NOT_READY'
                ? '현재위치 좌표가 저장되었습니다. 지도 로드 후 주소를 다시 확인할 수 있습니다.'
                : '현재위치 좌표가 저장되었습니다. 주소·지역은 직접 확인해 주세요.';
            }
            finish();
          });
        }, function () {
          finish();
          if (status) status.textContent = '현재위치 권한이 거부되었습니다. 주소 검색 또는 지도 선택을 이용해 주세요.';
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 });
      });
    }

    if (addressInput && btn) {
      addressInput.addEventListener('blur', function () {
        if (addressInput.value.trim().length < 8) {
          return;
        }
        clearTimeout(geocodeTimer);
        geocodeTimer = setTimeout(function () {
          runGeocode('auto');
        }, 400);
      });
    }

    document.addEventListener('eottae:geocoder-ready', onMapsReady);
    document.addEventListener('eottae:shop-maps-ready', onMapsReady);

    function onMapsReady() {
      if (btn && addressInput && addressInput.value.trim().length >= 8 && !lastGeocodedAddress) {
        runGeocode('auto');
      }
    }
  }

  function initShopCoordinatePicker() {
    var mapEl = qs('#shopCoordMap');
    var details = qs('.shop-register-page__advanced');
    if (!mapEl || !details) return;

    var latInput = qs('#wr_9');
    var lngInput = qs('#wr_10');
    var status = qs('#shopGeocodeStatus');
    var map = null;
    var marker = null;
    var initialized = false;

    function parseCoord(input, fallback) {
      var n = parseFloat(input && input.value ? input.value : '');
      return isFinite(n) ? n : fallback;
    }

    function getDefaultCenter() {
      return {
        lat: parseFloat(mapEl.getAttribute('data-default-lat')) || 10.313,
        lng: parseFloat(mapEl.getAttribute('data-default-lng')) || 123.9174
      };
    }

    function getDefaultZoom() {
      var z = parseInt(mapEl.getAttribute('data-default-zoom'), 10);
      return isFinite(z) ? z : 14;
    }

    function formatCoord(value) {
      return Number(value).toFixed(7);
    }

    function updateInputs(lat, lng, message) {
      if (latInput) latInput.value = formatCoord(lat);
      if (lngInput) lngInput.value = formatCoord(lng);
      if (status && message) status.textContent = message;
    }

    function setMarkerPosition(latLng) {
      if (!marker) {
        marker = new google.maps.Marker({
          map: map,
          position: latLng,
          draggable: true
        });
        marker.addListener('dragend', function () {
          var pos = marker.getPosition();
          updateInputs(pos.lat(), pos.lng(), '지도에서 위치를 선택했습니다.');
        });
      } else {
        marker.setPosition(latLng);
        marker.setMap(map);
      }
    }

    function initMap() {
      if (initialized || !window.google || !google.maps) return;
      initialized = true;

      var fallback = getDefaultCenter();
      var lat = parseCoord(latInput, fallback.lat);
      var lng = parseCoord(lngInput, fallback.lng);
      var hasCoords = latInput && latInput.value.trim() !== '' && lngInput && lngInput.value.trim() !== '';
      var center = hasCoords ? { lat: lat, lng: lng } : fallback;

      map = new google.maps.Map(mapEl, {
        center: center,
        zoom: hasCoords ? 16 : getDefaultZoom(),
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl: true
      });

      if (hasCoords) {
        setMarkerPosition(center);
      }

      map.addListener('click', function (event) {
        setMarkerPosition(event.latLng);
        updateInputs(event.latLng.lat(), event.latLng.lng(), '지도에서 위치를 선택했습니다.');
      });
    }

    function refreshMapSize() {
      if (!map) return;
      google.maps.event.trigger(map, 'resize');
      if (marker && marker.getPosition()) {
        map.panTo(marker.getPosition());
      } else {
        map.panTo(map.getCenter());
      }
    }

    function ensureMap() {
      if (window.google && google.maps) {
        initMap();
        window.setTimeout(refreshMapSize, 120);
      }
    }

    function syncFromInputs() {
      if (!map || !initialized) return;
      var lat = parseCoord(latInput, NaN);
      var lng = parseCoord(lngInput, NaN);
      if (!isFinite(lat) || !isFinite(lng)) return;
      var pos = { lat: lat, lng: lng };
      setMarkerPosition(pos);
      map.setCenter(pos);
      map.setZoom(16);
    }

    details.addEventListener('toggle', function () {
      if (details.open) {
        ensureMap();
      }
    });

    document.addEventListener('eottae:geocoder-ready', function () {
      if (details.open) ensureMap();
    });

    document.addEventListener('eottae:shop-coords-updated', function (event) {
      if (!event.detail || event.detail.lat == null || event.detail.lng == null) return;
      if (details.open) ensureMap();
      if (!map || !initialized) return;
      var pos = { lat: Number(event.detail.lat), lng: Number(event.detail.lng) };
      if (!isFinite(pos.lat) || !isFinite(pos.lng)) return;
      setMarkerPosition(pos);
      map.setCenter(pos);
      map.setZoom(16);
    });

    if (latInput) latInput.addEventListener('change', syncFromInputs);
    if (lngInput) lngInput.addEventListener('change', syncFromInputs);
  }

  function initEstateGeocode() {
    var btn = qs('#estateGeocodeBtn');
    if (!btn) return;

    var addressInput = qs('#estate_address');
    var latInput = qs('#estate_lat');
    var lngInput = qs('#estate_lng');
    var regionInput = qs('#estate_region_field');
    var status = qs('#estateGeocodeStatus');
    var geocodeTimer = null;
    var lastGeocodedAddress = '';

    function dispatchFieldChange(el) {
      if (!el) return;
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function runGeocode(trigger) {
      var address = addressInput ? addressInput.value.trim() : '';
      if (!address) {
        if (trigger === 'manual') {
          alert('주소를 입력해 주세요.');
        }
        return;
      }
      if (trigger === 'auto' && address === lastGeocodedAddress) {
        return;
      }

      btn.disabled = true;
      if (status) status.textContent = '주소를 확인하는 중…';

      shopRunGeocode(address)
        .then(function (data) {
          btn.disabled = false;
          if (!data.ok) {
            if (status) status.textContent = shopGeocodeErrorMessage(data);
            return;
          }
          lastGeocodedAddress = address;
          if (latInput) latInput.value = data.lat;
          if (lngInput) lngInput.value = data.lng;
          if (regionInput && data.region && !regionInput.value.trim()) {
            regionInput.value = data.region;
            dispatchFieldChange(regionInput);
          }
          dispatchFieldChange(latInput);
          dispatchFieldChange(lngInput);
          dispatchFieldChange(addressInput);
          if (status) {
            status.textContent = data.region
              ? '좌표와 지역이 설정되었습니다.'
              : '좌표가 설정되었습니다.';
          }
          document.dispatchEvent(new CustomEvent('eottae:shop-coords-updated', {
            detail: { lat: data.lat, lng: data.lng, source: 'estate-geocode' }
          }));
        })
        .catch(function () {
          btn.disabled = false;
          if (status) status.textContent = '요청에 실패했습니다.';
        });
    }

    btn.addEventListener('click', function () {
      runGeocode('manual');
    });

    if (addressInput) {
      addressInput.addEventListener('blur', function () {
        if (addressInput.value.trim().length < 8) {
          return;
        }
        clearTimeout(geocodeTimer);
        geocodeTimer = setTimeout(function () {
          runGeocode('auto');
        }, 400);
      });
    }

    document.addEventListener('eottae:geocoder-ready', function () {
      if (addressInput && addressInput.value.trim().length >= 8 && !lastGeocodedAddress) {
        runGeocode('auto');
      }
    });
  }

  function initEstateCoordinatePicker() {
    var mapEl = qs('#estateCoordMap');
    var details = qs('.sebu-property-template__map-details');
    if (!mapEl || !details) return;

    var latInput = qs('#estate_lat');
    var lngInput = qs('#estate_lng');
    var status = qs('#estateGeocodeStatus');
    var map = null;
    var marker = null;
    var initialized = false;

    function parseCoord(input, fallback) {
      var n = parseFloat(input && input.value ? input.value : '');
      return isFinite(n) ? n : fallback;
    }

    function getDefaultCenter() {
      return {
        lat: parseFloat(mapEl.getAttribute('data-default-lat')) || 10.313,
        lng: parseFloat(mapEl.getAttribute('data-default-lng')) || 123.9174
      };
    }

    function getDefaultZoom() {
      var z = parseInt(mapEl.getAttribute('data-default-zoom'), 10);
      return isFinite(z) ? z : 14;
    }

    function formatCoord(value) {
      return Number(value).toFixed(7);
    }

    function dispatchFieldChange(el) {
      if (!el) return;
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function updateInputs(lat, lng, message) {
      if (latInput) latInput.value = formatCoord(lat);
      if (lngInput) lngInput.value = formatCoord(lng);
      dispatchFieldChange(latInput);
      dispatchFieldChange(lngInput);
      if (status && message) status.textContent = message;
    }

    function setMarkerPosition(latLng) {
      if (!marker) {
        marker = new google.maps.Marker({
          map: map,
          position: latLng,
          draggable: true
        });
        marker.addListener('dragend', function () {
          var pos = marker.getPosition();
          updateInputs(pos.lat(), pos.lng(), '지도에서 위치를 선택했습니다.');
        });
      } else {
        marker.setPosition(latLng);
        marker.setMap(map);
      }
    }

    function initMap() {
      if (initialized || !window.google || !google.maps) return;
      initialized = true;

      var fallback = getDefaultCenter();
      var lat = parseCoord(latInput, fallback.lat);
      var lng = parseCoord(lngInput, fallback.lng);
      var hasCoords = latInput && latInput.value.trim() !== '' && lngInput && lngInput.value.trim() !== '';
      var center = hasCoords ? { lat: lat, lng: lng } : fallback;

      map = new google.maps.Map(mapEl, {
        center: center,
        zoom: hasCoords ? 16 : getDefaultZoom(),
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl: true
      });

      if (hasCoords) {
        setMarkerPosition(center);
      }

      map.addListener('click', function (event) {
        setMarkerPosition(event.latLng);
        updateInputs(event.latLng.lat(), event.latLng.lng(), '지도에서 위치를 선택했습니다.');
      });
    }

    function refreshMapSize() {
      if (!map) return;
      google.maps.event.trigger(map, 'resize');
      if (marker && marker.getPosition()) {
        map.panTo(marker.getPosition());
      } else {
        map.panTo(map.getCenter());
      }
    }

    function ensureMap() {
      if (window.google && google.maps) {
        initMap();
        window.setTimeout(refreshMapSize, 120);
      }
    }

    function syncFromInputs() {
      if (!map || !initialized) return;
      var lat = parseCoord(latInput, NaN);
      var lng = parseCoord(lngInput, NaN);
      if (!isFinite(lat) || !isFinite(lng)) return;
      var pos = { lat: lat, lng: lng };
      setMarkerPosition(pos);
      map.setCenter(pos);
      map.setZoom(16);
    }

    details.addEventListener('toggle', function () {
      if (details.open) {
        ensureMap();
      }
    });

    document.addEventListener('eottae:geocoder-ready', function () {
      if (details.open) ensureMap();
    });

    document.addEventListener('eottae:shop-coords-updated', function (event) {
      if (!event.detail || event.detail.lat == null || event.detail.lng == null) return;
      if (details.open) ensureMap();
      if (!map || !initialized) return;
      var pos = { lat: Number(event.detail.lat), lng: Number(event.detail.lng) };
      if (!isFinite(pos.lat) || !isFinite(pos.lng)) return;
      setMarkerPosition(pos);
      map.setCenter(pos);
      map.setZoom(16);
    });

    if (latInput) latInput.addEventListener('change', syncFromInputs);
    if (lngInput) lngInput.addEventListener('change', syncFromInputs);

    if (details.open) {
      ensureMap();
    }
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

  function initBusinessWriteSnippets() {
    qsa('[data-business-snippets]').forEach(function (root) {
      bindBusinessSnippetsPanel(root, {
        subjectInput: qs('#wr_subject'),
        contentInput: qs('#wr_content'),
      });
    });
  }

  function bindBusinessSnippetsPanel(root, options) {
    if (!root || !window.fetch) return;

    options = options || {};
    var toggle = qs('[data-snippets-toggle]', root);
    var panel = qs('[data-snippets-panel]', root);
    var listEl = qs('[data-snippets-list]', root);
    var emptyEl = qs('[data-snippets-empty]', root);
    var statusEl = qs('[data-snippets-status]', root);
    var subjectInput = options.subjectInput || qs('#wr_subject');
    var contentInput = options.contentInput || qs('#wr_content');
    var caSelect = document.getElementById('ca_name');
    var isDesktop = window.matchMedia('(min-width: 768px)').matches;

    function getAllowedCategories() {
      var raw = root.getAttribute('data-snippets-allowed-categories') || '';
      return raw.split(',').map(function (part) {
        return part.trim();
      }).filter(Boolean);
    }

    function getWriteBoTable() {
      var form = document.getElementById('fwrite');
      if (!form) return '';
      var input = form.querySelector('input[name="bo_table"]');
      return input ? String(input.value || '').trim() : '';
    }

    function getWriteCategory() {
      return caSelect ? String(caSelect.value || '').trim() : '';
    }

    function isWriteCategoryAllowed() {
      var category = getWriteCategory();
      if (!category) return false;
      return getAllowedCategories().indexOf(category) !== -1;
    }

    function appendWriteContext(fd) {
      var boTable = getWriteBoTable();
      var category = getWriteCategory();
      if (boTable) fd.append('bo_table', boTable);
      if (category) fd.append('ca_name', category);
    }

    function syncWriteSnippetVisibility() {
      var allowed = isWriteCategoryAllowed();
      root.hidden = !allowed;
      root.classList.toggle('business-snippets--hidden', !allowed);
      if (!allowed) {
        if (panel) panel.hidden = true;
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      }
      return allowed;
    }

    function setStatus(msg, isError) {
      if (!statusEl) return;
      statusEl.textContent = msg || '';
      statusEl.classList.toggle('is-error', !!isError);
    }

    function escapeHtml(str) {
      return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function truncate(text, len) {
      text = String(text || '').replace(/\s+/g, ' ').trim();
      if (text.length <= len) return text;
      return text.slice(0, len) + '…';
    }

    function applySnippet(snippet, skipConfirm) {
      if (!snippet) return;
      var contentVal = contentInput ? contentInput.value.trim() : '';
      if (contentInput && typeof oEditors !== 'undefined' && oEditors.getById && oEditors.getById.wr_content) {
        oEditors.getById.wr_content.exec('UPDATE_CONTENTS_FIELD', []);
        contentVal = contentInput.value.trim();
      }
      var hasExisting = (subjectInput && subjectInput.value.trim()) || contentVal;
      if (!skipConfirm && hasExisting && !window.confirm('현재 작성 중인 제목·내용을 불러온 문구로 바꿀까요?')) {
        return;
      }
      if (subjectInput) subjectInput.value = snippet.wr_subject || '';
      if (contentInput) {
        eottaeSetEditorContent('wr_content', snippet.wr_content || '', document);
      }
      setStatus('문구를 불러왔습니다.', false);
      if (panel && toggle && !isDesktop) {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
      }
    }

    function renderList(items) {
      if (!listEl) return;
      listEl.innerHTML = '';
      var hasItems = items && items.length;
      if (emptyEl) emptyEl.hidden = !!hasItems;
      if (!hasItems) return;

      items.forEach(function (item) {
        var li = document.createElement('li');
        li.className = 'business-snippets__item';
        li.innerHTML =
          '<button type="button" class="business-snippets__apply" data-snippets-apply>' +
          '<span class="business-snippets__label">' + escapeHtml(item.label || '홍보 문구') + '</span>' +
          '<span class="business-snippets__preview">' + escapeHtml(truncate(item.wr_content, isDesktop ? 120 : 60)) + '</span>' +
          '</button>' +
          '<button type="button" class="business-snippets__delete" data-snippets-delete>삭제</button>';
        li.querySelector('[data-snippets-apply]').addEventListener('click', function () {
          applySnippet(item, false);
        });
        li.querySelector('[data-snippets-delete]').addEventListener('click', function (e) {
          e.stopPropagation();
          if (!window.confirm('이 홍보 문구를 삭제할까요?')) return;
          deleteSnippet(item.snippet_id);
        });
        listEl.appendChild(li);
      });
    }

    function loadList() {
      return fetch('/proc/eottae-business-snippets.php?action=list', { credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '문구 목록을 불러오지 못했습니다.');
          }
          renderList(json.data || []);
        })
        .catch(function (err) {
          setStatus(err.message, true);
        });
    }

    function saveSnippet(label, subject, content, snippetId) {
      var fd = new FormData();
      fd.append('action', 'save');
      if (snippetId) fd.append('snippet_id', String(snippetId));
      fd.append('label', label || '');
      fd.append('wr_subject', subject || '');
      fd.append('wr_content', content || '');
      appendWriteContext(fd);
      return fetch('/proc/eottae-business-snippets.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '저장에 실패했습니다.');
          }
          setStatus('자주 쓰는 문구로 저장했습니다.', false);
          return loadList();
        });
    }

    function deleteSnippet(id) {
      var fd = new FormData();
      fd.append('action', 'delete');
      fd.append('snippet_id', String(id));
      fetch('/proc/eottae-business-snippets.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '삭제에 실패했습니다.');
          }
          setStatus('문구를 삭제했습니다.', false);
          loadList();
        })
        .catch(function (err) {
          setStatus(err.message, true);
        });
    }

    function runAiGenerate(button) {
      var topic = window.prompt('홍보 주제 (선택, 예: 주말 할인, 신메뉴)', '') || '';
      button.disabled = true;
      setStatus('AI가 홍보 문구를 작성 중입니다...', false);
      var fd = new FormData();
      fd.append('topic', topic);
      appendWriteContext(fd);
      return aiFetchJson(eottaeProcPath('eottae-business-snippet-ai.php'), {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      }, 50000)
        .then(function (json) {
          return json.data || {};
        })
        .finally(function () {
          button.disabled = false;
        });
    }

    if (toggle && panel) {
      toggle.addEventListener('click', function () {
        if (!isWriteCategoryAllowed()) {
          setStatus('분류에서 광고판을 선택하면 홍보 문구를 사용할 수 있습니다.', true);
          return;
        }
        var open = panel.hidden;
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) loadList();
      });

      if (isDesktop && syncWriteSnippetVisibility()) {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        root.classList.add('is-desktop-open');
        loadList();
      }
    }

    if (caSelect) {
      caSelect.addEventListener('change', function () {
        var allowed = syncWriteSnippetVisibility();
        if (allowed && isDesktop && panel && toggle) {
          panel.hidden = false;
          toggle.setAttribute('aria-expanded', 'true');
          loadList();
        }
      });
    }

    syncWriteSnippetVisibility();

    var aiBtn = qs('[data-snippets-ai-generate]', root);
    if (aiBtn) {
      aiBtn.addEventListener('click', function () {
        if (!isWriteCategoryAllowed()) {
          setStatus('분류에서 광고판을 선택하면 AI 홍보 문구를 사용할 수 있습니다.', true);
          return;
        }
        runAiGenerate(aiBtn)
          .then(function (data) {
            if (window.confirm('생성된 문구를 글에 적용할까요?')) {
              applySnippet(data, true);
            }
            if (window.confirm('이 문구를 자주 쓰는 목록에 저장할까요?')) {
              return saveSnippet(data.label, data.wr_subject, data.wr_content);
            }
            setStatus('AI 문구를 생성했습니다.', false);
          })
          .catch(function (err) {
            setStatus(err.message, true);
          });
      });
    }

    var saveBtn = qs('[data-snippets-save-current]', root);
    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        if (!isWriteCategoryAllowed()) {
          setStatus('분류에서 광고판을 선택하면 홍보 문구를 저장할 수 있습니다.', true);
          return;
        }
        if (contentInput && typeof oEditors !== 'undefined' && oEditors.getById && oEditors.getById.wr_content) {
          oEditors.getById.wr_content.exec('UPDATE_CONTENTS_FIELD', []);
        }
        var content = contentInput ? contentInput.value.trim() : '';
        if (!content) {
          alert('저장할 내용을 먼저 입력해 주세요.');
          if (contentInput) contentInput.focus();
          return;
        }
        var subject = subjectInput ? subjectInput.value.trim() : '';
        var label = window.prompt('문구 이름 (목록에 표시)', subject || '홍보 문구');
        if (label === null) return;
        saveBtn.disabled = true;
        saveSnippet(label, subject, content)
          .catch(function (err) { setStatus(err.message, true); })
          .finally(function () { saveBtn.disabled = false; });
      });
    }
  }

  function initBusinessSnippetsManager() {
    var root = qs('[data-business-snippets-manager]');
    if (!root || !window.fetch) return;

    var idInput = qs('#business_snippet_id', root);
    var labelInput = qs('#business_snippet_label', root);
    var subjectInput = qs('#business_snippet_subject', root);
    var contentInput = qs('#business_snippet_content', root);
    var statusEl = qs('[data-manager-status]', root);
    var listEl = qs('[data-manager-list]', root);
    var emptyEl = qs('[data-manager-empty]', root);
    var writeLink = qs('[data-manager-write-link]', root);
    var communityWriteBase = writeLink ? writeLink.getAttribute('href') : '';

    function setStatus(msg, isError) {
      if (!statusEl) return;
      statusEl.textContent = msg || '';
      statusEl.classList.toggle('is-error', !!isError);
    }

    function escapeHtml(str) {
      return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function resetForm() {
      if (idInput) idInput.value = '0';
      if (labelInput) labelInput.value = '';
      if (subjectInput) subjectInput.value = '';
      if (contentInput) contentInput.value = '';
      if (writeLink) writeLink.setAttribute('href', communityWriteBase);
    }

    function fillForm(item) {
      if (idInput) idInput.value = String(item.snippet_id || 0);
      if (labelInput) labelInput.value = item.label || '';
      if (subjectInput) subjectInput.value = item.wr_subject || '';
      if (contentInput) contentInput.value = item.wr_content || '';
      if (writeLink && item.snippet_id) {
        writeLink.setAttribute('href', communityWriteBase + (communityWriteBase.indexOf('?') >= 0 ? '&' : '?') + 'snippet_id=' + item.snippet_id);
      }
    }

    function renderList(items) {
      if (!listEl) return;
      listEl.innerHTML = '';
      var hasItems = items && items.length;
      if (emptyEl) emptyEl.hidden = !!hasItems;
      if (!hasItems) return;

      items.forEach(function (item) {
        var li = document.createElement('li');
        li.className = 'business-snippets-manager__item';
        li.innerHTML =
          '<button type="button" class="business-snippets-manager__pick" data-manager-pick>' +
          '<strong>' + escapeHtml(item.label || '홍보 문구') + '</strong>' +
          '<span>' + escapeHtml(item.wr_subject || '') + '</span>' +
          '</button>' +
          '<div class="business-snippets-manager__item-actions">' +
          '<a href="' + escapeHtml(communityWriteBase + (communityWriteBase.indexOf('?') >= 0 ? '&' : '?') + 'snippet_id=' + item.snippet_id) + '" class="business-snippets__btn business-snippets__btn--link">글쓰기</a>' +
          '<button type="button" class="business-snippets__delete" data-manager-delete>삭제</button>' +
          '</div>';
        li.querySelector('[data-manager-pick]').addEventListener('click', function () {
          fillForm(item);
          setStatus('문구를 불러왔습니다. 수정 후 저장할 수 있습니다.', false);
        });
        li.querySelector('[data-manager-delete]').addEventListener('click', function () {
          if (!window.confirm('이 홍보 문구를 삭제할까요?')) return;
          deleteSnippet(item.snippet_id);
        });
        listEl.appendChild(li);
      });
    }

    function loadList() {
      return fetch('/proc/eottae-business-snippets.php?action=list', { credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '문구 목록을 불러오지 못했습니다.');
          }
          renderList(json.data || []);
        })
        .catch(function (err) {
          setStatus(err.message, true);
        });
    }

    function saveCurrent() {
      var content = contentInput ? contentInput.value.trim() : '';
      if (!content) {
        alert('내용을 입력해 주세요.');
        if (contentInput) contentInput.focus();
        return Promise.resolve();
      }
      var fd = new FormData();
      fd.append('action', 'save');
      fd.append('snippet_id', idInput ? idInput.value : '0');
      fd.append('label', labelInput ? labelInput.value : '');
      fd.append('wr_subject', subjectInput ? subjectInput.value : '');
      fd.append('wr_content', content);
      return fetch('/proc/eottae-business-snippets.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '저장에 실패했습니다.');
          }
          if (json.data) fillForm(json.data);
          setStatus('홍보 문구를 저장했습니다.', false);
          return loadList();
        });
    }

    function deleteSnippet(id) {
      var fd = new FormData();
      fd.append('action', 'delete');
      fd.append('snippet_id', String(id));
      fetch('/proc/eottae-business-snippets.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.message) || '삭제에 실패했습니다.');
          }
          if (idInput && String(idInput.value) === String(id)) resetForm();
          setStatus('문구를 삭제했습니다.', false);
          loadList();
        })
        .catch(function (err) {
          setStatus(err.message, true);
        });
    }

    var aiBtn = qs('[data-manager-ai-generate]', root);
    if (aiBtn) {
      aiBtn.addEventListener('click', function () {
        var topic = window.prompt('홍보 주제 (선택, 예: 주말 할인, 신메뉴)', '') || '';
        aiBtn.disabled = true;
        setStatus('AI가 홍보 문구를 작성 중입니다...', false);
        var fd = new FormData();
        fd.append('topic', topic);
        aiFetchJson(eottaeProcPath('eottae-business-snippet-ai.php'), {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        }, 50000)
          .then(function (json) {
            var data = json.data || {};
            fillForm(data);
            if (idInput) idInput.value = '0';
            setStatus('AI 문구를 생성했습니다. 확인 후 저장해 주세요.', false);
          })
          .catch(function (err) {
            setStatus(aiAbortErrorMessage(err), true);
          })
          .finally(function () {
            aiBtn.disabled = false;
          });
      });
    }

    var saveBtn = qs('[data-manager-save]', root);
    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        saveBtn.disabled = true;
        saveCurrent()
          .catch(function (err) { setStatus(err.message, true); })
          .finally(function () { saveBtn.disabled = false; });
      });
    }

    var resetBtn = qs('[data-manager-reset]', root);
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        resetForm();
        setStatus('새 문구 작성을 시작합니다.', false);
      });
    }

    loadList();
  }

  function initGnbMegaMenu() {
    if (document.documentElement.getAttribute('data-eottae-gnb-mega-init') === '1') {
      return;
    }

    var shell = qs('[data-eottae-gnb-shell]');
    var nav = qs('[data-eottae-gnb-nav]');
    var panel = qs('[data-eottae-gnb-mega]');
    if (!shell || !nav || !panel) {
      return;
    }

    var desktopHead = shell.querySelector('[data-eottae-gnb-desktop-head]');
    if (desktopHead && panel.parentElement !== desktopHead) {
      desktopHead.appendChild(panel);
    }

    if (window.matchMedia('(max-width: 1023px)').matches) {
      return;
    }

    document.documentElement.setAttribute('data-eottae-gnb-mega-init', '1');

    var closeTimer = null;
    var open = false;

    function setOpen(next) {
      open = !!next;
      shell.classList.toggle('is-mega-open', open);
      panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    function clearCloseTimer() {
      if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = null;
      }
    }

    function scheduleClose() {
      clearCloseTimer();
      closeTimer = setTimeout(function () {
        setOpen(false);
      }, 280);
    }

    function handleEnter() {
      clearCloseTimer();
      setOpen(true);
    }

    function isInsideMegaArea(node) {
      return node instanceof Node && shell.contains(node);
    }

    shell.addEventListener('mouseenter', handleEnter);
    shell.addEventListener('mouseleave', scheduleClose);

    nav.addEventListener('focusin', handleEnter);
    panel.addEventListener('focusin', handleEnter);
    shell.addEventListener('focusout', function (event) {
      var related = event.relatedTarget;
      if (isInsideMegaArea(related)) {
        return;
      }
      scheduleClose();
    });
  }

  function initMobileMenu() {
    if (document.documentElement.getAttribute('data-eottae-mobile-menu-init') === '1') {
      return;
    }

    var openBtns = qsa('.mobile-menu-btn, .site-header__menu-btn, .eottae-gnb-header__menu-btn');
    var menus = qsa('.mobile-menu, #siteMobileNav');
    if (!openBtns.length || !menus.length) {
      return;
    }

    document.documentElement.setAttribute('data-eottae-mobile-menu-init', '1');

    var menu = menus[0];
    var overlay = qs('.mobile-menu-overlay, .site-header__overlay, .eottae-gnb-header__overlay');
    var closeBtns = qsa('.mobile-menu-close, .site-header__mobile-close');
    var isDropdownMenu = menu.classList.contains('eottae-gnb-header__mobile');
    var isOpen = false;

    function setOpen(open) {
      isOpen = !!open;
      menu.classList.toggle('is-open', isOpen);
      if (overlay) {
        overlay.classList.toggle('is-open', isOpen);
      }
      openBtns.forEach(function (btn) {
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        btn.setAttribute('aria-label', isOpen ? '메뉴 닫기' : '메뉴 열기');
      });
      menu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
      document.body.style.overflow = isOpen && !isDropdownMenu ? 'hidden' : '';
    }

    openBtns.forEach(function (btn) {
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        setOpen(!menu.classList.contains('is-open'));
      });
    });

    closeBtns.forEach(function (btn) {
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        setOpen(false);
      });
    });

    if (overlay) {
      overlay.addEventListener('click', function () {
        setOpen(false);
      });
    }

    document.addEventListener('click', function (event) {
      if (!isOpen) {
        return;
      }
      var target = event.target;
      if (!(target instanceof Element)) {
        return;
      }
      if (menu.contains(target)) {
        return;
      }
      var clickedOpenBtn = openBtns.some(function (btn) {
        return btn.contains(target);
      });
      if (!clickedOpenBtn) {
        setOpen(false);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && isOpen) {
        setOpen(false);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('eottae-page');
    initMobileMenu();
    initGnbMegaMenu();
    initShopRegisterWizard();
    initShopGeocode();
    initShopCoordinatePicker();
    initEstateGeocode();
    initEstateCoordinatePicker();
    initMemberType();
    initTalkApplyForm();
    initReviewWriteForms();
    initReviewReply();
    initReviewLoadMore();
    initReviewDelete();
    initReviewEdit();
    initShopSave();
    initShopSpotApply();
    initShopDetailGallery();
    initShopDetailContentEditor();
    initPhotoPreview();
    initAdCarousel();
    initBusinessWriteSnippets();
    initBusinessSnippetsManager();
  });

  function bindReviewStarPicker(root, ratingInput) {
    if (!root || !ratingInput) return;

    var starBtns = qsa('.review-write-form__star', root);

    function setRating(value) {
      ratingInput.value = String(value);
      starBtns.forEach(function (btn) {
        var star = parseInt(btn.getAttribute('data-star'), 10);
        btn.classList.toggle('is-active', star <= value);
      });
    }

    setRating(parseInt(ratingInput.value, 10) || 5);

    starBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setRating(parseInt(btn.getAttribute('data-star'), 10));
      });
    });
  }

  function initReviewWriteForms() {
    var forms = qsa('[data-review-write-form]');
    if (!forms.length) return;

    forms.forEach(function (form) {
      var ratingInput = qs('[data-review-rating-input]', form);
      var starsWrap = qs('[data-review-stars]', form) || form;
      bindReviewStarPicker(starsWrap, ratingInput);

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var submitBtn = qs('.review-write-form__submit', form);
        if (submitBtn) submitBtn.disabled = true;

        fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
          .then(function (res) { return parseJsonResponse(res); })
          .then(function (data) {
            if (data.success) {
              window.location.href = data.redirect_url || window.location.href.split('#')[0] + '#shop-reviews';
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
    });

    document.addEventListener('click', function (e) {
      var jump = e.target.closest('a[href="#shop-review-write"]');
      if (!jump) return;
      var target = qs('#shop-review-write');
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        var ta = qs('textarea', target);
        if (ta) ta.focus();
      }
    });
  }

  function initReviewEdit() {
    document.addEventListener('click', function (e) {
      var openBtn = e.target.closest('[data-review-edit-open]');
      if (openBtn) {
        e.preventDefault();
        var card = openBtn.closest('.review-card');
        if (!card) return;
        var body = qs('[data-review-body]', card);
        var form = qs('[data-review-edit-form]', card);
        if (body) body.hidden = true;
        if (form) {
          form.hidden = false;
          var ratingInput = qs('[data-review-rating-input]', form);
          var starsWrap = qs('[data-review-stars]', form);
          bindReviewStarPicker(starsWrap, ratingInput);
        }
        return;
      }

      var cancelBtn = e.target.closest('[data-review-edit-cancel]');
      if (cancelBtn) {
        e.preventDefault();
        var card2 = cancelBtn.closest('.review-card');
        if (!card2) return;
        var body2 = qs('[data-review-body]', card2);
        var form2 = qs('[data-review-edit-form]', card2);
        if (body2) body2.hidden = false;
        if (form2) form2.hidden = true;
      }
    });

    document.addEventListener('submit', function (e) {
      var form = e.target.closest('[data-review-edit-form]');
      if (!form || form.hidden) return;
      e.preventDefault();

      var submitBtn = qs('.review-card__edit-save', form);
      if (submitBtn) submitBtn.disabled = true;

      fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (data) {
          if (data.success) {
            if (data.redirect_url) {
              window.location.href = data.redirect_url;
            } else {
              window.location.reload();
            }
            return;
          }
          alert(data.message || '리뷰 수정에 실패했습니다.');
          if (submitBtn) submitBtn.disabled = false;
        })
        .catch(function () {
          alert('네트워크 오류가 발생했습니다.');
          if (submitBtn) submitBtn.disabled = false;
        });
    });
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
        .then(function (res) { return parseJsonResponse(res); })
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

  function initReviewLoadMore() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-review-load-more]');
      if (!btn || btn.disabled) return;
      e.preventDefault();

      var section = btn.closest('#shop-reviews');
      var list = section ? qs('[data-review-list]', section) : null;
      if (!list) return;

      var shopId = btn.getAttribute('data-shop-id');
      var offset = parseInt(btn.getAttribute('data-offset'), 10) || 0;
      var limit = parseInt(btn.getAttribute('data-limit'), 10) || 10;
      var total = parseInt(btn.getAttribute('data-total'), 10) || 0;
      var label = btn.textContent;

      btn.disabled = true;
      btn.textContent = '불러오는 중…';

      var url = '/proc/eottae-shop-reviews-more.php?shop_wr_id=' + encodeURIComponent(shopId)
        + '&offset=' + encodeURIComponent(String(offset))
        + '&limit=' + encodeURIComponent(String(limit));

      fetch(url, { credentials: 'same-origin' })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (data) {
          if (!data.success) {
            throw new Error(data.message || '리뷰를 불러오지 못했습니다.');
          }

          if (data.html) {
            list.insertAdjacentHTML('beforeend', data.html);
          }

          var loaded = typeof data.loaded === 'number' ? data.loaded : offset + limit;
          btn.setAttribute('data-offset', String(loaded));

          if (data.has_more) {
            btn.disabled = false;
            btn.textContent = '리뷰 더보기 (' + loaded.toLocaleString('ko-KR') + '/' + total.toLocaleString('ko-KR') + ')';
            return;
          }

          var wrap = btn.closest('.review-summary__more-wrap');
          if (wrap) {
            wrap.remove();
          } else {
            btn.remove();
          }
        })
        .catch(function (err) {
          alert(err && err.message ? err.message : '리뷰를 불러오지 못했습니다.');
          btn.disabled = false;
          btn.textContent = label;
        });
    });
  }

  function initReviewDelete() {
    function postReviewDelete(action, btn, extraFields) {
      var reviewId = btn.getAttribute('data-review-id');
      var shopId = btn.getAttribute('data-shop-id');
      var token = btn.getAttribute('data-delete-token') || btn.getAttribute('data-manage-token') || '';
      var fd = new FormData();
      fd.append('action', action);
      fd.append('review_wr_id', reviewId);
      fd.append('shop_wr_id', shopId);
      fd.append('eottae_review_delete_token', token);
      if (action === 'author_delete') {
        fd.append('eottae_review_token', btn.getAttribute('data-manage-token') || token);
      }
      if (extraFields) {
        Object.keys(extraFields).forEach(function (key) {
          fd.append(key, extraFields[key]);
        });
      }

      btn.disabled = true;
      return fetch('/proc/eottae-review-delete.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
      })
        .then(function (res) { return parseJsonResponse(res); })
        .then(function (data) {
          btn.disabled = false;
          if (!data.success) {
            alert(data.message || '처리에 실패했습니다.');
            return;
          }
          var card = btn.closest('.review-card');
          if (card) {
            card.remove();
            var section = qs('#shop-reviews');
            var list = section ? qs('[data-review-list]', section) : null;
            if (list && !list.children.length) {
              window.location.reload();
            }
          } else {
            window.location.reload();
          }
        })
        .catch(function () {
          btn.disabled = false;
          alert('네트워크 오류가 발생했습니다.');
        });
    }

    document.addEventListener('click', function (e) {
      var deleteBtn = e.target.closest('[data-review-super-delete], [data-review-author-delete]');
      if (deleteBtn && deleteBtn.getAttribute('data-review-id')) {
        e.preventDefault();
        var isSuper = deleteBtn.getAttribute('data-review-super-delete') === '1';
        if (!confirm('이 리뷰를 삭제하시겠습니까? 삭제 후 복구할 수 없습니다.')) return;
        postReviewDelete(isSuper ? 'super_delete' : 'author_delete', deleteBtn);
        return;
      }

      var reqBtn = e.target.closest('[data-review-delete-request]');
      if (reqBtn) {
        e.preventDefault();
        var reason = window.prompt('삭제 요청 사유 (선택):', '');
        if (reason === null) return;
        var reviewId = reqBtn.getAttribute('data-review-id');
        var shopId = reqBtn.getAttribute('data-shop-id');
        var token = reqBtn.getAttribute('data-delete-token') || '';
        var fd = new FormData();
        fd.append('action', 'request');
        fd.append('review_wr_id', reviewId);
        fd.append('shop_wr_id', shopId);
        fd.append('eottae_review_delete_token', token);
        if (reason) fd.append('reason', reason);

        reqBtn.disabled = true;
        fetch('/proc/eottae-review-delete.php', { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (res) { return parseJsonResponse(res); })
          .then(function (data) {
            reqBtn.disabled = false;
            if (!data.success) {
              alert(data.message || '요청에 실패했습니다.');
              return;
            }
            alert(data.message || '삭제 요청이 접수되었습니다.');
            var foot = reqBtn.closest('.review-card__foot');
            if (foot) {
              foot.innerHTML = '<span class="review-card__delete-pending">삭제 검토 중</span>';
            }
          })
          .catch(function () {
            reqBtn.disabled = false;
            alert('네트워크 오류가 발생했습니다.');
          });
      }
    });
  }

  function initShopSpotApply() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-shop-spot-apply-btn]');
      if (!btn) return;
      var section = btn.closest('[data-shop-spot-apply]');
      if (!section) return;
      e.preventDefault();

      var procUrl = section.getAttribute('data-proc-url') || '';
      var boTable = section.getAttribute('data-bo-table') || '';
      var shopBoTable = section.getAttribute('data-shop-bo-table') || boTable;
      var wrId = section.getAttribute('data-wr-id') || '';
      var slot = btn.getAttribute('data-shop-spot-apply-btn') || '';
      var statusEl = section.querySelector('[data-shop-spot-status]');

      if (!procUrl || !boTable || !wrId || !slot) return;
      if (!window.confirm(slot + '번 자리 최우수 노출을 신청하시겠습니까? 포인트가 차감됩니다.')) return;

      btn.disabled = true;
      if (statusEl) statusEl.textContent = '신청 처리 중…';

      var fd = new FormData();
      fd.append('action', 'apply');
      fd.append('bo_table', boTable);
      fd.append('shop_bo_table', shopBoTable);
      fd.append('wr_id', wrId);
      fd.append('spot_slot', slot);

      fetch(procUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (statusEl) statusEl.textContent = data.message || '';
          if (data.ok) {
            setTimeout(function () { location.reload(); }, 700);
            return;
          }
          btn.disabled = false;
          alert(data.message || '신청에 실패했습니다.');
        })
        .catch(function () {
          btn.disabled = false;
          if (statusEl) statusEl.textContent = '';
          alert('네트워크 오류가 발생했습니다.');
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
        .then(function (res) { return parseJsonResponse(res); })
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
})(window);
