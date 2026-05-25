<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_admin_url')) {
    include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
}

if (!function_exists('eottae_talkroom_admin_append_body_class')) {
    function eottae_talkroom_admin_append_body_class($class)
    {
        global $g5;

        $class = trim((string) $class);
        if ($class === '') {
            return;
        }

        if (!isset($g5['body_script'])) {
            $g5['body_script'] = '';
        }

        if (preg_match('/class="([^"]*)"/', $g5['body_script'], $matches)) {
            if (strpos($matches[1], $class) !== false) {
                return;
            }
            $g5['body_script'] = preg_replace(
                '/class="([^"]*)"/',
                'class="'.trim($matches[1].' '.$class).'"',
                $g5['body_script'],
                1
            );

            return;
        }

        $g5['body_script'] .= ' class="'.$class.'"';
    }
}

if (!function_exists('eottae_talkroom_admin_page_assets')) {
    function eottae_talkroom_admin_page_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae.css">', 19);
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-talkroom-ui.css">', 21);

        foreach (array('eottae-page', 'talkroom-ui', 'talk-admin-shell') as $class) {
            if (function_exists('eottae_talkroom_append_body_class')) {
                eottae_talkroom_append_body_class($class);
            } else {
                eottae_talkroom_admin_append_body_class($class);
            }
        }
    }
}

eottae_talkroom_admin_page_assets();

if (!function_exists('eottae_talkroom_render_admin_nav')) {
    function eottae_talkroom_render_admin_nav($active = 'applies')
    {
        $pending = function_exists('eottae_talkroom_pending_count') ? eottae_talkroom_pending_count() : 0;
        $report_pending = function_exists('eottae_talkroom_admin_pending_report_count') ? eottae_talkroom_admin_pending_report_count() : 0;
        ?>
        <nav class="talk-admin-nav" aria-label="세부톡방 관리">
            <a href="<?php echo eottae_talkroom_admin_rooms_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'rooms' ? ' is-active' : ''; ?>">톡방 목록</a>
            <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'applies' ? ' is-active' : ''; ?>">
                개설 신청 관리<?php if ($pending > 0) { ?> (<?php echo number_format($pending); ?>)<?php } ?>
            </a>
            <a href="<?php echo eottae_talkroom_admin_kicked_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'kicked' ? ' is-active' : ''; ?>">강퇴 회원</a>
            <a href="<?php echo eottae_talkroom_admin_reports_url('pending'); ?>" class="talk-admin-nav__item<?php echo $active === 'reports' ? ' is-active' : ''; ?>">
                신고 관리<?php if ($report_pending > 0) { ?> (<?php echo number_format($report_pending); ?>)<?php } ?>
            </a>
            <a href="<?php echo eottae_talkroom_ai_admin_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'ai' ? ' is-active' : ''; ?>">AI 도우미 설정</a>
            <a href="<?php echo eottae_talkroom_ai_logs_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'ai_logs' ? ' is-active' : ''; ?>">AI 발언 로그</a>
        </nav>
        <?php
    }
}

if (!function_exists('eottae_talkroom_render_admin_actions_script')) {
    function eottae_talkroom_render_admin_actions_script($admin_token)
    {
        ?>
        <script>
        (function () {
          var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;

          function postTalkAdminAction(action, roomId, rejectReason) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('room_id', String(roomId));
            fd.append('eottae_talkroom_admin_token', adminToken);
            if (rejectReason) fd.append('reject_reason', rejectReason);
            return fetch('/proc/eottae-talkroom-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
              .then(function (r) { return r.json(); });
          }

          document.querySelectorAll('[data-talk-approve]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('이 톡방 개설 신청을 승인하시겠습니까?')) return;
              btn.disabled = true;
              postTalkAdminAction('approve', btn.getAttribute('data-talk-approve'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                })
                .catch(function () {
                  alert('네트워크 오류가 발생했습니다.');
                  btn.disabled = false;
                });
            });
          });

          document.querySelectorAll('[data-talk-reject]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              var reason = window.prompt('반려 사유를 입력해 주세요.', '');
              if (reason === null) return;
              btn.disabled = true;
              postTalkAdminAction('reject', btn.getAttribute('data-talk-reject'), reason)
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                })
                .catch(function () {
                  alert('네트워크 오류가 발생했습니다.');
                  btn.disabled = false;
                });
            });
          });

          document.querySelectorAll('[data-talk-stop]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('이 톡방을 운영중지하시겠습니까? 일반 목록에서 숨겨집니다.')) return;
              btn.disabled = true;
              postTalkAdminAction('stop', btn.getAttribute('data-talk-stop'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                })
                .catch(function () {
                  alert('네트워크 오류가 발생했습니다.');
                  btn.disabled = false;
                });
            });
          });

          function postTalkAdminUnkick(roomId, memberId, statusAfter) {
            var fd = new FormData();
            fd.append('action', 'unkick_member');
            fd.append('room_id', String(roomId));
            fd.append('member_id', String(memberId));
            fd.append('status_after', statusAfter || 'left');
            fd.append('eottae_talkroom_admin_token', adminToken);
            return fetch('/proc/eottae-talkroom-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
              .then(function (r) { return r.json(); });
          }

          document.querySelectorAll('[data-talk-unkick]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              var restoreActive = window.confirm('강퇴 해제 방식을 선택해 주세요.\n\n확인 = 바로 참여(active) 복구\n취소 = 탈퇴(left) 상태로 해제 후 재참여 신청');
              if (!window.confirm('강퇴를 해제하시겠습니까?')) return;
              btn.disabled = true;
              postTalkAdminUnkick(
                btn.getAttribute('data-talk-unkick-room'),
                btn.getAttribute('data-talk-unkick'),
                restoreActive ? 'active' : 'left'
              ).then(function (data) {
                if (data.success) location.reload();
                else alert(data.message || '처리에 실패했습니다.');
                btn.disabled = false;
              }).catch(function () {
                alert('네트워크 오류가 발생했습니다.');
                btn.disabled = false;
              });
            });
          });
        })();
        </script>
        <?php
    }
}
