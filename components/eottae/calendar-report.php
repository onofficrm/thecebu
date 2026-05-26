<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_render_report_modal')) {
    function eottae_calendar_render_report_modal()
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        $reasons = eottae_calendar_report_reasons();
        ?>
        <div class="sebu-cal-report-modal" id="sebuCalReportModal" hidden>
            <div class="sebu-cal-report-modal__backdrop" data-sebu-cal-report-close></div>
            <div class="sebu-cal-report-modal__panel" role="dialog" aria-labelledby="sebuCalReportModalTitle">
                <h3 class="sebu-cal-report-modal__title" id="sebuCalReportModalTitle">일정 신고</h3>
                <form id="sebuCalReportForm" class="sebu-cal-report-form">
                    <input type="hidden" name="event_id" id="sebu_cal_report_event_id" value="">
                    <fieldset class="sebu-cal-report-form__reasons">
                        <legend>신고 사유</legend>
                        <?php foreach ($reasons as $code => $label) { ?>
                        <label class="sebu-cal-report-form__radio">
                            <input type="radio" name="reason" value="<?php echo get_text($code); ?>" required>
                            <span><?php echo get_text($label); ?></span>
                        </label>
                        <?php } ?>
                    </fieldset>
                    <div class="sebu-cal-report-form__memo" id="sebuCalReportMemoWrap">
                        <label for="sebu_cal_report_memo">추가 메모 (선택)</label>
                        <textarea id="sebu_cal_report_memo" name="memo" class="sebu-cal-form__textarea" rows="3" maxlength="1000" placeholder="신고 내용을 입력해 주세요."></textarea>
                    </div>
                    <div class="sebu-cal-report-modal__actions">
                        <button type="button" class="sebu-cal-btn" data-sebu-cal-report-close>취소</button>
                        <button type="submit" class="sebu-cal-btn sebu-cal-btn--danger">신고하기</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('eottae_calendar_report_button_attrs')) {
    function eottae_calendar_report_button_attrs(array $event, $member, $is_admin)
    {
        $mb_id = is_array($member) ? (string) ($member['mb_id'] ?? '') : '';
        $check = eottae_calendar_can_report_event($event, $mb_id, $is_admin === 'super');

        if (empty($check['ok'])) {
            $message = (string) ($check['message'] ?? '신고할 수 없습니다.');
            if ($mb_id === '') {
                $login_url = function_exists('eottae_login_url')
                    ? eottae_login_url(eottae_calendar_event_url((int) ($event['event_id'] ?? 0)))
                    : G5_BBS_URL.'/login.php';

                return array(
                    'visible' => true,
                    'attrs'   => ' data-sebu-cal-report-login="'.htmlspecialchars($login_url, ENT_QUOTES, 'UTF-8').'" title="'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'"',
                    'label'   => '신고',
                );
            }

            return array(
                'visible' => false,
                'attrs'   => '',
                'label'   => '',
            );
        }

        return array(
            'visible' => true,
            'attrs'   => ' data-sebu-cal-report="'.(int) ($event['event_id'] ?? 0).'"',
            'label'   => '신고',
        );
    }
}

if (!function_exists('eottae_calendar_render_event_report_button')) {
    function eottae_calendar_render_event_report_button(array $event, $member, $is_admin, $variant = 'detail')
    {
        $btn = eottae_calendar_report_button_attrs($event, $member, $is_admin);
        if (empty($btn['visible'])) {
            return;
        }

        eottae_calendar_render_report_modal();
        $class = $variant === 'card'
            ? 'sebu-cal-event-card__report-btn'
            : 'sebu-cal-btn sebu-cal-btn--ghost sebu-cal-detail__report-btn';
        ?>
        <button type="button" class="<?php echo $class; ?>"<?php echo $btn['attrs']; ?>><?php echo get_text($btn['label']); ?></button>
        <?php
    }
}

if (!function_exists('eottae_calendar_render_report_script')) {
    function eottae_calendar_render_report_script()
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        $token = eottae_calendar_report_token();
        ?>
        <script>
        (function () {
          var reportToken = <?php echo json_encode((string) $token, JSON_UNESCAPED_UNICODE); ?>;
          var modal = document.getElementById('sebuCalReportModal');
          var form = document.getElementById('sebuCalReportForm');
          if (!modal || !form) return;

          function openModal(eventId) {
            document.getElementById('sebu_cal_report_event_id').value = String(eventId || '');
            form.reset();
            document.getElementById('sebu_cal_report_event_id').value = String(eventId || '');
            modal.removeAttribute('hidden');
          }

          function closeModal() {
            modal.setAttribute('hidden', 'hidden');
          }

          document.querySelectorAll('[data-sebu-cal-report-close]').forEach(function (el) {
            el.addEventListener('click', closeModal);
          });

          document.querySelectorAll('[data-sebu-cal-report]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
              e.preventDefault();
              e.stopPropagation();
              openModal(btn.getAttribute('data-sebu-cal-report'));
            });
          });

          document.querySelectorAll('[data-sebu-cal-report-login]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
              e.preventDefault();
              e.stopPropagation();
              var href = btn.getAttribute('data-sebu-cal-report-login');
              if (href) window.location.href = href;
            });
          });

          form.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(form);
            fd.append('eottae_calendar_report_token', reportToken);
            fetch('/proc/eottae-calendar-report.php', { method: 'POST', body: fd, credentials: 'same-origin' })
              .then(function (r) { return r.json(); })
              .then(function (res) {
                alert(res.message || (res.success ? '신고가 접수되었습니다.' : '신고에 실패했습니다.'));
                if (res.success) {
                  closeModal();
                  window.location.reload();
                }
              })
              .catch(function () {
                alert('신고 처리 중 오류가 발생했습니다.');
              });
          });
        })();
        </script>
        <?php
    }
}

if (!function_exists('eottae_calendar_render_post_calendar_button')) {
    function eottae_calendar_render_post_calendar_button($board, $write, $member, $is_admin)
    {
        if (!function_exists('eottae_talkroom_is_talkroom_board') || !eottae_talkroom_is_talkroom_board($board['bo_table'] ?? '')) {
            return;
        }
        if (!empty($write['wr_is_comment'])) {
            return;
        }

        $room_id = eottae_talkroom_get_write_room_id($write);
        if ($room_id < 1) {
            return;
        }

        $mb_id = is_array($member) ? (string) ($member['mb_id'] ?? '') : '';
        $is_super = ($is_admin === 'super');
        if ($mb_id === '' || !eottae_calendar_can_create_from_talk($room_id, $mb_id, $is_super)) {
            return;
        }

        $post_url = get_pretty_url($board['bo_table'], (int) $write['wr_id']);
        $title = get_text((string) ($write['wr_subject'] ?? ''));
        $description = get_text(strip_tags((string) ($write['wr_content'] ?? '')));
        if (function_exists('cut_str')) {
            $description = cut_str($description, 500, '…');
        }
        $href = eottae_calendar_create_from_talk_post_url($room_id, $post_url, $title, $description);
        ?>
        <div class="talk-calendar-actions talk-calendar-actions--post">
            <a href="<?php echo $href; ?>" class="talk-page__btn talk-page__btn--calendar">캘린더에 등록</a>
        </div>
        <?php
    }
}

if (!function_exists('eottae_calendar_render_admin_report_script')) {
    function eottae_calendar_render_admin_report_script($admin_token)
    {
        ?>
        <script>
        (function () {
          var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;

          function postAction(action, reportId) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('report_id', String(reportId));
            fd.append('eottae_talkroom_admin_token', adminToken);
            return fetch('/proc/eottae-calendar-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
              .then(function (r) { return r.json(); });
          }

          document.querySelectorAll('[data-sebu-cal-report-review]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('검토 중으로 표시할까요?')) return;
              postAction('review', btn.getAttribute('data-sebu-cal-report-review')).then(function (res) {
                alert(res.message || '');
                if (res.success) window.location.reload();
              });
            });
          });

          document.querySelectorAll('[data-sebu-cal-report-reject]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('신고를 기각할까요?')) return;
              postAction('reject', btn.getAttribute('data-sebu-cal-report-reject')).then(function (res) {
                alert(res.message || '');
                if (res.success) window.location.reload();
              });
            });
          });

          document.querySelectorAll('[data-sebu-cal-report-hide]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('해당 일정을 숨김 처리할까요?')) return;
              postAction('hide_event', btn.getAttribute('data-sebu-cal-report-hide')).then(function (res) {
                alert(res.message || '');
                if (res.success) window.location.reload();
              });
            });
          });

          document.querySelectorAll('[data-sebu-cal-report-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('해당 일정을 삭제(숨김) 처리할까요?')) return;
              postAction('delete_event', btn.getAttribute('data-sebu-cal-report-delete')).then(function (res) {
                alert(res.message || '');
                if (res.success) window.location.reload();
              });
            });
          });
        })();
        </script>
        <?php
    }
}
