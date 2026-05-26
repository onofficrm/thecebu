<?php
include_once(dirname(__FILE__).'/_init.php');

include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
include_once G5_PATH.'/components/eottae/member-growth-display.php';

eottae_member_growth_ensure_schema();

$viewer_mb_id = !empty($member['mb_id']) ? $member['mb_id'] : '';
$badges = eottae_member_growth_badge_book($viewer_mb_id);
$owned_count = 0;
foreach ($badges as $badge) {
    if (!empty($badge['owned'])) {
        $owned_count++;
    }
}

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth.css">', 23);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth-social.css">', 24);

g5_page_start('뱃지 도감');
?>

<main class="sebu-badge-book-page">
    <h1 class="sebu-rank-page__title">뱃지 도감</h1>
    <p class="sebu-rank-page__desc">
        세부어때에서 받을 수 있는 뱃지를 모아 두었습니다.
        <?php if ($is_member) { ?>
        현재 <strong><?php echo number_format($owned_count); ?></strong> / <?php echo number_format(count($badges)); ?>개 획득
        <?php } ?>
    </p>

    <ul class="sebu-badge-book-grid">
        <?php foreach ($badges as $badge) {
            $owned = !empty($badge['owned']);
            ?>
        <li class="sebu-badge-book-card<?php echo $owned ? ' is-owned' : ' is-locked'; ?>">
            <div class="sebu-badge-book-card__head">
                <?php echo eottae_member_growth_render_badge($badge, !empty($badge['is_main'])); ?>
                <?php if ($owned) { ?><span class="sebu-badge" style="background:#dcfce7;color:#166534">획득</span><?php } ?>
            </div>
            <p class="sebu-badge-book-card__condition"><?php echo get_text($badge['condition_label'] ?? ''); ?></p>
            <?php if (!empty($badge['badge_description'])) { ?>
            <p class="sebu-badge-book-card__desc"><?php echo get_text($badge['badge_description']); ?></p>
            <?php } ?>
        </li>
        <?php } ?>
    </ul>

    <p style="margin-top:24px">
        <a href="<?php echo eottae_member_growth_ranking_url('week'); ?>">활동 랭킹</a>
        <?php if ($is_member) { ?>
        · <a href="<?php echo eottae_member_growth_mypage_url(); ?>">내 등급/뱃지</a>
        <?php } ?>
    </p>
</main>

<?php
g5_page_end();
