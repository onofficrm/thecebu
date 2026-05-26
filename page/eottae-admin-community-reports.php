<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-community-report.lib.php';

$status = isset($_GET['status']) ? trim((string) $_GET['status']) : 'pending';
$allowed = array('pending', 'reviewed', 'deleted', 'rejected', 'all');
if (!in_array($status, $allowed, true)) {
    $status = 'pending';
}

$reports = eottae_community_admin_list_reports($status, 300);
$pending_count = eottae_community_admin_pending_report_count();
$admin_token = eottae_community_admin_token();
$community_url = function_exists('eottae_community_list_url') ? eottae_community_list_url() : G5_URL;

g5_page_start('커뮤니티 신고 관리');
?>

<main class="promo-admin-page talk-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo $community_url; ?>" class="promo-admin-page__back">← 커뮤니티</a>
            <a href="<?php echo G5_URL.'/page/eottae-admin-member-growth.php'; ?>" class="promo-admin-page__back">회원 등급 관리</a>
        </div>
        <h1 class="promo-admin-page__title">커뮤니티 신고 관리</h1>
        <p class="promo-admin-page__desc">커뮤니티 글·댓글 신고를 확인하고 처리합니다. 삭제 처리 시 신고자에게 활동 점수가 지급될 수 있습니다.</p>
    </header>

    <nav class="talk-admin-filter talk-reports-filter">
        <a href="<?php echo eottae_community_admin_reports_url('pending'); ?>" class="talk-admin-filter__item<?php echo $status === 'pending' ? ' is-active' : ''; ?>">접수<?php if ($pending_count > 0) { ?> (<?php echo number_format($pending_count); ?>)<?php } ?></a>
        <a href="<?php echo eottae_community_admin_reports_url('all'); ?>" class="talk-admin-filter__item<?php echo $status === 'all' ? ' is-active' : ''; ?>">전체</a>
        <a href="<?php echo eottae_community_admin_reports_url('reviewed'); ?>" class="talk-admin-filter__item<?php echo $status === 'reviewed' ? ' is-active' : ''; ?>">확인</a>
        <a href="<?php echo eottae_community_admin_reports_url('deleted'); ?>" class="talk-admin-filter__item<?php echo $status === 'deleted' ? ' is-active' : ''; ?>">삭제처리</a>
        <a href="<?php echo eottae_community_admin_reports_url('rejected'); ?>" class="talk-admin-filter__item<?php echo $status === 'rejected' ? ' is-active' : ''; ?>">기각</a>
    </nav>

    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($reports)) { ?>
        <p class="promo-admin-empty">표시할 신고가 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table talk-admin-table--reports">
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
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-community-report-review="<?php echo (int) $item['report_id']; ?>">확인</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-community-report-dismiss="<?php echo (int) $item['report_id']; ?>">기각</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-community-report-delete="<?php echo (int) $item['report_id']; ?>">삭제 처리</button>
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
include_once G5_PATH.'/components/eottae/community-report.php';
eottae_community_render_report_handle_script($admin_token);
g5_page_end();
