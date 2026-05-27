(function () {
  'use strict';

  var cfg = window.EOTTaeGolfJoinCreate || {};
  var courses = cfg.courses || [];
  var registerModes = cfg.registerModes || {};
  var scheduleSlotsMap = cfg.scheduleSlots || {};
  var venueTypesMeta = cfg.venueTypes || {};

  var form = document.getElementById('golf-join-create-form');
  var modeSheet = document.getElementById('golf-join-mode-sheet');
  var modeInput = document.getElementById('golf-join-register-mode');
  var modeHint = document.getElementById('golf-join-mode-hint');
  var modeLabel = document.getElementById('golf-join-mode-label');
  var modeChangeBtn = document.getElementById('golf-join-mode-change');
  var dateInput = document.getElementById('golf-join-date-input');
  var roundDateHidden = document.getElementById('golf-join-round-date');
  var teeTimeWrap = document.getElementById('golf-join-tee-time-wrap');
  var courseList = document.getElementById('golf-join-course-list');
  var courseCustom = document.getElementById('golf-join-course-custom');
  var submitBtn = document.getElementById('golf-join-submit-btn');

  var hiddenMap = {
    venue_type: 'golf-join-venue-type',
    schedule_slot: 'golf-join-schedule-slot',
    recruit_slots: 'golf-join-recruit-slots',
    gender_preference: 'golf-join-gender-pref',
    age_preference: 'golf-join-age-pref',
    score_preference: 'golf-join-score-pref',
    host_gender: 'golf-join-host-gender',
    host_age_group: 'golf-join-host-age',
    host_score_range: 'golf-join-host-score',
  };

  function getHidden(id) {
    return document.getElementById(id);
  }

  function setHiddenField(group, value) {
    var id = hiddenMap[group];
    if (id) {
      var el = getHidden(id);
      if (el) {
        el.value = value;
      }
    }
    if (group === 'round_date' && roundDateHidden) {
      roundDateHidden.value = value;
    }
  }

  function clearShopSelection() {
    getHidden('golf-join-course-id').value = '0';
    getHidden('golf-join-course-name').value = '';
    getHidden('golf-join-shop-bo-table').value = '';
    getHidden('golf-join-shop-wr-id').value = '0';
    getHidden('golf-join-region').value = '';
  }

  function setShopSelection(course) {
    getHidden('golf-join-course-id').value = String(course.id || 0);
    getHidden('golf-join-course-name').value = course.name || '';
    getHidden('golf-join-shop-bo-table').value = course.shop_bo_table || '';
    getHidden('golf-join-shop-wr-id').value = String(course.shop_wr_id || course.id || 0);
    getHidden('golf-join-region').value = course.region || '';
  }

  function getVenueType() {
    var el = getHidden('golf-join-venue-type');
    return el && el.value ? el.value : 'golf';
  }

  function renderScheduleSlots(venueType) {
    var grid = document.getElementById('golf-join-schedule-slot-grid');
    if (!grid) {
      return;
    }
    var slots = scheduleSlotsMap[venueType] || scheduleSlotsMap.golf || {};
    var codes = Object.keys(slots);
    grid.className =
      'golf-join-option-grid golf-join-option-grid--' + (codes.length >= 4 ? '4' : '3');
    grid.innerHTML = '';
    codes.forEach(function (code) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'golf-join-option-btn';
      btn.setAttribute('data-value', code);
      btn.textContent = slots[code];
      grid.appendChild(btn);
    });
    getHidden('golf-join-schedule-slot').value = '';
  }

  function applyVenueType(venueType) {
    venueType = venueType || 'golf';
    var venueInput = getHidden('golf-join-venue-type');
    if (venueInput) {
      venueInput.value = venueType;
    }

    var isScreen = venueType === 'screen_golf';
    var venueLabel = document.getElementById('golf-join-venue-label');
    var venueHint = document.getElementById('golf-join-venue-hint');
    var scheduleHint = document.getElementById('golf-join-schedule-hint');
    if (venueLabel) {
      venueLabel.innerHTML =
        (isScreen ? '스크린골프장' : '골프장') +
        ' <span class="golf-join-create-required">*</span>';
    }
    if (venueHint) {
      venueHint.textContent = isScreen
        ? '업체정보에 등록된 스크린골프장이 표시됩니다.'
        : '업체정보에 등록된 골프장이 자동으로 표시됩니다.';
    }
    if (scheduleHint) {
      scheduleHint.hidden = !isScreen;
    }
    if (courseCustom) {
      courseCustom.placeholder = isScreen ? '스크린골프장명 직접 입력' : '골프장명 직접 입력';
    }

    renderScheduleSlots(venueType);
    clearShopSelection();
    if (courseCustom) {
      courseCustom.value = '';
    }
    if (courseList) {
      courseList.querySelectorAll('.golf-join-course-item').forEach(function (el) {
        el.classList.remove('is-active');
      });
    }
    renderCourses();
  }

  function showModeSheet(show) {
    if (!modeSheet) {
      return;
    }
    if (show) {
      modeSheet.removeAttribute('hidden');
      document.body.classList.add('golf-join-sheet-open');
    } else {
      modeSheet.setAttribute('hidden', '');
      document.body.classList.remove('golf-join-sheet-open');
    }
  }

  function applyRegisterMode(mode) {
    if (!modeInput) {
      return;
    }
    modeInput.value = mode;
    var meta = registerModes[mode];
    if (modeLabel && meta) {
      modeLabel.textContent = meta.title || '';
    }
    if (modeHint) {
      modeHint.removeAttribute('hidden');
    }
    if (teeTimeWrap) {
      if (mode === 'fixed_tee') {
        teeTimeWrap.removeAttribute('hidden');
      } else {
        teeTimeWrap.setAttribute('hidden', '');
        var tee = document.getElementById('golf-join-tee-time');
        if (tee) {
          tee.value = '';
        }
      }
    }
    if (mode === 'members_first') {
      var unknownBtn = document.querySelector('[data-option-group="schedule_slot"] [data-value="unknown"]');
      if (unknownBtn) {
        selectOptionButton(unknownBtn);
      }
    }
  }

  function selectOptionButton(btn) {
    if (!btn) {
      return;
    }
    var group = btn.closest('[data-option-group]');
    if (!group) {
      return;
    }
    var groupName = group.getAttribute('data-option-group');
    var isMulti = group.getAttribute('data-multi') === '1';
    var max = parseInt(group.getAttribute('data-max') || '99', 10);

    if (isMulti) {
      btn.classList.toggle('is-active');
      var selected = group.querySelectorAll('.golf-join-option-btn.is-active');
      if (selected.length > max) {
        btn.classList.remove('is-active');
        alert('분위기 태그는 최대 ' + max + '개까지 선택할 수 있습니다.');
        return;
      }
      syncMoodTagsHidden(group);
      return;
    }

    group.querySelectorAll('.golf-join-option-btn').forEach(function (b) {
      b.classList.remove('is-active');
    });
    btn.classList.add('is-active');

    if (groupName === 'venue_type') {
      applyVenueType(btn.getAttribute('data-value') || 'golf');
      return;
    }

    if (hiddenMap[groupName]) {
      setHiddenField(groupName, btn.getAttribute('data-value') || '');
    }

    if (groupName === 'schedule_slot') {
      var slot = btn.getAttribute('data-value');
      if (slot === 'unknown' && teeTimeWrap) {
        teeTimeWrap.setAttribute('hidden', '');
      } else if (modeInput && modeInput.value === 'fixed_tee' && teeTimeWrap) {
        teeTimeWrap.removeAttribute('hidden');
      }
    }

    if (groupName === 'host_gender' || groupName === 'host_age_group' || groupName === 'host_score_range') {
      updateProfileLabels();
    }
  }

  function syncMoodTagsHidden(group) {
    var active = group.querySelectorAll('.golf-join-option-btn.is-active');
    var values = [];
    active.forEach(function (btn) {
      values.push(btn.getAttribute('data-value'));
    });
    var existing = form.querySelectorAll('input[name="mood_tags[]"]');
    existing.forEach(function (el) {
      el.remove();
    });
    values.forEach(function (val) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'mood_tags[]';
      input.value = val;
      form.appendChild(input);
    });
  }

  function renderCourses() {
    if (!courseList) {
      return;
    }
    courseList.innerHTML = '';

    var venueType = getVenueType();
    var filtered = courses.filter(function (course) {
      return (course.venue_type || 'golf') === venueType;
    });

    if (!filtered.length) {
      courseList.innerHTML =
        '<p class="golf-join-create-field__hint">업체정보에 등록된 ' +
        (venueType === 'screen_golf' ? '스크린골프장' : '골프장') +
        '이 없습니다. 아래에 직접 입력해 주세요.</p>';
      return;
    }

    filtered.forEach(function (course) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'golf-join-course-item';
      btn.setAttribute('data-course-id', String(course.id));
      btn.setAttribute('role', 'option');

      var thumbHtml = course.thumb_url
        ? '<span class="golf-join-course-item__thumb"><img src="' +
          escapeHtml(course.thumb_url) +
          '" alt="" loading="lazy" decoding="async"></span>'
        : '<span class="golf-join-course-item__thumb golf-join-course-item__thumb--empty" aria-hidden="true">' +
          (course.venue_type === 'screen_golf' ? '🖥' : '⛳') +
          '</span>';

      var meta = course.region_label || course.address || '';
      btn.innerHTML =
        thumbHtml +
        '<span class="golf-join-course-item__body">' +
        '<span class="golf-join-course-item__name">' +
        escapeHtml(course.name) +
        '</span>' +
        (meta ? '<span class="golf-join-course-item__meta">' + escapeHtml(meta) + '</span>' : '') +
        '</span>';

      btn.addEventListener('click', function () {
        courseList.querySelectorAll('.golf-join-course-item').forEach(function (el) {
          el.classList.remove('is-active');
        });
        btn.classList.add('is-active');
        setShopSelection(course);
        if (courseCustom) {
          courseCustom.value = '';
        }
      });
      courseList.appendChild(btn);
    });
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function updateProfileLabels() {
    var labels = cfg.hostLabels || {};
    var gender = getHidden('golf-join-host-gender').value;
    var age = getHidden('golf-join-host-age').value;
    var score = getHidden('golf-join-host-score').value;
    var gEl = document.getElementById('golf-join-profile-gender-label');
    var aEl = document.getElementById('golf-join-profile-age-label');
    var sEl = document.getElementById('golf-join-profile-score-label');
    if (gEl) {
      gEl.textContent = (labels.gender && labels.gender[gender]) || '미입력';
    }
    if (aEl) {
      aEl.textContent = (labels.age && labels.age[age]) || '미입력';
    }
    if (sEl) {
      sEl.textContent = (labels.score && labels.score[score]) || '미입력';
    }
  }

  function validateForm() {
    if (!modeInput || !modeInput.value) {
      alert('등록 방식을 선택해 주세요.');
      showModeSheet(true);
      return false;
    }
    if (!roundDateHidden || !roundDateHidden.value) {
      alert('라운드 날짜를 선택해 주세요.');
      dateInput && dateInput.focus();
      return false;
    }
    if (!getHidden('golf-join-schedule-slot').value) {
      alert('시간대를 선택해 주세요.');
      return false;
    }
    var shopWrId = parseInt(getHidden('golf-join-shop-wr-id').value || '0', 10);
    var custom = courseCustom ? courseCustom.value.trim() : '';
    if (shopWrId < 1 && !custom) {
      alert(getVenueType() === 'screen_golf' ? '스크린골프장을 선택하거나 직접 입력해 주세요.' : '골프장을 선택하거나 직접 입력해 주세요.');
      return false;
    }
    if (!getHidden('golf-join-recruit-slots').value) {
      alert('모집 인원을 선택해 주세요.');
      return false;
    }
    if (!getHidden('golf-join-gender-pref').value) {
      alert('성별 조건을 선택해 주세요.');
      return false;
    }
    if (!getHidden('golf-join-age-pref').value) {
      alert('나이 조건을 선택해 주세요.');
      return false;
    }
    if (!getHidden('golf-join-score-pref').value) {
      alert('타수 조건을 선택해 주세요.');
      return false;
    }
    var title = document.getElementById('golf-join-title');
    if (!title || !title.value.trim()) {
      alert('방 제목을 입력해 주세요.');
      title && title.focus();
      return false;
    }
    var desc = document.getElementById('golf-join-description');
    if (!desc || !desc.value.trim()) {
      alert('방 소개글을 입력해 주세요.');
      desc && desc.focus();
      return false;
    }
    var moodCount = form.querySelectorAll('input[name="mood_tags[]"]').length;
    if (moodCount > 3) {
      alert('분위기 태그는 최대 3개까지 선택할 수 있습니다.');
      return false;
    }
    return true;
  }

  document.querySelectorAll('[data-option-group]').forEach(function (group) {
    group.addEventListener('click', function (e) {
      var btn = e.target.closest('.golf-join-option-btn');
      if (!btn) {
        return;
      }
      selectOptionButton(btn);
    });
  });

  if (dateInput) {
    dateInput.addEventListener('change', function () {
      setHiddenField('round_date', dateInput.value);
    });
  }

  if (courseCustom) {
    courseCustom.addEventListener('input', function () {
      if (courseCustom.value.trim() !== '') {
        courseList.querySelectorAll('.golf-join-course-item').forEach(function (el) {
          el.classList.remove('is-active');
        });
        clearShopSelection();
      }
    });
  }

  document.querySelectorAll('[data-register-mode]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var mode = btn.getAttribute('data-register-mode');
      applyRegisterMode(mode);
      showModeSheet(false);
    });
  });

  if (modeChangeBtn) {
    modeChangeBtn.addEventListener('click', function () {
      showModeSheet(true);
    });
  }

  if (modeSheet) {
    modeSheet.querySelectorAll('[data-sheet-close]').forEach(function (el) {
      el.addEventListener('click', function () {
        if (modeInput && modeInput.value) {
          showModeSheet(false);
        }
      });
    });
  }

  var profileToggle = document.getElementById('golf-join-profile-toggle');
  var profileEdit = document.getElementById('golf-join-profile-edit');
  if (profileToggle && profileEdit) {
    profileToggle.addEventListener('click', function () {
      var open = profileEdit.hasAttribute('hidden');
      if (open) {
        profileEdit.removeAttribute('hidden');
        profileToggle.textContent = '닫기';
      } else {
        profileEdit.setAttribute('hidden', '');
        profileToggle.textContent = '수정';
      }
    });
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      var custom = courseCustom ? courseCustom.value.trim() : '';
      if (custom !== '') {
        clearShopSelection();
        getHidden('golf-join-region').value = 'cebu';
      } else if (parseInt(getHidden('golf-join-shop-wr-id').value || '0', 10) > 0) {
        if (courseCustom) {
          courseCustom.value = '';
        }
      }

      if (!validateForm()) {
        e.preventDefault();
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('is-loading');
      }
    });
  }

  document.querySelectorAll('[data-option-group="host_gender"] .is-active, [data-option-group="host_age_group"] .is-active, [data-option-group="host_score_range"] .is-active').forEach(function (btn) {
    var group = btn.closest('[data-option-group]').getAttribute('data-option-group');
    if (hiddenMap[group]) {
      setHiddenField(group, btn.getAttribute('data-value') || '');
    }
  });

  applyVenueType(getVenueType());
  updateProfileLabels();

  if (cfg.prefillMode) {
    applyRegisterMode(cfg.prefillMode);
  } else if (!modeInput || !modeInput.value) {
    showModeSheet(true);
  } else {
    applyRegisterMode(modeInput.value);
  }
})();
