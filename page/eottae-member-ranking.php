<?php
include_once(dirname(__FILE__).'/_init.php');

include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
include_once G5_PATH.'/components/eottae/member-growth-display.php';

eottae_member_growth_ensure_schema();

$type = isset($_GET['type']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['type']) : 'week';
$types = eottae_member_growth_ranking_types();
if (!isset($types[$type])) {
    $type = 'week';
}

$ranking = eottae_member_growth_ranking_list($type, 30);
$badge_book_url = eottae_member_growth_badge_book_url();
$history_week = isset($_GET['history_week']) ? preg_replace('/[^0-9W\-]/', '', (string) $_GET['history_week']) : '';
$history_weeks = eottae_member_growth_list_ranking_week_keys('week', 8);
$history = ($type === 'week' && !empty($history_weeks))
    ? eottae_member_growth_list_ranking_history($history_week !== '' ? $history_week : '', 'week', 10)
    : array('week_key' => '', 'items' => array());

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth.css">', 23);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth-social.css">', 24);

g5_page_start('활동 랭킹');
?>

<main class="sebu-rank-page">
    <h1 class="sebu-rank-page__title">세부어때 활동 랭킹</h1>
    <p class="sebu-rank-page__desc">과도한 경쟁보다는 꾸준히 도움이 되는 활동을 응원합니다. 주간·월간 활동을 중심으로 소개해요.</p>

    <nav class="sebu-rank-tabs" aria-label="랭킹 종류">
        <?php foreach ($types as $key => $meta) { ?>
        <a href="<?php echo eottae_member_growth_ranking_url($key); ?>" class="sebu-rank-tabs__btn<?php echo $key === $type ? ' is-active' : ''; ?>">
            <?php echo get_text($meta['label']); ?>
        </a>
        <?php } ?>
    </nav>

    <?php if (empty($ranking)) { ?>
    <p>아직 집계할 활동이 없습니다. 글·댓글·챌린지 참여로 첫 발을 내딛어 보세요!</p>
    <?php } else { ?>
    <ol class="sebu-rank-list">
        <?php foreach ($ranking as $row) {
            $profile = $row['profile'] ?? array();
            $stats = $row['stats'] ?? array();
            $profile_url = eottae_member_growth_profile_url($row['mb_id']);
            ?>
        <li class="sebu-rank-list__item">
            <span class="sebu-rank-list__rank"><?php echo (int) $row['rank']; ?></span>
            <div>
                <a href="<?php echo get_text($profile_url); ?>" class="sebu-rank-list__nick"><?php echo get_text($row['display_nick']); ?></a>
                <div class="sebu-rank-list__meta">
                    <?php if (!empty($profile['main_badge'])) {
                        echo eottae_member_growth_render_badge($profile['main_badge'], true);
                    } elseif (!empty($profile['level'])) {
                        echo eottae_member_growth_render_level_chip($profile['level']);
                    } ?>
                    <span class="sebu-rank-list__stats">
                        글 <?php echo number_format((int) ($stats['post_count'] ?? 0)); ?>
                        · 댓글 <?php echo number_format((int) ($stats['comment_count'] ?? 0)); ?>
                    </span>
                </div>
            </div>
            <span class="sebu-rank-list__score"><?php echo number_format((int) ($row['rank_score'] ?? 0)); ?>점</span>
        </li>
        <?php } ?>
    </ol>
    <?php } ?>

    <?php if ($type === 'week' && !empty($history_weeks)) { ?>
    <section class="sebu-rank-history" style="margin-top:32px">
        <h2 class="sebu-rank-page__title" style="font-size:1.05rem">지난 주 랭킹</h2>
        <nav class="sebu-rank-tabs" aria-label="지난 주">
            <?php foreach ($history_weeks as $wk) { ?>
            <a href="<?php echo eottae_member_growth_ranking_url('week'); ?>&amp;history_week=<?php echo urlencode($wk); ?>" class="sebu-rank-tabs__btn<?php echo ($history['week_key'] ?? '') === $wk ? ' is-active' : ''; ?>"><?php echo get_text($wk); ?></a>
            <?php } ?>
        </nav>
        <?php if (!empty($history['items'])) { ?>
        <ol class="sebu-rank-list">
            <?php foreach ($history['items'] as $row) {
                $hp = $row['profile'] ?? array();
                ?>
            <li class="sebu-rank-list__item">
                <span class="sebu-rank-list__rank"><?php echo (int) $row['rank_position']; ?></span>
                <div>
                    <a href="<?php echo get_text($row['profile_url'] ?? '#'); ?>" class="sebu-rank-list__nick"><?php echo get_text($row['display_nick']); ?></a>
                    <?php if (!empty($hp['main_badge'])) {
                        echo eottae_member_growth_render_badge($hp['main_badge'], true);
                    } ?>
                </div>
                <span class="sebu-rank-list__score"><?php echo number_format((int) $row['rank_score']); ?>점</span>
            </li>
            <?php } ?>
        </ol>
        <?php } else { ?>
        <p>저장된 지난 주 랭킹이 없습니다. 관리자가 주간 스냅샷을 저장하면 표시됩니다.</p>
        <?php } ?>
    </section>
    <?php } ?>

    <p style="margin-top:24px">
        <a href="<?php echo $badge_book_url; ?>">뱃지 도감 보기</a>
        <?php if ($is_member) { ?>
        · <a href="<?php echo eottae_member_growth_mypage_url(); ?>">내 등급/뱃지</a>
        <?php } ?>
    </p>
</main>

<?php
g5_page_end();
