<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-adroom.lib.php';
$adroom_skin_css = G5_SKIN_PATH.'/board/eottae-adroom/style.css';
$adroom_skin_ver = is_file($adroom_skin_css) ? (string) filemtime($adroom_skin_css) : '1';
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?ver='.$adroom_skin_ver.'">', 0);

$adroom_tabs = eottae_adroom_category_tabs($board);
$list_base = function_exists('eottae_adroom_list_url') ? eottae_adroom_list_url() : get_pretty_url($bo_table);
$hero = eottae_adroom_board_hero($board, $sca);
$today_count = function_exists('eottae_community_today_count') ? eottae_community_today_count($bo_table) : 0;
$adroom_elig = function_exists('eottae_adroom_write_eligibility')
    ? eottae_adroom_write_eligibility($member ?? array(), $board, !empty($is_member), $is_admin === 'super')
    : array('can_write' => false);
$can_write_ad = !empty($adroom_elig['can_write']);
if (function_exists('eottae_adroom_list_write_href')) {
    $write_href = eottae_adroom_list_write_href($write_href, !empty($is_member));
    if ($write_href !== '' && !empty($is_member)) {
        $can_write_ad = true;
    }
}
$adroom_promo_notice = function_exists('eottae_adroom_render_promotion_notice') ? eottae_adroom_render_promotion_notice() : '';
$adroom_write_guide = function_exists('eottae_adroom_render_write_guide')
    ? eottae_adroom_render_write_guide($member ?? array(), $board, !empty($is_member), $is_admin === 'super', $write_href, $can_write_ad)
    : '';
$adroom_show_guide_btn = $adroom_write_guide !== '';
if ($adroom_show_guide_btn) {
    add_javascript('<script src="'.G5_JS_URL.'/eottae-adroom-list.js?ver='.(@filemtime(G5_JS_PATH.'/eottae-adroom-list.js') ?: '1').'"></script>', 10);
}
?>

<div class="adroom-page board-wrap board-wrap--eottae-adroom" id="bo_list" style="width:<?php echo $width; ?>">

    <section class="adroom-hero">
        <div class="adroom-hero__inner">
            <p class="adroom-hero__kicker"><?php echo get_text($hero['kicker']); ?></p>
            <h1 class="adroom-hero__title"><?php echo get_text($hero['title']); ?></h1>
            <p class="adroom-hero__desc"><?php echo get_text($hero['desc']); ?></p>
            <p class="adroom-hero__stats">
                <span>전체 광고 <strong><?php echo number_format((int) $total_count); ?></strong></span>
                <?php if ($today_count > 0) { ?>
                <span class="adroom-hero__stats-divider" aria-hidden="true">|</span>
                <span>오늘 등록 <strong><?php echo number_format((int) $today_count); ?></strong></span>
                <?php } ?>
            </p>
            <?php if ($write_href && $can_write_ad) { ?>
            <a href="<?php echo $write_href; ?>" class="adroom-hero__write">광고 등록</a>
            <?php } elseif ($adroom_show_guide_btn) { ?>
            <button type="button" class="adroom-hero__write adroom-hero__write--guide" data-adroom-show-write-guide aria-controls="adroom-write-guide">광고 등록</button>
            <?php } elseif (!$is_member) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/login.php?url=<?php echo urlencode(eottae_adroom_list_url()); ?>" class="adroom-hero__write adroom-hero__write--muted">로그인 후 광고 등록</a>
            <?php } ?>
        </div>
    </section>

    <?php echo $adroom_promo_notice; ?>

    <?php echo $adroom_write_guide; ?>

    <?php if ($is_category && !empty($adroom_tabs)) { ?>
    <nav class="adroom-tabs" aria-label="광고 분류">
        <?php foreach ($adroom_tabs as $tab) {
            $href = $tab['slug'] === '' ? $list_base : get_pretty_url($bo_table, '', 'sca='.urlencode($tab['slug']));
            $active = ($tab['slug'] === '' && $sca === '') || ($tab['slug'] !== '' && $sca === $tab['slug']);
            ?>
        <a href="<?php echo $href; ?>" class="adroom-tabs__item<?php echo $active ? ' is-active' : ''; ?>"><?php echo get_text($tab['label']); ?></a>
        <?php } ?>
    </nav>
    <?php } ?>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="sca" value="<?php echo $sca; ?>">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">

    <div class="adroom-list">
        <?php
        for ($i = 0; $i < count($list); $i++) {
            $item = $list[$i];
            $wr_id_item = (int) ($item['wr_id'] ?? 0);
            $ca_name = isset($item['ca_name']) ? get_text($item['ca_name']) : '';
            $region = isset($item['wr_3']) ? get_text($item['wr_3']) : '';
            $snippet = eottae_adroom_snippet(isset($item['wr_content']) ? $item['wr_content'] : '', 120);
            $thumb = eottae_adroom_list_thumb($wr_id_item, $item);
            $shop_block = eottae_adroom_shop_block($item);
            $shop_name = (string) ($shop_block['name'] ?? '');
            $author = strip_tags(isset($item['name']) ? $item['name'] : '');
            $time_label = function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time(isset($item['wr_datetime']) ? $item['wr_datetime'] : '')
                : '';
            ?>
        <article class="adroom-card">
            <a href="<?php echo $item['href']; ?>" class="adroom-card__link">
                <div class="adroom-card__thumb<?php echo $thumb === '' ? ' adroom-card__thumb--empty' : ''; ?>"<?php if ($thumb !== '') { ?> style="background-image:url('<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>')"<?php } ?> aria-hidden="true"></div>
                <div class="adroom-card__body">
                    <div class="adroom-card__tags">
                        <?php if ($ca_name !== '') { ?>
                        <span class="adroom-badge adroom-badge--cate"><?php echo $ca_name; ?></span>
                        <?php } ?>
                        <?php if ($region !== '') { ?>
                        <span class="adroom-badge adroom-badge--region"><?php echo $region; ?></span>
                        <?php } ?>
                    </div>
                    <h2 class="adroom-card__title"><?php echo $item['subject']; ?></h2>
                    <?php if ($snippet !== '') { ?>
                    <p class="adroom-card__excerpt"><?php echo $snippet; ?></p>
                    <?php } ?>
                    <div class="adroom-card__meta">
                        <?php if ($shop_name !== '') { ?>
                        <span class="adroom-card__shop"><?php echo get_text($shop_name); ?></span>
                        <?php } ?>
                        <span class="adroom-card__author"><?php echo $author; ?></span>
                        <?php if ($time_label !== '') { ?><span class="adroom-card__time"><?php echo $time_label; ?></span><?php } ?>
                    </div>
                </div>
            </a>
        </article>
        <?php } ?>

        <?php if (count($list) === 0) { ?>
        <div class="adroom-empty">
            <p class="adroom-empty__title">등록된 광고가 없습니다</p>
            <?php if ($write_href && $can_write_ad) { ?>
            <p class="adroom-empty__desc">첫 광고를 등록해 보세요.</p>
            <a href="<?php echo $write_href; ?>" class="adroom-btn adroom-btn--primary">광고 등록하기</a>
            <?php } elseif ($adroom_write_guide === '') { ?>
            <p class="adroom-empty__desc">업체 회원이라면 첫 광고를 등록해 보세요.</p>
            <?php } else { ?>
            <p class="adroom-empty__desc">위 안내를 확인한 뒤 조건을 충족하면 광고를 등록할 수 있습니다.</p>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <nav class="board-paging adroom-paging" aria-label="페이지"><?php echo $write_pages; ?></nav>
    </form>
</div>
