(function () {
  'use strict';

  var cfg = window.EOTTaeGolfJoinDetail || {};
  var root = document.getElementById('golf-join-detail');

  function toast(message) {
    if (typeof window.showEottaeToast === 'function') {
      window.showEottaeToast(message);
      return;
    }
    alert(message);
  }

  function openSheet(sheet) {
    if (!sheet) {
      return;
    }
    sheet.removeAttribute('hidden');
    document.body.classList.add('golf-join-sheet-open');
  }

  function closeSheet(sheet) {
    if (!sheet) {
      return;
    }
    sheet.setAttribute('hidden', '');
    if (!document.querySelector('.golf-join-sheet:not([hidden])')) {
      document.body.classList.remove('golf-join-sheet-open');
    }
  }

  document.querySelectorAll('.golf-join-sheet [data-sheet-close]').forEach(function (el) {
    el.addEventListener('click', function () {
      closeSheet(el.closest('.golf-join-sheet'));
    });
  });

  function postForm(url, data) {
    var body = new URLSearchParams();
    Object.keys(data).forEach(function (key) {
      if (data[key] !== undefined && data[key] !== null) {
        body.append(key, data[key]);
      }
    });

    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: body.toString(),
    }).then(function (res) {
      return res.json();
    });
  }

  function handleResult(result) {
    if (!result) {
      toast('처리에 실패했습니다.');
      return;
    }
    if (result.success) {
      toast(result.message || '처리되었습니다.');
      if (result.reload) {
        setTimeout(function () {
          window.location.reload();
        }, 600);
      }
      return;
    }
    toast(result.message || '처리에 실패했습니다.');
    if (result.redirect) {
      setTimeout(function () {
        window.location.href = result.redirect;
      }, 800);
    }
  }

  var shareBtn = document.getElementById('golf-join-share');
  if (shareBtn) {
    shareBtn.addEventListener('click', function () {
      var url = shareBtn.getAttribute('data-share-url') || window.location.href;
      var title = shareBtn.getAttribute('data-share-title') || document.title;
      if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(function () {});
        return;
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
          toast('링크가 복사되었습니다.');
        });
        return;
      }
      prompt('아래 링크를 복사하세요.', url);
    });
  }

  var loginSheet = document.getElementById('golf-join-login-sheet');
  var applySheet = document.getElementById('golf-join-apply-sheet');
  var applyMessage = document.getElementById('golf-join-apply-message');

  var guestBtn = document.getElementById('golf-join-apply-guest-btn');
  if (guestBtn) {
    guestBtn.addEventListener('click', function () {
      openSheet(loginSheet);
    });
  }

  var applyBtn = document.getElementById('golf-join-apply-btn');
  if (applyBtn) {
    applyBtn.addEventListener('click', function () {
      openSheet(applySheet);
      if (applyMessage) {
        applyMessage.focus();
      }
    });
  }

  var applyConfirm = document.getElementById('golf-join-apply-confirm');
  if (applyConfirm) {
    applyConfirm.addEventListener('click', function () {
      if (!cfg.memberToken) {
        openSheet(loginSheet);
        return;
      }
      applyConfirm.disabled = true;
      postForm(cfg.memberProcUrl, {
        action: 'apply',
        join_id: cfg.joinId,
        eottae_golf_join_token: cfg.memberToken,
        message: applyMessage ? applyMessage.value : '',
      })
        .then(handleResult)
        .catch(function () {
          toast('신청 처리에 실패했습니다.');
        })
        .finally(function () {
          applyConfirm.disabled = false;
          closeSheet(applySheet);
        });
    });
  }

  var cancelBtn = document.getElementById('golf-join-cancel-btn');
  if (cancelBtn) {
    cancelBtn.addEventListener('click', function () {
      if (!window.confirm('조인 신청을 취소하시겠습니까?')) {
        return;
      }
      cancelBtn.disabled = true;
      postForm(cfg.memberProcUrl, {
        action: 'cancel_apply',
        join_id: cfg.joinId,
        eottae_golf_join_token: cfg.memberToken,
      })
        .then(handleResult)
        .catch(function () {
          toast('취소 처리에 실패했습니다.');
        })
        .finally(function () {
          cancelBtn.disabled = false;
        });
    });
  }

  var closeRecruitBtn = document.getElementById('golf-join-close-btn');
  if (closeRecruitBtn) {
    closeRecruitBtn.addEventListener('click', function () {
      if (!window.confirm('모집을 마감하시겠습니까?')) {
        return;
      }
      closeRecruitBtn.disabled = true;
      postForm(cfg.ownerProcUrl, {
        action: 'close',
        join_id: cfg.joinId,
        eottae_golf_join_owner_token: cfg.ownerToken,
      })
        .then(handleResult)
        .catch(function () {
          toast('모집 마감에 실패했습니다.');
        })
        .finally(function () {
          closeRecruitBtn.disabled = false;
        });
    });
  }

  if (cfg.isHost && root) {
    root.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-action][data-member-id]');
      if (!btn) {
        return;
      }
      var action = btn.getAttribute('data-action');
      var memberId = btn.getAttribute('data-member-id');
      if (!action || !memberId) {
        return;
      }

      var confirmMsg = action === 'approve' ? '이 신청을 승인하시겠습니까?' : '이 신청을 거절하시겠습니까?';
      if (!window.confirm(confirmMsg)) {
        return;
      }

      btn.disabled = true;
      postForm(cfg.ownerProcUrl, {
        action: action,
        join_id: cfg.joinId,
        member_id: memberId,
        eottae_golf_join_owner_token: cfg.ownerToken,
      })
        .then(handleResult)
        .catch(function () {
          toast('처리에 실패했습니다.');
        })
        .finally(function () {
          btn.disabled = false;
        });
    });
  }
})();
