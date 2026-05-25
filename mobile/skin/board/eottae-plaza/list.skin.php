<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
include_once G5_LIB_PATH.'/eottae-plaza-likes.lib.php';
include_once G5_PATH.'/components/eottae/plaza-like.php';
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
if (function_exists('eottae_plaza_load_assets')) {
    eottae_plaza_load_assets();
}

$plaza_tabs = eottae_plaza_category_tabs($board);
$today_count = eottae_plaza_today_count();
$sort_options = eottae_plaza_sort_options(isset($sst) ? $sst : '', isset($sod) ? $sod : 'desc');
$region_options = eottae_plaza_region_options();
$current_region = (isset($sfl) && $sfl === 'wr_1' && !empty($stx)) ? trim((string) $stx) : '';
$list_base = function_exists('eottae_plaza_list_url') ? eottae_plaza_list_url() : get_pretty_url($bo_table);
$hero = function_exists('eottae_plaza_hero_data') ? eottae_plaza_hero_data() : array();
$plaza_is_super = ($is_admin === 'super');
$plaza_wr_ids = array();
for ($pi = 0; $pi < count($list); $pi++) {
    if (function_exists('eottae_plaza_is_post_visible') && !eottae_plaza_is_post_visible($list[$pi], $plaza_is_super)) {
        continue;
    }
    $plaza_wr_ids[] = (int) ($list[$pi]['wr_id'] ?? 0);
}
$plaza_like_counts = eottae_plaza_like_counts_batch($plaza_wr_ids);
$plaza_user_liked = (!empty($is_member) && !empty($member['mb_id']))
    ? eottae_plaza_user_liked_batch($member['mb_id'], $plaza_wr_ids)
    : array();
$plaza_member_token = eottae_plaza_member_token();
$plaza_login_url = eottae_plaza_login_url();
$plaza_mb_id = !empty($member['mb_id']) ? $member['mb_id'] : '';
include_once G5_PATH.'/components/eottae/plaza-talk-guide.php';
include_once G5_PATH.'/components/eottae/plaza-rules.php';
include_once G5_PATH.'/components/eottae/plaza-related-rooms.php';
include_once G5_PATH.'/components/eottae/plaza-ai-message-ui.php';
$plaza_list_context = array(
    'ca_name'    => isset($sca) ? trim((string) $sca) : '',
    'wr_subject' => '',
    'wr_content' => '',
);
?>

<div class="plaza-page board-wrap board-wrap--eottae-plaza" id="bo_list" style="width:<?php echo $width; ?>">

    <?php include G5_PATH.'/components/eottae/plaza-hero.php'; ?>

    <?php eottae_plaza_render_talk_guide('list'); ?>
    <?php eottae_plaza_render_rules(true); ?>
    <?php if ($plaza_list_context['ca_name'] !== '') {
        eottae_plaza_render_related_rooms($plaza_list_context, 3);
    } ?>

    <?php if ($is_category && !empty($plaza_tabs)) { ?>
    <nav class="plaza-tabs" aria-label="글 유형">
        <?php foreach ($plaza_tabs as $tab) {
            $href = $tab['slug'] === '' ? $list_base : get_pretty_url($bo_table, '', 'sca='.urlencode($tab['slug']));
            $active = ($tab['slug'] === '' && $sca === '') || ($tab['slug'] !== '' && $sca === $tab['slug']);
            ?>
        <a href="<?php echo $href; ?>" class="plaza-tabs__item<?php echo $active ? ' is-active' : ''; ?>">
            <span><?php echo get_text($tab['label']); ?></span>
        </a>
        <?php } ?>
    </nav>
    <?php } ?>

    <section class="plaza-toolbar">
        <form class="plaza-filter" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <?php if ($sca) { ?><input type="hidden" name="sca" value="<?php echo get_text($sca); ?>"><?php } ?>
            <label class="sound_only" for="plaza_region">지역</label>
            <select id="plaza_region" name="stx" class="plaza-filter__select" onchange="this.form.sfl.value=this.value?'wr_1':''; this.form.submit();">
                <option value="">지역 전체</option>
                <?php foreach ($region_options as $region) { ?>
                <option value="<?php echo get_text($region); ?>"<?php echo $current_region === $region ? ' selected' : ''; ?>><?php echo get_text($region); ?></option>
                <?php } ?>
            </select>
            <input type="hidden" name="sfl" value="<?php echo $current_region !== '' ? 'wr_1' : ''; ?>">
        </form>
        <form class="plaza-filter" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <?php if ($sca) { ?><input type="hidden" name="sca" value="<?php echo get_text($sca); ?>"><?php } ?>
            <?php if ($current_region !== '') { ?>
            <input type="hidden" name="sfl" value="wr_1">
            <input type="hidden" name="stx" value="<?php echo get_text($current_region); ?>">
            <?php } ?>
            <label class="sound_only" for="plaza_sort">정렬</label>
            <select id="plaza_sort" name="sst" class="plaza-filter__select" onchange="this.form.submit();">
                <?php foreach ($sort_options as $opt) { ?>
                <option value="<?php echo $opt['sst']; ?>" data-sod="<?php echo $opt['sod']; ?>"<?php echo $opt['active'] ? ' selected' : ''; ?>><?php echo $opt['label']; ?></option>
                <?php } ?>
            </select>
            <input type="hidden" name="sod" value="<?php echo isset($sod) && $sod ? $sod : 'desc'; ?>">
        </form>
    </section>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">

    <div class="plaza-feed">
        <?php
        for ($i = 0; $i < count($list); $i++) {
            $item = $list[$i];
            if (function_exists('eottae_plaza_is_post_visible') && !eottae_plaza_is_post_visible($item, $plaza_is_super)) {
                continue;
            }
            $item_wr_id = (int) ($item['wr_id'] ?? 0);
            $like_count = (int) ($plaza_like_counts[$item_wr_id] ?? 0);
            $is_liked = !empty($plaza_user_liked[$item_wr_id]);
            $can_like = !empty($is_member) && $plaza_mb_id !== '' && $plaza_mb_id !== ($item['mb_id'] ?? '');
            $ca_name = isset($item['ca_name']) ? get_text($item['ca_name']) : '';
            $region = isset($item['wr_1']) ? get_text($item['wr_1']) : '';
            $snippet = eottae_plaza_snippet(isset($item['wr_content']) ? $item['wr_content'] : '');
            $thumb = eottae_plaza_list_thumb($bo_table, (int) $item['wr_id']);
            $comment_num = isset($item['wr_comment']) ? (int) $item['wr_comment'] : 0;
            $hit_num = isset($item['wr_hit']) ? (int) $item['wr_hit'] : 0;
            $author = strip_tags(isset($item['name']) ? $item['name'] : '');
            $time_label = eottae_plaza_relative_time(isset($item['wr_datetime']) ? $item['wr_datetime'] : '');
            $is_ai_post = function_exists('eottae_plaza_ai_message_is_ai') && eottae_plaza_ai_message_is_ai($item);
            $card_class = eottae_plaza_ai_message_row_class($item, 'plaza-card');
            ?>
        <article class="<?php echo $card_class; ?>">
            <a href="<?php echo $item['href']; ?>" class="plaza-card__link">
                <div class="plaza-card__body">
                    <div class="plaza-card__badges">
                        <?php if ($is_ai_post) { ?>
                        <?php echo eottae_plaza_ai_message_render_badge($item, 'sm'); ?>
                        <?php } elseif ($ca_name !== '') { ?>
                        <span class="plaza-badge <?php echo eottae_plaza_type_badge_class($ca_name); ?>"><?php echo $ca_name; ?></span>
                        <?php } ?>
                        <?php if ($region !== '' && $region !== '기타') { ?>
                        <span class="plaza-badge plaza-badge--region"><?php echo $region; ?></span>
                        <?php } ?>
                    </div>
                    <h2 class="plaza-card__title"><?php echo $item['subject']; ?></h2>
                    <?php if ($snippet !== '') { ?>
                    <p class="plaza-card__excerpt"><?php echo $snippet; ?></p>
                    <?php } ?>
                    <p class="plaza-card__meta">
                        <span class="plaza-card__author"><?php echo get_text($author); ?></span>
                        <?php if ($time_label !== '') { ?>
                        <span class="plaza-card__dot" aria-hidden="true">·</span>
                        <span class="plaza-card__time"><?php echo get_text($time_label); ?></span>
                        <?php } ?>
                    </p>
                    <p class="plaza-card__stats">
                        <?php if ($like_count > 0) { ?>공감 <?php echo number_format($like_count); ?><?php } ?>
                        <?php if ($like_count > 0 && $comment_num > 0) { ?> · <?php } ?>
                        <?php if ($comment_num > 0) { ?>댓글 <?php echo number_format($comment_num); ?><?php } ?>
                        <?php if (($like_count > 0 || $comment_num > 0) && $hit_num > 0) { ?> · <?php } ?>
                        <?php if ($hit_num > 0) { ?>조회 <?php echo number_format($hit_num); ?><?php } ?>
                    </p>
                </div>
                <?php if ($thumb !== '') { ?>
                <div class="plaza-card__thumb" style="background-image:url('<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>')" aria-hidden="true"></div>
                <?php } ?>
            </a>
            <div class="plaza-card__footer">
                <?php
                eottae_plaza_render_like_button(
                    $item_wr_id,
                    $like_count,
                    $is_liked,
                    $can_like || (!$is_member && $plaza_login_url !== ''),
                    $plaza_login_url
                );
                ?>
            </div>
        </article>
        <?php } ?>

        <?php if (count($list) === 0) { ?>
        <div class="plaza-empty">
            <p class="plaza-empty__title">아직 올라온 글이 없습니다</p>
            <p class="plaza-empty__desc">세부광장에 첫 이야기를 남겨보세요.</p>
            <?php if ($write_href) { ?>
            <a href="<?php echo $write_href; ?>" class="plaza-btn plaza-btn--primary">글쓰기</a>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <nav class="plaza-paging" aria-label="페이지"><?php echo $write_pages; ?></nav>
    </form>
</div>

<script>
(function () {
    var sortSelect = document.getElementById('plaza_sort');
    if (!sortSelect) return;
    sortSelect.addEventListener('change', function () {
        var form = sortSelect.form;
        var opt = sortSelect.options[sortSelect.selectedIndex];
        var sod = opt.getAttribute('data-sod') || 'desc';
        var sodInput = form.querySelector('input[name="sod"]');
        if (sodInput) sodInput.value = sod;
    });
    var regionSelect = document.getElementById('plaza_region');
    if (regionSelect) {
        regionSelect.addEventListener('change', function () {
            var form = regionSelect.form;
            var sflInput = form.querySelector('input[name="sfl"]');
            if (sflInput) sflInput.value = regionSelect.value ? 'wr_1' : '';
        });
    }
})();
</script>
<?php eottae_plaza_render_like_script($plaza_member_token, $plaza_login_url); ?>
