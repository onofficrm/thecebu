<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
include_once(G5_LIB_PATH.'/eottae-api.lib.php');
$community_skin_css = G5_PATH.'/skin/board/eottae-community/style.css';
$community_skin_ver = is_file($community_skin_css) ? (int) filemtime($community_skin_css) : 0;
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?ver='.$community_skin_ver.'">', 30);

$community_tabs = eottae_community_category_tabs($board);
$today_count = eottae_community_today_count($bo_table);
$sort_options = eottae_community_sort_options(isset($sst) ? $sst : '', isset($sod) ? $sod : 'desc');
$region_options = eottae_community_region_options();
$current_region = isset($_GET['region']) ? trim($_GET['region']) : '';
$list_base = get_pretty_url($bo_table);
$hero = eottae_community_board_hero($board, $sca);
$is_talkroom_board = function_exists('eottae_talkroom_board_table') && $bo_table === eottae_talkroom_board_table();
if ($is_talkroom_board) {
    include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
}
?>

<div class="community-page board-wrap board-wrap--eottae-community" id="bo_list" style="width:<?php echo $width; ?>">

<div class="community-page__layout">
<main class="community-page__main">

    <?php include G5_PATH.'/components/eottae/community-hero.php'; ?>

    <?php if ($is_category && !empty($community_tabs)) { ?>
    <nav class="community-tabs" aria-label="게시판 분류">
        <?php foreach ($community_tabs as $tab) {
            $href = $tab['slug'] === '' ? $list_base : get_pretty_url($bo_table, '', 'sca='.urlencode($tab['slug']));
            $active = ($tab['slug'] === '' && $sca === '') || ($tab['slug'] !== '' && $sca === $tab['slug']);
            ?>
        <a href="<?php echo $href; ?>" class="community-tabs__item<?php echo $active ? ' is-active' : ''; ?>">
            <span><?php echo get_text($tab['label']); ?></span>
            <em><?php echo number_format($tab['count']); ?></em>
        </a>
        <?php } ?>
    </nav>
    <?php } ?>

    <section class="community-toolbar community-toolbar--filters">
        <div class="community-toolbar__filters">
            <form class="community-filter" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <?php if ($sca) { ?><input type="hidden" name="sca" value="<?php echo get_text($sca); ?>"><?php } ?>
                <?php if (!empty($stx)) { ?><input type="hidden" name="stx" value="<?php echo get_text($stx); ?>"><input type="hidden" name="sfl" value="wr_subject||wr_content"><?php } ?>
                <label class="sound_only" for="community_region">구역</label>
                <select id="community_region" name="stx" class="community-filter__select" onchange="this.form.sfl.value='wr_subject||wr_content'; if(this.value){this.form.submit();}">
                    <option value="">구역 전체</option>
                    <?php foreach ($region_options as $region) { ?>
                    <option value="<?php echo get_text($region); ?>"<?php echo ($stx === $region) ? ' selected' : ''; ?>><?php echo get_text($region); ?></option>
                    <?php } ?>
                </select>
            </form>
            <form class="community-filter" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <?php if ($sca) { ?><input type="hidden" name="sca" value="<?php echo get_text($sca); ?>"><?php } ?>
                <?php if (!empty($stx)) { ?><input type="hidden" name="stx" value="<?php echo get_text($stx); ?>"><input type="hidden" name="sfl" value="wr_subject||wr_content"><?php } ?>
                <label class="sound_only" for="community_sort">정렬</label>
                <select id="community_sort" name="sst" class="community-filter__select" onchange="this.form.submit();">
                    <?php foreach ($sort_options as $opt) { ?>
                    <option value="<?php echo $opt['sst']; ?>" data-sod="<?php echo $opt['sod']; ?>"<?php echo $opt['active'] ? ' selected' : ''; ?>><?php echo $opt['label']; ?></option>
                    <?php } ?>
                </select>
                <input type="hidden" name="sod" value="<?php echo isset($sod) && $sod ? $sod : 'desc'; ?>">
            </form>
        </div>
    </section>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">

    <div class="community-list">
        <?php
        if (function_exists('eottae_member_growth_prefetch_members')) {
            include_once G5_PATH.'/components/eottae/member-growth-display.php';
            $growth_mb_ids = array();
            for ($gi = 0; $gi < count($list); $gi++) {
                $gid = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($list[$gi]['mb_id'] ?? ''));
                if ($gid !== '') {
                    $growth_mb_ids[] = $gid;
                }
            }
            if ($growth_mb_ids) {
                eottae_member_growth_prefetch_members(array_values(array_unique($growth_mb_ids)));
            }
        }
        for ($i = 0; $i < count($list); $i++) {
            $item = $list[$i];
            $ca_name = isset($item['ca_name']) ? get_text($item['ca_name']) : '';
            $is_notice = !empty($item['is_notice']) || $ca_name === '공지';
            $region = isset($item['wr_1']) ? get_text($item['wr_1']) : '';
            $snippet = eottae_community_snippet(isset($item['wr_content']) ? $item['wr_content'] : '');
            $thumb = eottae_community_list_thumb(
                $bo_table,
                (int) $item['wr_id'],
                isset($item['wr_content']) ? $item['wr_content'] : ''
            );
            $comment_num = isset($item['wr_comment']) ? (int) $item['wr_comment'] : 0;
            $hit_num = isset($item['wr_hit']) ? (int) $item['wr_hit'] : 0;
            $good_num = isset($item['wr_good']) ? (int) $item['wr_good'] : 0;
            $author = strip_tags(isset($item['name']) ? $item['name'] : '');
            $time_label = eottae_community_relative_time(isset($item['wr_datetime']) ? $item['wr_datetime'] : '');
            $is_new = !$is_notice && eottae_community_is_new(isset($item['wr_datetime']) ? $item['wr_datetime'] : '');
            $is_hot = !$is_notice && eottae_community_is_hot($hit_num, $comment_num, $board);
            $is_ai_post = $is_talkroom_board && function_exists('eottae_talkroom_ai_message_is_ai') && eottae_talkroom_ai_message_is_ai($item);
            $item_class = 'community-post'.($is_notice ? ' community-post--notice' : '');
            if ($thumb !== '') {
                $item_class .= ' community-post--has-thumb';
            }
            if ($is_ai_post) {
                $item_class .= ' community-post--ai is-talk-ai-message';
            }
            include __DIR__.'/list-item.inc.php';
        }
        ?>

        <?php if (count($list) === 0) { ?>
        <div class="empty-state community-list__empty">
            <p class="empty-state__title">게시글이 없습니다</p>
            <p>첫 글을 작성해 보세요.</p>
            <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="community-hero__write community-hero__write--inline">글쓰기</a><?php } ?>
        </div>
        <?php } ?>
    </div>

    <nav class="board-paging community-paging" aria-label="페이지"><?php echo $write_pages; ?></nav>
    </form>
</main>

<?php include G5_PATH.'/components/eottae/community-sidebar.php'; ?>

</div>
</div>

<script>
(function () {
    var sortSelect = document.getElementById('community_sort');
    if (!sortSelect) return;
    sortSelect.addEventListener('change', function () {
        var form = sortSelect.form;
        var opt = sortSelect.options[sortSelect.selectedIndex];
        var sod = opt.getAttribute('data-sod') || 'desc';
        var sodInput = form.querySelector('input[name="sod"]');
        if (sodInput) sodInput.value = sod;
    });
})();
</script>
