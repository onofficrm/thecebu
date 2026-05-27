<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

$list = eottae_talkroom_list_public(array(
    'page'  => isset($_GET['page']) ? (int) $_GET['page'] : 1,
    'limit' => 50,
));
$rooms = eottae_talkroom_apply_card_viewer_context(
    $list['rows'],
    !empty($member['mb_id']) ? (string) $member['mb_id'] : '',
    ($is_admin === 'super')
);
$talk_owner_token = !empty($is_member) ? eottae_talkroom_owner_token() : '';

g5_page_start('세부톡방');
?>

<main class="mypage-subpage talk-page">
    <header class="talk-page__hero">
        <h1 class="talk-page__title">세부톡방</h1>
        <?php if ($is_admin === 'super') { ?>
        <p class="talk-page__admin-links">
            <?php
            $talk_pending = eottae_talkroom_pending_count();
            ?>
            <a href="<?php echo eottae_talkroom_admin_rooms_url(); ?>">톡방 목록 관리<?php if ($talk_pending > 0) { ?> (승인 대기 <?php echo number_format($talk_pending); ?>)<?php } ?></a>
            <?php
            $talk_kicked = function_exists('eottae_talkroom_admin_kicked_count') ? eottae_talkroom_admin_kicked_count() : 0;
            ?>
            · <a href="<?php echo eottae_talkroom_admin_kicked_url(); ?>">강퇴 회원 관리<?php if ($talk_kicked > 0) { ?> (<?php echo number_format($talk_kicked); ?>)<?php } ?></a>
        </p>
        <?php } ?>
        <p class="talk-page__intro">
            세부 교민, 여행자, 사업자들이 주제별로 소통하는 공간입니다.<br>
            관심 있는 톡방에 참여하거나 직접 새로운 톡방을 개설해보세요.
        </p>
        <nav class="talk-page__actions" aria-label="세부톡방 주요 메뉴">
            <a href="<?php echo eottae_talkroom_create_url(); ?>" class="talk-page__btn talk-page__btn--primary">톡방 만들기</a>
            <a href="<?php echo eottae_talkroom_my_url(); ?>" class="talk-page__btn">내 톡방</a>
            <a href="<?php echo eottae_talkroom_apply_status_url(); ?>" class="talk-page__btn">내가 만든 톡방</a>
        </nav>
    </header>

    <section class="talk-page__list" aria-label="톡방 목록">
        <?php if (empty($rooms)) { ?>
        <div class="empty-state talk-page__empty">
            <p class="empty-state__title">아직 개설된 톡방이 없습니다.</p>
            <p>첫 번째 세부톡방을 만들어보세요.</p>
            <a href="<?php echo eottae_talkroom_create_url(); ?>" class="talk-page__btn talk-page__btn--primary talk-page__empty-btn">톡방 만들기</a>
        </div>
        <?php } else { ?>
        <div class="talk-room-grid">
            <?php foreach ($rooms as $room) {
                eottae_talkroom_render_card($room);
            } ?>
        </div>
        <?php } ?>
    </section>
</main>

<?php if ($talk_owner_token !== '') { ?>
<script>
(function () {
  var ownerToken = <?php echo json_encode($talk_owner_token, JSON_UNESCAPED_UNICODE); ?>;

  function postDelete(roomId) {
    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('room_id', String(roomId));
    fd.append('eottae_talkroom_owner_token', ownerToken);
    return fetch('/proc/eottae-talkroom-owner.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
    }).then(function (res) { return res.json(); });
  }

  document.querySelectorAll('[data-talk-room-delete]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var roomName = btn.getAttribute('data-room-name') || '이 톡방';
      var roomId = btn.getAttribute('data-talk-room-delete');
      if (!roomId) {
        return;
      }
      if (!window.confirm(roomName + ' 톡방을 완전히 삭제하시겠습니까?\n\n멤버·신고·로그 데이터가 함께 삭제되며 되돌릴 수 없습니다.')) {
        return;
      }
      btn.disabled = true;
      postDelete(roomId)
        .then(function (data) {
          if (data && data.success) {
            var card = btn.closest('.talk-room-card');
            if (card && card.parentNode) {
              card.parentNode.removeChild(card);
              if (!document.querySelector('.talk-room-card')) {
                window.location.reload();
              }
              return;
            }
            window.location.reload();
            return;
          }
          window.alert((data && data.message) ? data.message : '삭제에 실패했습니다.');
          btn.disabled = false;
        })
        .catch(function () {
          window.alert('네트워크 오류가 발생했습니다.');
          btn.disabled = false;
        });
    });
  });
}());
</script>
<?php } ?>

<?php
g5_page_end();
