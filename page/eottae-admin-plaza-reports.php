<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';
include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
include_once G5_PATH.'/components/eottae/plaza-admin-nav.php';

$status = isset($_GET['status']) ? trim((string) $_GET['status']) : 'pending';
$allowed = array('pending', 'reviewed', 'deleted', 'rejected', 'all');
if (!in_array($status, $allowed, true)) {
    $status = 'pending';
}

$reports = eottae_plaza_admin_list_reports($status, 300);
$pending_count = eottae_plaza_admin_pending_report_count();
$admin_token = eottae_plaza_admin_token();

g5_page_start('세부광장 신고 관리');
?>

<main class="promo-admin-page talk-admin-page plaza-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_plaza_list_url(); ?>" class="promo-admin-page__back">← 세부광장</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">세부광장 신고 관리</h1>
        <p class="promo-admin-page__desc">세부광장 글·댓글 신고를 확인하고 처리합니다.</p>
        <?php eottae_plaza_render_admin_nav('reports'); ?>
    </header>

    <nav class="talk-admin-filter talk-reports-filter">
        <a href="<?php echo eottae_plaza_admin_reports_url('pending'); ?>" class="talk-admin-filter__item<?php echo $status === 'pending' ? ' is-active' : ''; ?>">접수<?php if ($pending_count > 0) { ?> (<?php echo number_format($pending_count); ?>)<?php } ?></a>
        <a href="<?php echo eottae_plaza_admin_reports_url('all'); ?>" class="talk-admin-filter__item<?php echo $status === 'all' ? ' is-active' : ''; ?>">전체</a>
        <a href="<?php echo eottae_plaza_admin_reports_url('reviewed'); ?>" class="talk-admin-filter__item<?php echo $status === 'reviewed' ? ' is-active' : ''; ?>">확인</a>
        <a href="<?php echo eottae_plaza_admin_reports_url('deleted'); ?>" class="talk-admin-filter__item<?php echo $status === 'deleted' ? ' is-active' : ''; ?>">삭제처리</a>
        <a href="<?php echo eottae_plaza_admin_reports_url('rejected'); ?>" class="talk-admin-filter__item<?php echo $status === 'rejected' ? ' is-active' : ''; ?>">기각</a>
    </nav>

    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($reports)) { ?>
        <p class="promo-admin-empty">표시할 신고가 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table talk-admin-table--reports plaza-admin-table">
                <thead>
                    <tr>
                        <th scope="col">신고일</th>
                        <th scope="col">대상</th>
                        <th scope="col">사유</th>
                        <th scope="col">신고자</th>
                        <th scope="col">상태</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $item) {
                        $status_class = 'talk-apply-status';
                        if ($item['status'] === 'pending') {
                            $status_class .= ' talk-apply-status--pending';
                        } elseif ($item['status'] === 'rejected') {
                            $status_class .= ' talk-apply-status--rejected';
                        } elseif ($item['status'] === 'deleted') {
                            $status_class .= ' talk-apply-status--approved';
                        }
                        ?>
                    <tr>
                        <td data-label="신고일"><?php echo $item['created_at'] !== '0000-00-00 00:00:00' ? substr($item['created_at'], 0, 16) : '-'; ?></td>
                        <td data-label="대상">
                            <?php echo $item['target_type_label']; ?> #<?php echo $item['target_id']; ?>
                            <div class="talk-report-list__preview"><?php echo $item['target_preview']; ?></div>
                            <div class="talk-report-list__meta"><?php echo $item['target_author']; ?></div>
                            <?php if ($item['target_href'] !== '') { ?><a href="<?php echo $item['target_href']; ?>" target="_blank" rel="noopener noreferrer">보기</a><?php } ?>
                        </td>
                        <td data-label="사유"><?php echo $item['reason_label']; ?><?php if ($item['memo'] !== '') { ?><div class="talk-report-list__meta"><?php echo $item['memo']; ?></div><?php } ?></td>
                        <td data-label="신고자"><?php echo $item['reporter_nick']; ?></td>
                        <td data-label="상태"><span class="<?php echo $status_class; ?>"><?php echo $item['status_label']; ?></span></td>
                        <td data-label="관리" class="talk-report-list__actions talk-report-list__actions--table">
                            <?php if ($item['status'] === 'pending' || $item['status'] === 'reviewed') { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-plaza-report-review="<?php echo (int) $item['report_id']; ?>">확인</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-plaza-report-dismiss="<?php echo (int) $item['report_id']; ?>">기각</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-plaza-report-delete="<?php echo (int) $item['report_id']; ?>">삭제 처리</button>
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
eottae_plaza_render_report_handle_script($admin_token);
g5_page_end();
