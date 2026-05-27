/**
 * 세부어때 캘린더 — 폼·일정 상세 팝업
 */
(function () {
  'use strict';

  var ui = window.__EOTTae_CALENDAR_UI__ || {};
  var modal = null;
  var modalBody = null;
  var lastFocus = null;
  var modalInitialized = false;

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function initAllDayToggle(form) {
    var checkbox = form.querySelector('[data-sebu-cal-all-day]');
    var timeFields = form.querySelector('[data-sebu-cal-time-fields]');
    if (!checkbox || !timeFields) {
      return;
    }

    function sync() {
      if (checkbox.checked) {
        timeFields.setAttribute('hidden', 'hidden');
      } else {
        timeFields.removeAttribute('hidden');
      }
    }

    checkbox.addEventListener('change', sync);
    sync();
  }

  function initStartEndDate(form) {
    var start = form.querySelector('#sebu_cal_start_date');
    var end = form.querySelector('#sebu_cal_end_date');
    if (!start || !end) {
      return;
    }

    start.addEventListener('change', function () {
      if (!end.value || end.value < start.value) {
        end.value = start.value;
      }
      if (end.min !== undefined) {
        end.min = start.value;
      }
    });
  }

  function ensureModal() {
    if (modal && modalBody) {
      return true;
    }
    modal = document.getElementById('sebuCalEventModal');
    modalBody = document.getElementById('sebuCalEventModalBody');
    return !!(modal && modalBody);
  }

  function metaRow(label, value) {
    if (!value) {
      return '';
    }
    return ''
      + '<div>'
      + '<dt>' + esc(label) + '</dt>'
      + '<dd>' + esc(value) + '</dd>'
      + '</div>';
  }

  function renderDetail(event) {
    var badges = ''
      + '<span class="sebu-cal-detail__category ' + esc(event.category_class || '') + '">' + esc(event.category_label || '') + '</span>'
      + '<span class="sebu-cal-detail__badge ' + esc(event.badge_class || '') + '">' + esc(event.badge_label || '') + '</span>';
    if (event.is_google) {
      badges += '<span class="sebu-cal-detail__source">Google Calendar</span>';
    }

    var meta = ''
      + metaRow('날짜', event.date_label)
      + metaRow('시간', event.time_label)
      + metaRow('지역', event.area_label)
      + metaRow('장소', event.location)
      + metaRow('출처', event.source_label)
      + metaRow('작성자', event.writer_display)
      + metaRow('등록일', event.created_at);
    if (event.updated_at && event.updated_at !== event.created_at) {
      meta += metaRow('수정일', event.updated_at);
    }

    var description = '';
    if (event.description_html) {
      description = ''
        + '<section class="sebu-cal-detail__section">'
        + '<h2 class="sebu-cal-detail__section-title">설명</h2>'
        + '<div class="sebu-cal-detail__content">' + event.description_html + '</div>'
        + '</section>';
    }

    var relatedUrl = '';
    if (event.related_url) {
      relatedUrl = '<p class="sebu-cal-detail__link"><a href="' + esc(event.related_url) + '" target="_blank" rel="noopener noreferrer">관련 링크 열기</a></p>';
    }

    var relatedRoom = '';
    if (event.related_room_name) {
      relatedRoom = ''
        + '<section class="sebu-cal-detail__section sebu-cal-detail__related">'
        + '<h2 class="sebu-cal-detail__section-title">관련 세부톡방</h2>'
        + '<p class="sebu-cal-detail__room">';
      if (event.related_room_href) {
        relatedRoom += '<a href="' + esc(event.related_room_href) + '" class="sebu-cal-btn">톡방으로 이동</a>';
      }
      relatedRoom += '<span>' + esc(event.related_room_name) + '</span></p>';
      if (event.related_post_url) {
        relatedRoom += '<p class="sebu-cal-detail__link"><a href="' + esc(event.related_post_url) + '" target="_blank" rel="noopener noreferrer">관련 글 보기</a></p>';
      }
      relatedRoom += '</section>';
    }

    var actions = '';
    if (event.can_edit && event.edit_href) {
      actions += '<a href="' + esc(event.edit_href) + '" class="sebu-cal-btn sebu-cal-btn--primary">수정</a>';
    }
    if (event.can_delete && ui.member_token && ui.proc_url) {
      actions += ''
        + '<form method="post" action="' + esc(ui.proc_url) + '" class="sebu-cal-detail__delete-form" data-sebu-cal-event-delete>'
        + '<input type="hidden" name="action" value="delete">'
        + '<input type="hidden" name="event_id" value="' + esc(String(event.event_id)) + '">'
        + '<input type="hidden" name="eottae_calendar_token" value="' + esc(ui.member_token) + '">'
        + '<button type="submit" class="sebu-cal-btn sebu-cal-btn--danger">' + esc(event.delete_label || '삭제') + '</button>'
        + '</form>';
    }
    if (event.detail_href) {
      actions += '<a href="' + esc(event.detail_href) + '" class="sebu-cal-btn">전체 페이지</a>';
    }

    return ''
      + '<article class="sebu-cal-detail sebu-cal-detail--modal">'
      + '<header class="sebu-cal-detail__head">'
      + '<div class="sebu-cal-detail__badges">' + badges + '</div>'
      + '<h2 class="sebu-cal-detail__title" id="sebuCalEventModalTitle">' + esc(event.title) + '</h2>'
      + '</header>'
      + '<dl class="sebu-cal-detail__meta">' + meta + '</dl>'
      + description
      + relatedUrl
      + relatedRoom
      + (actions ? '<div class="sebu-cal-detail__actions">' + actions + '</div>' : '')
      + '</article>';
  }

  function openModal() {
    if (!ensureModal()) {
      return;
    }
    modal.removeAttribute('hidden');
    document.body.classList.add('sebu-cal-event-modal-open');
    var closeBtn = modal.querySelector('.sebu-cal-event-modal__close');
    if (closeBtn) {
      closeBtn.focus();
    }
  }

  function closeModal() {
    if (!ensureModal()) {
      return;
    }
    modal.setAttribute('hidden', 'hidden');
    document.body.classList.remove('sebu-cal-event-modal-open');
    if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus();
    }
  }

  function showLoading() {
    if (!ensureModal()) {
      return;
    }
    modalBody.innerHTML = '<p class="sebu-cal-event-modal__loading">일정을 불러오는 중…</p>';
    openModal();
  }

  function showError(message) {
    if (!ensureModal()) {
      return;
    }
    modalBody.innerHTML = '<p class="sebu-cal-event-modal__error">' + esc(message || '일정을 불러오지 못했습니다.') + '</p>';
    openModal();
  }

  function openEventDetail(eventId) {
    var apiUrl = ui.api_url || '/proc/eottae-calendar-detail.php';
    if (!eventId) {
      return;
    }
    if (!ensureModal()) {
      window.location.href = (ui.list_url || '/calendar/');
      return;
    }

    showLoading();

    fetch(apiUrl + '?event_id=' + encodeURIComponent(String(eventId)), {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !data.event) {
          showError((data && data.message) || '일정을 찾을 수 없습니다.');
          return;
        }
        modalBody.innerHTML = renderDetail(data.event);
        bindDeleteForms();
      })
      .catch(function () {
        showError('네트워크 오류가 발생했습니다.');
      });
  }

  function bindDeleteForms() {
    if (!modalBody) {
      return;
    }
    modalBody.querySelectorAll('[data-sebu-cal-event-delete]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        var confirmMsg = '이 일정을 삭제할까요?';
        var btn = form.querySelector('button[type="submit"]');
        if (btn && btn.textContent.indexOf('숨김') !== -1) {
          confirmMsg = '이 Google 일정을 숨김 처리할까요?';
        }
        if (!window.confirm(confirmMsg)) {
          e.preventDefault();
        }
      });
    });
  }

  function handleEventClick(e) {
    var trigger = e.target.closest('[data-sebu-cal-event]');
    if (!trigger) {
      return;
    }
    var eventId = trigger.getAttribute('data-sebu-cal-event');
    if (!eventId || eventId === '0') {
      return;
    }
    e.preventDefault();
    lastFocus = trigger;
    openEventDetail(eventId);
  }

  function initModal() {
    if (modalInitialized) {
      return ensureModal();
    }

    if (!ensureModal()) {
      return false;
    }

    modal.addEventListener('click', function (e) {
      if (e.target.closest('[data-sebu-cal-event-close]')) {
        e.preventDefault();
        closeModal();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal && !modal.hasAttribute('hidden')) {
        closeModal();
      }
    });

    document.addEventListener('click', handleEventClick);
    modalInitialized = true;

    return true;
  }

  document.querySelectorAll('[data-sebu-cal-form]').forEach(function (form) {
    initAllDayToggle(form);
    initStartEndDate(form);
  });

  function bootModal() {
    initModal();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootModal);
  } else {
    bootModal();
  }

  window.addEventListener('load', bootModal);

  window.eottaeCalendarOpenEvent = openEventDetail;
  window.eottaeCalendarInitEventModal = bootModal;
})();
