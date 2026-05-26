<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
include_once G5_PATH.'/components/eottae/talk-room-chat.php';

$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;
$mb_id = !empty($is_member) && !empty($member['mb_id']) ? $member['mb_id'] : '';
$ctx = eottae_talkroom_build_detail_context($room_id, $mb_id);

if (!$ctx) {
    alert('운영 중인 톡방을 찾을 수 없습니다.', eottae_talkroom_list_url());
}

if ($is_member && $mb_id !== '') {
    eottae_talkroom_reads_auto_mark_on_view($room_id, $mb_id, $ctx);
}

$room = $ctx['room'];
$member_token = $is_member ? eottae_talkroom_member_token() : '';
$is_super = ($is_admin === 'super');
$can_register_calendar = $is_member && eottae_calendar_can_create_from_talk($room_id, $mb_id, $is_super);
$calendar_create_href = $can_register_calendar
    ? eottae_calendar_create_from_talk_url($room_id, array('room_name' => $room['room_name'] ?? ''))
    : '';
$login_url = function_exists('eottae_login_url')
    ? eottae_login_url(eottae_talkroom_enter_url($room_id))
    : G5_BBS_URL.'/login.php';

add_javascript('<script src="'.G5_JS_URL.'/eottae-talkroom-chat.js" defer></script>', 26);

g5_page_start($room['room_name']);
?>

<main class="mypage-subpage talk-room-detail-page talk-room-detail-page--chat">
    <p class="mypage-subpage__back"><a href="<?php echo eottae_talkroom_list_url(); ?>">← 세부톡방</a></p>

    <?php if (!empty($_GET['created'])) { ?>
    <div class="talk-applies-page__success talk-room-detail__flash" role="status">
        <strong>톡방이 만들어졌습니다.</strong>
        <p>지금 바로 대화를 시작해 보세요.</p>
    </div>
    <?php } ?>

    <details class="talk-room-detail__drawer">
        <summary class="talk-room-detail__drawer-summary">
            <span class="talk-room-detail__drawer-emoji" aria-hidden="true"><?php echo $room['emoji']; ?></span>
            <span class="talk-room-detail__drawer-label">톡방 정보</span>
            <span class="talk-room-detail__drawer-meta"><?php echo $room['category']; ?> · <?php echo $room['visibility_label']; ?></span>
        </summary>

        <div class="talk-room-detail__drawer-body">
            <?php if ($ctx['can_view_notice'] && $room['room_notice'] !== '') { ?>
            <aside class="talk-room-detail__banner" role="note">
                <strong class="talk-room-detail__banner-label">공지</strong>
                <div class="talk-room-detail__banner-body"><?php echo nl2br($room['room_notice']); ?></div>
            </aside>
            <?php } ?>

            <div class="talk-room-detail__hero talk-room-detail__hero--compact">
                <div class="talk-room-detail__head">
                    <h1 class="talk-room-detail__title"><?php echo $room['room_name']; ?></h1>
                    <p class="talk-room-detail__desc"><?php echo $room['room_description']; ?></p>
                    <div class="talk-room-detail__tags">
                        <span class="talk-room-card__tag"><?php echo $room['category']; ?></span>
                        <span class="talk-room-card__badge talk-room-card__badge--<?php echo $room['visibility'] === 'private' ? 'private' : 'public'; ?>"><?php echo $room['visibility_label']; ?></span>
                        <span class="talk-room-card__tag"><?php echo $room['join_type_label']; ?></span>
                    </div>
                    <dl class="talk-room-detail__stats">
                        <div><dt>방장</dt><dd><?php echo $room['owner_nick']; ?></dd></div>
                        <div><dt>참여</dt><dd><?php echo number_format((int) $room['member_count']); ?></dd></div>
                        <div><dt>대화</dt><dd><?php echo number_format((int) $room['post_count']); ?></dd></div>
                    </dl>
                </div>
            </div>

            <?php if (!empty($ctx['can_share_invite'])) {
                $talk_invite_url = $room['invite_href'] ?? eottae_talkroom_invite_url($room_id);
                $talk_invite_room_name = $room['room_name'] ?? '';
                include G5_PATH.'/components/eottae/talk-invite-share.php';
            } ?>

            <?php if ($ctx['can_view_full']) { ?>
            <section class="talk-room-detail__section talk-room-detail__section--inner">
                <h2 class="talk-room-detail__section-title">톡방 소개</h2>
                <div class="talk-room-detail__content"><?php echo nl2br($room['room_detail']); ?></div>
            </section>

            <section class="talk-room-detail__section talk-room-detail__section--inner">
                <h2 class="talk-room-detail__section-title">운영 규칙</h2>
                <div class="talk-room-detail__content"><?php echo nl2br($room['rules']); ?></div>
            </section>
            <?php } elseif ($room['visibility'] === 'private') { ?>
            <section class="talk-room-detail__notice">
                <p>비공개 톡방입니다. 참여 승인 후 상세 내용과 대화를 볼 수 있습니다.</p>
            </section>
            <?php } ?>

            <section class="talk-room-detail__actions talk-room-detail__actions--drawer" aria-label="참여 관리">
                <?php if ($ctx['can_manage'] && $room['manage_href'] !== '') { ?>
                <a href="<?php echo $room['manage_href']; ?>" class="talk-page__btn talk-page__btn--manage">톡방 관리</a>
                <a href="<?php echo eottae_talkroom_ai_settings_url($room_id); ?>" class="talk-page__btn">AI 도우미 설정</a>
                <?php } ?>
                <?php if (!$is_member) { ?>
                <a href="<?php echo $login_url; ?>" class="talk-page__btn talk-page__btn--primary">로그인 후 참여하기</a>
                <?php } elseif ($ctx['membership'] === 'owner' || $ctx['membership'] === 'active') { ?>
                <span class="talk-room-detail__status talk-room-detail__status--active">참여중</span>
                <?php if ($ctx['can_leave']) { ?>
                <button type="button" class="talk-page__btn" data-talk-leave="<?php echo (int) $room_id; ?>">탈퇴하기</button>
                <?php } elseif ($ctx['membership'] === 'owner') { ?>
                <p class="talk-room-detail__hint"><?php echo get_text($ctx['join_blocked_reason']); ?></p>
                <?php } ?>
                <?php if ($calendar_create_href !== '') { ?>
                <a href="<?php echo $calendar_create_href; ?>" class="talk-page__btn talk-page__btn--calendar">캘린더에 등록</a>
                <?php } ?>
                <?php } elseif ($ctx['membership'] === 'pending') { ?>
                <span class="talk-room-detail__status talk-room-detail__status--pending">참여 승인 대기중</span>
                <p class="talk-room-detail__hint">방장 승인 후 대화에 참여할 수 있습니다.</p>
                <?php } elseif ($ctx['membership'] === 'kicked') { ?>
                <span class="talk-room-detail__status talk-room-detail__status--blocked">참여 불가</span>
                <p class="talk-room-detail__hint"><?php echo get_text($ctx['join_blocked_reason']); ?></p>
                <?php } elseif ($ctx['can_join']) { ?>
                <button type="button" class="talk-page__btn talk-page__btn--primary" data-talk-join="<?php echo (int) $room_id; ?>">참여하기</button>
                <?php } else { ?>
                <p class="talk-room-detail__hint"><?php echo get_text($ctx['join_blocked_reason']); ?></p>
                <?php } ?>
            </section>
        </div>
    </details>

    <?php echo eottae_talkroom_chat_html($room_id, $ctx); ?>
</main>

<?php if ($is_member) { ?>
<script>
(function () {
  var memberToken = <?php echo json_encode((string) $member_token, JSON_UNESCAPED_UNICODE); ?>;

  function postTalkMemberAction(action, roomId) {
    var fd = new FormData();
    fd.append('action', action);
    fd.append('room_id', String(roomId));
    fd.append('eottae_talkroom_member_token', memberToken);
    return fetch('/proc/eottae-talkroom-member.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  document.querySelectorAll('[data-talk-join]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.disabled = true;
      postTalkMemberAction('join', btn.getAttribute('data-talk-join'))
        .then(function (data) {
          if (data.success) {
            location.reload();
          } else {
            alert(data.message || '처리에 실패했습니다.');
            btn.disabled = false;
          }
        })
        .catch(function () {
          alert('네트워크 오류가 발생했습니다.');
          btn.disabled = false;
        });
    });
  });

  document.querySelectorAll('[data-talk-leave]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('이 톡방에서 탈퇴하시겠습니까?')) return;
      btn.disabled = true;
      postTalkMemberAction('leave', btn.getAttribute('data-talk-leave'))
        .then(function (data) {
          if (data.success) {
            location.reload();
          } else {
            alert(data.message || '처리에 실패했습니다.');
            btn.disabled = false;
          }
        })
        .catch(function () {
          alert('네트워크 오류가 발생했습니다.');
          btn.disabled = false;
        });
    });
  });
})();
</script>
<?php } ?>

<?php
g5_page_end();
