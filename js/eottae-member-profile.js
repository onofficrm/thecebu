/**
 * 회원가입/정보수정 — 프로필 사진 미리보기·AI 생성
 */
(function () {
  'use strict';

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function setStatus(root, message, isError) {
    var el = qs('[data-profile-ai-status]', root);
    if (!el) {
      return;
    }
    if (!message) {
      el.hidden = true;
      el.textContent = '';
      el.classList.remove('is-error');
      return;
    }
    el.hidden = false;
    el.textContent = message;
    el.classList.toggle('is-error', !!isError);
  }

  function updatePreview(root, url) {
    var preview = qs('[data-profile-preview]', root);
    var img = qs('[data-profile-preview-img]', root);
    var initial = qs('[data-profile-preview-initial]', root);
    if (!preview || !img || !initial) {
      return;
    }

    if (url) {
      img.src = url;
      img.hidden = false;
      initial.hidden = true;
      preview.classList.add('has-image');
      setPickLabel(root, true);
      var del = qs('[data-profile-delete-checkbox]', root);
      if (del) {
        del.checked = false;
      }
      return;
    }

    img.removeAttribute('src');
    img.hidden = true;
    initial.hidden = false;
    preview.classList.remove('has-image');
    setPickLabel(root, false);
  }

  function syncInitial(root) {
    var nickInput = document.getElementById('reg_mb_nick');
    var initial = qs('[data-profile-preview-initial]', root);
    if (!nickInput || !initial) {
      return;
    }
    var nick = (nickInput.value || '').trim();
    initial.textContent = nick ? nick.charAt(0) : '?';
  }

  function setFilename(root, text) {
    var el = qs('[data-profile-filename]', root);
    if (el) {
      el.textContent = text || '선택된 사진이 없습니다.';
    }
  }

  function setPickLabel(root, hasImage) {
    var el = qs('[data-profile-pick-label]', root);
    if (el) {
      el.textContent = hasImage ? '사진 변경' : '사진 선택';
    }
  }

  function bindProfileField(root) {
    if (!root || root.getAttribute('data-eottae-member-profile-bound') === '1') {
      return;
    }
    root.setAttribute('data-eottae-member-profile-bound', '1');

    var fileInput = qs('[data-profile-file-input]', root);
    var aiTmpInput = qs('[data-profile-ai-tmp]', root);
    var aiBtn = qs('[data-profile-ai-generate]', root);
    var procUrl = root.getAttribute('data-proc-url') || '';
    var token = root.getAttribute('data-token') || '';
    var aiEnabled = root.getAttribute('data-ai-enabled') === '1';

    if (fileInput) {
      fileInput.addEventListener('change', function () {
        if (aiTmpInput) {
          aiTmpInput.value = '';
        }
        setStatus(root, '', false);
        if (!fileInput.files || !fileInput.files[0]) {
          if (!aiTmpInput || !aiTmpInput.value) {
            updatePreview(root, '');
            setFilename(root, '선택된 사진이 없습니다.');
          }
          return;
        }
        var file = fileInput.files[0];
        setFilename(root, file.name);
        var reader = new FileReader();
        reader.onload = function (ev) {
          updatePreview(root, ev.target && ev.target.result ? ev.target.result : '');
        };
        reader.readAsDataURL(file);
      });
    }

    var nickInput = document.getElementById('reg_mb_nick');
    if (nickInput) {
      nickInput.addEventListener('input', function () {
        if (!qs('[data-profile-preview-img]', root) || qs('[data-profile-preview-img]', root).hidden) {
          syncInitial(root);
        }
      });
    }

    var delCheckbox = qs('[data-profile-delete-checkbox]', root);
    if (delCheckbox) {
      delCheckbox.addEventListener('change', function () {
        if (delCheckbox.checked) {
          if (fileInput) {
            fileInput.value = '';
          }
          if (aiTmpInput) {
            aiTmpInput.value = '';
          }
          updatePreview(root, '');
          setFilename(root, '선택된 사진이 없습니다.');
          setStatus(root, '', false);
        }
      });
    }

    if (!aiEnabled || !aiBtn || !procUrl) {
      return;
    }

    aiBtn.addEventListener('click', function () {
      var nick = nickInput ? (nickInput.value || '').trim() : '';
      if (!nick) {
        alert('닉네임을 먼저 입력해 주세요.');
        if (nickInput) {
          nickInput.focus();
        }
        return;
      }

      var form = root.closest('form');
      var audienceInput = form ? form.querySelector('[name="mb_2"]:checked, [name="mb_2"]') : null;
      var roleInput = form ? form.querySelector('[name="mb_1"]:checked, [name="mb_1"]') : null;
      var audience = audienceInput ? audienceInput.value : '';
      var role = roleInput ? roleInput.value : '';
      var wInput = form ? form.querySelector('[name="w"]') : null;
      var w = wInput ? wInput.value : '';

      aiBtn.disabled = true;
      setStatus(root, 'AI가 프로필 사진을 만들고 있습니다…', false);

      var fd = new FormData();
      fd.append('eottae_profile_ai_token', token);
      fd.append('mb_nick', nick);
      fd.append('mb_2', audience);
      fd.append('mb_1', role);
      fd.append('w', w);

      fetch(procUrl, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (json) {
          if (!json || !json.success || !json.data) {
            throw new Error((json && json.message) ? json.message : 'AI 프로필 생성에 실패했습니다.');
          }
          if (fileInput) {
            fileInput.value = '';
          }
          if (aiTmpInput) {
            aiTmpInput.value = json.data.tmp || '';
          }
          updatePreview(root, json.data.url || '');
          setFilename(root, 'AI 프로필 미리보기 (저장 시 반영)');
          setStatus(root, 'AI 프로필이 적용되었습니다. 저장하면 반영됩니다.', false);
        })
        .catch(function (err) {
          setStatus(root, err.message || 'AI 프로필 생성에 실패했습니다.', true);
        })
        .finally(function () {
          aiBtn.disabled = false;
        });
    });
  }

  function init() {
    document.querySelectorAll('[data-eottae-member-profile="1"]').forEach(bindProfileField);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}());
