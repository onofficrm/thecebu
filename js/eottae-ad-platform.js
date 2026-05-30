(function (window, document) {
  'use strict';

  function qs(selector, root) {
    return (root || document).querySelector(selector);
  }

  function qsa(selector, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(selector));
  }

  function postJson(url, formData) {
    return fetch(url, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: { Accept: 'application/json' }
    }).then(function (response) {
      return response.json();
    });
  }

  function initQuote(form) {
    var slotEl = qs('[data-ad-platform-slot]', form);
    var daysEl = qs('[data-ad-platform-days]', form);
    var quoteEl = qs('[data-ad-platform-quote]', form);
    var bidEl = qs('[data-ad-bid-bonus]', form);
    var categoryEl = qs('[data-ad-target-category]', form);
    var root = form.closest('.ad-platform-register') || form.closest('.ad-platform-edit') || form;
    var categoryMap = {};
    if (root && root.getAttribute('data-ad-category-map')) {
      try {
        categoryMap = JSON.parse(root.getAttribute('data-ad-category-map')) || {};
      } catch (e) {
        categoryMap = {};
      }
    }
    if (!slotEl || !daysEl || !quoteEl) {
      return;
    }

    function readSlotMeta() {
      if (slotEl.tagName === 'SELECT') {
        var opt = slotEl.options[slotEl.selectedIndex];
        if (!opt) {
          return null;
        }
        return {
          code: slotEl.value,
          minDays: parseInt(opt.getAttribute('data-min-days') || '3', 10),
          maxDays: parseInt(opt.getAttribute('data-max-days') || '90', 10)
        };
      }
      return {
        code: slotEl.value || slotEl.getAttribute('value') || '',
        minDays: parseInt(slotEl.getAttribute('data-min-days') || '3', 10),
        maxDays: parseInt(slotEl.getAttribute('data-max-days') || '90', 10)
      };
    }

    function syncCategoryOptions() {
      if (!categoryEl || categoryEl.tagName !== 'SELECT') {
        return;
      }
      var meta = readSlotMeta();
      if (!meta || !meta.code) {
        return;
      }
      var current = categoryEl.value;
      var cats = categoryMap[meta.code] || [];
      categoryEl.innerHTML = '<option value="">전체 (타깃 없음)</option>';
      cats.forEach(function (cat) {
        var opt = document.createElement('option');
        opt.value = cat;
        opt.textContent = cat;
        if (cat === current) {
          opt.selected = true;
        }
        categoryEl.appendChild(opt);
      });
    }

    function updateQuote() {
      var meta = readSlotMeta();
      if (!meta || !meta.code) {
        return;
      }
      daysEl.min = String(meta.minDays);
      daysEl.max = String(meta.maxDays);
      if (parseInt(daysEl.value, 10) < meta.minDays) {
        daysEl.value = String(meta.minDays);
      }
      if (parseInt(daysEl.value, 10) > meta.maxDays) {
        daysEl.value = String(meta.maxDays);
      }

      var fd = new FormData();
      fd.append('action', 'quote');
      fd.append('slot_code', meta.code);
      fd.append('days', daysEl.value);
      if (bidEl) {
        fd.append('bid_bonus', bidEl.value || '0');
      }
      postJson(form.action, fd).then(function (data) {
        if (data.ok) {
          quoteEl.textContent = Number(data.total_points).toLocaleString() + 'P';
        }
      });
    }

    if (slotEl.tagName === 'SELECT') {
      slotEl.addEventListener('change', function () {
        syncCategoryOptions();
        updateQuote();
      });
    }
    daysEl.addEventListener('input', updateQuote);
    if (bidEl) {
      bidEl.addEventListener('input', updateQuote);
    }
    syncCategoryOptions();
    updateQuote();
  }

  function initShopLink(form) {
    var shopEl = qs('#ad_shop_wr_id', form);
    var linkEl = qs('#ad_link_url', form);
    if (!shopEl || !linkEl) {
      return;
    }
    shopEl.addEventListener('change', function () {
      var opt = shopEl.options[shopEl.selectedIndex];
      var url = opt ? opt.getAttribute('data-shop-url') : '';
      if (url) {
        linkEl.value = url;
      }
    });
  }

  function initAiPanel(root) {
    var aiUrl = root.getAttribute('data-ai-url');
    if (!aiUrl) {
      return;
    }

    var copyBtn = qs('[data-ad-ai-copy]', root);
    var imageBtn = qs('[data-ad-ai-image]', root);
    var topicEl = qs('[data-ad-ai-topic]', root);
    var toneEl = qs('[data-ad-ai-tone]', root);
    var offerEl = qs('[data-ad-ai-offer]', root);
    var statusEl = qs('[data-ad-ai-status]', root);
    var previewEl = qs('[data-ad-ai-preview]', root);
    var titleEl = qs('#ad_title', root) || qs('[name="title"]', root);
    var descEl = qs('#ad_description', root) || qs('[name="description"]', root);
    var buttonEl = qs('#ad_button_text', root) || qs('[name="button_text"]', root);
    var imageUrlEl = qs('#ad_image_url', root) || qs('[name="image_url"]', root);
    var slotEl = qs('[data-ad-platform-slot]', root) || qs('[name="slot_code"]', root);

    function setStatus(message, isError) {
      if (!statusEl) {
        return;
      }
      statusEl.textContent = message || '';
      statusEl.classList.toggle('is-error', !!isError);
    }

    function baseFormData(action) {
      var fd = new FormData();
      fd.append('action', action);
      if (slotEl) {
        fd.append('slot_code', slotEl.value || slotEl.getAttribute('value') || '');
      }
      if (topicEl) {
        fd.append('topic', topicEl.value.trim());
      }
      if (toneEl) {
        fd.append('tone', toneEl.value);
      }
      if (offerEl) {
        fd.append('offer', offerEl.value.trim());
      }
      if (titleEl) {
        fd.append('title', titleEl.value.trim());
      }
      return fd;
    }

    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        setStatus('AI 문안 생성 중…');
        copyBtn.disabled = true;
        postJson(aiUrl, baseFormData('generate_copy'))
          .then(function (data) {
            if (!data.success) {
              setStatus(data.message || '생성 실패', true);
              return;
            }
            if (titleEl && data.data.title) {
              titleEl.value = data.data.title;
            }
            if (descEl && data.data.description) {
              descEl.value = data.data.description;
            }
            if (buttonEl && data.data.button_text) {
              buttonEl.value = data.data.button_text;
            }
            setStatus('AI 문안이 적용되었습니다.');
          })
          .catch(function () {
            setStatus('네트워크 오류', true);
          })
          .finally(function () {
            copyBtn.disabled = false;
          });
      });
    }

    if (imageBtn) {
      imageBtn.addEventListener('click', function () {
        setStatus('AI 이미지 생성 중… (30~60초)');
        imageBtn.disabled = true;
        postJson(aiUrl, baseFormData('generate_image'))
          .then(function (data) {
            if (!data.success) {
              setStatus(data.message || '생성 실패', true);
              return;
            }
            if (imageUrlEl && data.data.image_url) {
              imageUrlEl.value = data.data.image_url;
            }
            if (previewEl) {
              previewEl.innerHTML = '<img src="' + data.data.image_url + '" alt="AI 생성 광고 이미지">';
              previewEl.hidden = false;
            }
            setStatus('AI 이미지가 적용되었습니다.');
          })
          .catch(function () {
            setStatus('네트워크 오류', true);
          })
          .finally(function () {
            imageBtn.disabled = false;
          });
      });
    }
  }

  function initRegisterForm() {
    var form = qs('#adPlatformRegisterForm');
    if (!form) {
      return;
    }
    initQuote(form);
    initShopLink(form);
    initAiPanel(form.closest('.ad-platform-register') || form);

    var statusEl = qs('[data-ad-platform-status]', form);
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      if (statusEl) {
        statusEl.textContent = '신청 중…';
      }
      postJson(form.action, new FormData(form))
        .then(function (data) {
          if (statusEl) {
            statusEl.textContent = data.message || (data.ok ? '신청되었습니다.' : '신청 실패');
          }
          if (data.ok) {
            window.setTimeout(function () {
              window.location.reload();
            }, 700);
          }
        })
        .catch(function () {
          if (statusEl) {
            statusEl.textContent = '네트워크 오류';
          }
        });
    });
  }

  function initEditForm() {
    var form = qs('#adPlatformEditForm');
    if (!form) {
      return;
    }

    initQuote(form);
    initShopLink(form);
    initAiPanel(form.closest('.ad-platform-edit') || form);

    var statusEl = qs('[data-ad-platform-status]', form);
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      if (statusEl) {
        statusEl.textContent = '저장 중…';
      }
      postJson(form.action, new FormData(form))
        .then(function (data) {
          if (statusEl) {
            statusEl.textContent = data.message || (data.ok ? '저장되었습니다.' : '저장 실패');
          }
          if (data.ok) {
            window.setTimeout(function () {
              window.location.href = form.getAttribute('data-back-url') || window.location.href;
            }, 700);
          }
        })
        .catch(function () {
          if (statusEl) {
            statusEl.textContent = '네트워크 오류';
          }
        });
    });

    var extendForm = qs('#adPlatformExtendForm');
    if (extendForm) {
      var extendDaysEl = qs('[data-ad-extend-days]', extendForm);
      var extendQuoteEl = qs('[data-ad-extend-quote]', extendForm);
      var extendStatusEl = qs('[data-ad-extend-status]', extendForm);

      function updateExtendQuote() {
        var fd = new FormData();
        fd.append('action', 'extend_quote');
        fd.append('ad_id', extendForm.querySelector('[name="ad_id"]').value);
        fd.append('extra_days', extendDaysEl.value);
        postJson(extendForm.action, fd).then(function (data) {
          if (data.ok && extendQuoteEl) {
            extendQuoteEl.textContent = Number(data.extra_points).toLocaleString() + 'P';
          }
        });
      }

      if (extendDaysEl) {
        extendDaysEl.addEventListener('input', updateExtendQuote);
        updateExtendQuote();
      }

      extendForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (extendStatusEl) {
          extendStatusEl.textContent = '연장 처리 중…';
        }
        postJson(extendForm.action, new FormData(extendForm))
          .then(function (data) {
            if (extendStatusEl) {
              extendStatusEl.textContent = data.message || (data.ok ? '연장되었습니다.' : '연장 실패');
            }
            if (data.ok) {
              window.setTimeout(function () {
                window.location.reload();
              }, 700);
            }
          })
          .catch(function () {
            if (extendStatusEl) {
              extendStatusEl.textContent = '네트워크 오류';
            }
          });
      });
    }

    qsa('[data-ad-cancel]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!window.confirm('광고 신청을 취소하시겠습니까?')) {
          return;
        }
        var fd = new FormData();
        fd.append('action', 'cancel');
        fd.append('ad_id', btn.getAttribute('data-ad-id'));
        postJson(form.action, fd).then(function (data) {
          alert(data.message || (data.ok ? '취소되었습니다.' : '취소 실패'));
          if (data.ok) {
            window.location.href = form.getAttribute('data-back-url') || window.location.href;
          }
        });
      });
    });
  }

  function init() {
    initRegisterForm();
    initEditForm();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window, document);
