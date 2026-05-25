<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';

if (!empty($_GET['batch_approve']) && isset($_GET['confirm']) && (string) $_GET['confirm'] === '1') {
    $batch_result = eottae_talkroom_approve_all_pending_rooms($member['mb_id']);
    $batch_message = (string) ($batch_result['message'] ?? '일괄 승인 처리를 완료했습니다.');
    goto_url(eottae_talkroom_admin_applies_url().'?status=pending&batch_done=1&msg='.urlencode($batch_message));
}

$filter = isset($_GET['status']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['status']) : 'pending';
if (!in_array($filter, array('pending', 'all', 'rejected', 'approved', 'stopped'), true)) {
    $filter = 'pending';
}

$pending_count = eottae_talkroom_pending_count();
$applications = eottae_talkroom_admin_resolve_applications($filter === 'all' ? 'all' : $filter, 200);
if (!is_array($applications)) {
    $applications = array();
}

$admin_token = eottae_talkroom_admin_token();

g5_talk_admin_page_start('개설 신청 관리');
?>

<main class="promo-admin-page talk-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_talkroom_list_url(); ?>" class="promo-admin-page__back">← 세부톡방</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">개설 신청 관리</h1>
        <p class="promo-admin-page__desc">
            회원 톡방 개설 신청을 검토하고 승인 또는 반려합니다.
            <?php if ($pending_count > 0) { ?>
            <strong class="talk-admin-page__pending">승인 대기 <?php echo number_format($pending_count); ?>건</strong>
            <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>?batch_approve=1&amp;confirm=1" class="talk-admin-page__batch-approve" onclick="return confirm('승인 대기 <?php echo number_format($pending_count); ?>건을 모두 승인할까요?');">전체 승인</a>
            <?php } ?>
            <?php if (!empty($_GET['batch_done']) && !empty($_GET['msg'])) { ?>
            <span class="talk-admin-page__batch-done" role="status"><?php echo htmlspecialchars((string) $_GET['msg'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php } ?>
        </p>
        <?php eottae_talkroom_render_admin_nav('applies'); ?>
    </header>

    <?php eottae_talkroom_render_admin_applies_panel($applications, $filter, $pending_count); ?>
</main>

<?php
eottae_talkroom_render_admin_actions_script($admin_token);
g5_talk_admin_page_end();
