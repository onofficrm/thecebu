<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';
include_once G5_PATH.'/components/eottae/talk-report.php';

$status = isset($_GET['status']) ? trim((string) $_GET['status']) : 'pending';
$reports = eottae_talkroom_admin_list_reports($status === 'all' ? 'all' : $status, 300);
$pending_count = eottae_talkroom_admin_pending_report_count();
$admin_token = eottae_talkroom_admin_token();

g5_page_start('신고 관리');
?>

<main class="promo-admin-page talk-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_talkroom_list_url(); ?>" class="promo-admin-page__back">← 세부톡방</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">신고 관리</h1>
        <p class="promo-admin-page__desc">전체 톡방 신고를 확인하고 처리합니다.</p>
        <?php eottae_talkroom_render_admin_nav('reports'); ?>
    </header>

    <nav class="talk-admin-filter talk-reports-filter">
        <a href="<?php echo eottae_talkroom_admin_reports_url('pending'); ?>" class="talk-admin-filter__item<?php echo $status === 'pending' ? ' is-active' : ''; ?>">접수<?php if ($pending_count > 0) { ?> (<?php echo number_format($pending_count); ?>)<?php } ?></a>
        <a href="<?php echo eottae_talkroom_admin_reports_url('all'); ?>" class="talk-admin-filter__item<?php echo $status === 'all' ? ' is-active' : ''; ?>">전체</a>
        <a href="<?php echo eottae_talkroom_admin_reports_url('resolved'); ?>" class="talk-admin-filter__item<?php echo $status === 'resolved' ? ' is-active' : ''; ?>">처리완료</a>
        <a href="<?php echo eottae_talkroom_admin_reports_url('dismissed'); ?>" class="talk-admin-filter__item<?php echo $status === 'dismissed' ? ' is-active' : ''; ?>">기각</a>
    </nav>

    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($reports)) { ?>
        <p class="promo-admin-empty">표시할 신고가 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table talk-admin-table--reports">
                <thead>
                    <tr>
                        <th scope="col">접수일</th>
                        <th scope="col">톡방</th>
                        <th scope="col">대상</th>
                        <th scope="col">사유</th>
                        <th scope="col">신고자</th>
                        <th scope="col">상태</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $item) { ?>
                    <tr>
                        <td data-label="접수일"><?php echo $item['created_at'] !== '0000-00-00 00:00:00' ? substr($item['created_at'], 0, 16) : '-'; ?></td>
                        <td data-label="톡방"><a href="<?php echo eottae_talkroom_owner_manage_url($item['room_id']); ?>"><?php echo $item['emoji']; ?> <?php echo $item['room_name']; ?></a></td>
                        <td data-label="대상">
                            <?php echo $item['target_type_label']; ?> #<?php echo $item['target_id']; ?>
                            <div class="talk-report-list__preview"><?php echo $item['target_preview']; ?></div>
                            <div class="talk-report-list__meta"><?php echo $item['target_author']; ?></div>
                            <?php if ($item['target_href'] !== '') { ?><a href="<?php echo $item['target_href']; ?>" target="_blank" rel="noopener noreferrer">보기</a><?php } ?>
                        </td>
                        <td data-label="사유"><?php echo $item['reason_label']; ?><?php if ($item['memo'] !== '') { ?><div class="talk-report-list__meta"><?php echo $item['memo']; ?></div><?php } ?></td>
                        <td data-label="신고자"><?php echo $item['reporter_nick']; ?></td>
                        <td data-label="상태"><span class="talk-apply-status <?php echo $item['status_class']; ?>"><?php echo $item['status_label']; ?></span></td>
                        <td data-label="관리" class="talk-report-list__actions talk-report-list__actions--table">
                            <?php if ($item['status'] === 'pending' || $item['status'] === 'reviewed') { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-report-review="<?php echo (int) $item['report_id']; ?>" data-talk-report-room="<?php echo (int) $item['room_id']; ?>">확인</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-report-dismiss="<?php echo (int) $item['report_id']; ?>" data-talk-report-room="<?php echo (int) $item['room_id']; ?>">기각</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-talk-report-delete="<?php echo (int) $item['report_id']; ?>" data-talk-report-room="<?php echo (int) $item['room_id']; ?>">삭제</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-report-kick="<?php echo (int) $item['report_id']; ?>" data-talk-report-room="<?php echo (int) $item['room_id']; ?>">강퇴</button>
                            <?php } else { ?>
                            <?php echo $item['handled_by_nick'] !== '' ? $item['handled_by_nick'] : '-'; ?>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>

<?php
eottae_talkroom_render_admin_actions_script($admin_token);
eottae_talkroom_render_report_handle_script($admin_token, true);
g5_page_end();
