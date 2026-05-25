<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';

$filter = isset($_GET['status']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['status']) : 'pending';
if (!in_array($filter, array('pending', 'all', 'rejected', 'approved', 'stopped'), true)) {
    $filter = 'pending';
}

$pending_count = eottae_talkroom_pending_count();
$applications = eottae_talkroom_admin_resolve_applications($filter === 'all' ? 'all' : $filter, 200);
if (!is_array($applications)) {
    $applications = array();
}

if (function_exists('eottae_talkroom_set_admin_applies_context')) {
    eottae_talkroom_set_admin_applies_context($applications, $filter, $pending_count);
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
            <?php } ?>
        </p>
        <?php eottae_talkroom_render_admin_nav('applies'); ?>
    </header>
</main>

<?php
eottae_talkroom_render_admin_actions_script($admin_token);
g5_talk_admin_page_end();
