<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
include_once G5_LIB_PATH.'/eottae-calendar-report.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/calendar-admin-nav.php';

$status = isset($_GET['status']) ? trim((string) $_GET['status']) : 'pending';
$reports = eottae_calendar_admin_list_reports($status === 'all' ? 'all' : $status, 300);
$pending_count = eottae_calendar_admin_pending_report_count();
$admin_token = eottae_talkroom_admin_token();

g5_talk_admin_page_start('일정 신고 관리');
?>

<main class="promo-admin-page talk-admin-page sebu-cal-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_calendar_list_url(); ?>" class="promo-admin-page__back">← 세부어때 캘린더</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">일정 신고 관리</h1>
        <p class="promo-admin-page__desc">회원·Google Calendar 일정에 대한 신고를 확인하고 처리합니다.</p>
        <?php eottae_calendar_render_admin_nav('reports'); ?>
    </header>

    <nav class="talk-admin-filter talk-reports-filter">
        <a href="<?php echo eottae_calendar_admin_reports_url('pending'); ?>" class="talk-admin-filter__item<?php echo $status === 'pending' ? ' is-active' : ''; ?>">접수<?php if ($pending_count > 0) { ?> (<?php echo number_format($pending_count); ?>)<?php } ?></a>
        <a href="<?php echo eottae_calendar_admin_reports_url('all'); ?>" class="talk-admin-filter__item<?php echo $status === 'all' ? ' is-active' : ''; ?>">전체</a>
        <a href="<?php echo eottae_calendar_admin_reports_url('reviewed'); ?>" class="talk-admin-filter__item<?php echo $status === 'reviewed' ? ' is-active' : ''; ?>">검토중</a>
        <a href="<?php echo eottae_calendar_admin_reports_url('deleted'); ?>" class="talk-admin-filter__item<?php echo $status === 'deleted' ? ' is-active' : ''; ?>">일정 처리</a>
        <a href="<?php echo eottae_calendar_admin_reports_url('rejected'); ?>" class="talk-admin-filter__item<?php echo $status === 'rejected' ? ' is-active' : ''; ?>">기각</a>
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
                        <th scope="col">일정</th>
                        <th scope="col">출처</th>
                        <th scope="col">사유</th>
                        <th scope="col">신고자</th>
                        <th scope="col">상태</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $item) { ?>
                    <tr>
                        <td data-label="신고일"><?php echo $item['created_at'] !== '0000-00-00 00:00:00' ? substr($item['created_at'], 0, 16) : '-'; ?></td>
                        <td data-label="일정">
                            <?php if ($item['event_href'] !== '') { ?>
                            <a href="<?php echo $item['event_href']; ?>" target="_blank" rel="noopener noreferrer"><?php echo get_text($item['event_title']); ?></a>
                            <?php } else { ?>
                            <?php echo get_text($item['event_title']); ?>
                            <?php } ?>
                        </td>
                        <td data-label="출처"><?php echo $item['event_source'] === 'google' ? 'Google Calendar' : '회원 등록'; ?></td>
                        <td data-label="사유">
                            <?php echo get_text($item['reason_label']); ?>
                            <?php if ($item['memo'] !== '') { ?><div class="talk-report-list__meta"><?php echo get_text($item['memo']); ?></div><?php } ?>
                        </td>
                        <td data-label="신고자"><?php echo get_text($item['reporter_nick']); ?></td>
                        <td data-label="상태"><span class="talk-apply-status <?php echo $item['status_class']; ?>"><?php echo get_text($item['status_label']); ?></span></td>
                        <td data-label="관리" class="talk-report-list__actions talk-report-list__actions--table">
                            <?php if ($item['status'] === 'pending' || $item['status'] === 'reviewed') { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-sebu-cal-report-review="<?php echo (int) $item['report_id']; ?>">검토</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-sebu-cal-report-reject="<?php echo (int) $item['report_id']; ?>">기각</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-sebu-cal-report-hide="<?php echo (int) $item['report_id']; ?>">일정 숨김</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-sebu-cal-report-delete="<?php echo (int) $item['report_id']; ?>">일정 삭제</button>
                            <?php } else { ?>
                            <?php echo $item['handled_by_nick'] !== '' ? get_text($item['handled_by_nick']) : '-'; ?>
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
include_once G5_PATH.'/components/eottae/calendar-report.php';
eottae_calendar_render_admin_report_script($admin_token);
g5_talk_admin_page_end();
