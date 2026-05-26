<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';

$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;
$room = eottae_talkroom_get_room($room_id);

if (!$room) {
    alert('톡방 정보를 찾을 수 없습니다.', eottae_talkroom_admin_rooms_url());
}

$admin_token = eottae_talkroom_admin_token();

g5_talk_admin_page_start('톡방 상세');
?>

<main class="promo-admin-page talk-admin-page talk-admin-detail-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_talkroom_admin_rooms_url(); ?>" class="promo-admin-page__back">← 톡방 목록</a>
        </div>
        <h1 class="promo-admin-page__title">톡방 상세</h1>
        <p class="promo-admin-page__desc">
            <?php echo $room['emoji']; ?> <?php echo $room['room_name']; ?>
            <span class="talk-apply-status <?php echo $room['status_class']; ?>"><?php echo $room['status_label']; ?></span>
        </p>
        <?php eottae_talkroom_render_admin_nav('rooms'); ?>
    </header>

    <section class="promo-admin-panel talk-admin-detail">
        <dl class="talk-admin-detail__list">
            <div class="talk-admin-detail__row">
                <dt>톡방 이름</dt>
                <dd><?php echo $room['room_name']; ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>한 줄 소개</dt>
                <dd><?php echo $room['room_description'] !== '' ? $room['room_description'] : '-'; ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>상세 설명</dt>
                <dd class="talk-admin-detail__multiline"><?php echo nl2br($room['room_detail']); ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>카테고리</dt>
                <dd><?php echo $room['category']; ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>대표 이모지</dt>
                <dd><?php echo $room['emoji']; ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>운영 규칙</dt>
                <dd class="talk-admin-detail__multiline"><?php echo nl2br($room['rules']); ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>방장</dt>
                <dd><?php echo $room['owner_nick']; ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>공개 여부</dt>
                <dd><?php echo $room['visibility_label']; ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>가입 방식</dt>
                <dd><?php echo $room['join_type_label']; ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>신청 사유</dt>
                <dd class="talk-admin-detail__multiline"><?php echo nl2br($room['apply_reason']); ?></dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>신청자</dt>
                <dd>
                    <?php echo $room['owner_nick']; ?> (<?php echo $room['owner_mb_id']; ?>)
                    <?php if ($room['owner_email'] !== '') { ?><br><span class="talk-admin-table__sub"><?php echo $room['owner_email']; ?></span><?php } ?>
                </dd>
            </div>
            <div class="talk-admin-detail__row">
                <dt>신청일</dt>
                <dd><?php echo $room['created_at'] !== '0000-00-00 00:00:00' ? $room['created_at'] : '-'; ?></dd>
            </div>
            <?php if ($room['approved_at'] !== '' && $room['approved_at'] !== '0000-00-00 00:00:00') { ?>
            <div class="talk-admin-detail__row">
                <dt>승인 정보</dt>
                <dd><?php echo $room['approved_by']; ?> · <?php echo $room['approved_at']; ?></dd>
            </div>
            <?php } ?>
            <?php if ($room['status'] === 'rejected' && $room['reject_reason'] !== '') { ?>
            <div class="talk-admin-detail__row talk-admin-detail__row--reject">
                <dt>반려 사유</dt>
                <dd class="talk-admin-detail__multiline"><?php echo nl2br($room['reject_reason']); ?></dd>
            </div>
            <?php } ?>
        </dl>

        <div class="talk-admin-detail__actions">
            <?php if (in_array($room['status'], array('approved', 'active'), true)) { ?>
            <button type="button" class="promo-admin-btn" data-talk-stop="<?php echo (int) $room['room_id']; ?>">운영중지</button>
            <a href="<?php echo eottae_talkroom_enter_url((int) $room['room_id']); ?>" class="promo-admin-btn" target="_blank" rel="noopener noreferrer">톡방 열기</a>
            <a href="<?php echo eottae_talkroom_list_url(); ?>" class="promo-admin-btn" target="_blank" rel="noopener noreferrer">공개 목록 보기</a>
            <?php } ?>
            <a href="<?php echo eottae_talkroom_admin_rooms_url(); ?>" class="promo-admin-btn">목록으로</a>
        </div>
    </section>
</main>

<?php
eottae_talkroom_render_admin_actions_script($admin_token);
g5_talk_admin_page_end();
