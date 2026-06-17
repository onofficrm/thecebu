<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH.'/extend/eottae.config.php';
include_once G5_LIB_PATH.'/eottae-secrets.lib.php';
include_once G5_LIB_PATH.'/eottae-i18n.lib.php';
include_once G5_LIB_PATH.'/eottae-translation.lib.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-language-meta.lib.php';
include_once G5_LIB_PATH.'/eottae-lang-seo.lib.php';
include_once G5_LIB_PATH.'/eottae-ai-generate.lib.php';
include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
include_once G5_LIB_PATH.'/eottae-ad.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-seo.lib.php';
include_once G5_LIB_PATH.'/eottae-board-seo.lib.php';
include_once G5_LIB_PATH.'/eottae-job-template.lib.php';
include_once G5_LIB_PATH.'/eottae-job.lib.php';
include_once G5_LIB_PATH.'/eottae-property-template.lib.php';
include_once G5_LIB_PATH.'/eottae-community-hub.lib.php';
include_once G5_LIB_PATH.'/eottae-estate.lib.php';
include_once G5_LIB_PATH.'/eottae-free-board.lib.php';
include_once G5_LIB_PATH.'/eottae-board-write-mobile.lib.php';
include_once G5_LIB_PATH.'/eottae-icrm.lib.php';
include_once G5_LIB_PATH.'/eottae-icrm-template.lib.php';
include_once G5_LIB_PATH.'/eottae-board-editor.lib.php';
include_once G5_LIB_PATH.'/eottae-business-snippet.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-spot.lib.php';
include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';

include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';
include_once G5_LIB_PATH.'/eottae-promo-coupon.lib.php';
include_once G5_LIB_PATH.'/eottae-review-delete.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-notify.lib.php';
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
include_once G5_LIB_PATH.'/eottae-calendar-google.lib.php';
include_once G5_LIB_PATH.'/eottae-calendar-report.lib.php';
include_once G5_LIB_PATH.'/eottae-briefing.lib.php';
include_once G5_LIB_PATH.'/eottae-location.lib.php';
include_once G5_LIB_PATH.'/eottae-market.lib.php';
include_once G5_LIB_PATH.'/eottae-report.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-likes.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-report.lib.php';
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-columnist-recruit.lib.php';
include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
include_once G5_LIB_PATH.'/eottae-column-bookmarks.lib.php';
include_once G5_LIB_PATH.'/eottae-column-report.lib.php';
include_once G5_LIB_PATH.'/eottae-adroom.lib.php';
include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
include_once G5_LIB_PATH.'/eottae-member-profile.lib.php';
include_once G5_LIB_PATH.'/eottae-gnb-menu.lib.php';
include_once G5_LIB_PATH.'/eottae-seo-feed.lib.php';
include_once G5_LIB_PATH.'/eottae-sitemap.lib.php';
include_once G5_LIB_PATH.'/eottae-pwa.lib.php';

if (function_exists('eottae_secrets_load')) {
    eottae_secrets_load();
} elseif (function_exists('eottae_merge_runtime_secrets')) {
    eottae_merge_runtime_secrets();
}

if (function_exists('eottae_business_coupon_ensure_schema')) {
    eottae_business_coupon_ensure_schema();
}
if (function_exists('eottae_promo_coupon_ensure_schema')) {
    eottae_promo_coupon_ensure_schema();
}
if (function_exists('eottae_review_delete_ensure_schema')) {
    eottae_review_delete_ensure_schema();
}
if (function_exists('eottae_talkroom_ensure_schema')) {
    eottae_talkroom_ensure_schema();
}
if (function_exists('eottae_calendar_ensure_schema')) {
    eottae_calendar_ensure_schema();
}
if (function_exists('eottae_challenge_ensure_schema')) {
    eottae_challenge_ensure_schema();
}
if (function_exists('eottae_golf_join_ensure_schema')) {
    eottae_golf_join_ensure_schema();
}
if (function_exists('eottae_column_ensure_schema')) {
    eottae_column_ensure_schema();
}
if (function_exists('eottae_adroom_ensure_schema')) {
    eottae_adroom_ensure_schema();
}
if (function_exists('eottae_report_ensure_schema')) {
    eottae_report_ensure_schema();
}
if (function_exists('eottae_market_ensure_schema')) {
    eottae_market_ensure_schema();
}
if (function_exists('eottae_free_ensure_schema')) {
    eottae_free_ensure_schema();
}
if (function_exists('eottae_member_growth_ensure_schema')) {
    eottae_member_growth_ensure_schema();
}
if (function_exists('eottae_translation_ensure_schema')) {
    eottae_translation_ensure_schema();
}
if (function_exists('eottae_seo_ensure_rss_boards')) {
    eottae_seo_ensure_rss_boards();
}
if (is_file(G5_LIB_PATH.'/eottae-community-report.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-community-report.lib.php';
    if (function_exists('eottae_community_reports_ensure_schema')) {
        eottae_community_reports_ensure_schema();
    }
}
if (function_exists('eottae_talkroom_ai_ensure_schema')) {
    eottae_talkroom_ai_ensure_schema();
}
if (is_file(G5_LIB_PATH.'/eottae-public-ai.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
    if (function_exists('eottae_public_ai_ensure_schema')) {
        eottae_public_ai_ensure_schema();
    }
}
if (is_file(G5_LIB_PATH.'/eottae-public-ai-generator.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-public-ai-generator.lib.php';
}
if (is_file(G5_LIB_PATH.'/eottae-public-ai-publish.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';
}
foreach (array('guard', 'weather', 'news', 'news-feed', 'poll', 'openai') as $public_ai_module) {
    $public_ai_lib = G5_LIB_PATH.'/eottae-public-ai-'.$public_ai_module.'.lib.php';
    if (is_file($public_ai_lib)) {
        include_once $public_ai_lib;
    }
}
if (function_exists('eottae_ad_ensure_table')) {
    eottae_ad_ensure_table();
}
if (function_exists('eottae_shop_seo_ensure_table')) {
    eottae_shop_seo_ensure_table();
}
if (function_exists('eottae_shop_map_thumb_ensure_table')) {
    eottae_shop_map_thumb_ensure_table();
}
if (function_exists('eottae_business_snippet_ensure_table')) {
    eottae_business_snippet_ensure_table();
}
if (function_exists('eottae_shop_sync_board_categories')) {
    eottae_shop_sync_board_categories();
}
if (function_exists('eottae_shop_backfill_missing_coords')) {
    eottae_shop_backfill_missing_coords(50);
}

if (!function_exists('eottae_on_register_after')) {
    function eottae_on_register_after($mb_id, $w)
    {
        if ($w !== '' || $mb_id === '') {
            return;
        }

        eottae_coupon_ensure_welcome($mb_id);

        if (function_exists('eottae_member_growth_on_register')) {
            eottae_member_growth_on_register($mb_id);
        }
    }
}
add_event('register_form_update_after', 'eottae_on_register_after', 10, 2);

add_event('write_update_after', 'eottae_member_growth_on_write', 28, 3);
add_event('comment_update_after', 'eottae_member_growth_on_comment', 28, 5);

if (function_exists('add_replace')) {
    add_replace('get_pretty_url', 'eottae_pretty_shop_board_url', 20, 5);
}

if (!function_exists('eottae_shop_write_update_move_url')) {
    /**
     * 업체 등록·수정 후 이동 URL — short_url_clean 이 /shop/{seo} (영카트) 로 바꾸는 것 방지
     */
    function eottae_shop_write_update_move_url($redirect_url, $board, $wr_id, $w, $qstr, $file_upload_msg)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($board['bo_table'])) {
            return $redirect_url;
        }

        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !function_exists('eottae_shop_view_url')) {
            return $redirect_url;
        }

        $qs = '';
        if ($qstr !== '') {
            $qs = ltrim(str_replace('&amp;', '&', (string) $qstr), '&');
        }

        return eottae_shop_view_url($wr_id, $board['bo_table'], $qs);
    }
}
add_replace('write_update_move_url', 'eottae_shop_write_update_move_url', 10, 6);

if (!function_exists('eottae_shop_board_write_rewrite_lines')) {
    /**
     * 업체 게시판 글쓰기 URL — 영카트 /shop/{it_id} 규칙보다 먼저 bbs/write.php 로 연결
     *
     * @return string
     */
    function eottae_shop_board_write_rewrite_lines()
    {
        if (!function_exists('eottae_shop_board_tables')) {
            return '';
        }

        $lines = array();
        foreach (eottae_shop_board_tables() as $bo_table) {
            $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
            if ($bo_table === '') {
                continue;
            }
            $lines[] = 'RewriteRule ^'.$bo_table.'/write$ '.G5_BBS_DIR.'/write.php?bo_table='.$bo_table.'&rewrite=1 [QSA,L]';
        }

        return implode("\n", $lines);
    }
}

if (!function_exists('eottae_add_shop_board_write_rewrite_pre_rules')) {
    function eottae_add_shop_board_write_rewrite_pre_rules($rules, $get_path_url, $base_path, $return_string = false)
    {
        $lines = eottae_shop_board_write_rewrite_lines();
        if ($lines === '') {
            return $rules;
        }

        return $lines."\n".$rules;
    }
}
add_replace('add_mod_rewrite_pre_rules', 'eottae_add_shop_board_write_rewrite_pre_rules', 5, 3);

if (!function_exists('eottae_add_shop_board_write_nginx_pre_rules')) {
    function eottae_add_shop_board_write_nginx_pre_rules($rules, $get_path_url, $base_path, $return_string = false)
    {
        if (!function_exists('eottae_shop_board_tables')) {
            return $rules;
        }

        $lines = array();
        foreach (eottae_shop_board_tables() as $bo_table) {
            $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
            if ($bo_table === '') {
                continue;
            }
            $lines[] = 'rewrite ^'.$base_path.$bo_table.'/write$ '.$base_path.G5_BBS_DIR.'/write.php?bo_table='.$bo_table.'&rewrite=1 break;';
        }

        if (empty($lines)) {
            return $rules;
        }

        return implode("\n", $lines)."\n".$rules;
    }
}
add_replace('add_nginx_conf_pre_rules', 'eottae_add_shop_board_write_nginx_pre_rules', 5, 3);

if (function_exists('eottae_lang_seo_init')) {
    eottae_lang_seo_init();
}
if (function_exists('add_replace')) {
    add_replace('add_mod_rewrite_pre_rules', 'eottae_lang_seo_rewrite_pre_rules', 4, 3);
    add_replace('add_nginx_conf_pre_rules', 'eottae_lang_seo_nginx_pre_rules', 4, 3);
}

if (!function_exists('eottae_on_lang_seo_board_head')) {
    function eottae_on_lang_seo_board_head($board, $write, $wr_id)
    {
        if (function_exists('eottae_lang_seo_apply_board_view_robots')) {
            eottae_lang_seo_apply_board_view_robots($board, $write, (int) $wr_id);
        }
    }
}
add_event('board_head_before', 'eottae_on_lang_seo_board_head', 8, 3);

if (!function_exists('eottae_on_lang_seo_board_meta')) {
    function eottae_on_lang_seo_board_meta($board, $write, $wr_id)
    {
        if (function_exists('eottae_lang_seo_apply_board_view_meta')) {
            eottae_lang_seo_apply_board_view_meta($board, $write, (int) $wr_id);
        }
    }
}
add_event('board_head_before', 'eottae_on_lang_seo_board_meta', 11, 3);

if (!function_exists('eottae_on_shop_write_before')) {
    function eottae_on_shop_write_before($board, $wr_id, $w, $qstr)
    {
        global $is_admin;

        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        if (function_exists('eottae_shop_prepare_write_post')) {
            eottae_shop_prepare_write_post($board);
        }

        $ca = isset($_POST['ca_name']) ? trim((string) $_POST['ca_name']) : '';
        $wr1 = isset($_POST['wr_1']) ? trim((string) $_POST['wr_1']) : '';

        if ($ca !== '' && $wr1 === '') {
            $_POST['wr_1'] = $ca;
        } elseif ($wr1 !== '' && $ca === '') {
            $_POST['ca_name'] = $wr1;
        }

        $address = isset($_POST['wr_3']) ? trim((string) $_POST['wr_3']) : '';
        $region = isset($_POST['wr_2']) ? trim((string) $_POST['wr_2']) : '';
        if ($region === '' && $address !== '' && function_exists('eottae_shop_detect_region')) {
            $_POST['wr_2'] = eottae_shop_detect_region($address);
        }

        if ($is_admin === 'super' && isset($_POST['eottae_owner_mb_id'])) {
            $owner_mb_id = trim(strip_tags((string) $_POST['eottae_owner_mb_id']));
            if ($owner_mb_id !== '') {
                $check = eottae_shop_owner_validate($owner_mb_id);
                if (empty($check['ok'])) {
                    alert($check['message']);
                }
            }
        }
    }
}
add_event('write_update_before', 'eottae_on_shop_write_before', 10, 4);

if (!function_exists('eottae_on_shop_owner_assign')) {
    function eottae_on_shop_owner_assign($board, $wr_id, $w, $qstr, $redirect_url)
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return;
        }
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }
        if (!isset($_POST['eottae_owner_mb_id'])) {
            return;
        }

        $owner_mb_id = trim(strip_tags((string) $_POST['eottae_owner_mb_id']));
        if ($owner_mb_id === '') {
            return;
        }

        eottae_shop_assign_owner($board['bo_table'], (int) $wr_id, $owner_mb_id);
    }
}
add_event('write_update_after', 'eottae_on_shop_owner_assign', 15, 5);

if (!function_exists('eottae_on_shop_write_after')) {
    function eottae_on_shop_write_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        if (function_exists('eottae_ad_sync_from_shop')) {
            eottae_ad_sync_from_shop($board['bo_table'], (int) $wr_id);
        }

        if (function_exists('eottae_shop_seo_save')) {
            eottae_shop_seo_save($board['bo_table'], (int) $wr_id, eottae_shop_seo_from_post());
        }

        if (function_exists('eottae_shop_map_thumb_save_from_upload')) {
            eottae_shop_map_thumb_save_from_upload($board['bo_table'], (int) $wr_id);
        }

        if (function_exists('eottae_shop_ensure_inquiry_code')) {
            eottae_shop_ensure_inquiry_code($board['bo_table'], (int) $wr_id);
        }
    }
}
add_event('write_update_after', 'eottae_on_shop_write_after', 10, 5);

if (!function_exists('eottae_on_shop_board_head')) {
    function eottae_on_shop_board_head($board, $write, $wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !is_array($write) || empty($write['wr_id'])) {
            return;
        }
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        eottae_shop_seo_apply_page($board, $write);

        if (function_exists('eottae_auto_seed_luckyvilla_reviews_if_needed')) {
            eottae_auto_seed_luckyvilla_reviews_if_needed($wr_id, $write);
        }
    }
}
add_event('board_head_before', 'eottae_on_shop_board_head', 10, 3);

if (!function_exists('eottae_on_board_seo_head')) {
    function eottae_on_board_seo_head($board, $write, $wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !is_array($write) || empty($write['wr_id'])) {
            return;
        }
        if (!function_exists('eottae_board_seo_apply_page')) {
            return;
        }

        eottae_board_seo_apply_page($board, $write);
    }
}
add_event('board_head_before', 'eottae_on_board_seo_head', 9, 3);

if (!function_exists('eottae_on_board_seo_write_after')) {
    function eottae_on_board_seo_write_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_board_seo_is_target_board')
            || !eottae_board_seo_is_target_board($board)) {
            return;
        }

        if (function_exists('eottae_board_seo_sync_write')) {
            eottae_board_seo_sync_write($board['bo_table'], (int) $wr_id);
        }
    }
}
add_event('write_update_after', 'eottae_on_board_seo_write_after', 18, 5);

if (!function_exists('eottae_icrm_on_write_update_after')) {
    /**
     * 모든 게시판 — wr_seo_title 비어 있으면 write_update.php와 동일 로직으로 확정 (iCRM·직접 INSERT 대비)
     */
    function eottae_icrm_on_write_update_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_icrm_ensure_wr_seo_title')) {
            return;
        }

        eottae_icrm_ensure_wr_seo_title($board['bo_table'], (int) $wr_id);
    }
}
add_event('write_update_after', 'eottae_icrm_on_write_update_after', 12, 5);

if (!function_exists('eottae_icrm_on_board_view')) {
    /**
     * 게시글 조회 시 wr_seo_title 자동 보정 + iCRM 템플릿 CSS 로드
     */
    function eottae_icrm_on_board_view($board, $write_row, $wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || empty($board['bo_table']) || !is_array($write_row) || empty($write_row['wr_id'])) {
            return;
        }

        if (function_exists('eottae_icrm_maybe_enqueue_template_styles')) {
            eottae_icrm_maybe_enqueue_template_styles($write_row);
        }

        if (function_exists('eottae_is_estate_board') && eottae_is_estate_board($board['bo_table'])) {
            if (!function_exists('eottae_estate_template_sync_wr3_from_row')) {
                include_once G5_LIB_PATH.'/eottae-estate-template.lib.php';
            }
            if (function_exists('eottae_estate_template_sync_wr3_from_row')) {
                eottae_estate_template_sync_wr3_from_row($board['bo_table'], $wr_id);
                $write_row = get_write(get_write_table_name($board['bo_table']), $wr_id, true);
            }
        }

        if (function_exists('eottae_is_event_board') && eottae_is_event_board($board['bo_table'])) {
            if (!function_exists('eottae_event_sync_fields_from_row')) {
                include_once G5_LIB_PATH.'/eottae-event.lib.php';
            }
            if (function_exists('eottae_event_sync_fields_from_row')) {
                eottae_event_sync_fields_from_row($board['bo_table'], $wr_id);
            }
        }

        if (!function_exists('eottae_icrm_ensure_wr_seo_title')) {
            return;
        }

        $result = eottae_icrm_ensure_wr_seo_title($board['bo_table'], $wr_id);
        if (empty($result['ok']) || !isset($result['wr_seo_title'])) {
            return;
        }

        global $write;
        if (is_array($write) && (int) $write['wr_id'] === $wr_id) {
            $write['wr_seo_title'] = (string) $result['wr_seo_title'];
        }
    }
}
add_event('board_head_before', 'eottae_icrm_on_board_view', 8, 3);

add_event('html_purifier_config', 'eottae_icrm_html_purifier_config', 10, 2);
add_replace('html_purifier_result', 'eottae_icrm_html_purifier_result', 10, 3);

if (!function_exists('eottae_on_shop_delete')) {
    function eottae_on_shop_delete($write, $board)
    {
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }
        if (!is_array($write) || empty($write['wr_id'])) {
            return;
        }

        eottae_shop_seo_delete($board['bo_table'], (int) $write['wr_id']);
        if (function_exists('eottae_shop_map_thumb_delete')) {
            eottae_shop_map_thumb_delete($board['bo_table'], (int) $write['wr_id']);
        }
        if (function_exists('eottae_ad_deactivate_by_shop')) {
            eottae_ad_deactivate_by_shop($board['bo_table'], (int) $write['wr_id']);
        }
    }
}
add_event('bbs_delete', 'eottae_on_shop_delete', 10, 2);

if (!function_exists('eottae_is_youtube_board')) {
    function eottae_is_youtube_board($bo_table)
    {
        $bo_table = (string) $bo_table;
        $youtube = defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube';

        return $bo_table !== '' && $bo_table === $youtube;
    }
}

if (!function_exists('eottae_on_youtube_write_after')) {
    function eottae_on_youtube_write_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !eottae_is_youtube_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('g5b_youtube_save_duration')) {
            include_once G5_SKIN_PATH.'/board/_inc/g5b-youtube.php';
        }

        g5b_youtube_save_duration($board['bo_table'], (int) $wr_id);
    }
}
add_event('write_update_after', 'eottae_on_youtube_write_after', 20, 5);

if (!function_exists('eottae_is_community_board')) {
    function eottae_is_community_board($bo_table)
    {
        $community = defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';

        return (string) $bo_table !== '' && (string) $bo_table === $community;
    }
}

if (!function_exists('eottae_on_community_write_before')) {
    function eottae_on_community_write_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table'])) {
            return;
        }

        $bo_table = $board['bo_table'];
        if (function_exists('eottae_is_community_hub_board') && eottae_is_community_hub_board($bo_table)) {
            if (function_exists('eottae_community_hub_apply_runtime')) {
                eottae_community_hub_apply_runtime($bo_table);
            }
        } elseif (!eottae_is_community_board($bo_table)) {
            return;
        }

        if (function_exists('eottae_community_board_ensure_settings')) {
            eottae_community_board_ensure_settings();
        }

        if (isset($_POST['wr_link1'])) {
            $_POST['wr_link1'] = function_exists('eottae_community_normalize_url')
                ? eottae_community_normalize_url($_POST['wr_link1'])
                : trim((string) $_POST['wr_link1']);
        }

        if (isset($_POST['wr_link2'])) {
            $_POST['wr_link2'] = function_exists('eottae_community_normalize_url')
                ? eottae_community_normalize_url($_POST['wr_link2'])
                : trim((string) $_POST['wr_link2']);
        }
    }
}
add_event('write_update_before', 'eottae_on_community_write_before', 12, 4);

if (function_exists('eottae_community_board_ensure_settings')) {
    eottae_community_board_ensure_settings();
}

if (function_exists('eottae_gallery_board_ensure_settings')) {
    eottae_gallery_board_ensure_settings();
}

if (!function_exists('eottae_on_gallery_write_before')) {
    function eottae_on_gallery_write_before($board, $wr_id, $w, $qstr)
    {
        global $board;

        if (empty($board['bo_table']) || !function_exists('eottae_is_gallery_board_table') || !eottae_is_gallery_board_table($board['bo_table'])) {
            return;
        }

        if (function_exists('eottae_gallery_board_ensure_settings')) {
            eottae_gallery_board_ensure_settings();
        }

        $board['bo_upload_size'] = max((int) ($board['bo_upload_size'] ?? 0), eottae_gallery_upload_size());
        $board['bo_upload_count'] = max((int) ($board['bo_upload_count'] ?? 0), eottae_gallery_photo_limit());
    }
}
add_event('write_update_before', 'eottae_on_gallery_write_before', 11, 4);

if (!function_exists('eottae_on_promo_write_after')) {
    function eottae_on_promo_write_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        global $member;

        if (empty($board['bo_table']) || !eottae_is_community_board($board['bo_table'])) {
            return;
        }
        if (!function_exists('eottae_promo_check_post_count')) {
            return;
        }

        $mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
        if ($mb_id === '') {
            return;
        }

        eottae_promo_check_post_count($mb_id);
    }
}
add_event('write_update_after', 'eottae_on_promo_write_after', 25, 5);

if (!function_exists('eottae_on_promo_board_view')) {
    function eottae_on_promo_board_view($board, $write, $wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !is_array($write) || empty($write['wr_id'])) {
            return;
        }
        if (empty($board['bo_table']) || !eottae_is_community_board($board['bo_table'])) {
            return;
        }
        if (!function_exists('eottae_promo_check_post_views')) {
            return;
        }

        eottae_promo_check_post_views($board, $write, $wr_id);
    }
}
add_event('board_head_before', 'eottae_on_promo_board_view', 20, 3);

if (function_exists('eottae_translation_on_write_update_after')) {
    add_event('write_update_after', 'eottae_translation_on_write_update_after', 40, 5);
}
if (function_exists('eottae_lang_on_write_update_after')) {
    add_event('write_update_after', 'eottae_lang_on_write_update_after', 41, 5);
}
if (function_exists('eottae_translation_on_write_update_prewarm_after')) {
    add_event('write_update_after', 'eottae_translation_on_write_update_prewarm_after', 42, 5);
}
if (function_exists('eottae_translation_on_traffic_tick_common_header')) {
    add_event('common_header', 'eottae_translation_on_traffic_tick_common_header', 98, 0);
}
if (function_exists('eottae_lang_get_sql_search_filter')) {
    add_replace('get_sql_search', 'eottae_lang_get_sql_search_filter', 20, 5);
}
if (function_exists('eottae_lang_on_board_head_before')) {
    add_event('board_head_before', 'eottae_lang_on_board_head_before', 2, 3);
}

if (eottae_should_load_assets()) {
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/custom.css">', 10);
    if (function_exists('g5site_cfg')) {
        $eottae_brand_css = '';
        $eottae_primary = g5site_cfg('primary_color', '');
        $eottae_secondary = g5site_cfg('secondary_color', '');
        if ($eottae_primary !== '' && preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $eottae_primary)) {
            $eottae_brand_css .= '--color-primary:'.$eottae_primary.';';
        }
        if ($eottae_secondary !== '' && preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $eottae_secondary)) {
            $eottae_brand_css .= '--color-secondary:'.$eottae_secondary.';--color-muted:'.$eottae_secondary.';';
        }
        if ($eottae_brand_css !== '') {
            add_stylesheet('<style>:root{'.$eottae_brand_css.'}</style>', 11);
        }
    }
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae.css">', 20);
    $eottae_list_thumb_css = G5_PATH.'/css/eottae-list-thumb.css';
    if (is_file($eottae_list_thumb_css)) {
        add_stylesheet(
            '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-list-thumb.css?ver='.(int) filemtime($eottae_list_thumb_css).'">',
            21
        );
    }
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth.css">', 22);
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth-social.css">', 23);
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-kakao-chat.css">', 23);
    $eottae_ai_cfg = function_exists('eottae_ai_generate_bootstrap_config')
        ? eottae_ai_generate_bootstrap_config()
        : array('enabled' => false, 'api_key' => '');
    $eottae_js_version = defined('G5_PATH') && is_file(G5_PATH.'/js/eottae.js')
        ? (string) @filemtime(G5_PATH.'/js/eottae.js')
        : (defined('G5_JS_VER') ? G5_JS_VER : '');
    if (!function_exists('eottae_message_token') && is_file(G5_LIB_PATH.'/eottae-message.lib.php')) {
        include_once G5_LIB_PATH.'/eottae-message.lib.php';
    }
    add_javascript(
        '<script>window.__EOTTae__='.json_encode(array(
            'url' => G5_URL,
            'procBase' => G5_URL.'/proc',
            'aiEnabled' => !empty($eottae_ai_cfg['enabled']) && !empty($eottae_ai_cfg['api_key']),
            'jsVersion' => $eottae_js_version,
            'isMember' => !empty($is_member),
            'messageUrl' => function_exists('eottae_message_url') ? eottae_message_url() : G5_URL.'/page/eottae-messages.php',
            'messageProcUrl' => function_exists('eottae_message_proc_url') ? eottae_message_proc_url() : G5_URL.'/proc/eottae-message.php',
            'messageToken' => (!empty($is_member) && function_exists('eottae_message_token')) ? eottae_message_token() : '',
            'loginUrl' => function_exists('eottae_login_url') ? eottae_login_url() : G5_BBS_URL.'/login.php',
        ), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).';</script>',
        18
    );
    add_javascript(
        '<script>window.__EOTTaeI18N__='.json_encode(array(
            'defaultLanguage' => 'ko',
            'storageKey' => 'cebuatteLanguage',
            'supportedLanguages' => array('ko', 'en', 'ja', 'zh'),
            'localesBaseUrl' => G5_URL.'/locales',
            'seoEnabled' => function_exists('eottae_lang_seo_enabled') ? eottae_lang_seo_enabled() : false,
            'seoAutoRouteEnabled' => function_exists('eottae_lang_seo_auto_route_enabled') ? eottae_lang_seo_auto_route_enabled() : true,
            'seoDefaultLanguage' => 'ko',
            'seoPrefixedLanguages' => function_exists('eottae_lang_seo_config')
                ? (array) (eottae_lang_seo_config()['prefixes'] ?? array('en', 'ja', 'zh'))
                : array('en', 'ja', 'zh'),
            'memberPreferredLanguage' => (!empty($is_member) && function_exists('eottae_member_preferred_language_get'))
                ? eottae_member_preferred_language_get($member)
                : '',
            'memberLanguageSaveUrl' => G5_URL.'/proc/eottae-member-language.php',
            'isMember' => !empty($is_member),
        ), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).';</script>',
        18
    );
    $eottae_i18n_js_version = defined('G5_PATH') && is_file(G5_PATH.'/js/eottae-i18n.js')
        ? (string) @filemtime(G5_PATH.'/js/eottae-i18n.js')
        : (defined('G5_JS_VER') ? G5_JS_VER : '');
    add_javascript('<script src="'.G5_JS_URL.'/eottae-i18n.js'.($eottae_i18n_js_version !== '' ? '?v='.$eottae_i18n_js_version : '').'" defer></script>', 19);
    if (function_exists('eottae_list_translation_enqueue_assets')) {
        eottae_list_translation_enqueue_assets();
    }
    add_javascript('<script src="'.G5_JS_URL.'/eottae.js'.($eottae_js_version !== '' ? '?v='.$eottae_js_version : '').'" defer></script>', 20);
    if (!defined('_INDEX_') && function_exists('eottae_builder_inject_home_header_actions_script')) {
        add_javascript(eottae_builder_inject_home_header_actions_script(), 21);
    }
    if (defined('G5_IS_MOBILE') && G5_IS_MOBILE) {
        add_javascript('<script src="'.G5_JS_URL.'/custom.js"></script>', 22);
    }
    if (function_exists('eottae_coupon_ensure_ready')) {
        eottae_coupon_ensure_ready();
    }
    if (!isset($g5['body_script'])) {
        $g5['body_script'] = '';
    }
    if (strpos($g5['body_script'], 'eottae-page') === false) {
        $g5['body_script'] .= ' class="eottae-page"';
    }
    if (function_exists('eottae_maybe_append_auth_body_class')) {
        eottae_maybe_append_auth_body_class();
    }

    if (defined('G5_IS_MOBILE') && G5_IS_MOBILE && function_exists('eottae_filter_mobile_duplicate_head_assets')) {
        add_replace('html_process_css_files', function ($links) {
            return eottae_filter_mobile_duplicate_head_assets($links, array(
                'font-awesome.min.css',
                '/custom.css',
                '/eottae.css',
                '/eottae-kakao-chat.css',
                '/eottae-talkroom-ui.css',
                ':root{',
            ));
        }, 99, 1);
    }
}

if (!function_exists('eottae_talkroom_append_body_class')) {
    function eottae_talkroom_append_body_class($class)
    {
        global $g5;

        $class = trim((string) $class);
        if ($class === '') {
            return;
        }

        if (!isset($g5['body_script'])) {
            $g5['body_script'] = '';
        }

        if (preg_match('/class="([^"]*)"/', $g5['body_script'], $matches)) {
            if (strpos($matches[1], $class) !== false) {
                return;
            }
            $g5['body_script'] = preg_replace(
                '/class="([^"]*)"/',
                'class="'.trim($matches[1].' '.$class).'"',
                $g5['body_script'],
                1
            );

            return;
        }

        $g5['body_script'] .= ' class="'.$class.'"';
    }
}

if (!function_exists('eottae_maybe_append_auth_body_class')) {
    function eottae_maybe_append_auth_body_class()
    {
        if (defined('G5_IS_ADMIN') && G5_IS_ADMIN) {
            return;
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['SCRIPT_NAME'] ?? '');
        $auth_scripts = array(
            'login.php',
            'register.php',
            'register_form.php',
            'register_result.php',
            'password_lost.php',
            'password_reset.php',
            'member_confirm.php',
        );

        if (!in_array($script, $auth_scripts, true)) {
            return;
        }

        if (function_exists('eottae_talkroom_append_body_class')) {
            eottae_talkroom_append_body_class('eottae-auth-page');
        }
    }
}

if (!function_exists('eottae_talkroom_should_load_ui')) {
    function eottae_talkroom_should_load_ui()
    {
        global $bo_table, $board;

        if (function_exists('eottae_talkroom_is_talkroom_board')) {
            if (!empty($bo_table) && eottae_talkroom_is_talkroom_board($bo_table)) {
                return true;
            }
            if (is_array($board) && !empty($board['bo_table']) && eottae_talkroom_is_talkroom_board($board['bo_table'])) {
                return true;
            }
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
        $talk_scripts = array(
            'talk.php',
            'eottae-talk.php',
            'eottae-talk-create.php',
            'eottae-talk-applies.php',
            'eottae-talk-room.php',
            'eottae-talk-manage.php',
            'eottae-talk-my.php',
            'eottae-mypage-talk.php',
            'eottae-talk-reports.php',
            'eottae-admin-talk-rooms.php',
            'eottae-admin-talk-detail.php',
            'eottae-admin-talk-kicked.php',
            'eottae-admin-talk-reports.php',
            'eottae-admin-talk-ai.php',
            'eottae-admin-talk-ai-logs.php',
            'eottae-admin-public-ai.php',
            'eottae-admin-public-ai-candidates.php',
            'eottae-admin-public-ai-logs.php',
        );
        if (in_array($script, $talk_scripts, true)) {
            return true;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        return (bool) preg_match('#/(?:talk)(?:[/?]|$)|/mypage/talk(?:\.php)?(?:[/?]|$)|/page/eottae-(?:talk|admin-talk|mypage-talk)-#', $uri);
    }
}

if (!function_exists('eottae_talkroom_load_ui_assets')) {
    function eottae_talkroom_load_ui_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        if (!function_exists('eottae_should_load_assets') || !eottae_should_load_assets()) {
            return;
        }
        if (!function_exists('eottae_talkroom_should_load_ui') || !eottae_talkroom_should_load_ui()) {
            return;
        }

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-talkroom-ui.css">', 23);
        eottae_talkroom_append_body_class('talkroom-ui');

        if (function_exists('eottae_talkroom_enqueue_card_delete_assets')) {
            eottae_talkroom_enqueue_card_delete_assets();
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['SCRIPT_NAME'] ?? '');
        if ($script === 'eottae-talk-room.php') {
            eottae_talkroom_append_body_class('talk-room-chat-active');
        }

        $loaded = true;
    }
}

eottae_talkroom_load_ui_assets();

if (!function_exists('eottae_calendar_should_load_ui')) {
    function eottae_calendar_should_load_ui()
    {
        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
        $calendar_scripts = array(
            'index.php',
            'eottae-calendar.php',
            'eottae-calendar-create.php',
            'eottae-calendar-edit.php',
            'eottae-calendar-event.php',
            'eottae-admin-calendar-reports.php',
        );
        if (in_array($script, $calendar_scripts, true)) {
            return true;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        return (bool) preg_match('#/(?:calendar)(?:[/?]|$)|/page/eottae-calendar#', $uri);
    }
}

if (!function_exists('eottae_calendar_is_admin_shell_page')) {
    function eottae_calendar_is_admin_shell_page()
    {
        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '');

        return $script === 'eottae-admin-calendar-reports.php';
    }
}

if (!function_exists('eottae_calendar_load_ui_assets')) {
    function eottae_calendar_load_ui_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        if (!function_exists('eottae_should_load_assets') || !eottae_should_load_assets()) {
            return;
        }
        if (!function_exists('eottae_calendar_should_load_ui') || !eottae_calendar_should_load_ui()) {
            return;
        }

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-calendar.css">', 21);
        add_javascript('<script src="'.G5_JS_URL.'/eottae-calendar.js" defer></script>', 21);
        eottae_talkroom_append_body_class('calendar-ui');
        $loaded = true;
    }
}

eottae_calendar_load_ui_assets();

if (!function_exists('eottae_challenge_should_load_ui')) {
    function eottae_challenge_should_load_ui()
    {
        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
        $challenge_pages = array(
            'eottae-challenge.php',
            'eottae-challenge-view.php',
            'eottae-challenge-write.php',
            'eottae-challenge-entry.php',
            'eottae-mypage-challenges.php',
            'eottae-admin-challenges.php',
            'index.php',
        );
        if (in_array($script, $challenge_pages, true)) {
            return true;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        return (bool) preg_match('#/(?:challenge|mypage/challenges)(?:[/?]|$)|/page/eottae-challenge|/page/eottae-admin-challenges|/page/eottae-mypage-challenges#', $uri);
    }
}

if (!function_exists('eottae_challenge_load_ui_assets')) {
    function eottae_challenge_load_ui_assets()
    {
        if (!function_exists('eottae_challenge_should_load_ui') || !eottae_challenge_should_load_ui()) {
            return;
        }

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-challenge.css">', 24);
    }
}

eottae_challenge_load_ui_assets();

if (!function_exists('eottae_golf_join_should_load_ui')) {
    function eottae_golf_join_should_load_ui()
    {
        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
        $pages = array(
            'eottae-golf-join.php',
            'eottae-golf-join-detail.php',
            'eottae-golf-join-create.php',
            'eottae-golf-join-manage.php',
            'eottae-golf-join-chat.php',
            'index.php',
            'view.php',
        );
        if (in_array($script, $pages, true)) {
            return true;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        return (bool) preg_match('#/golf-join(?:[/?]|/(?:create|[0-9]+|chat))|/page/eottae-golf-join#', $uri);
    }
}

if (!function_exists('eottae_golf_join_load_ui_assets')) {
    function eottae_golf_join_load_ui_assets()
    {
        if (!function_exists('eottae_golf_join_should_load_ui') || !eottae_golf_join_should_load_ui()) {
            return;
        }

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-golf-join.css">', 24);
        if (function_exists('eottae_talkroom_append_body_class')) {
            eottae_talkroom_append_body_class('golf-join-ui');
        }
    }
}

eottae_golf_join_load_ui_assets();

if (!function_exists('eottae_talkroom_admin_shell_scripts')) {
    function eottae_talkroom_admin_shell_scripts()
    {
        return array(
            'eottae-admin-talk-rooms.php',
            'eottae-admin-talk-detail.php',
            'eottae-admin-talk-kicked.php',
            'eottae-admin-talk-reports.php',
            'eottae-admin-talk-ai.php',
            'eottae-admin-talk-ai-logs.php',
            'eottae-admin-plaza-posts.php',
            'eottae-admin-plaza-reports.php',
            'eottae-admin-plaza-ai.php',
            'eottae-admin-review-deletes.php',
            'eottae-admin-promo-coupons.php',
            'eottae-admin-challenges.php',
            'eottae-admin-member-growth.php',
            'eottae-admin-calendar-reports.php',
            'eottae-admin-public-ai.php',
            'eottae-admin-public-ai-candidates.php',
            'eottae-admin-public-ai-logs.php',
            'eottae-admin-public-ai-news.php',
            'eottae-admin-public-ai-weather.php',
            'eottae-admin-golf-join.php',
        );
    }
}

if (!function_exists('eottae_talkroom_is_admin_shell_request')) {
    /**
     * 세부톡·플라자 등 관리자 전용 셸 페이지 URL/스크립트 판별 (상수 정의 전에도 사용)
     */
    function eottae_talkroom_is_admin_shell_request()
    {
        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
        if (function_exists('eottae_talkroom_admin_shell_scripts')
            && in_array($script, eottae_talkroom_admin_shell_scripts(), true)) {
            return true;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        return (bool) preg_match('#/page/eottae-admin-(?:talk|plaza|promo|review|challenge|member-growth|golf|public-ai)#', $uri);
    }
}

if (!function_exists('eottae_talkroom_is_admin_shell_page')) {
    function eottae_talkroom_is_admin_shell_page()
    {
        if (defined('EOTTAE_TALK_ADMIN_SHELL')) {
            return (bool) EOTTAE_TALK_ADMIN_SHELL;
        }

        return function_exists('eottae_talkroom_is_admin_shell_request')
            && eottae_talkroom_is_admin_shell_request();
    }
}

if (!function_exists('eottae_talkroom_admin_shell_inline_css')) {
    function eottae_talkroom_admin_shell_inline_css()
    {
        if (!function_exists('eottae_talkroom_admin_shell_css_rules')) {
            include_once G5_PATH.'/components/eottae/talk-admin-layout.php';
        }

        return '<style id="eottae-talk-admin-shell-css">'.eottae_talkroom_admin_shell_css_rules().'</style>';
    }
}

if (!function_exists('eottae_talkroom_register_admin_shell_assets')) {
    function eottae_talkroom_register_admin_shell_assets()
    {
        if (!defined('EOTTAE_TALK_ADMIN_SHELL')) {
            define('EOTTAE_TALK_ADMIN_SHELL', true);
        }

        add_stylesheet(eottae_talkroom_admin_shell_inline_css(), -5);

        if (!G5_IS_MOBILE) {
            add_stylesheet('<meta name="viewport" content="width=device-width,initial-scale=1">', -6);
        }

        foreach (array('eottae-page', 'talkroom-ui', 'talk-admin-shell') as $class) {
            if (function_exists('eottae_talkroom_append_body_class')) {
                eottae_talkroom_append_body_class($class);
            }
        }

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae.css">', 19);
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-talkroom-ui.css">', 21);
    }
}

if (!function_exists('eottae_talkroom_load_admin_shell_assets')) {
    function eottae_talkroom_load_admin_shell_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        if (!eottae_talkroom_is_admin_shell_page()) {
            return;
        }

        $loaded = true;
        eottae_talkroom_register_admin_shell_assets();
    }
}

eottae_talkroom_load_admin_shell_assets();

if (!function_exists('eottae_talkroom_on_pre_head_ui')) {
    function eottae_talkroom_on_pre_head_ui()
    {
        eottae_talkroom_load_ui_assets();
    }
}
add_event('pre_head', 'eottae_talkroom_on_pre_head_ui', 5);

if (!function_exists('eottae_talkroom_on_pre_head_admin_shell')) {
    function eottae_talkroom_on_pre_head_admin_shell()
    {
        eottae_talkroom_load_admin_shell_assets();
    }
}
add_event('pre_head', 'eottae_talkroom_on_pre_head_admin_shell', 6);

if (!function_exists('eottae_talkroom_on_board_head_ui')) {
    function eottae_talkroom_on_board_head_ui($board, $write, $wr_id)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_talkroom_is_talkroom_board') || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        eottae_talkroom_load_ui_assets();
    }
}
add_event('board_head_before', 'eottae_talkroom_on_board_head_ui', 8, 3);

if (!function_exists('eottae_community_on_board_head')) {
    function eottae_community_on_board_head($board, $write, $wr_id)
    {
        if (empty($board['bo_table'])) {
            return;
        }

        if (function_exists('eottae_community_hub_apply_runtime')) {
            eottae_community_hub_apply_runtime($board['bo_table']);
        }

        if (empty($board['bo_skin']) || (string) $board['bo_skin'] !== 'eottae-community') {
            return;
        }

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/g5b-board.css">', 4);

        $community_board_css = G5_PATH.'/css/eottae-community-board.css';
        if (is_file($community_board_css)) {
            add_stylesheet(
                '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-community-board.css?ver='.(int) filemtime($community_board_css).'">',
                99
            );
        }
    }
}
add_event('board_head_before', 'eottae_community_on_board_head', 5, 3);

if (!function_exists('eottae_community_hub_on_board_head')) {
    function eottae_community_hub_on_board_head($board, $write, $wr_id)
    {
        if (!function_exists('eottae_community_hub_redirect_legacy_list')) {
            return;
        }

        eottae_community_hub_redirect_legacy_list($board, $write, $wr_id);
    }
}
add_event('board_head_before', 'eottae_community_hub_on_board_head', 4, 3);

if (isset($board) && is_array($board) && isset($board['bo_skin'])) {
    $skin = (string) $board['bo_skin'];
    if (strpos($skin, 'eottae-') === 0 && $skin !== 'eottae-community') {
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/g5b-board.css">', 4);
    }
}

if (!function_exists('eottae_is_media_board_table')) {
    function eottae_is_media_board_table($bo_table)
    {
        $bo_table = (string) $bo_table;
        if ($bo_table === '') {
            return false;
        }
        if (defined('EOTTae_GALLERY_TABLE') && $bo_table === EOTTae_GALLERY_TABLE) {
            return true;
        }
        if (defined('EOTTae_YOUTUBE_TABLE') && $bo_table === EOTTae_YOUTUBE_TABLE) {
            return true;
        }

        return in_array($bo_table, array('gallery', 'youtube'), true);
    }
}

if (!function_exists('eottae_is_media_board')) {
    function eottae_is_media_board($board)
    {
        return is_array($board) && !empty($board['bo_table']) && eottae_is_media_board_table($board['bo_table']);
    }
}

if (!function_exists('eottae_ensure_media_board_skins')) {
    /** gallery/youtube 게시판이 구형 스킨이면 모던 목록 스킨으로 자동 교체 */
    function eottae_ensure_media_board_skins()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        global $g5, $board;

        $targets = array(
            'gallery' => array('skin' => 'gallery-grid', 'mobile' => 'gallery-grid'),
            'youtube' => array('skin' => 'youtube-list', 'mobile' => 'youtube-list'),
        );

        foreach ($targets as $bo_table => $skins) {
            $row = sql_fetch(" select bo_skin, bo_mobile_skin from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");
            if (!$row) {
                continue;
            }
            if ($row['bo_skin'] === $skins['skin'] && $row['bo_mobile_skin'] === $skins['mobile']) {
                continue;
            }

            sql_query(" update {$g5['board_table']} set
                bo_skin = '".sql_escape_string($skins['skin'])."',
                bo_mobile_skin = '".sql_escape_string($skins['mobile'])."'
                where bo_table = '".sql_escape_string($bo_table)."' ");

            if (is_array($board) && isset($board['bo_table']) && $board['bo_table'] === $bo_table) {
                $board['bo_skin'] = $skins['skin'];
                $board['bo_mobile_skin'] = $skins['mobile'];
            }
        }
    }
}

if (!function_exists('eottae_load_media_board_assets')) {
    function eottae_load_media_board_assets()
    {
        global $board;

        if (!eottae_is_media_board($board)) {
            return;
        }

        eottae_ensure_media_board_skins();

        if (function_exists('eottae_gallery_board_ensure_settings')) {
            eottae_gallery_board_ensure_settings();
        }

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/g5b-board.css">', 4);
        $media_boards_css = G5_PATH.'/css/eottae-media-boards.css';
        add_stylesheet(
            '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-media-boards.css?ver='.(is_file($media_boards_css) ? (int) filemtime($media_boards_css) : 4).'">',
            30
        );
    }
}
add_event('board_head_before', 'eottae_load_media_board_assets', 5);

if (!function_exists('eottae_load_shop_board_map_assets')) {
    function eottae_load_shop_board_map_assets()
    {
        global $board;

        if (empty($board['bo_table']) || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        $wr_id = isset($_GET['wr_id']) ? (int) $_GET['wr_id'] : 0;
        if ($wr_id > 0) {
            return;
        }

        $w = isset($_GET['w']) ? trim((string) $_GET['w']) : '';
        if ($w !== '') {
            return;
        }

        if (function_exists('eottae_enqueue_google_maps')) {
            eottae_enqueue_google_maps();
        }
    }
}
add_event('board_head_before', 'eottae_load_shop_board_map_assets', 6);

if (!function_exists('eottae_load_estate_board_map_assets')) {
    function eottae_load_estate_board_map_assets()
    {
        global $board;

        if (empty($board['bo_table']) || !function_exists('eottae_is_estate_board') || !eottae_is_estate_board($board['bo_table'])) {
            return;
        }

        $wr_id = isset($_GET['wr_id']) ? (int) $_GET['wr_id'] : 0;
        if ($wr_id < 1) {
            return;
        }

        if (!function_exists('eottae_enqueue_google_maps')) {
            return;
        }

        global $g5;
        $write_table = $g5['write_prefix'].$board['bo_table'];
        $write = sql_fetch(" SELECT wr_id, wr_4, wr_5, wr_6, wr_3 FROM `{$write_table}` WHERE wr_id = '{$wr_id}' AND wr_is_comment = 0 LIMIT 1 ");
        if (!$write || !function_exists('eottae_estate_has_map_location') || !eottae_estate_has_map_location($write)) {
            return;
        }

        eottae_enqueue_google_maps();
    }
}
add_event('board_head_before', 'eottae_load_estate_board_map_assets', 7);

if (function_exists('eottae_shop_apply_segment_board_context')) {
    eottae_shop_apply_segment_board_context();
}

if (!function_exists('eottae_talkroom_is_talkroom_board')) {
    function eottae_talkroom_is_talkroom_board($bo_table)
    {
        return function_exists('eottae_talkroom_board_table')
            && (string) $bo_table === eottae_talkroom_board_table();
    }
}

if (!function_exists('eottae_talkroom_resolve_write_room_id')) {
    function eottae_talkroom_resolve_write_room_id($board, $wr_id, $w)
    {
        global $write;

        if (!eottae_talkroom_is_talkroom_board($board['bo_table'] ?? '')) {
            return 0;
        }

        if (($w === 'u' || $w === 'r') && is_array($write) && !empty($write['wr_1'])) {
            return (int) $write['wr_1'];
        }

        if (isset($_REQUEST['wr_1'])) {
            return (int) $_REQUEST['wr_1'];
        }

        return 0;
    }
}

if (!function_exists('eottae_talkroom_on_bbs_write')) {
    function eottae_talkroom_on_bbs_write($board, $wr_id, $w)
    {
        global $is_member, $member, $is_admin, $write;

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        if (!$is_member || empty($member['mb_id'])) {
            alert('로그인 후 글을 작성할 수 있습니다.', eottae_login_url(G5_URL.$_SERVER['REQUEST_URI']));
        }

        $is_super = ($is_admin === 'super');
        $room_id = eottae_talkroom_resolve_write_room_id($board, $wr_id, $w);
        if ($room_id < 1) {
            alert('톡방 정보가 없습니다. 톡방 상세 페이지에서 글쓰기를 이용해 주세요.', eottae_talkroom_list_url());
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            alert('운영 중인 톡방이 아닙니다.', eottae_talkroom_list_url());
        }

        if ($w === 'u') {
            if (!is_array($write) || empty($write['wr_id'])) {
                alert('글이 존재하지 않습니다.');
            }
            if (eottae_talkroom_is_post_deleted($write) && !$is_super) {
                alert('삭제된 글은 수정할 수 없습니다.');
            }
            if (!eottae_talkroom_user_can_edit_write($write, $board, $member['mb_id'], $is_super)) {
                alert('글을 수정할 권한이 없습니다.', eottae_talkroom_enter_url($room_id));
            }

            return;
        }

        if ($w === 'r') {
            alert('톡방 게시판에서는 답글을 사용할 수 없습니다.', eottae_talkroom_enter_url($room_id));
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $member['mb_id']);
        if (!eottae_talkroom_can_write_posts($room, $member_row, $member['mb_id'])) {
            alert('톡방 참여자만 글을 작성할 수 있습니다.', eottae_talkroom_enter_url($room_id));
        }
    }
}
if (!function_exists('eottae_on_bbs_write_force_dhtml_editor')) {
    function eottae_on_bbs_write_force_dhtml_editor($board, $wr_id, $w)
    {
        if (!empty($board['bo_table'])
            && function_exists('eottae_is_job_board')
            && eottae_is_job_board($board['bo_table'])) {
            return;
        }

        if (function_exists('eottae_board_force_dhtml_editor')) {
            eottae_board_force_dhtml_editor();
        }
    }
}
add_event('bbs_write', 'eottae_on_bbs_write_force_dhtml_editor', 5, 3);

add_event('bbs_write', 'eottae_talkroom_on_bbs_write', 10, 3);

if (!function_exists('eottae_talkroom_on_write_update_before')) {
    function eottae_talkroom_on_write_update_before($board, $wr_id, $w, $qstr)
    {
        eottae_talkroom_assert_write_update_access($board, $wr_id, $w);

        if ($w !== 'u' || empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        global $write;
        $target = is_array($write) && !empty($write['wr_id']) ? $write : null;
        if (!$target && $wr_id > 0) {
            $write_table = eottae_talkroom_write_table();
            $target = sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '".(int) $wr_id."' ", false);
        }

        if (!$target || empty($target['wr_id'])) {
            return;
        }

        $_POST['wr_1'] = (string) ($target['wr_1'] ?? '');
        for ($i = 2; $i <= 5; $i++) {
            $_POST['wr_'.$i] = (string) ($target['wr_'.$i] ?? '');
        }
    }
}
add_event('write_update_before', 'eottae_talkroom_on_write_update_before', 20, 4);

if (!function_exists('eottae_talkroom_on_comment_before')) {
    function eottae_talkroom_on_comment_before($board, $wr, $wr_id, $w)
    {
        eottae_talkroom_assert_comment_update_access($board, $wr, $wr_id, $w);
    }
}
add_event('comment_update_before', 'eottae_talkroom_on_comment_before', 10, 4);

if (!function_exists('eottae_talkroom_on_comment_after')) {
    function eottae_talkroom_on_comment_after($board, $wr_id, $w, $qstr, $redirect_url, $comment_id, $reply_array)
    {
        if ($w !== 'c' || (int) $comment_id < 1) {
            return;
        }

        if (empty($board['bo_table']) || !function_exists('eottae_talkroom_is_talkroom_board')
            || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        global $member, $is_member;
        $commenter_mb_id = (!empty($is_member) && !empty($member['mb_id'])) ? $member['mb_id'] : '';

        if (!function_exists('eottae_talkroom_notify_comment_on_post')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-notify.lib.php';
        }

        eottae_talkroom_notify_comment_on_post($board, (int) $wr_id, (int) $comment_id, $commenter_mb_id);
    }
}
add_event('comment_update_after', 'eottae_talkroom_on_comment_after', 20, 7);

if (!function_exists('eottae_talkroom_ai_on_write_update_after')) {
    function eottae_talkroom_ai_on_write_update_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if ($w !== '' || (int) $wr_id < 1) {
            return;
        }

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('eottae_talkroom_ai_schedule_reaction_for_post')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-reaction.lib.php';
        }

        eottae_talkroom_ai_schedule_reaction_for_post((int) $wr_id);
    }
}
add_event('write_update_after', 'eottae_talkroom_ai_on_write_update_after', 30, 5);

if (!function_exists('eottae_talkroom_on_board_head')) {
    function eottae_talkroom_on_board_head($board, $write, $wr_id)
    {
        eottae_talkroom_guard_board_list_access($board, $wr_id);
        eottae_talkroom_guard_board_view($board, $write, $wr_id);
    }
}
add_event('board_head_before', 'eottae_talkroom_on_board_head', 12, 3);

if (!function_exists('eottae_plaza_guard_board_view')) {
    function eottae_plaza_guard_board_view($board, $write, $wr_id)
    {
        global $is_admin;

        if (empty($board['bo_table']) || !eottae_plaza_is_plaza_board($board['bo_table'])) {
            return;
        }

        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !is_array($write) || empty($write['wr_id']) || !empty($write['wr_is_comment'])) {
            return;
        }

        if (!eottae_plaza_is_post_visible($write, $is_admin === 'super')) {
            alert('삭제되었거나 볼 수 없는 글입니다.', eottae_plaza_list_url());
        }
    }
}

if (!function_exists('eottae_plaza_on_board_head')) {
    function eottae_plaza_on_board_head($board, $write, $wr_id)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_plaza_is_plaza_board')) {
            return;
        }
        if (!eottae_plaza_is_plaza_board($board['bo_table'])) {
            return;
        }

        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        include_once G5_LIB_PATH.'/eottae-plaza-likes.lib.php';
        include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';
        include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';
        eottae_plaza_likes_ensure_schema();
        eottae_plaza_reports_ensure_schema();
        eottae_plaza_ai_ensure_schema();
        eottae_plaza_load_assets();
        eottae_plaza_guard_board_view($board, $write, $wr_id);
    }
}
add_event('board_head_before', 'eottae_plaza_on_board_head', 7, 3);

if (!function_exists('eottae_plaza_on_write_update_before')) {
    function eottae_plaza_on_write_update_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_plaza_is_plaza_board')) {
            return;
        }
        if (!eottae_plaza_is_plaza_board($board['bo_table'])) {
            return;
        }

        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';

        if ($w === 'u' && (int) $wr_id > 0) {
            include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';
            global $is_admin, $g5;
            $write_table = $g5['write_prefix'].eottae_plaza_board_table();
            $existing = sql_fetch("
                SELECT *
                FROM `{$write_table}`
                WHERE wr_id = '".(int) $wr_id."'
                  AND wr_is_comment = 0
                LIMIT 1
            ", false);
            if (is_array($existing) && function_exists('eottae_plaza_ai_is_ai_write_row')
                && eottae_plaza_ai_is_ai_write_row($existing) && $is_admin !== 'super') {
                alert('AI 질문 글은 수정할 수 없습니다.', eottae_plaza_list_url());
            }
        }

        if ($w !== 'u') {
            global $is_member;
            if (empty($is_member)) {
                alert('로그인 후 이용해 주세요.', eottae_plaza_login_url());
            }
        }

        $result = eottae_plaza_validate_write_input();
        if (empty($result['ok'])) {
            alert($result['message']);
        }

        eottae_plaza_apply_write_defaults($w);

        global $is_admin;
        if ($is_admin !== 'super' && isset($_POST['wr_3'])) {
            $marker = trim((string) $_POST['wr_3']);
            if (strpos($marker, 'ai:') === 0) {
                unset($_POST['wr_3']);
            }
        }
        if ($is_admin !== 'super' && isset($_POST['ca_name']) && trim((string) $_POST['ca_name']) === 'AI질문') {
            alert('선택할 수 없는 글 유형입니다.');
        }
    }
}
add_event('write_update_before', 'eottae_plaza_on_write_update_before', 18, 4);

if (!function_exists('eottae_column_is_column_board')) {
    function eottae_column_is_column_board($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === (defined('EOTTae_COLUMN_TABLE') ? EOTTae_COLUMN_TABLE : 'column');
    }
}

if (!function_exists('eottae_column_on_board_head')) {
    function eottae_column_on_board_head($board, $write, $wr_id)
    {
        if (empty($board['bo_table']) || !eottae_column_is_column_board($board['bo_table'])) {
            return;
        }

        include_once G5_LIB_PATH.'/eottae-column.lib.php';
        eottae_column_ensure_schema();
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);

        global $bo_table;
        if (isset($_GET['wr_id']) && (int) $_GET['wr_id'] > 0) {
            goto_url(eottae_column_view_url((int) $_GET['wr_id']));
        }
        if ($bo_table === eottae_column_board_table() && empty($_GET['wr_id'])) {
            $script = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : '';
            if ($script === 'board.php') {
                goto_url(eottae_column_list_url());
            }
        }
    }
}
add_event('board_head_before', 'eottae_column_on_board_head', 6, 3);

if (!function_exists('eottae_column_on_comment_after')) {
    function eottae_column_on_comment_after($board, $wr_id, $w, $qstr, $redirect_url, $comment_id, $reply_array)
    {
        if ($w !== 'c' || (int) $wr_id < 1) {
            return;
        }
        if (empty($board['bo_table']) || !eottae_column_is_column_board($board['bo_table'])) {
            return;
        }

        include_once G5_LIB_PATH.'/eottae-column.lib.php';
        goto_url(eottae_column_view_url((int) $wr_id).'#sebu-comments-title');
        exit;
    }
}
add_event('comment_update_after', 'eottae_column_on_comment_after', 6, 7);

if (!function_exists('eottae_column_on_write_update_before')) {
    function eottae_column_on_write_update_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table']) || !eottae_column_is_column_board($board['bo_table'])) {
            return;
        }

        include_once G5_LIB_PATH.'/eottae-column.lib.php';
        global $is_member, $member, $is_admin;

        if ($is_admin !== 'super') {
            if (empty($is_member) || !eottae_column_can_write($member['mb_id'] ?? '', false)) {
                alert('승인된 칼럼니스트만 컬럼을 작성할 수 있습니다.', eottae_column_list_url());
            }
            goto_url(eottae_column_write_url($w === 'u' ? (int) $wr_id : 0));
        }
    }
}
add_event('write_update_before', 'eottae_column_on_write_update_before', 17, 4);

if (!function_exists('eottae_column_on_bbs_write')) {
    function eottae_column_on_bbs_write($board, $write, $wr_id)
    {
        if (empty($board['bo_table']) || !eottae_column_is_column_board($board['bo_table'])) {
            return;
        }

        include_once G5_LIB_PATH.'/eottae-column.lib.php';
        global $is_member, $member, $is_admin, $w;

        if ($is_admin !== 'super') {
            if (empty($is_member) || !eottae_column_can_write($member['mb_id'] ?? '', false)) {
                alert('승인된 칼럼니스트만 컬럼을 작성할 수 있습니다.', eottae_column_list_url());
            }
            goto_url(eottae_column_write_url($w === 'u' ? (int) $wr_id : 0));
        }
    }
}
add_event('bbs_write', 'eottae_column_on_bbs_write', 10, 3);

if (!function_exists('eottae_on_estate_write_before')) {
    function eottae_on_estate_write_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_estate_board') || !eottae_is_estate_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('sanitize_location_fields') && is_file(G5_LIB_PATH.'/eottae-location.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-location.lib.php';
        }

        $region = isset($_POST['wr_1']) ? trim(strip_tags((string) $_POST['wr_1'])) : '';
        if (function_exists('cut_str') && $region !== '') {
            $region = cut_str($region, 120, '');
        } elseif ($region !== '' && function_exists('mb_substr')) {
            $region = mb_substr($region, 0, 120, 'UTF-8');
        }
        $_POST['wr_1'] = $region;

        $status = isset($_POST['wr_2']) ? $_POST['wr_2'] : (isset($_POST['estate_deal_status']) ? $_POST['estate_deal_status'] : 'trading');
        $_POST['wr_2'] = eottae_estate_normalize_deal_status($status);

        if (!function_exists('eottae_estate_template_apply_to_post')) {
            include_once G5_LIB_PATH.'/eottae-estate-template.lib.php';
        }
        if (function_exists('eottae_estate_template_apply_to_post')) {
            eottae_estate_template_apply_to_post();
        }

        if (function_exists('sanitize_location_fields')) {
            $location = sanitize_location_fields($_POST, array(
                'auto_area'     => 'estate_location_auto_area',
                'location_text' => 'wr_4',
                'latitude'      => 'wr_5',
                'longitude'     => 'wr_6',
                'map_visible'   => 'wr_7',
            ));

            $_POST['wr_1'] = function_exists('eottae_location_area_label')
                ? eottae_location_area_label($location['auto_area'])
                : ($_POST['wr_1'] ?: $location['auto_area']);
            $_POST['wr_4'] = $location['location_text'];
            $_POST['wr_5'] = $location['latitude'];
            $_POST['wr_6'] = $location['longitude'];
            $_POST['wr_7'] = $location['map_visible'];
        }
    }
}
add_event('write_update_before', 'eottae_on_estate_write_before', 14, 4);

if (!function_exists('eottae_estate_on_write_update_after')) {
    function eottae_estate_on_write_update_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_estate_board') || !eottae_is_estate_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('eottae_estate_template_sync_wr3_from_row')) {
            include_once G5_LIB_PATH.'/eottae-estate-template.lib.php';
        }

        if (function_exists('eottae_estate_template_sync_wr3_from_row')) {
            eottae_estate_template_sync_wr3_from_row($board['bo_table'], (int) $wr_id);
        }
    }
}
add_event('write_update_after', 'eottae_estate_on_write_update_after', 13, 5);

if (!function_exists('eottae_event_on_write_update_after')) {
    function eottae_event_on_write_update_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_event_board') || !eottae_is_event_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('eottae_event_sync_fields_from_row')) {
            include_once G5_LIB_PATH.'/eottae-event.lib.php';
        }

        if (function_exists('eottae_event_sync_fields_from_row')) {
            eottae_event_sync_fields_from_row($board['bo_table'], (int) $wr_id);
        }
    }
}
add_event('write_update_after', 'eottae_event_on_write_update_after', 13, 5);

if (!function_exists('eottae_on_job_write_before')) {
    function eottae_on_job_write_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_job_board') || !eottae_is_job_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('sanitize_location_fields') && is_file(G5_LIB_PATH.'/eottae-location.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-location.lib.php';
        }

        $region = isset($_POST['wr_1']) ? trim(strip_tags((string) $_POST['wr_1'])) : '';
        if (function_exists('cut_str') && $region !== '') {
            $region = cut_str($region, 120, '');
        } elseif ($region !== '' && function_exists('mb_substr')) {
            $region = mb_substr($region, 0, 120, 'UTF-8');
        }
        $_POST['wr_1'] = $region;

        $status = isset($_POST['wr_2']) ? $_POST['wr_2'] : (isset($_POST['job_recruit_status']) ? $_POST['job_recruit_status'] : 'recruiting');
        $_POST['wr_2'] = eottae_job_normalize_recruit_status($status);

        if (function_exists('eottae_job_template_apply_to_post')) {
            eottae_job_template_apply_to_post();
        }

        if (function_exists('eottae_job_shop_apply_to_post')) {
            eottae_job_shop_apply_to_post();
        }

        if (function_exists('sanitize_location_fields')) {
            $location = sanitize_location_fields($_POST, array(
                'auto_area'     => 'job_location_auto_area',
                'location_text' => 'wr_4',
                'latitude'      => 'wr_5',
                'longitude'     => 'wr_6',
                'map_visible'   => 'wr_7',
            ));

            $_POST['wr_1'] = function_exists('eottae_location_area_label')
                ? eottae_location_area_label($location['auto_area'])
                : ($_POST['wr_1'] ?: $location['auto_area']);
            $_POST['wr_4'] = $location['location_text'];
            $_POST['wr_5'] = $location['latitude'];
            $_POST['wr_6'] = $location['longitude'];
            $_POST['wr_7'] = $location['map_visible'];
        }
    }
}
add_event('write_update_before', 'eottae_on_job_write_before', 14, 4);

if (!function_exists('eottae_on_event_write_before')) {
    function eottae_on_event_write_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_event_board') || !eottae_is_event_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('eottae_event_validate_write_post')) {
            include_once G5_LIB_PATH.'/eottae-event.lib.php';
        }

        if (function_exists('eottae_event_ensure_content_on_save')) {
            eottae_event_ensure_content_on_save();
        }

        $check = eottae_event_validate_write_post($_POST);
        if (empty($check['ok'])) {
            alert($check['message'] ?? '입력값을 확인해 주세요.');
        }

        $normalized = eottae_event_normalize_write_post($_POST, $w === 'u');
        eottae_event_apply_write_post($normalized);
    }
}
add_event('write_update_before', 'eottae_on_event_write_before', 14, 4);

if (!function_exists('eottae_on_review_write_before')) {
    function eottae_on_review_write_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_review_board') || !eottae_is_review_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('eottae_review_board_validate_write_post')) {
            include_once G5_LIB_PATH.'/eottae-review-board.lib.php';
        }

        global $member, $is_admin;

        $check = eottae_review_board_validate_write_post($_POST, $member ?? array(), $is_admin ?? '', (int) $wr_id, (string) $w);
        if (empty($check['ok'])) {
            alert($check['message'] ?? '입력값을 확인해 주세요.');
        }

        eottae_review_board_apply_write_post($check['normalized'] ?? array());
    }
}
add_event('write_update_before', 'eottae_on_review_write_before', 14, 4);

if (!function_exists('eottae_review_on_write_update_after')) {
    function eottae_review_on_write_update_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_review_board') || !eottae_is_review_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('eottae_review_board_after_write')) {
            include_once G5_LIB_PATH.'/eottae-review-board.lib.php';
        }

        $write_table = get_write_table_name($board['bo_table']);
        $row = get_write($write_table, (int) $wr_id, true);
        if (!is_array($row) || empty($row['wr_id'])) {
            return;
        }

        $normalized = eottae_review_board_normalize_write_post(array(
            'eottae_review_shop_wr_id'     => $row['wr_1'] ?? 0,
            'eottae_review_shop_bo_table'  => $row['wr_6'] ?? '',
            'eottae_review_shop_name'      => $row['wr_3'] ?? '',
            'wr_2'                         => $row['wr_2'] ?? 0,
        ), true);

        if (!empty($normalized['ok'])) {
            eottae_review_board_after_write((int) $wr_id, $normalized);
        }
    }
}
add_event('write_update_after', 'eottae_review_on_write_update_after', 13, 5);

if (!function_exists('eottae_on_report_write_before')) {
    function eottae_on_report_write_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_report_board') || !eottae_is_report_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('eottae_report_validate_write_post')) {
            include_once G5_LIB_PATH.'/eottae-report.lib.php';
        }

        global $is_admin;

        $check = eottae_report_validate_write_post($_POST);
        if (empty($check['ok'])) {
            alert($check['message'] ?? '입력값을 확인해 주세요.');
        }

        $existing = null;
        if ($w === 'u' && (int) $wr_id > 0) {
            $existing = eottae_report_get_existing_write($board['bo_table'], (int) $wr_id);
        }

        $is_board_admin = !empty($is_admin);
        $normalized = eottae_report_normalize_write_post(
            $_POST,
            $existing,
            $is_board_admin,
            $w === 'u'
        );
        eottae_report_apply_write_post($normalized);
    }
}
add_event('write_update_before', 'eottae_on_report_write_before', 14, 4);

if (!function_exists('eottae_on_market_write_before')) {
    function eottae_on_market_write_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_market_board') || !eottae_is_market_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('eottae_market_validate_write_post')) {
            include_once G5_LIB_PATH.'/eottae-market.lib.php';
        }

        $check = eottae_market_validate_write_post($_POST);
        if (empty($check['ok'])) {
            alert($check['message'] ?? '입력값을 확인해 주세요.');
        }

        eottae_market_apply_write_post(eottae_market_normalize_write_post($_POST));
    }
}
add_event('write_update_before', 'eottae_on_market_write_before', 14, 4);

if (!function_exists('eottae_report_on_board_head_before')) {
    function eottae_report_on_board_head_before($board, $write, $wr_id)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_report_board') || !eottae_is_report_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('eottae_report_prepare_list_context')) {
            include_once G5_LIB_PATH.'/eottae-report.lib.php';
        }

        eottae_report_prepare_list_context($board);

        $wr_id = (int) $wr_id;
        if ($wr_id > 0 && is_array($write) && !empty($write['wr_id'])) {
            eottae_report_assert_can_read($write);
        }
    }
}
add_event('board_head_before', 'eottae_report_on_board_head_before', 4, 3);

add_event('bbs_write', 'eottae_adroom_on_bbs_write', 11, 3);
add_event('write_update_before', 'eottae_adroom_on_write_update_before', 16, 4);
add_event('write_update_after', 'eottae_adroom_on_write_after', 26, 5);

if (!function_exists('eottae_adroom_on_board_head')) {
    function eottae_adroom_on_board_head($board, $write, $wr_id)
    {
        if (empty($board['bo_table']) || !eottae_adroom_is_board($board['bo_table'])) {
            return;
        }

        global $bo_table;
        $script = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : '';
        if ($bo_table === eottae_adroom_board_table() && empty($_GET['wr_id']) && $script === 'board.php') {
            goto_url(eottae_adroom_list_url());
        }
    }
}
add_event('board_head_before', 'eottae_adroom_on_board_head', 7, 3);

if (!function_exists('eottae_google_oauth_remove_from_servicelist')) {
    function eottae_google_oauth_remove_from_servicelist()
    {
        global $config;

        $config['cf_google_clientid'] = '';
        $config['cf_google_secret'] = '';

        $services = array_filter(array_map('trim', explode(',', (string) ($config['cf_social_servicelist'] ?? ''))));
        $services = array_values(array_filter($services, static function ($s) {
            return $s !== 'google';
        }));
        $config['cf_social_servicelist'] = implode(',', $services);
    }
}

if (!function_exists('eottae_google_oauth_credentials_valid')) {
    function eottae_google_oauth_credentials_valid($client_id, $client_secret)
    {
        $client_id = trim((string) $client_id);
        $client_secret = trim((string) $client_secret);

        if ($client_id === '' || $client_secret === '') {
            return false;
        }

        return strpos($client_id, '.apps.googleusercontent.com') !== false
            && strlen($client_id) >= 40;
    }
}

if (!function_exists('eottae_google_oauth_resolve_credentials')) {
    /**
     * Google OAuth 자격증명 — 파일(_site.config.local / data) 우선, 없으면 관리자 DB(cf_google_*).
     *
     * @return array{client_id: string, client_secret: string}
     */
    function eottae_google_oauth_resolve_credentials()
    {
        global $config;

        if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $client_id = '';
        $client_secret = '';

        if (function_exists('g5site_cfg')) {
            $client_id = trim((string) g5site_cfg('google_oauth_client_id', ''));
            $client_secret = trim((string) g5site_cfg('google_oauth_client_secret', ''));
        }

        if ($client_id === '' || $client_secret === '') {
            $client_id = trim((string) ($config['cf_google_clientid'] ?? ''));
            $client_secret = trim((string) ($config['cf_google_secret'] ?? ''));
        }

        return array(
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
        );
    }
}

if (!function_exists('eottae_google_oauth_configured')) {
    function eottae_google_oauth_configured()
    {
        $creds = eottae_google_oauth_resolve_credentials();

        return eottae_google_oauth_credentials_valid($creds['client_id'], $creds['client_secret']);
    }
}

if (!function_exists('eottae_google_oauth_runtime_diagnostics')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_google_oauth_runtime_diagnostics()
    {
        $data_dir = defined('G5_DATA_PATH') ? G5_DATA_PATH : G5_PATH.'/data';
        $local_file = $data_dir.'/eottae-google-oauth.local.php';
        $example_file = $data_dir.'/eottae-google-oauth.local.example.php';
        $creds = function_exists('eottae_google_oauth_resolve_credentials')
            ? eottae_google_oauth_resolve_credentials()
            : array('client_id' => '', 'client_secret' => '');
        $client_id = isset($creds['client_id']) ? trim((string) $creds['client_id']) : '';
        $client_secret = isset($creds['client_secret']) ? trim((string) $creds['client_secret']) : '';
        $example_has_keys = false;
        if (is_file($example_file) && is_readable($example_file)) {
            $eottae_oauth_override = null;
            include $example_file;
            if (isset($eottae_oauth_override['google_oauth_client_id'], $eottae_oauth_override['google_oauth_client_secret'])) {
                $example_has_keys = trim((string) $eottae_oauth_override['google_oauth_client_id']) !== ''
                    && trim((string) $eottae_oauth_override['google_oauth_client_secret']) !== '';
            }
        }

        return array(
            'local_file'        => $local_file,
            'local_exists'      => is_file($local_file),
            'local_readable'    => is_file($local_file) && is_readable($local_file),
            'example_exists'    => is_file($example_file),
            'example_has_keys'  => $example_has_keys,
            'configured'        => function_exists('eottae_google_oauth_configured') && eottae_google_oauth_configured(),
            'client_id_suffix'  => $client_id !== '' ? preg_replace('/^.*([a-z0-9]{8}\\.apps\\.googleusercontent\\.com)$/i', '$1', $client_id) : '',
            'has_secret'        => $client_secret !== '',
            'redirect_uri'      => function_exists('get_social_callbackurl') ? get_social_callbackurl('google') : '',
        );
    }
}

if (!function_exists('eottae_apply_google_oauth_config')) {
    /**
     * Google OAuth — 파일 우선, 없으면 관리자 소셜로그인 설정(DB) 사용.
     * Client ID가 .apps.googleusercontent.com 형식이 아니면 비활성화(invalid_client 방지).
     */
    function eottae_apply_google_oauth_config()
    {
        global $config;

        $creds = eottae_google_oauth_resolve_credentials();
        $client_id = $creds['client_id'];
        $client_secret = $creds['client_secret'];

        if (!eottae_google_oauth_credentials_valid($client_id, $client_secret)) {
            eottae_google_oauth_remove_from_servicelist();
            return;
        }

        $config['cf_social_login_use'] = 1;
        $config['cf_google_clientid'] = $client_id;
        $config['cf_google_secret'] = $client_secret;

        $services = array_filter(array_map('trim', explode(',', (string) ($config['cf_social_servicelist'] ?? ''))));
        if (!in_array('google', $services, true)) {
            $services[] = 'google';
            $config['cf_social_servicelist'] = implode(',', $services);
        }
    }
}
eottae_apply_google_oauth_config();

if (function_exists('eottae_apply_free_board_skin_runtime')) {
    eottae_apply_free_board_skin_runtime();
}

if (!defined('G5_IS_ADMIN') && is_file(G5_PATH.'/components/onoff-chatbot.php')) {
    if (!defined('ONOFF_CHATBOT_LIBRARY_ONLY')) {
        define('ONOFF_CHATBOT_LIBRARY_ONLY', true);
    }
    include_once G5_PATH.'/components/onoff-chatbot.php';
}
