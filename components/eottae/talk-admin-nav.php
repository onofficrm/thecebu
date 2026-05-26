<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_admin_url')) {
    include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
}

include_once G5_PATH.'/components/eottae/talk-admin-layout.php';

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

        if (function_exists('eottae_talkroom_register_admin_shell_assets')) {
            eottae_talkroom_register_admin_shell_assets();
        } elseif (function_exists('eottae_talkroom_load_admin_shell_assets')) {
            eottae_talkroom_load_admin_shell_assets();
        } else {
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
            <a href="<?php echo eottae_talkroom_admin_kicked_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'kicked' ? ' is-active' : ''; ?>">
                강퇴 회원<?php
                $kicked_total = function_exists('eottae_talkroom_admin_kicked_count') ? eottae_talkroom_admin_kicked_count() : 0;
                if ($kicked_total > 0) {
                    echo ' ('.number_format($kicked_total).')';
                }
                ?>
            </a>
            <a href="<?php echo eottae_talkroom_admin_reports_url('pending'); ?>" class="talk-admin-nav__item<?php echo $active === 'reports' ? ' is-active' : ''; ?>">
                신고 관리<?php if ($report_pending > 0) { ?> (<?php echo number_format($report_pending); ?>)<?php } ?>
            </a>
            <a href="<?php echo eottae_talkroom_ai_admin_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'ai' ? ' is-active' : ''; ?>">AI 도우미 설정</a>
            <a href="<?php echo eottae_talkroom_ai_logs_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'ai_logs' ? ' is-active' : ''; ?>">AI 발언 로그</a>
            <?php if (function_exists('eottae_public_ai_admin_settings_url')) {
                include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
                ?>
            <a href="<?php echo eottae_public_ai_admin_settings_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'public_ai' ? ' is-active' : ''; ?>">공개톡 AI</a>
            <?php } ?>
        </nav>
        <?php
    }
}

if (!function_exists('eottae_talkroom_render_mypage_super_admin_talk_tools')) {
    function eottae_talkroom_render_mypage_super_admin_talk_tools($preview_limit = 8)
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return;
        }

        $pending = function_exists('eottae_talkroom_pending_count') ? eottae_talkroom_pending_count() : 0;
        $kicked_total = function_exists('eottae_talkroom_admin_kicked_count') ? eottae_talkroom_admin_kicked_count() : 0;
        $report_pending = function_exists('eottae_talkroom_admin_pending_report_count') ? eottae_talkroom_admin_pending_report_count() : 0;
        $preview_limit = max(1, min(20, (int) $preview_limit));
        $recent_kicked = function_exists('eottae_talkroom_admin_list_kicked_members')
            ? eottae_talkroom_admin_list_kicked_members($preview_limit)
            : array();
        ?>
        <section class="my-talk-section my-talk-section--panel my-talk-super-admin" id="my-talk-super-admin" aria-labelledby="my-talk-super-admin-title">
            <h2 class="my-talk-section__title" id="my-talk-super-admin-title">세부톡방 관리 (최고관리자)</h2>
            <p class="my-talk-section__desc">개설 신청, 강퇴 회원, 신고 등 사이트 전체 톡방을 관리할 수 있습니다.</p>
            <div class="my-talk-super-admin__links">
                <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">개설 신청<?php if ($pending > 0) { ?> (<?php echo number_format($pending); ?>)<?php } ?></a>
                <a href="<?php echo eottae_talkroom_admin_rooms_url(); ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">톡방 목록</a>
                <a href="<?php echo eottae_talkroom_admin_kicked_url(); ?>" class="my-talk-btn my-talk-btn--primary my-talk-btn--sm">강퇴 회원<?php if ($kicked_total > 0) { ?> (<?php echo number_format($kicked_total); ?>)<?php } ?></a>
                <a href="<?php echo eottae_talkroom_admin_reports_url('pending'); ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">신고 관리<?php if ($report_pending > 0) { ?> (<?php echo number_format($report_pending); ?>)<?php } ?></a>
                <a href="<?php echo eottae_talkroom_ai_admin_url(); ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">AI 설정</a>
                <?php if (function_exists('eottae_public_ai_admin_settings_url')) {
                    include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
                    $public_ai_pending = eottae_public_ai_pending_count();
                    ?>
                <a href="<?php echo eottae_public_ai_admin_settings_url(); ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">공개톡 AI<?php if ($public_ai_pending > 0) { ?> (<?php echo number_format($public_ai_pending); ?>)<?php } ?></a>
                <?php } ?>
            </div>

            <div class="my-talk-super-admin__kicked">
                <div class="my-talk-super-admin__kicked-head">
                    <h3 class="my-talk-super-admin__kicked-title">강퇴 회원</h3>
                    <a href="<?php echo eottae_talkroom_admin_kicked_url(); ?>" class="my-talk-section__more">전체 보기</a>
                </div>
                <?php if (empty($recent_kicked)) { ?>
                <p class="my-talk-super-admin__empty">강퇴된 회원이 없습니다.</p>
                <?php } else { ?>
                <ul class="my-talk-super-admin__kicked-list">
                    <?php foreach ($recent_kicked as $item) { ?>
                    <li class="my-talk-super-admin__kicked-item">
                        <div class="my-talk-super-admin__kicked-main">
                            <span class="my-talk-super-admin__kicked-room"><?php echo $item['emoji']; ?> <?php echo $item['room_name']; ?></span>
                            <span class="my-talk-super-admin__kicked-member"><?php echo $item['mb_nick']; ?> <span class="my-talk-super-admin__kicked-id">(<?php echo $item['mb_id']; ?>)</span></span>
                        </div>
                        <div class="my-talk-super-admin__kicked-meta">
                            <span><?php echo $item['kicked_at'] !== '0000-00-00 00:00:00' ? substr($item['kicked_at'], 0, 16) : '-'; ?></span>
                            <span>처리: <?php echo $item['kicked_by_nick']; ?></span>
                        </div>
                        <?php if ($item['kicked_reason'] !== '') { ?>
                        <p class="my-talk-super-admin__kicked-reason"><?php echo nl2br($item['kicked_reason']); ?></p>
                        <?php } ?>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </section>
        <?php
    }
}

if (!function_exists('eottae_talkroom_render_admin_applies_panel')) {
    function eottae_talkroom_render_admin_applies_panel($applications, $filter, $pending_count)
    {
        $filter = trim((string) $filter);
        if ($filter === '') {
            $filter = 'pending';
        }
        ?>
        <section id="talk-admin-applies-panel" class="promo-admin-panel talk-admin-panel talk-admin-applies__panel" aria-label="개설 신청 목록">
            <nav class="talk-admin-filter talk-admin-applies__filter" aria-label="신청 상태 필터">
                <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>?status=pending" class="talk-admin-filter__item<?php echo $filter === 'pending' ? ' is-active' : ''; ?>">
                    승인대기<?php if ($pending_count > 0) { ?> (<?php echo number_format($pending_count); ?>)<?php } ?>
                </a>
                <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>?status=all" class="talk-admin-filter__item<?php echo $filter === 'all' ? ' is-active' : ''; ?>">전체</a>
                <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>?status=rejected" class="talk-admin-filter__item<?php echo $filter === 'rejected' ? ' is-active' : ''; ?>">반려</a>
            </nav>

            <?php if (empty($applications)) { ?>
                <div class="talk-admin-applies__empty">
                    <p class="promo-admin-empty">표시할 신청 내역이 없습니다.</p>
                    <?php if ($filter === 'pending' && $pending_count > 0) { ?>
                    <p class="talk-admin-applies__empty-hint talk-admin-applies__empty-hint--warn">승인 대기 <?php echo number_format($pending_count); ?>건이 있으나 목록을 불러오지 못했습니다. <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>?status=all">전체</a> 탭을 확인하거나 페이지를 새로고침해 주세요.</p>
                    <?php } elseif ($filter === 'pending') { ?>
                    <p class="talk-admin-applies__empty-hint">새 개설 신청이 들어오면 이 목록에 표시됩니다.</p>
                    <?php } ?>
                </div>
                <?php } else { ?>
                <div class="talk-admin-applies__summary">
                    <span>총 <strong><?php echo number_format(count($applications)); ?></strong>건</span>
                    <?php if ($filter === 'pending' && $pending_count > 0) { ?>
                    <span class="talk-admin-applies__summary-pending">승인 필요 <?php echo number_format($pending_count); ?>건</span>
                    <?php } ?>
                </div>
                <div class="talk-admin-table-wrap">
                    <table class="talk-admin-table talk-admin-applies__table">
                        <thead>
                            <tr>
                                <th scope="col">신청일</th>
                                <th scope="col">신청자</th>
                                <th scope="col">톡방 이름</th>
                                <th scope="col">카테고리</th>
                                <th scope="col">공개</th>
                                <th scope="col">가입</th>
                                <th scope="col">상태</th>
                                <th scope="col">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $item) { ?>
                            <tr class="talk-admin-applies__row<?php echo ($item['status'] ?? '') === 'pending' ? ' is-pending' : ''; ?>">
                                <td data-label="신청일"><?php echo $item['created_at'] !== '0000-00-00 00:00:00' ? substr($item['created_at'], 0, 16) : '-'; ?></td>
                                <td data-label="신청자">
                                    <?php echo $item['owner_nick']; ?><br>
                                    <span class="talk-admin-table__sub"><?php echo $item['owner_mb_id']; ?></span>
                                </td>
                                <td data-label="톡방"><span class="talk-admin-applies__room"><?php echo $item['emoji']; ?> <?php echo $item['room_name']; ?></span></td>
                                <td data-label="카테고리"><?php echo $item['category']; ?></td>
                                <td data-label="공개"><?php echo $item['visibility_label']; ?></td>
                                <td data-label="가입"><?php echo $item['join_type_label']; ?></td>
                                <td data-label="상태"><span class="talk-apply-status <?php echo $item['status_class']; ?>"><?php echo $item['status_label']; ?></span></td>
                                <td data-label="관리" class="talk-admin-table__actions">
                                    <a href="<?php echo $item['detail_url']; ?>" class="promo-admin-btn promo-admin-btn--sm">상세</a>
                                    <?php if (($item['status'] ?? '') === 'pending') { ?>
                                    <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-talk-approve="<?php echo (int) $item['room_id']; ?>">승인</button>
                                    <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-reject="<?php echo (int) $item['room_id']; ?>">반려</button>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
        </section>
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
