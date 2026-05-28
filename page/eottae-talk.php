<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

$list = eottae_talkroom_list_browsable(array(
    'page'  => isset($_GET['page']) ? (int) $_GET['page'] : 1,
    'limit' => 50,
),
    !empty($member['mb_id']) ? (string) $member['mb_id'] : '',
    ($is_admin === 'super')
);
$rooms = eottae_talkroom_apply_card_viewer_context(
    $list['rows'],
    !empty($member['mb_id']) ? (string) $member['mb_id'] : '',
    ($is_admin === 'super')
);
if (!empty($is_member)) {
    eottae_talkroom_enqueue_card_delete_assets();
}

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

<?php
g5_page_end();
