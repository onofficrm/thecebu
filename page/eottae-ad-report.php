<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-ad-register.php'));
}

include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';
eottae_ad_platform_ensure_schema();

if (!eottae_ad_platform_can_manage($member)) {
    alert('사업자회원 또는 최고관리자만 이용할 수 있습니다.', G5_URL.'/page/eottae-mypage.php');
}

$ad_id = isset($_GET['ad_id']) ? (int) $_GET['ad_id'] : 0;
$report = eottae_ad_platform_get_campaign_report($ad_id, 30);
$is_super = ($is_admin === 'super');

if (!$report || !eottae_ad_platform_member_owns_campaign($report['campaign'], $member['mb_id'], $is_super)) {
    alert('광고를 찾을 수 없습니다.', G5_URL.'/page/eottae-ad-register.php');
}

$campaign = $report['campaign'];
$daily = $report['daily'];
$summary = $report['summary'];
$back_url = G5_URL.'/page/eottae-ad-register.php';
$edit_url = eottae_ad_platform_edit_url((int) $campaign['ad_id']);
$max_impressions = 0;
foreach ($daily as $row) {
    if ((int) $row['impressions'] > $max_impressions) {
        $max_impressions = (int) $row['impressions'];
    }
}

g5_page_start('광고 성과 리포트');
?>

<main class="mypage-subpage ad-platform-report">
    <?php eottae_render_mypage_back(); ?>
    <header class="ad-platform-register__header">
        <h1 class="mypage-subpage__title">광고 성과 리포트</h1>
        <p class="ad-platform-register__lead">
            <?php echo get_text($campaign['title']); ?> · <?php echo get_text($campaign['slot_name']); ?>
            · <?php echo get_text($campaign['start_date']); ?> ~ <?php echo get_text($campaign['end_date']); ?>
        </p>
        <p class="ad-platform-register__point">
            상태 <?php echo get_text($campaign['status_label']); ?>
            <?php if ((int) $summary['days_left'] >= 0 && $campaign['status'] === 'active') { ?>
            · 종료까지 <?php echo (int) $summary['days_left']; ?>일
            <?php } ?>
        </p>
    </header>

    <section class="ad-platform-report__summary promo-admin-panel">
        <div class="ad-platform-report__metric">
            <span class="ad-platform-report__metric-label">노출</span>
            <strong><?php echo number_format((int) $summary['impressions']); ?></strong>
        </div>
        <div class="ad-platform-report__metric">
            <span class="ad-platform-report__metric-label">클릭</span>
            <strong><?php echo number_format((int) $summary['clicks']); ?></strong>
        </div>
        <div class="ad-platform-report__metric">
            <span class="ad-platform-report__metric-label">CTR</span>
            <strong><?php echo number_format((float) $summary['ctr'], 2); ?>%</strong>
        </div>
        <div class="ad-platform-report__metric">
            <span class="ad-platform-report__metric-label">집행 포인트</span>
            <strong><?php echo number_format((int) $campaign['total_points']); ?>P</strong>
        </div>
        <?php if ((int) $summary['bid_bonus'] > 0) { ?>
        <div class="ad-platform-report__metric">
            <span class="ad-platform-report__metric-label">입찰 보너스</span>
            <strong><?php echo number_format((int) $summary['bid_bonus']); ?>P</strong>
        </div>
        <?php } ?>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">최근 30일 노출 추이</h2>
        <?php if (empty($daily)) { ?>
        <p class="promo-admin-form__hint">아직 집계된 일별 데이터가 없습니다.</p>
        <?php } else { ?>
        <div class="ad-platform-report__chart" role="img" aria-label="최근 30일 광고 노출 추이">
            <?php foreach ($daily as $row) {
                $height = $max_impressions > 0 ? max(4, round(((int) $row['impressions'] / $max_impressions) * 100)) : 4;
                $label = $row['stat_date'] !== '' ? date('n/j', strtotime($row['stat_date'])) : '';
                ?>
            <div class="ad-platform-report__bar" style="--bar-height: <?php echo (int) $height; ?>%;" title="<?php echo get_text($label); ?> · 노출 <?php echo number_format((int) $row['impressions']); ?> · 클릭 <?php echo number_format((int) $row['clicks']); ?>">
                <span class="ad-platform-report__bar-fill"></span>
                <span class="ad-platform-report__bar-label"><?php echo get_text($label); ?></span>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">일별 상세</h2>
        <?php if (empty($daily)) { ?>
        <p class="promo-admin-form__hint">노출·클릭이 발생하면 여기에 표시됩니다.</p>
        <?php } else { ?>
        <div class="ad-platform-report__table-wrap">
            <table class="ad-platform-report__table">
                <thead>
                    <tr>
                        <th scope="col">날짜</th>
                        <th scope="col">노출</th>
                        <th scope="col">클릭</th>
                        <th scope="col">CTR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($daily) as $row) { ?>
                    <tr>
                        <td><?php echo get_text($row['stat_date']); ?></td>
                        <td><?php echo number_format((int) $row['impressions']); ?></td>
                        <td><?php echo number_format((int) $row['clicks']); ?></td>
                        <td><?php echo number_format((float) $row['ctr'], 2); ?>%</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>

    <section class="ad-platform-report__actions">
        <a href="<?php echo htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8'); ?>" class="promo-reward-btn">광고 목록</a>
        <?php if (eottae_ad_platform_member_can_extend_campaign($campaign)) { ?>
        <a href="<?php echo htmlspecialchars($edit_url, ENT_QUOTES, 'UTF-8'); ?>" class="promo-reward-btn promo-reward-btn--primary">연장하기</a>
        <?php } ?>
    </section>
</main>

<?php
g5_page_end();
