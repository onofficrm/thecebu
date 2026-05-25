<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/talk-report.php';

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-talk-reports.php'));
}

eottae_talkroom_upgrade_schema();

$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;
$is_super = ($is_admin === 'super');
$status = isset($_GET['status']) ? trim((string) $_GET['status']) : 'pending';

if (!eottae_talkroom_can_manage_room($room_id, $member['mb_id'], $is_super)) {
    alert('신고 관리 권한이 없습니다.', eottae_talkroom_list_url());
}

$room = eottae_talkroom_get_operating_room($room_id);
if (!$room) {
    alert('운영 중인 톡방을 찾을 수 없습니다.', eottae_talkroom_list_url());
}

$detail = eottae_talkroom_format_detail($room, eottae_talkroom_room_stats($room_id));
$reports = eottae_talkroom_list_room_reports($room_id, $status === 'all' ? 'all' : $status, 200);
$pending_count = eottae_talkroom_pending_report_count($room_id);
$owner_token = eottae_talkroom_owner_token();

g5_page_start('신고 관리');
?>

<main class="mypage-subpage talk-reports-page">
    <p class="mypage-subpage__back">
        <a href="<?php echo eottae_talkroom_owner_manage_url($room_id); ?>">← 톡방 관리</a>
        · <a href="<?php echo eottae_talkroom_enter_url($room_id); ?>">톡방으로</a>
    </p>
    <h1 class="mypage-subpage__title">신고 관리</h1>
    <p class="talk-manage-page__intro"><?php echo $detail['emoji']; ?> <?php echo $detail['room_name']; ?></p>

    <nav class="talk-admin-filter talk-reports-filter">
        <a href="<?php echo eottae_talkroom_owner_reports_url($room_id); ?>?status=pending" class="talk-admin-filter__item<?php echo $status === 'pending' ? ' is-active' : ''; ?>">접수<?php if ($pending_count > 0) { ?> (<?php echo number_format($pending_count); ?>)<?php } ?></a>
        <a href="<?php echo eottae_talkroom_owner_reports_url($room_id); ?>?status=all" class="talk-admin-filter__item<?php echo $status === 'all' ? ' is-active' : ''; ?>">전체</a>
        <a href="<?php echo eottae_talkroom_owner_reports_url($room_id); ?>?status=resolved" class="talk-admin-filter__item<?php echo $status === 'resolved' ? ' is-active' : ''; ?>">처리완료</a>
        <a href="<?php echo eottae_talkroom_owner_reports_url($room_id); ?>?status=dismissed" class="talk-admin-filter__item<?php echo $status === 'dismissed' ? ' is-active' : ''; ?>">기각</a>
    </nav>

    <section class="promo-admin-panel talk-manage-panel">
        <?php if (empty($reports)) { ?>
        <p class="promo-admin-empty">표시할 신고가 없습니다.</p>
        <?php } else { ?>
        <ul class="talk-report-list">
            <?php foreach ($reports as $item) { ?>
            <li class="talk-report-list__item">
                <div class="talk-report-list__head">
                    <span class="talk-apply-status <?php echo $item['status_class']; ?>"><?php echo $item['status_label']; ?></span>
                    <strong><?php echo $item['target_type_label']; ?> #<?php echo $item['target_id']; ?></strong>
                    <span class="talk-report-list__meta"><?php echo $item['created_at'] !== '0000-00-00 00:00:00' ? substr($item['created_at'], 0, 16) : '-'; ?></span>
                </div>
                <p class="talk-report-list__reason"><strong><?php echo $item['reason_label']; ?></strong><?php if ($item['memo'] !== '') { ?> · <?php echo $item['memo']; ?><?php } ?></p>
                <p class="talk-report-list__meta">신고 <?php echo $item['reporter_nick']; ?> (<?php echo $item['reporter_mb_id']; ?>) · 대상 <?php echo $item['target_author']; ?> (<?php echo $item['target_mb_id']; ?>)</p>
                <?php if ($item['target_preview'] !== '') { ?>
                <p class="talk-report-list__preview"><?php echo $item['target_preview']; ?></p>
                <?php } ?>
                <?php if ($item['target_href'] !== '') { ?>
                <p class="talk-report-list__link"><a href="<?php echo $item['target_href']; ?>" target="_blank" rel="noopener noreferrer">대상 보기</a></p>
                <?php } ?>
                <?php if ($item['status'] === 'pending' || $item['status'] === 'reviewed') { ?>
                <div class="talk-report-list__actions">
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-report-review="<?php echo (int) $item['report_id']; ?>" data-talk-report-room="<?php echo (int) $room_id; ?>">확인</button>
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-report-dismiss="<?php echo (int) $item['report_id']; ?>" data-talk-report-room="<?php echo (int) $room_id; ?>">기각</button>
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-talk-report-delete="<?php echo (int) $item['report_id']; ?>" data-talk-report-room="<?php echo (int) $room_id; ?>">삭제</button>
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-report-kick="<?php echo (int) $item['report_id']; ?>" data-talk-report-room="<?php echo (int) $room_id; ?>">강퇴</button>
                </div>
                <?php } elseif ($item['handled_by'] !== '') { ?>
                <p class="talk-report-list__meta">처리 <?php echo $item['handled_by_nick']; ?> · <?php echo $item['handled_at'] !== '0000-00-00 00:00:00' ? substr($item['handled_at'], 0, 16) : '-'; ?></p>
                <?php } ?>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>
</main>

<?php eottae_talkroom_render_report_handle_script($owner_token, false); ?>

<?php
g5_page_end();
