<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
include_once(G5_LIB_PATH.'/eottae-api.lib.php');
include_once(G5_LIB_PATH.'/eottae-estate.lib.php');
include_once(G5_LIB_PATH.'/eottae-job.lib.php');
include_once(G5_LIB_PATH.'/eottae-community-hub.lib.php');
include_once(G5_LIB_PATH.'/eottae-event-template.lib.php');
include_once(G5_LIB_PATH.'/eottae-event.lib.php');
include_once(G5_LIB_PATH.'/eottae-report.lib.php');
include_once(G5_LIB_PATH.'/eottae-report-template.lib.php');
include_once(G5_LIB_PATH.'/eottae-briefing.lib.php');
if (function_exists('eottae_briefing_load_assets')) {
    eottae_briefing_load_assets();
}
$community_skin_css = G5_PATH.'/skin/board/eottae-community/style.css';
$community_skin_ver = is_file($community_skin_css) ? (int) filemtime($community_skin_css) : 0;
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?ver='.$community_skin_ver.'">', 30);
$community_board_css = G5_PATH.'/css/eottae-community-board.css';
if (is_file($community_board_css)) {
    add_stylesheet(
        '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-community-board.css?ver='.(int) filemtime($community_board_css).'">',
        99
    );
}

$is_community_hub_list = function_exists('eottae_is_community_hub_board') && eottae_is_community_hub_board($bo_table);
$is_community_hub_all_list = $is_community_hub_list
    && function_exists('eottae_community_hub_is_all_view')
    && eottae_community_hub_is_all_view($bo_table);
$community_tabs = $is_community_hub_list
    ? eottae_community_hub_tabs($bo_table)
    : eottae_community_category_tabs($board);
if ($is_community_hub_all_list) {
    $today_count = isset($eottae_community_hub_today_count)
        ? (int) $eottae_community_hub_today_count
        : (function_exists('eottae_community_hub_today_count') ? eottae_community_hub_today_count() : 0);
} else {
    $today_count = eottae_community_today_count($bo_table);
}
$sort_options = eottae_community_sort_options(isset($sst) ? $sst : '', isset($sod) ? $sod : 'desc');
$region_options = eottae_community_region_options();
$current_region = isset($_GET['region']) ? trim($_GET['region']) : '';
$current_lang = function_exists('eottae_lang_from_request') ? eottae_lang_from_request('lang') : '';
$list_base = get_pretty_url($bo_table);
$hero = $is_community_hub_list
    ? eottae_community_hub_hero($board)
    : eottae_community_board_hero($board, $sca);
$is_free_board_list = function_exists('eottae_is_free_board') && eottae_is_free_board($bo_table);
$is_estate_board_list = function_exists('eottae_is_estate_board') && eottae_is_estate_board($bo_table);
$is_job_board_list = function_exists('eottae_is_job_board') && eottae_is_job_board($bo_table);
$is_event_board_list = function_exists('eottae_is_event_board') && eottae_is_event_board($bo_table);
$is_report_board_list = function_exists('eottae_is_report_board') && eottae_is_report_board($bo_table);
if ($is_report_board_list) {
    if (function_exists('eottae_report_board_load_assets')) {
        eottae_report_board_load_assets();
    }
    $hero = function_exists('eottae_report_board_hero') ? eottae_report_board_hero($board) : eottae_community_board_hero($board, $sca);
    $community_hero_write_label = '제보하기';
    $community_hero_hide_search = true;
}
if ($is_event_board_list && function_exists('eottae_event_board_load_assets')) {
    eottae_event_board_load_assets();
}
if ($is_estate_board_list) {
    $estate_board_css = G5_PATH.'/css/eottae-estate-board.css';
    if (is_file($estate_board_css)) {
        add_stylesheet(
            '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-estate-board.css?ver='.(int) filemtime($estate_board_css).'">',
            100
        );
    }
    $estate_list_css = G5_PATH.'/css/eottae-estate-list.css';
    if (is_file($estate_list_css)) {
        add_stylesheet(
            '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-estate-list.css?ver='.(int) filemtime($estate_list_css).'">',
            102
        );
    }
}
if ($is_job_board_list) {
    $job_board_css = G5_PATH.'/css/eottae-job-board.css';
    if (is_file($job_board_css)) {
        add_stylesheet(
            '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-job-board.css?ver='.(int) filemtime($job_board_css).'">',
            100
        );
    }
}
$eottae_list_thumb_css = G5_PATH.'/css/eottae-list-thumb.css';
if (is_file($eottae_list_thumb_css)) {
    add_stylesheet(
        '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-list-thumb.css?ver='.(int) filemtime($eottae_list_thumb_css).'">',
        101
    );
}
$is_talkroom_board = function_exists('eottae_talkroom_board_table') && $bo_table === eottae_talkroom_board_table();
if ($is_talkroom_board) {
    include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
}
if ($is_community_hub_all_list && function_exists('eottae_community_hub_load_all_list_assets')) {
    eottae_community_hub_load_all_list_assets();
}
if (function_exists('eottae_community_hub_prepare_list_context') && eottae_is_community_hub_board($bo_table)) {
    eottae_community_hub_prepare_list_context($bo_table);
}
if ($is_community_hub_list && !$is_community_hub_all_list && empty($write_href) && function_exists('eottae_community_hub_write_href')) {
    $write_href = eottae_community_hub_write_href($bo_table);
}
?>

<div class="community-page board-wrap board-wrap--eottae-community<?php echo !empty($is_report_board_list) ? ' community-page--report' : ''; ?>" id="bo_list" style="width:<?php echo $width; ?>">

<div class="community-page__layout">
<main class="community-page__main">

    <?php
    if ($is_community_hub_all_list) {
        $community_hero_search_placeholder = '커뮤니티 전체에서 검색해보세요';
    } elseif ($is_community_hub_list) {
        $hub_def = eottae_community_hub_board_def($bo_table);
        $hub_label = !empty($hub_def['label']) ? get_text($hub_def['label']) : '게시판';
        $community_hero_search_placeholder = $hub_label.'에서 검색해보세요';
    } elseif ($is_free_board_list) {
        $community_hero_search_placeholder = '자유게시판에서 검색해보세요';
    } else {
        $community_hero_search_placeholder = '궁금한 세부 정보를 검색해보세요';
    }
    include G5_PATH.'/components/eottae/community-hero.php';
    ?>

    <?php if ($is_job_board_list || $is_estate_board_list) {
        $map_type = $is_job_board_list ? 'job' : 'estate';
        $map_primary_label = '지도에서 보기';
        $map_near_label = $is_job_board_list ? '내 주변 일자리 보기' : '지도에서 매물 보기';
        $map_url = G5_URL.'/cebu-map/?type='.$map_type;
        $map_near_url = $map_url.'&near=1';
        ?>
    <nav class="community-map-actions" aria-label="<?php echo $is_job_board_list ? '구인구직' : '부동산'; ?> 지도 보기">
        <a href="<?php echo get_text($map_url); ?>" class="community-map-actions__btn community-map-actions__btn--primary"><?php echo get_text($map_primary_label); ?></a>
        <a href="<?php echo get_text($map_near_url); ?>" class="community-map-actions__btn"><?php echo get_text($map_near_label); ?></a>
    </nav>
    <?php } ?>

    <?php if (!empty($is_community_hub_list) && empty($is_report_board_list) && function_exists('render_today_sebu_briefing_community_strip')) {
        render_today_sebu_briefing_community_strip();
    } ?>

    <?php if (!empty($is_report_board_list)) {
        include G5_PATH.'/components/eottae/report-list-notice.php';
        include G5_PATH.'/components/eottae/report-status-filter.php';
    } ?>

    <?php if (!empty($community_tabs) && (empty($is_report_board_list) || !empty($is_community_hub_list))) { ?>
    <nav class="community-tabs" aria-label="<?php echo $is_community_hub_list ? '커뮤니티 게시판' : '게시판 분류'; ?>">
        <?php foreach ($community_tabs as $tab) {
            if ($is_community_hub_list) {
                $href = $tab['href'] ?? $list_base;
                $active = !empty($tab['active']);
            } else {
                $href = $tab['slug'] === '' ? $list_base : get_pretty_url($bo_table, '', 'sca='.urlencode($tab['slug']));
                $active = ($tab['slug'] === '' && $sca === '') || ($tab['slug'] !== '' && $sca === $tab['slug']);
            }
            ?>
        <a href="<?php echo $href; ?>" class="community-tabs__item<?php echo $active ? ' is-active' : ''; ?>">
            <span><?php echo get_text($tab['label']); ?></span>
            <em><?php echo number_format((int) ($tab['count'] ?? 0)); ?></em>
        </a>
        <?php } ?>
    </nav>
    <?php } ?>

    <?php if (empty($is_report_board_list)) { ?>
    <section class="community-toolbar community-toolbar--filters">
        <div class="community-toolbar__filters">
            <form class="community-filter" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <?php if ($is_community_hub_all_list) { ?><input type="hidden" name="hub" value="all"><?php } ?>
                <?php if (!$is_community_hub_list && $sca) { ?><input type="hidden" name="sca" value="<?php echo get_text($sca); ?>"><?php } ?>
                <?php if ($current_lang !== '') { ?><input type="hidden" name="lang" value="<?php echo $current_lang; ?>"><?php } ?>
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
                <?php if ($is_community_hub_all_list) { ?><input type="hidden" name="hub" value="all"><?php } ?>
                <?php if (!$is_community_hub_list && $sca) { ?><input type="hidden" name="sca" value="<?php echo get_text($sca); ?>"><?php } ?>
                <?php if ($current_lang !== '') { ?><input type="hidden" name="lang" value="<?php echo $current_lang; ?>"><?php } ?>
                <?php if (!empty($stx)) { ?><input type="hidden" name="stx" value="<?php echo get_text($stx); ?>"><input type="hidden" name="sfl" value="wr_subject||wr_content"><?php } ?>
                <label class="sound_only" for="community_sort">정렬</label>
                <select id="community_sort" name="sst" class="community-filter__select" onchange="this.form.submit();">
                    <?php foreach ($sort_options as $opt) { ?>
                    <option value="<?php echo $opt['sst']; ?>" data-sod="<?php echo $opt['sod']; ?>"<?php echo $opt['active'] ? ' selected' : ''; ?>><?php echo $opt['label']; ?></option>
                    <?php } ?>
                </select>
                <input type="hidden" name="sod" value="<?php echo isset($sod) && $sod ? $sod : 'desc'; ?>">
            </form>
            <nav class="community-filter community-lang-filter" aria-label="게시글 언어">
                <?php
                $lang_extra = array();
                if ($is_community_hub_all_list) {
                    $lang_extra['hub'] = 'all';
                }
                if (!$is_community_hub_list && $sca) {
                    $lang_extra['sca'] = $sca;
                }
                if (!empty($stx)) {
                    $lang_extra['stx'] = $stx;
                    $lang_extra['sfl'] = 'wr_subject||wr_content';
                }
                ?>
                <a href="<?php echo eottae_lang_filter_url($bo_table, '', $lang_extra); ?>" class="community-lang-filter__item<?php echo $current_lang === '' ? ' is-active' : ''; ?>">전체</a>
                <?php foreach (eottae_lang_supported() as $lang_code => $lang_meta) { ?>
                <a href="<?php echo eottae_lang_filter_url($bo_table, $lang_code, $lang_extra); ?>" class="community-lang-filter__item<?php echo $current_lang === $lang_code ? ' is-active' : ''; ?>"><?php echo get_text($lang_meta['label']); ?></a>
                <?php } ?>
            </nav>
        </div>
    </section>
    <?php } ?>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <?php if ($is_community_hub_all_list) { ?><input type="hidden" name="hub" value="all"><?php } ?>
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <?php if (!$is_community_hub_list) { ?><input type="hidden" name="sca" value="<?php echo $sca ?>"><?php } ?>
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">

    <div class="community-list<?php echo $is_community_hub_all_list ? ' community-list--hub-all' : ''; ?><?php echo !$is_community_hub_all_list && $is_event_board_list ? ' community-list--event' : ''; ?><?php echo !$is_community_hub_all_list && $is_estate_board_list ? ' community-list--estate' : ''; ?>">
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
            $item_bo_table = $bo_table;
            if ($is_community_hub_all_list && !empty($item['hub_bo_table'])) {
                $item_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $item['hub_bo_table']);
            }
            $ca_name = isset($item['ca_name']) ? get_text($item['ca_name']) : '';
            if ($is_community_hub_all_list && $item_bo_table !== '' && function_exists('eottae_community_hub_board_label')) {
                $ca_name = eottae_community_hub_board_label($item_bo_table);
            } elseif ($is_community_hub_list) {
                $ca_name = '';
            }
            $is_notice = !empty($item['is_notice']) || (!$is_community_hub_list && $ca_name === '공지');
            $region = isset($item['wr_1']) ? get_text($item['wr_1']) : '';
            $is_event_board_list = function_exists('eottae_is_event_board') && eottae_is_event_board($item_bo_table);
            $is_estate_board_list = function_exists('eottae_is_estate_board') && eottae_is_estate_board($item_bo_table);
            $is_job_board_list = function_exists('eottae_is_job_board') && eottae_is_job_board($item_bo_table);
            $estate_deal_status = '';
            $estate_deal_label = '';
            $estate_thumb_html = '';
            $job_recruit_status = '';
            $job_recruit_label = '';
            $job_thumb_html = '';
            $job_location_label = '';
            $job_shop = null;
            $post_thumb = eottae_community_list_thumb(
                $item_bo_table,
                (int) $item['wr_id'],
                isset($item['wr_content']) ? $item['wr_content'] : ''
            );
            if ($is_estate_board_list) {
                $estate_deal_status = eottae_estate_deal_status_from_row($item);
                $estate_deal_meta = eottae_estate_deal_status_meta($estate_deal_status);
                $estate_deal_label = $estate_deal_meta['label'];
                if (!$is_community_hub_all_list) {
                    $estate_thumb_html = eottae_estate_render_list_thumb($item, $post_thumb);
                }
            }
            $job_badge_label = '';
            if ($is_job_board_list) {
                $job_recruit_status = eottae_job_recruit_status_from_row($item);
                $job_recruit_meta = eottae_job_recruit_status_meta($job_recruit_status);
                $job_recruit_label = $job_recruit_meta['label'];
                if (function_exists('eottae_job_shop_from_row')) {
                    $job_shop = eottae_job_shop_from_row($item);
                }
                if (!$is_community_hub_all_list) {
                    $job_thumb_html = eottae_job_render_list_thumb($item, $post_thumb);
                }
                if (function_exists('eottae_job_template_from_row')) {
                    $job_tpl = eottae_job_template_from_row($item);
                    if (is_array($job_tpl)) {
                        if (!empty($job_tpl['job_type'])) {
                            $job_badge_label = get_text($job_tpl['job_type']);
                        }
                        if (!empty($job_tpl['region']) && $region === '') {
                            $region = get_text($job_tpl['region']);
                        }
                    }
                }
                if (function_exists('eottae_job_location_from_row')) {
                    $job_loc = eottae_job_location_from_row($item);
                    if (!empty($job_loc['display'])) {
                        $job_location_label = get_text($job_loc['display']);
                        $region = $job_location_label;
                    }
                }
            }
            if ($is_job_board_list && function_exists('eottae_job_list_snippet')) {
                $snippet = eottae_job_list_snippet(
                    $item,
                    isset($item['subject']) ? strip_tags($item['subject']) : '',
                    110
                );
            } else {
                $snippet = eottae_community_snippet(isset($item['wr_content']) ? $item['wr_content'] : '');
            }
            $thumb = ($is_community_hub_all_list || (!$is_estate_board_list && !$is_job_board_list))
                ? $post_thumb
                : '';
            $comment_num = isset($item['wr_comment']) ? (int) $item['wr_comment'] : 0;
            $hit_num = isset($item['wr_hit']) ? (int) $item['wr_hit'] : 0;
            $good_num = isset($item['wr_good']) ? (int) $item['wr_good'] : 0;
            $author = strip_tags(isset($item['name']) ? $item['name'] : '');
            $time_label = eottae_community_relative_time(isset($item['wr_datetime']) ? $item['wr_datetime'] : '');
            $is_new = !$is_notice && eottae_community_is_new(isset($item['wr_datetime']) ? $item['wr_datetime'] : '');
            $is_hot = !$is_notice && eottae_community_is_hot($hit_num, $comment_num, $board);
            $is_ai_post = $is_talkroom_board && function_exists('eottae_talkroom_ai_message_is_ai') && eottae_talkroom_ai_message_is_ai($item);
            $post_language = function_exists('eottae_lang_from_row') ? eottae_lang_from_row($item) : 'ko';
            $item_class = 'community-post'.($is_notice ? ' community-post--notice' : '');
            if ($thumb !== '') {
                $item_class .= ' community-post--has-thumb';
            }
            if ($is_ai_post) {
                $item_class .= ' community-post--ai is-talk-ai-message';
            }
            if ($is_estate_board_list && !$is_community_hub_all_list) {
                $item_class .= ' community-post--estate';
                if ($estate_thumb_html !== '') {
                    $item_class .= ' community-post--has-thumb';
                }
            }
            if ($is_job_board_list && !$is_community_hub_all_list) {
                $item_class .= ' community-post--job';
                if ($job_thumb_html !== '') {
                    $item_class .= ' community-post--has-thumb';
                }
            }
            if ($is_community_hub_all_list) {
                include __DIR__.'/list-item.inc.php';
            } elseif ($is_event_board_list) {
                $event_status = eottae_event_status_from_row($item);
                $event_type = eottae_event_normalize_type($item['wr_1'] ?? 'other');
                $event_display_name = get_text($item['wr_3'] ?? '');
                $event_benefit = get_text($item['wr_7'] ?? '');
                $event_period_label = eottae_event_period_label_from_row($item);
                $event_shop = eottae_event_shop_from_row($item);
                include __DIR__.'/list-event-card.inc.php';
            } elseif ($is_report_board_list || (function_exists('eottae_is_report_board') && eottae_is_report_board($item_bo_table))) {
                include __DIR__.'/list-report-card.inc.php';
            } elseif ($is_estate_board_list) {
                include __DIR__.'/list-estate-card.inc.php';
            } else {
                include __DIR__.'/list-item.inc.php';
            }
        }
        ?>

        <?php if (count($list) === 0) { ?>
        <div class="empty-state community-list__empty">
            <?php if (!empty($is_report_board_list)) { ?>
            <p class="empty-state__title">등록된 제보가 없습니다</p>
            <p>세부에서 본 소식을 첫 제보로 남겨 주세요.</p>
            <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="community-hero__write community-hero__write--inline">제보하기</a><?php } ?>
            <?php } else { ?>
            <p class="empty-state__title">게시글이 없습니다</p>
            <p>첫 글을 작성해 보세요.</p>
            <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="community-hero__write community-hero__write--inline">글쓰기</a><?php } ?>
            <?php } ?>
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
