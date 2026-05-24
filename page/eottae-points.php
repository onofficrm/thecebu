<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-points.php'));
}

$point = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
$rows = array();
$sql = " select * from {$g5['point_table']}
    where mb_id = '".sql_escape_string($member['mb_id'])."'
    order by po_id desc
    limit 30 ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $rows[] = $row;
}

g5_page_start('포인트');
?>

<main class="mypage-subpage">
    <?php eottae_render_mypage_back(); ?>
    <h1 class="mypage-subpage__title">포인트</h1>

    <section class="mypage-point-summary" style="margin-bottom:20px">
        <div class="mypage-point-summary__box" style="grid-column:1/-1">
            <p class="mypage-point-summary__label">보유 포인트</p>
            <p class="mypage-point-summary__value"><?php echo number_format($point); ?>P</p>
        </div>
    </section>

    <?php if (empty($rows)) { ?>
    <div class="empty-state">
        <p class="empty-state__title">포인트 내역이 없습니다</p>
        <p>리뷰 작성·커뮤니티 활동으로 포인트를 모아 보세요.</p>
    </div>
    <?php } else { ?>
    <ul class="mypage-point-list">
        <?php foreach ($rows as $row) {
            $delta = (int) $row['po_point'];
            $cls = $delta >= 0 ? 'positive' : 'negative';
            $sign = $delta >= 0 ? '+' : '';
            ?>
        <li class="mypage-point-list__item">
            <div>
                <div><?php echo get_text($row['po_content']); ?></div>
                <small style="color:var(--eottae-muted)"><?php echo substr($row['po_datetime'], 0, 16); ?></small>
            </div>
            <strong class="<?php echo $cls; ?>"><?php echo $sign.number_format($delta); ?>P</strong>
        </li>
        <?php } ?>
    </ul>
    <?php } ?>

    <p style="margin-top:16px;font-size:12px;color:var(--eottae-muted)">
        업체 리뷰 작성 시 기본 <?php echo number_format(defined('EOTTae_REVIEW_POINT_BASE') ? EOTTae_REVIEW_POINT_BASE : 30); ?>P, 사진 첨부 시 추가 <?php echo number_format(defined('EOTTae_REVIEW_POINT_PHOTO') ? EOTTae_REVIEW_POINT_PHOTO : 20); ?>P가 자동 지급됩니다.
    </p>
</main>

<?php
g5_page_end();
