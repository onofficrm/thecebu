<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($hero) || !is_array($hero)) {
    $hero = eottae_community_board_hero(isset($board) ? $board : array(), isset($sca) ? $sca : '');
}

$hero_image = !empty($hero['image']) ? $hero['image'] : '';
$hero_stx = isset($stx) ? get_text(stripslashes($stx)) : '';
$community_hero_search_placeholder = isset($community_hero_search_placeholder)
    ? (string) $community_hero_search_placeholder
    : '궁금한 세부 정보를 검색해보세요';
$community_hero_write_label = isset($community_hero_write_label)
    ? (string) $community_hero_write_label
    : '글쓰기';
$community_hero_hide_search = !empty($community_hero_hide_search);
?>

<section class="community-hero">
    <div class="community-hero__inner">
        <span class="community-hero__bg" aria-hidden="true"<?php if ($hero_image !== '') { ?> style="background-image: url('<?php echo htmlspecialchars($hero_image, ENT_QUOTES, 'UTF-8'); ?>')"<?php } ?>></span>
        <span class="community-hero__overlay" aria-hidden="true"></span>

        <div class="community-hero__content">
            <div class="community-hero__top">
                <div class="community-hero__copy">
                    <p class="community-hero__kicker"><?php echo $hero['kicker']; ?></p>
                    <h1 class="community-hero__title"><?php echo $hero['title']; ?></h1>
                    <?php if (!empty($hero['desc'])) { ?>
                    <p class="community-hero__desc"><?php echo $hero['desc']; ?></p>
                    <?php } ?>
                    <p class="community-hero__stats">
                        <span>전체글 <strong><?php echo number_format((int) $total_count); ?></strong></span>
                        <span class="community-hero__stats-divider" aria-hidden="true">|</span>
                        <span>오늘 새글 <strong><?php echo number_format((int) $today_count); ?></strong></span>
                    </p>
                </div>
                <?php if (!empty($write_href)) { ?>
                <a href="<?php echo $write_href; ?>" class="community-hero__write">
                    <span class="community-hero__write-icon" aria-hidden="true">✎</span>
                    <?php echo get_text($community_hero_write_label); ?>
                </a>
                <?php } ?>
            </div>

            <?php if (!$community_hero_hide_search) { ?>
            <form class="community-hero__search community-search" name="fsearch" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <?php
                $hero_hub_all = !empty($is_community_hub_all_list)
                    || (function_exists('eottae_community_hub_is_all_view') && !empty($bo_table) && eottae_community_hub_is_all_view($bo_table));
                if ($hero_hub_all) { ?><input type="hidden" name="hub" value="all"><?php } ?>
                <?php
                $hero_hide_sca = !empty($is_community_hub_list)
                    || (function_exists('eottae_is_community_hub_board') && !empty($bo_table) && eottae_is_community_hub_board($bo_table));
                if (!$hero_hide_sca && !empty($sca)) { ?><input type="hidden" name="sca" value="<?php echo get_text($sca); ?>"><?php } ?>
                <input type="hidden" name="sfl" value="wr_subject||wr_content">
                <label class="sound_only" for="community_stx">검색어</label>
                <span class="community-search__icon" aria-hidden="true">⌕</span>
                <input type="search" id="community_stx" name="stx" value="<?php echo $hero_stx; ?>" placeholder="<?php echo get_text($community_hero_search_placeholder); ?>" class="community-search__input">
                <button type="submit" class="community-hero__search-btn">검색</button>
            </form>
            <?php } ?>
        </div>
    </div>
</section>
