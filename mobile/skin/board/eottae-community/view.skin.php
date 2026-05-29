<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
include_once(G5_LIB_PATH.'/eottae-estate.lib.php');
include_once(G5_LIB_PATH.'/eottae-estate-template.lib.php');
include_once(G5_LIB_PATH.'/eottae-job.lib.php');
include_once(G5_LIB_PATH.'/eottae-community-hub.lib.php');
include_once(G5_LIB_PATH.'/eottae-event-template.lib.php');
include_once(G5_LIB_PATH.'/eottae-event.lib.php');
include_once(G5_LIB_PATH.'/eottae-report.lib.php');
include_once(G5_LIB_PATH.'/eottae-report-template.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$is_estate_board_view = function_exists('eottae_is_estate_board') && eottae_is_estate_board($bo_table);
$estate_deal_status = 'trading';
$estate_can_change_deal = false;
$estate_location = null;
if ($is_estate_board_view) {
    $estate_board_css = G5_PATH.'/css/eottae-estate-board.css';
    if (is_file($estate_board_css)) {
        add_stylesheet(
            '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-estate-board.css?ver='.(int) filemtime($estate_board_css).'">',
            100
        );
    }
    $estate_deal_status = eottae_estate_deal_status_from_row($view);
    if (function_exists('eottae_estate_location_from_row')) {
        $estate_location = eottae_estate_location_from_row($view);
    }
    $estate_can_change_deal = !empty($is_member) && !empty($member['mb_id'])
        && eottae_estate_can_change_deal_status($view, $member['mb_id'], ($is_admin === 'super'));
    if ($estate_can_change_deal) {
        $estate_deal_js = G5_PATH.'/js/eottae-estate-deal-status.js';
        if (is_file($estate_deal_js)) {
            add_javascript(
                '<script src="'.G5_JS_URL.'/eottae-estate-deal-status.js?ver='.(int) filemtime($estate_deal_js).'" defer></script>',
                25
            );
        }
    }
}

$is_job_board_view = function_exists('eottae_is_job_board') && eottae_is_job_board($bo_table);
$is_event_board_view = function_exists('eottae_is_event_board') && eottae_is_event_board($bo_table);
$is_report_board_view = function_exists('eottae_is_report_board') && eottae_is_report_board($bo_table);
$report_status = 'received';
$report_type = 'other';
$report_region_label = '';
$report_author_label = '';
$report_shop_name = '';
$report_link = '';
$report_contact_ok = false;
$report_contact = '';
$report_admin_memo = '';
$report_can_view_contact = false;
if ($is_report_board_view) {
    if (function_exists('eottae_report_board_load_assets')) {
        eottae_report_board_load_assets();
    }
    $report_status = eottae_report_normalize_status($view['wr_8'] ?? 'received');
    $report_type = eottae_report_normalize_type($view['wr_1'] ?? 'other');
    $report_region_label = eottae_report_region_label($view['wr_2'] ?? '');
    $report_author_label = eottae_report_display_author($view);
    $report_shop_name = get_text($view['wr_6'] ?? '');
    $report_link = get_text($view['wr_7'] ?? '');
    $report_contact_ok = (string) ($view['wr_4'] ?? '') === '1';
    $report_contact = get_text($view['wr_5'] ?? '');
    $report_admin_memo = get_text($view['wr_9'] ?? '');
    $report_can_view_contact = eottae_report_can_view_contact($is_admin ?? '');
}
$event_status = 'active';
$event_type = 'other';
$event_display_name = '';
$event_benefit = '';
$event_contact = '';
$event_period_label = '';
$event_shop = null;
$event_can_close = false;
if ($is_event_board_view) {
    if (function_exists('eottae_event_board_load_assets')) {
        eottae_event_board_load_assets();
    }
    if (function_exists('eottae_event_sync_fields_from_row')) {
        eottae_event_sync_fields_from_row($bo_table, (int) $view['wr_id']);
        $event_write_table = get_write_table_name($bo_table);
        $event_refreshed = get_write($event_write_table, (int) $view['wr_id'], true);
        if (is_array($event_refreshed) && !empty($event_refreshed['wr_id'])) {
            $view = array_merge($view, $event_refreshed);
        }
    }
    if (function_exists('eottae_event_enrich_row_from_content')) {
        $view = eottae_event_enrich_row_from_content($view);
    }
    $event_status = eottae_event_status_from_row($view);
    $event_type = eottae_event_normalize_type($view['wr_1'] ?? 'other');
    $event_display_name = get_text($view['wr_3'] ?? '');
    $event_benefit = get_text($view['wr_7'] ?? '');
    $event_contact = get_text($view['wr_8'] ?? '');
    $event_period_label = eottae_event_period_label_from_row($view);
    $event_shop = eottae_event_shop_from_row($view);
    $event_can_close = !empty($is_member) && eottae_event_can_show_close_button(
        $view,
        $member['mb_id'] ?? '',
        ($is_admin === 'super')
    );
    if ($event_can_close) {
        $event_close_js = G5_PATH.'/js/eottae-event-close.js';
        if (is_file($event_close_js)) {
            add_javascript(
                '<script src="'.G5_JS_URL.'/eottae-event-close.js?ver='.(int) filemtime($event_close_js).'" defer></script>',
                25
            );
        }
    }
}
$job_recruit_status = 'recruiting';
$job_can_change_recruit = false;
$job_location = null;
$job_shop = null;
if ($is_job_board_view) {
    $job_board_css = G5_PATH.'/css/eottae-job-board.css';
    if (is_file($job_board_css)) {
        add_stylesheet(
            '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-job-board.css?ver='.(int) filemtime($job_board_css).'">',
            100
        );
    }
    $job_recruit_status = eottae_job_recruit_status_from_row($view);
    if (function_exists('eottae_job_location_from_row')) {
        $job_location = eottae_job_location_from_row($view);
    }
    if (function_exists('eottae_job_shop_from_row')) {
        $job_shop = eottae_job_shop_from_row($view);
    }
    $job_can_change_recruit = !empty($is_member) && !empty($member['mb_id'])
        && eottae_job_can_change_recruit_status($view, $member['mb_id'], ($is_admin === 'super'));
    if ($job_can_change_recruit) {
        $job_recruit_js = G5_PATH.'/js/eottae-job-recruit-status.js';
        if (is_file($job_recruit_js)) {
            add_javascript(
                '<script src="'.G5_JS_URL.'/eottae-job-recruit-status.js?ver='.(int) filemtime($job_recruit_js).'" defer></script>',
                25
            );
        }
    }
}

$is_community_hub_view = function_exists('eottae_is_community_hub_board') && eottae_is_community_hub_board($bo_table);
$view_category = $is_community_hub_view ? '' : (isset($view['ca_name']) ? get_text($view['ca_name']) : '');
if ($is_community_hub_view) {
    $list_url = eottae_community_hub_list_url($bo_table);
} elseif (function_exists('eottae_is_free_board') && eottae_is_free_board($bo_table)) {
    $list_url = function_exists('eottae_free_list_url') ? eottae_free_list_url() : G5_BBS_URL.'/board.php?bo_table=free';
} elseif ($is_report_board_view) {
    $list_url = function_exists('eottae_board_list_url')
        ? eottae_board_list_url(eottae_report_board_table())
        : G5_BBS_URL.'/board.php?bo_table='.eottae_report_board_table();
} else {
    $list_url = eottae_community_list_url($view_category !== '' ? array('sca' => $view_category) : array());
}

$is_talkroom_board = function_exists('eottae_talkroom_board_table') && $bo_table === eottae_talkroom_board_table();
$is_ai_post = false;
if ($is_talkroom_board) {
    include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
    $is_ai_post = eottae_talkroom_ai_message_is_ai($view);
}
$article_class = 'community-view-page__article';
if ($is_ai_post) {
    $article_class .= ' community-view-page__article--ai is-talk-ai-message';
}
?>

<div class="community-view-page board-wrap board-wrap--eottae-community" id="bo_v" style="width:<?php echo $width; ?>">

<div class="community-view-page__layout">
<main class="community-view-page__main">

    <header class="community-view-page__header">
        <a href="<?php echo $list_href ? $list_href : $list_url; ?>" class="community-view-page__back">← 목록으로</a>
        <?php if ($view_category) { ?>
        <span class="community-view-page__category"><?php echo $view_category; ?></span>
        <?php } ?>
    </header>

    <article class="<?php echo $article_class; ?>">
        <?php if ($is_ai_post) { ?>
        <div class="community-view-page__ai-label">
            <?php echo eottae_talkroom_ai_message_render_badge($view); ?>
        </div>
        <?php } ?>

        <h1 class="community-view-page__title<?php echo $is_ai_post ? ' talk-ai-msg__title' : ''; ?>"><?php echo get_text($view['wr_subject']); ?></h1>

        <?php if ($is_report_board_view) { ?>
        <div class="report-info-panel">
            <div class="report-info-panel__head">
                <?php echo eottae_report_render_status_badge($report_status, 'report-status-badge--view'); ?>
                <?php echo eottae_report_render_type_badge($report_type); ?>
            </div>
            <dl class="report-info-panel__grid">
                <div class="report-info-panel__item">
                    <dt>지역</dt>
                    <dd><?php echo get_text($report_region_label); ?></dd>
                </div>
                <div class="report-info-panel__item">
                    <dt>작성자</dt>
                    <dd><?php echo get_text($report_author_label); ?></dd>
                </div>
                <?php if ($report_shop_name !== '') { ?>
                <div class="report-info-panel__item">
                    <dt>관련 업체</dt>
                    <dd><?php echo $report_shop_name; ?></dd>
                </div>
                <?php } ?>
                <?php if ($report_link !== '') { ?>
                <div class="report-info-panel__item report-info-panel__item--full report-info-panel__link">
                    <dt>관련 링크</dt>
                    <dd><a href="<?php echo htmlspecialchars($report_link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo $report_link; ?></a></dd>
                </div>
                <?php } ?>
            </dl>
        </div>
        <?php
        if ($report_can_view_contact) {
            include G5_PATH.'/components/eottae/report-admin-panel.php';
        }
        ?>
        <?php } ?>

        <?php if ($is_event_board_view) { ?>
        <div class="event-info-panel">
            <div class="event-info-panel__head">
                <?php echo eottae_event_render_status_badge($event_status, 'event-status-badge--view'); ?>
                <?php echo eottae_event_render_type_badge($event_type); ?>
            </div>
            <dl class="event-info-panel__grid">
                <?php if ($event_display_name !== '') { ?>
                <div class="event-info-panel__item">
                    <dt>업체/작성자</dt>
                    <dd><?php echo $event_display_name; ?></dd>
                </div>
                <?php } ?>
                <div class="event-info-panel__item">
                    <dt>이벤트 기간</dt>
                    <dd><?php echo get_text($event_period_label); ?></dd>
                </div>
                <?php if ($event_benefit !== '') { ?>
                <div class="event-info-panel__item event-info-panel__benefit">
                    <dt>혜택 요약</dt>
                    <dd><?php echo $event_benefit; ?></dd>
                </div>
                <?php } ?>
                <?php if ($event_contact !== '') { ?>
                <div class="event-info-panel__item">
                    <dt>문의 방법</dt>
                    <dd><?php echo $event_contact; ?></dd>
                </div>
                <?php } ?>
            </dl>
            <div class="event-info-panel__actions">
                <?php if (is_array($event_shop) && !empty($event_shop['view_url'])) { ?>
                <a href="<?php echo get_text($event_shop['view_url']); ?>" class="event-post__btn event-post__btn--shop">업체정보 바로가기</a>
                <?php } ?>
            </div>
        </div>
        <?php if ($event_can_close) { ?>
        <div class="event-close-panel" data-event-close-panel data-proc-url="<?php echo G5_URL; ?>/proc/eottae-event-close.php" data-bo-table="<?php echo get_text($bo_table); ?>" data-wr-id="<?php echo (int) $view['wr_id']; ?>">
            <button type="button" class="event-close-panel__btn" data-event-close-btn>이벤트 종료하기</button>
        </div>
        <?php } ?>
        <?php } ?>

        <?php if ($is_estate_board_view && !empty($view['mb_id'])) {
            $estate_view_thumb = eottae_estate_render_list_thumb($view, '', array('view_profile' => true));
            if ($estate_view_thumb !== '') { ?>
        <div class="community-view-page__estate-profile">
            <?php echo $estate_view_thumb; ?>
            <div class="community-view-page__estate-profile-meta">
                <?php if (function_exists('eottae_member_growth_render_author_line')) {
                    include_once G5_PATH.'/components/eottae/member-growth-display.php';
                    echo eottae_member_growth_render_author_line($view['mb_id'], $view['name'], array('inline' => true));
                } else { ?>
                <span class="community-view-page__author"><?php echo $view['name']; ?></span>
                <?php } ?>
            </div>
        </div>
            <?php }
        } ?>

        <?php if ($is_estate_board_view) { ?>
        <div class="estate-deal-panel"<?php echo $estate_can_change_deal ? ' data-estate-deal-panel data-proc-url="'.G5_URL.'/proc/eottae-estate-deal-status.php" data-bo-table="'.get_text($bo_table).'" data-wr-id="'.(int) $view['wr_id'].'"' : ''; ?>>
            <span class="estate-deal-panel__label">거래 상태</span>
            <?php echo eottae_estate_render_deal_badge($estate_deal_status, 'estate-deal-badge--view'); ?>
            <?php if ($estate_can_change_deal) { ?>
            <div class="estate-deal-panel__actions" role="group" aria-label="거래 상태 변경">
                <?php foreach (eottae_estate_deal_statuses() as $status_key => $status_label) { ?>
                <button type="button" class="estate-deal-panel__btn<?php echo $estate_deal_status === $status_key ? ' is-active' : ''; ?>" data-estate-status="<?php echo get_text($status_key); ?>" aria-pressed="<?php echo $estate_deal_status === $status_key ? 'true' : 'false'; ?>"><?php echo get_text($status_label); ?></button>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php
        $estate_template_data = $is_estate_board_view && function_exists('eottae_estate_template_from_row')
            ? eottae_estate_template_from_row($view)
            : null;
        if ($is_estate_board_view && is_array($estate_template_data)) {
            include G5_PATH.'/components/eottae/estate-view-detail.php';
        }

        if ($is_estate_board_view && is_array($estate_location) && ($estate_location['location_text'] !== '' || $estate_location['area_label'] !== '')) { ?>
        <section class="estate-location-panel" aria-label="매물 위치">
            <h2 class="estate-location-panel__title">매물 위치</h2>
            <p class="estate-location-panel__notice">정확한 호수나 개인 주소 대신 근처 위치 기준으로 표시됩니다.</p>
            <dl class="estate-location-panel__grid">
                <div class="estate-location-panel__item">
                    <dt>지역</dt>
                    <dd><?php echo get_text($estate_location['area_label']); ?></dd>
                </div>
                <?php if ($estate_location['location_text'] !== '') { ?>
                <div class="estate-location-panel__item">
                    <dt>상세위치</dt>
                    <dd><?php echo get_text($estate_location['location_text']); ?></dd>
                </div>
                <?php } ?>
            </dl>
            <?php if (function_exists('eottae_estate_has_map_location') && eottae_estate_has_map_location($view)) {
                $estate_map_name = get_text($view['wr_subject']);
                if (!empty($estate_template_data['building_name'])) {
                    $estate_map_name = get_text($estate_template_data['building_name']);
                }
                $shop_map = array(
                    'address' => $estate_location['location_text'],
                    'lat'     => $estate_location['latitude'],
                    'lng'     => $estate_location['longitude'],
                    'name'    => $estate_map_name,
                );
                if (function_exists('eottae_enqueue_google_maps')) {
                    eottae_enqueue_google_maps();
                }
                echo '<div class="estate-location-panel__map">';
                include G5_PATH.'/components/eottae/shop-detail-map.php';
                echo '</div>';
            } ?>
        </section>
        <?php }
        } ?>

        <?php if ($is_job_board_view && !empty($view['mb_id'])) {
            $job_view_thumb = eottae_job_render_list_thumb($view, '', array('view_profile' => true));
            if ($job_view_thumb !== '') { ?>
        <div class="community-view-page__job-profile">
            <?php echo $job_view_thumb; ?>
            <div class="community-view-page__job-profile-meta">
                <?php if (function_exists('eottae_member_growth_render_author_line')) {
                    include_once G5_PATH.'/components/eottae/member-growth-display.php';
                    echo eottae_member_growth_render_author_line($view['mb_id'], $view['name'], array('inline' => true));
                } else { ?>
                <span class="community-view-page__author"><?php echo $view['name']; ?></span>
                <?php } ?>
            </div>
        </div>
            <?php }
        } ?>

        <?php
        $job_template_data = $is_job_board_view && function_exists('eottae_job_template_from_row')
            ? eottae_job_template_from_row($view)
            : null;
        if ($is_job_board_view && is_array($job_shop) && !empty($job_shop['view_url'])) { ?>
        <section class="job-shop-panel" aria-label="연결 업체">
            <?php if (!empty($job_shop['thumb_url'])) { ?>
            <img src="<?php echo get_text($job_shop['thumb_url']); ?>" alt="" class="job-shop-panel__thumb" loading="lazy" decoding="async">
            <?php } ?>
            <div class="job-shop-panel__body">
                <span class="job-shop-panel__eyebrow">연결 업체</span>
                <strong class="job-shop-panel__name"><?php echo get_text($job_shop['name'] ?? ''); ?></strong>
                <?php
                $job_shop_meta = array_filter(array($job_shop['board_label'] ?? '', $job_shop['region'] ?? ''));
                if ($job_shop_meta) { ?>
                <span class="job-shop-panel__meta"><?php echo get_text(implode(' · ', $job_shop_meta)); ?></span>
                <?php } ?>
            </div>
            <a href="<?php echo get_text($job_shop['view_url']); ?>" class="job-shop-panel__link">업체정보 바로가기</a>
        </section>
        <?php }
        if ($is_job_board_view && is_array($job_template_data)) {
            include G5_PATH.'/components/eottae/job-view-detail.php';
        }
        ?>
        <?php if ($is_job_board_view && is_array($job_location) && ($job_location['location_text'] !== '' || $job_location['area_label'] !== '')) { ?>
        <section class="job-location-panel" aria-label="근무 위치">
            <h2 class="job-location-panel__title">근무 위치</h2>
            <dl class="job-location-panel__grid">
                <div class="job-location-panel__item">
                    <dt>지역</dt>
                    <dd><?php echo get_text($job_location['area_label']); ?></dd>
                </div>
                <?php if ($job_location['location_text'] !== '') { ?>
                <div class="job-location-panel__item">
                    <dt>상세위치</dt>
                    <dd><?php echo get_text($job_location['location_text']); ?></dd>
                </div>
                <?php } ?>
            </dl>
            <?php if (!empty($job_location['map_visible']) && $job_location['latitude'] !== '' && $job_location['longitude'] !== '' && is_numeric($job_location['latitude']) && is_numeric($job_location['longitude'])) {
                $shop_map = array(
                    'address' => $job_location['location_text'],
                    'lat'     => $job_location['latitude'],
                    'lng'     => $job_location['longitude'],
                    'name'    => get_text($view['wr_subject']),
                );
                if (function_exists('eottae_enqueue_google_maps')) {
                    eottae_enqueue_google_maps();
                }
                echo '<div class="job-location-panel__map">';
                include G5_PATH.'/components/eottae/shop-detail-map.php';
                echo '</div>';
            } ?>
        </section>
        <?php } ?>
        <?php if ($is_job_board_view) { ?>
        <div class="job-recruit-panel"<?php echo $job_can_change_recruit ? ' data-job-recruit-panel data-proc-url="'.G5_URL.'/proc/eottae-job-recruit-status.php" data-bo-table="'.get_text($bo_table).'" data-wr-id="'.(int) $view['wr_id'].'"' : ''; ?>>
            <span class="job-recruit-panel__label">모집 상태</span>
            <?php echo eottae_job_render_recruit_badge($job_recruit_status, 'job-recruit-badge--view'); ?>
            <?php if ($job_can_change_recruit) { ?>
            <div class="job-recruit-panel__actions" role="group" aria-label="모집 상태 변경">
                <?php foreach (eottae_job_recruit_statuses() as $status_key => $status_label) { ?>
                <button type="button" class="job-recruit-panel__btn<?php echo $job_recruit_status === $status_key ? ' is-active' : ''; ?>" data-job-recruit-status="<?php echo get_text($status_key); ?>" aria-pressed="<?php echo $job_recruit_status === $status_key ? 'true' : 'false'; ?>"><?php echo get_text($status_label); ?></button>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="community-view-page__meta">
            <?php if ($is_report_board_view) { ?>
            <span class="community-view-page__author"><?php echo get_text($report_author_label); ?></span>
            <?php } elseif ($is_ai_post) { ?>
            <span class="community-view-page__author talk-ai-msg__author-line"><?php echo eottae_talkroom_ai_message_display_name($view); ?></span>
            <?php } elseif (!$is_estate_board_view && !$is_job_board_view && function_exists('eottae_member_growth_render_author_line') && !empty($view['mb_id'])) { ?>
            <span class="community-view-page__author"><?php echo eottae_member_growth_render_author_line($view['mb_id'], $view['name'], array('inline' => true, 'badge_only' => true)); ?></span>
            <?php } else { ?>
            <span class="community-view-page__author"><?php echo $view['name']; ?></span>
            <?php } ?>
            <time datetime="<?php echo date('c', strtotime($view['wr_datetime'])); ?>"><?php echo date('Y.m.d H:i', strtotime($view['wr_datetime'])); ?></time>
            <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
            <?php if ($view['wr_comment']) { ?><span>댓글 <?php echo number_format($view['wr_comment']); ?></span><?php } ?>
        </div>

        <?php include_once G5_PATH.'/components/eottae/community-view-media.php'; ?>

        <?php if (function_exists('eottae_is_community_board') && eottae_is_community_board($bo_table)) {
            include_once G5_PATH.'/components/eottae/community-view-links.php';
        } ?>

        <?php
        $is_icrm_view_body = function_exists('eottae_icrm_content_should_preserve_html')
            && eottae_icrm_content_should_preserve_html($view['wr_content'] ?? '');
        $view_body_plain = trim(strip_tags((string) ($view['content'] ?? '')));
        $job_hide_plain_body = $is_job_board_view && is_array($job_template_data)
            && strpos($view_body_plain, '[구인정보]') !== false
            && !$is_icrm_view_body;
        $estate_hide_plain_body = $is_estate_board_view && is_array($estate_template_data)
            && (
                strpos($view_body_plain, '[부동산 매물정보]') !== false
                || $is_icrm_view_body
            );
        $event_hide_plain_body = $is_event_board_view && (
            (function_exists('eottae_event_view_should_hide_body') && eottae_event_view_should_hide_body($view))
            || ($is_icrm_view_body && function_exists('eottae_event_row_has_panel_data') && eottae_event_row_has_panel_data($view))
        );
        $hide_plain_body = $job_hide_plain_body || $estate_hide_plain_body || $event_hide_plain_body;
        ?>
        <section class="community-view-page__body talk-ai-msg__body<?php echo $is_ai_post ? ' talk-ai-msg__body--ai' : ''; ?><?php echo $hide_plain_body ? ' community-view-page__body--template' : ''; ?><?php echo $is_icrm_view_body ? ' community-view-page__body--icrm' : ''; ?>" id="bo_v_con"<?php echo $hide_plain_body ? ' hidden' : ''; ?>>
            <?php echo get_view_thumbnail($view['content']); ?>
        </section>
    </article>

    <?php include_once(G5_BBS_PATH.'/view_comment.php'); ?>

    <footer class="community-view-page__footer">
        <ul class="board-actions btn_bo_user community-view-page__actions">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href; ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href; ?>" class="btn_b01 btn">수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href; ?>" class="btn_b01 btn" onclick="return confirm('삭제하시겠습니까?');">삭제</a></li><?php } ?>
            <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href; ?>" class="btn_b01 btn">답변</a></li><?php } ?>
            <?php
            if (!$is_talkroom_board && !$is_report_board_view && function_exists('eottae_is_community_board') && eottae_is_community_board($bo_table)) {
                include_once G5_PATH.'/components/eottae/community-report.php';
                ?>
            <li><?php eottae_community_render_post_report_button($view, $member, $is_admin); ?></li>
            <?php } ?>
        </ul>
    </footer>
</main>

<?php include_once(G5_PATH.'/components/eottae/community-sidebar.php'); ?>
</div>

</div>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
<script>
$(function() {
    $(".community-view-page__gallery, .community-view-page__body").viewimageresize();
    $("a.view_image").on("click", function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });
});
</script>
