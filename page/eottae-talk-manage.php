<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-talk-manage.php'));
}

eottae_talkroom_upgrade_schema();

$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;
$is_super = ($is_admin === 'super');

if (!eottae_talkroom_can_manage_room($room_id, $member['mb_id'], $is_super)) {
    alert('톡방 관리 권한이 없습니다.', eottae_talkroom_list_url());
}

$room = eottae_talkroom_get_operating_room($room_id);
if (!$room) {
    alert('운영 중인 톡방을 찾을 수 없습니다.', eottae_talkroom_list_url());
}

$stats = eottae_talkroom_room_stats($room_id);
$detail = eottae_talkroom_format_detail($room, $stats);
$pending_members = eottae_talkroom_list_room_members($room_id, 'pending', 100);
$active_members = eottae_talkroom_list_room_members($room_id, 'active', 200);
$kicked_members = eottae_talkroom_list_room_members($room_id, 'kicked', 100);
$pending_reports = eottae_talkroom_pending_report_count($room_id);
$owner_token = eottae_talkroom_owner_token();
$admin_token = $is_super ? eottae_talkroom_admin_token() : '';

g5_page_start('톡방 관리');
?>

<main class="mypage-subpage talk-manage-page">
    <p class="mypage-subpage__back">
        <a href="<?php echo eottae_talkroom_enter_url($room_id); ?>">← 톡방으로</a>
        <?php if ($is_super) { ?>
        · <a href="<?php echo eottae_talkroom_admin_detail_url($room_id); ?>">관리자 상세</a>
        <?php } ?>
    </p>
    <h1 class="mypage-subpage__title">톡방 관리</h1>
    <p class="talk-manage-page__intro"><?php echo $detail['emoji']; ?> <?php echo $detail['room_name']; ?> · <?php echo $detail['category']; ?>
        · <a href="<?php echo eottae_talkroom_owner_reports_url($room_id); ?>">신고 관리<?php if ($pending_reports > 0) { ?> (<?php echo number_format($pending_reports); ?>)<?php } ?></a>
        · <a href="<?php echo eottae_talkroom_ai_settings_url($room_id); ?>">AI 도우미 설정</a>
    </p>

    <?php
    $talk_invite_url = $detail['invite_href'] ?? eottae_talkroom_invite_url($room_id);
    $talk_invite_room_name = $detail['room_name'] ?? '';
    $talk_invite_compact = true;
    include G5_PATH.'/components/eottae/talk-invite-share.php';
    ?>

    <section class="promo-admin-panel talk-manage-panel">
        <h2 class="promo-admin-panel__title">톡방 기본 정보</h2>
        <dl class="talk-manage-summary">
            <div><dt>방장</dt><dd><?php echo $detail['owner_nick']; ?> (<?php echo $detail['owner_mb_id']; ?>)</dd></div>
            <div><dt>공개</dt><dd><?php echo $detail['visibility_label']; ?></dd></div>
            <div><dt>가입</dt><dd><?php echo $detail['join_type_label']; ?></dd></div>
            <div><dt>참여</dt><dd><?php echo number_format((int) $detail['member_count']); ?>명</dd></div>
            <div><dt>게시글</dt><dd><?php echo number_format((int) $detail['post_count']); ?>개</dd></div>
        </dl>
    </section>

    <?php if ($detail['join_type'] === 'approval') { ?>
    <section class="promo-admin-panel talk-manage-panel" id="talk-manage-pending">
        <h2 class="promo-admin-panel__title">참여 신청 <?php if (count($pending_members) > 0) { ?>(<?php echo count($pending_members); ?>)<?php } ?></h2>
        <?php if (empty($pending_members)) { ?>
        <p class="promo-admin-empty">대기 중인 참여 신청이 없습니다.</p>
        <?php } else { ?>
        <ul class="talk-manage-member-list">
            <?php foreach ($pending_members as $item) { ?>
            <li class="talk-manage-member-list__item">
                <div class="talk-manage-member-list__main">
                    <strong><?php echo $item['mb_nick']; ?></strong>
                    <span class="talk-manage-member-list__id"><?php echo $item['mb_id']; ?></span>
                    <span class="talk-manage-member-list__meta">신청 <?php echo $item['requested_at'] !== '0000-00-00 00:00:00' ? substr($item['requested_at'], 0, 16) : '-'; ?></span>
                </div>
                <div class="talk-manage-member-list__actions">
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-talk-owner-approve="<?php echo (int) $item['id']; ?>">승인</button>
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-owner-reject="<?php echo (int) $item['id']; ?>">거절</button>
                </div>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>
    <?php } ?>

    <section class="promo-admin-panel talk-manage-panel" id="talk-manage-members">
        <?php if (empty($active_members)) { ?>
        <p class="promo-admin-empty">참여 중인 회원이 없습니다.</p>
        <?php } else { ?>
        <ul class="talk-manage-member-list talk-manage-member-list--compact">
            <?php foreach ($active_members as $item) {
                $kick_check = eottae_talkroom_can_kick_target($room, array(
                    'mb_id'  => $item['mb_id'],
                    'role'   => $item['role'],
                    'status' => $item['status'],
                ), $member['mb_id'], $is_super);
                ?>
            <li class="talk-manage-member-list__item">
                <div class="talk-manage-member-list__main">
                    <strong><?php echo $item['mb_nick']; ?></strong>
                    <?php if ($item['role'] === 'owner') { ?><span class="talk-apply-status talk-apply-status--approved">방장</span><?php } ?>
                    <span class="talk-manage-member-list__id"><?php echo $item['mb_id']; ?></span>
                    <span class="talk-manage-member-list__meta">참여 <?php echo $item['joined_at'] !== '0000-00-00 00:00:00' ? substr($item['joined_at'], 0, 16) : '-'; ?></span>
                </div>
                <?php if (!empty($kick_check['ok'])) { ?>
                <div class="talk-manage-member-list__actions">
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm talk-manage-kick-btn" data-talk-owner-kick="<?php echo (int) $item['id']; ?>" data-talk-owner-kick-nick="<?php echo get_text($item['mb_nick']); ?>">강퇴</button>
                </div>
                <?php } ?>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>

    <section class="promo-admin-panel talk-manage-panel">
        <h2 class="promo-admin-panel__title">강퇴 회원 (<?php echo count($kicked_members); ?>)</h2>
        <?php if (empty($kicked_members)) { ?>
        <p class="promo-admin-empty">강퇴된 회원이 없습니다.</p>
        <?php } else { ?>
        <ul class="talk-manage-member-list talk-manage-kicked-list">
            <?php foreach ($kicked_members as $item) { ?>
            <li class="talk-manage-member-list__item talk-manage-member-list__item--kicked">
                <div class="talk-manage-member-list__main">
                    <strong><?php echo $item['mb_nick']; ?></strong>
                    <span class="talk-manage-member-list__id"><?php echo $item['mb_id']; ?></span>
                    <span class="talk-manage-member-list__meta">
                        강퇴 <?php echo $item['kicked_at'] !== '0000-00-00 00:00:00' ? substr($item['kicked_at'], 0, 16) : '-'; ?>
                        · 처리 <?php echo $item['kicked_by']; ?>
                        · 재참여 <?php echo $item['can_rejoin'] ? '허용' : '불허'; ?>
                    </span>
                    <?php if ($item['kicked_reason'] !== '') { ?>
                    <p class="talk-manage-kicked-reason"><?php echo nl2br($item['kicked_reason']); ?></p>
                    <?php } ?>
                </div>
                <?php if ($is_super) { ?>
                <div class="talk-manage-member-list__actions">
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-talk-admin-unkick="<?php echo (int) $item['id']; ?>" data-talk-admin-unkick-room="<?php echo (int) $room_id; ?>">강퇴 해제</button>
                </div>
                <?php } ?>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>

    <section class="promo-admin-panel talk-manage-panel" id="talk-manage-notice">
        <form class="talk-manage-form" id="talkNoticeForm" method="post" action="<?php echo G5_URL; ?>/proc/eottae-talkroom-owner.php">
            <input type="hidden" name="action" value="save_notice">
            <input type="hidden" name="room_id" value="<?php echo (int) $room_id; ?>">
            <input type="hidden" name="eottae_talkroom_owner_token" value="<?php echo get_text($owner_token); ?>">
            <div class="talk-apply-form__field">
                <label for="talk_manage_notice">톡방 공지</label>
                <textarea id="talk_manage_notice" name="room_notice" class="talk-apply-form__textarea" rows="4" maxlength="2000" placeholder="톡방 상단에 표시될 공지입니다."><?php echo get_text($detail['room_notice']); ?></textarea>
            </div>
            <div class="talk-manage-form__actions">
                <button type="submit" class="talk-page__btn talk-page__btn--primary">공지 저장</button>
                <button type="button" class="talk-page__btn" data-talk-notice-clear>공지 삭제</button>
            </div>
        </form>
    </section>

    <section class="promo-admin-panel talk-manage-panel">
        <h2 class="promo-admin-panel__title">톡방 정보 수정</h2>
        <p class="talk-manage-page__hint">톡방 이름과 카테고리는 최고관리자만 변경할 수 있습니다.</p>
        <form class="talk-manage-form talk-apply-form" id="talkManageForm" method="post" action="<?php echo G5_URL; ?>/proc/eottae-talkroom-owner.php">
            <input type="hidden" name="action" value="update_room">
            <input type="hidden" name="room_id" value="<?php echo (int) $room_id; ?>">
            <input type="hidden" name="eottae_talkroom_owner_token" value="<?php echo get_text($owner_token); ?>">

            <div class="talk-apply-form__field">
                <label for="talk_manage_desc">톡방 한 줄 소개</label>
                <input type="text" id="talk_manage_desc" name="room_description" class="talk-apply-form__input" maxlength="500" required value="<?php echo get_text($detail['room_description']); ?>">
            </div>
            <div class="talk-apply-form__field">
                <label for="talk_manage_detail">상세 설명</label>
                <textarea id="talk_manage_detail" name="room_detail" class="talk-apply-form__textarea" rows="5" maxlength="5000" required><?php echo get_text($detail['room_detail']); ?></textarea>
            </div>
            <div class="talk-apply-form__field talk-apply-form__field--emoji">
                <label for="talk_manage_emoji">대표 이모지</label>
                <input type="text" id="talk_manage_emoji" name="emoji" class="talk-apply-form__input talk-apply-form__input--emoji" maxlength="8" value="<?php echo htmlspecialchars(eottae_talkroom_display_emoji($detail['emoji'], $detail['category_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="talk-apply-form__field">
                <label for="talk_manage_rules">운영 규칙</label>
                <textarea id="talk_manage_rules" name="rules" class="talk-apply-form__textarea" rows="4" maxlength="5000" required><?php echo get_text($detail['rules']); ?></textarea>
            </div>
            <div class="talk-apply-form__field">
                <label for="talk_manage_contact">연락처</label>
                <input type="text" id="talk_manage_contact" name="contact" class="talk-apply-form__input" maxlength="255" required value="<?php echo get_text($detail['contact']); ?>">
            </div>
            <fieldset class="talk-apply-form__fieldset">
                <legend>가입 방식</legend>
                <label class="talk-apply-form__radio">
                    <input type="radio" name="join_type" value="open"<?php echo $detail['join_type'] === 'open' ? ' checked' : ''; ?>>
                    <span>누구나 참여 가능</span>
                </label>
                <label class="talk-apply-form__radio">
                    <input type="radio" name="join_type" value="approval"<?php echo $detail['join_type'] === 'approval' ? ' checked' : ''; ?>>
                    <span>방장 승인 후 참여 가능</span>
                </label>
            </fieldset>
            <input type="hidden" name="room_notice" value="<?php echo get_text($detail['room_notice']); ?>" id="talk_manage_notice_hidden">

            <div class="talk-manage-form__actions">
                <button type="submit" class="talk-page__btn talk-page__btn--primary">정보 저장</button>
            </div>
        </form>
    </section>
</main>

<script>
(function () {
  var roomId = <?php echo (int) $room_id; ?>;
  var ownerToken = <?php echo json_encode((string) $owner_token, JSON_UNESCAPED_UNICODE); ?>;
  var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;

  function postOwnerAction(action, extra) {
    var fd = new FormData();
    fd.append('action', action);
    fd.append('room_id', String(roomId));
    fd.append('eottae_talkroom_owner_token', ownerToken);
    if (extra) {
      Object.keys(extra).forEach(function (key) {
        fd.append(key, extra[key]);
      });
    }
    return fetch('/proc/eottae-talkroom-owner.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  document.querySelectorAll('[data-talk-owner-approve]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('이 참여 신청을 승인하시겠습니까?')) return;
      btn.disabled = true;
      postOwnerAction('approve_member', { member_id: btn.getAttribute('data-talk-owner-approve') })
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

  document.querySelectorAll('[data-talk-owner-reject]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('이 참여 신청을 거절하시겠습니까?')) return;
      btn.disabled = true;
      postOwnerAction('reject_member', { member_id: btn.getAttribute('data-talk-owner-reject') })
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

  document.querySelectorAll('[data-talk-owner-kick]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var nick = btn.getAttribute('data-talk-owner-kick-nick') || '회원';
      var reason = window.prompt(nick + ' 회원을 강퇴합니다.\n강퇴 사유를 입력해 주세요.', '');
      if (reason === null) return;
      reason = reason.trim();
      if (reason === '') {
        alert('강퇴 사유를 입력해 주세요.');
        return;
      }
      var canRejoin = window.confirm('재참여를 허용하시겠습니까?\n\n확인 = 허용, 취소 = 불허');
      if (!window.confirm(nick + ' 회원을 강퇴하시겠습니까?')) return;
      btn.disabled = true;
      postOwnerAction('kick_member', {
        member_id: btn.getAttribute('data-talk-owner-kick'),
        kicked_reason: reason,
        can_rejoin: canRejoin ? '1' : '0'
      }).then(function (data) {
        if (data.success) location.reload();
        else alert(data.message || '처리에 실패했습니다.');
        btn.disabled = false;
      }).catch(function () {
        alert('네트워크 오류가 발생했습니다.');
        btn.disabled = false;
      });
    });
  });

  document.querySelectorAll('[data-talk-admin-unkick]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!adminToken) return;
      if (!window.confirm('강퇴를 해제하고 회원이 다시 참여 신청할 수 있게 하시겠습니까?')) return;
      btn.disabled = true;
      var fd = new FormData();
      fd.append('action', 'unkick_member');
      fd.append('room_id', btn.getAttribute('data-talk-admin-unkick-room'));
      fd.append('member_id', btn.getAttribute('data-talk-admin-unkick'));
      fd.append('status_after', 'left');
      fd.append('eottae_talkroom_admin_token', adminToken);
      fetch('/proc/eottae-talkroom-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
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

  var noticeForm = document.getElementById('talkNoticeForm');
  if (noticeForm) {
    noticeForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(noticeForm);
      fetch('/proc/eottae-talkroom-owner.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          alert(data.message || (data.success ? '저장되었습니다.' : '실패했습니다.'));
          if (data.success) {
            var hidden = document.getElementById('talk_manage_notice_hidden');
            var noticeField = document.getElementById('talk_manage_notice');
            if (hidden && noticeField) hidden.value = noticeField.value;
            location.reload();
          }
        })
        .catch(function () { alert('네트워크 오류가 발생했습니다.'); });
    });
  }

  var clearBtn = document.querySelector('[data-talk-notice-clear]');
  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      if (!confirm('공지를 삭제하시겠습니까?')) return;
      postOwnerAction('save_notice', { room_notice: '' })
        .then(function (data) {
          alert(data.message || '');
          if (data.success) location.reload();
        });
    });
  }

  var manageForm = document.getElementById('talkManageForm');
  if (manageForm) {
    manageForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var hidden = document.getElementById('talk_manage_notice_hidden');
      var noticeField = document.getElementById('talk_manage_notice');
      if (hidden && noticeField) hidden.value = noticeField.value;
      var fd = new FormData(manageForm);
      fetch('/proc/eottae-talkroom-owner.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          alert(data.message || '');
          if (data.success) location.reload();
        })
        .catch(function () { alert('네트워크 오류가 발생했습니다.'); });
    });
  }
})();
</script>

<?php
g5_page_end();
