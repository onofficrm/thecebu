<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
include_once(G5_LIB_PATH.'/eottae-api.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$community_tabs = eottae_community_category_tabs($board);
$today_count = eottae_community_today_count($bo_table);
$sort_options = eottae_community_sort_options(isset($sst) ? $sst : '', isset($sod) ? $sod : 'desc');
$region_options = eottae_community_region_options();
$current_region = isset($_GET['region']) ? trim($_GET['region']) : '';
$list_base = get_pretty_url($bo_table);
?>

<div class="community-page board-wrap board-wrap--eottae-community" id="bo_list" style="width:<?php echo $width; ?>">

<div class="community-page__layout">
<main class="community-page__main">

    <section class="community-hero">
        <div class="community-hero__inner">
            <div class="community-hero__copy">
                <p class="community-hero__kicker">세부 자유 게시판</p>
                <h1 class="community-hero__title"><?php echo $sca ? get_text($sca) : '세부 생활정보'; ?></h1>
                <p class="community-hero__desc">세부 교민과 여행자가 함께 나누는 생생한 로컬 생활정보 게시판입니다.</p>
                <p class="community-hero__stats">
                    <span>전체글 <strong><?php echo number_format($total_count); ?></strong></span>
                    <span class="community-hero__stats-divider">|</span>
                    <span>오늘 새글 <strong><?php echo number_format($today_count); ?></strong></span>
                </p>
            </div>
            <?php if ($write_href) { ?>
            <a href="<?php echo $write_href; ?>" class="community-hero__write">
                <span class="community-hero__write-icon" aria-hidden="true">✎</span>
                글쓰기
            </a>
            <?php } ?>
        </div>
    </section>

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

    <section class="community-toolbar">
        <form class="community-search" name="fsearch" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <?php if ($sca) { ?><input type="hidden" name="sca" value="<?php echo get_text($sca); ?>"><?php } ?>
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <label class="sound_only" for="community_stx">검색어</label>
            <span class="community-search__icon" aria-hidden="true">⌕</span>
            <input type="search" id="community_stx" name="stx" value="<?php echo isset($stx) ? get_text(stripslashes($stx)) : ''; ?>" placeholder="궁금한 세부 정보를 검색해보세요" class="community-search__input">
        </form>
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
        for ($i = 0; $i < count($list); $i++) {
            $item = $list[$i];
            $ca_name = isset($item['ca_name']) ? get_text($item['ca_name']) : '';
            $is_notice = !empty($item['is_notice']) || $ca_name === '공지';
            $region = isset($item['wr_1']) ? get_text($item['wr_1']) : '';
            $snippet = eottae_community_snippet(isset($item['wr_content']) ? $item['wr_content'] : '');
            $thumb = eottae_community_list_thumb($bo_table, (int) $item['wr_id']);
            $comment_num = isset($item['wr_comment']) ? (int) $item['wr_comment'] : 0;
            $hit_num = isset($item['wr_hit']) ? (int) $item['wr_hit'] : 0;
            $good_num = isset($item['wr_good']) ? (int) $item['wr_good'] : 0;
            $author = strip_tags(isset($item['name']) ? $item['name'] : '');
            $time_label = eottae_community_relative_time(isset($item['wr_datetime']) ? $item['wr_datetime'] : '');
            $is_new = !$is_notice && eottae_community_is_new(isset($item['wr_datetime']) ? $item['wr_datetime'] : '');
            $is_hot = !$is_notice && eottae_community_is_hot($hit_num, $comment_num, $board);
            $item_class = 'community-post'.($is_notice ? ' community-post--notice' : '');
            ?>
        <article class="<?php echo $item_class; ?>">
            <a href="<?php echo $item['href']; ?>" class="community-post__link">
                <div class="community-post__body">
                    <div class="community-post__tags">
                        <?php if ($is_notice) { ?>
                        <span class="community-badge community-badge--notice"><span aria-hidden="true">📣</span> 공지</span>
                        <?php } else { ?>
                            <?php if ($ca_name !== '') { ?>
                        <span class="community-badge <?php echo eottae_community_badge_class($ca_name); ?>"><?php echo $ca_name; ?></span>
                            <?php } ?>
                            <?php if ($region !== '') { ?>
                        <span class="community-badge community-badge--region"><?php echo $region; ?></span>
                            <?php } ?>
                            <?php if ($is_new) { ?><span class="community-badge community-badge--new">NEW</span><?php } ?>
                            <?php if ($is_hot) { ?><span class="community-badge community-badge--hot">HOT</span><?php } ?>
                        <?php } ?>
                    </div>
                    <h2 class="community-post__title"><?php echo $item['subject']; ?></h2>
                    <?php if ($snippet !== '') { ?>
                    <p class="community-post__excerpt"><?php echo $snippet; ?></p>
                    <?php } ?>
                    <div class="community-post__meta">
                        <span class="community-post__author"><?php echo $author; ?></span>
                        <span class="community-post__time"><?php echo $time_label; ?></span>
                    </div>
                    <div class="community-post__stats">
                        <span class="community-post__stat" title="조회"><span aria-hidden="true">👁</span> <?php echo number_format($hit_num); ?></span>
                        <span class="community-post__stat" title="추천"><span aria-hidden="true">👍</span> <?php echo number_format($good_num); ?></span>
                        <span class="community-post__stat" title="댓글"><span aria-hidden="true">💬</span> <?php echo number_format($comment_num); ?></span>
                    </div>
                </div>
                <?php if ($thumb !== '') { ?>
                <div class="community-post__thumb" style="background-image:url('<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>')" aria-hidden="true"></div>
                <?php } ?>
            </a>
        </article>
        <?php } ?>

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
