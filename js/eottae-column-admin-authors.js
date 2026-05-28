(function () {
  'use strict';

  var root = document.querySelector('.sebu-column-admin-authors');
  if (!root) {
    return;
  }

  var procUrl = root.getAttribute('data-proc-url');
  var adminToken = root.getAttribute('data-admin-token');
  var listFilter = root.getAttribute('data-list-filter') || 'all';
  var listSearch = root.getAttribute('data-list-search') || '';
  var dialog = document.getElementById('sebuColumnMemoDialog');
  var memoForm = dialog ? dialog.querySelector('[data-sebu-column-memo-form]') : null;
  var memoScopeInput = dialog ? dialog.querySelector('[data-sebu-column-memo-scope]') : null;
  var memoTitle = dialog ? dialog.querySelector('[data-sebu-column-memo-title]') : null;
  var memoDesc = dialog ? dialog.querySelector('[data-sebu-column-memo-desc]') : null;
  var selectedMbIds = [];
  var selectedCountEl = root.querySelector('[data-sebu-column-selected-count]');
  var selectedMemoBtn = root.querySelector('[data-sebu-column-selected-memo]');

  function postAdmin(data) {
    data.admin_token = adminToken;
    return fetch(procUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams(data).toString(),
      credentials: 'same-origin'
    }).then(function (res) { return res.json(); });
  }

  function updateSelectedCount() {
    var boxes = root.querySelectorAll('.sebu-column-author-select:checked:not(:disabled)');
    selectedMbIds = [];
    boxes.forEach(function (box) {
      if (box.value) {
        selectedMbIds.push(box.value);
      }
    });
    if (selectedCountEl) {
      selectedCountEl.textContent = String(selectedMbIds.length);
    }
    if (selectedMemoBtn) {
      selectedMemoBtn.disabled = selectedMbIds.length < 1;
    }
  }

  function openMemoDialog(options) {
    if (!dialog || !memoForm) {
      return;
    }
    options = options || {};
    var scope = options.scope || 'selected';
    if (memoScopeInput) {
      memoScopeInput.value = scope;
    }
    if (memoTitle) {
      memoTitle.textContent = options.title || '쪽지 발송';
    }
    if (memoDesc) {
      memoDesc.textContent = options.desc || '선택한 칼럼니스트에게 사이트 쪽지가 발송됩니다.';
    }
    var textarea = memoForm.querySelector('[name="memo_body"]');
    if (textarea && options.clear !== false) {
      textarea.value = '';
    }
    dialog.dataset.mbIds = options.mbIds ? options.mbIds.join(',') : '';
    if (typeof dialog.showModal === 'function') {
      dialog.showModal();
    } else {
      dialog.setAttribute('open', 'open');
    }
  }

  function closeMemoDialog() {
    if (!dialog) {
      return;
    }
    if (typeof dialog.close === 'function') {
      dialog.close();
    } else {
      dialog.removeAttribute('open');
    }
  }

  root.querySelectorAll('[data-sebu-column-memo-close]').forEach(function (btn) {
    btn.addEventListener('click', closeMemoDialog);
  });

  root.querySelectorAll('.sebu-column-author-select').forEach(function (box) {
    box.addEventListener('change', updateSelectedCount);
  });

  var selectAll = root.querySelector('[data-sebu-column-select-all]');
  if (selectAll) {
    selectAll.addEventListener('change', function () {
      var checked = selectAll.checked;
      root.querySelectorAll('.sebu-column-author-select:not(:disabled)').forEach(function (box) {
        box.checked = checked;
      });
      updateSelectedCount();
    });
  }

  var bulkMemoBtn = root.querySelector('[data-sebu-column-bulk-memo]');
  if (bulkMemoBtn) {
    bulkMemoBtn.addEventListener('click', function () {
      openMemoDialog({
        scope: 'all_active',
        title: '활성 칼럼니스트 전체 쪽지',
        desc: '활성 상태인 칼럼니스트 전원에게 쪽지를 보냅니다. (탈퇴·차단 회원 제외)',
        clear: true
      });
      if (memoScopeInput) {
        memoScopeInput.value = 'all_active';
      }
    });
  }

  var selectedMemoBtnEl = root.querySelector('[data-sebu-column-selected-memo]');
  if (selectedMemoBtnEl) {
    selectedMemoBtnEl.addEventListener('click', function () {
    if (selectedMbIds.length < 1) {
      return;
    }
    openMemoDialog({
      scope: 'selected',
      title: '선택 칼럼니스트 쪽지 (' + selectedMbIds.length + '명)',
      desc: selectedMbIds.join(', ') + ' 님에게 쪽지를 보냅니다.',
      mbIds: selectedMbIds,
      clear: true
    });
    });
  }

  root.querySelectorAll('[data-sebu-column-single-memo]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var mbId = btn.getAttribute('data-mb-id');
      var name = btn.getAttribute('data-display-name') || mbId;
      if (!mbId) {
        return;
      }
      openMemoDialog({
        scope: 'selected',
        title: name + ' 님에게 쪽지',
        desc: mbId + ' 님에게 개별 쪽지를 보냅니다.',
        mbIds: [mbId],
        clear: true
      });
    });
  });

  if (memoForm) {
    memoForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var scope = memoScopeInput ? memoScopeInput.value : 'selected';
      var body = memoForm.querySelector('[name="memo_body"]');
      var memoBody = body ? body.value.trim() : '';
      if (!memoBody) {
        alert('메시지 내용을 입력해 주세요.');
        return;
      }

      var mbIds = dialog && dialog.dataset.mbIds ? dialog.dataset.mbIds.split(',').filter(Boolean) : selectedMbIds;
      if (scope === 'selected' && mbIds.length < 1) {
        mbIds = selectedMbIds;
      }

      var data = {
        action: 'send_author_memo',
        memo_scope: scope,
        memo_body: memoBody,
        list_search: listSearch
      };
      mbIds.forEach(function (id, index) {
        data['mb_ids[' + index + ']'] = id;
      });

      postAdmin(data).then(function (res) {
        alert(res.message || (res.success ? '발송했습니다.' : '발송에 실패했습니다.'));
        if (res.success) {
          closeMemoDialog();
          if (body) {
            body.value = '';
          }
        }
      });
    });
  }

  root.querySelectorAll('[data-sebu-column-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var row = btn.closest('[data-sebu-column-author-row]');
      if (!row) {
        return;
      }
      var mbId = row.getAttribute('data-mb-id');
      var field = btn.getAttribute('data-sebu-column-toggle');
      var value = btn.getAttribute('data-value') || '1';
      postAdmin({
        action: 'toggle_author_flag',
        mb_id: mbId,
        field: field,
        value: value
      }).then(function (res) {
        if (!res.success) {
          alert(res.message || '저장에 실패했습니다.');
          return;
        }
        var on = String(res.value) === '1';
        btn.classList.toggle('is-on', on);
        btn.setAttribute('data-value', on ? '0' : '1');
      });
    });
  });

  var authorForm = root.querySelector('[data-sebu-column-author-form]');
  if (authorForm) {
    authorForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(authorForm);
      fd.append('admin_token', adminToken);
      fetch(procUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (res) {
          alert(res.message || (res.success ? '저장되었습니다.' : '실패했습니다.'));
          if (res.success) {
            var url = new URL(window.location.href);
            if (!url.searchParams.get('edit')) {
              window.location.reload();
              return;
            }
            url.searchParams.delete('edit');
            window.location.href = url.toString();
          }
        });
    });
  }

  updateSelectedCount();
})();
