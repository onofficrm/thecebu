<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_board_seo_life_tables')) {
    /**
     * 생활 게시판 (eottae-community 스킨) — SEO 자동 생성 대상
     *
     * @return array<int, string>
     */
    function eottae_board_seo_life_tables()
    {
        return array_values(array_unique(array_filter(array(
            defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community',
            defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people',
            defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job',
            defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate',
        ))));
    }
}

if (!function_exists('eottae_board_seo_is_target_board')) {
    function eottae_board_seo_is_target_board($board)
    {
        if (!is_array($board) || empty($board['bo_table'])) {
            return false;
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $board['bo_table']);
        if ($bo_table === '') {
            return false;
        }

        if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table)) {
            return false;
        }
        if (function_exists('eottae_talkroom_is_talkroom_board') && eottae_talkroom_is_talkroom_board($bo_table)) {
            return false;
        }
        if (function_exists('eottae_is_gallery_board_table') && eottae_is_gallery_board_table($bo_table)) {
            return false;
        }
        if (function_exists('eottae_is_youtube_board') && eottae_is_youtube_board($bo_table)) {
            return false;
        }

        return in_array($bo_table, eottae_board_seo_life_tables(), true);
    }
}

if (!function_exists('eottae_board_seo_excerpt')) {
    function eottae_board_seo_excerpt($html, $length = 160)
    {
        if (function_exists('eottae_shop_seo_excerpt')) {
            return eottae_shop_seo_excerpt($html, $length);
        }

        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $html)));
        if ($text === '') {
            return '';
        }
        if (function_exists('cut_str')) {
            return cut_str($text, (int) $length, '…');
        }
        if (mb_strlen($text, 'UTF-8') > $length) {
            return mb_substr($text, 0, $length, 'UTF-8').'…';
        }

        return $text;
    }
}

if (!function_exists('eottae_board_seo_site_name')) {
    function eottae_board_seo_site_name()
    {
        $site_name = function_exists('g5site_cfg') ? trim((string) g5site_cfg('site_name', '')) : '';
        if ($site_name !== '') {
            return $site_name;
        }

        global $config;
        if (isset($config['cf_title'])) {
            $site_name = trim(strip_tags((string) $config['cf_title']));
        }

        return $site_name !== '' ? $site_name : '세부어때';
    }
}

if (!function_exists('eottae_board_seo_board_label')) {
    function eottae_board_seo_board_label($board)
    {
        if (!is_array($board)) {
            return '게시판';
        }

        if (defined('G5_IS_MOBILE') && G5_IS_MOBILE && !empty($board['bo_mobile_subject'])) {
            return get_text($board['bo_mobile_subject']);
        }

        return !empty($board['bo_subject']) ? get_text($board['bo_subject']) : '게시판';
    }
}

if (!function_exists('eottae_board_seo_resolve')) {
    /**
     * @param array<string, mixed> $board
     * @param array<string, mixed> $write
     * @return array{meta_title:string,meta_description:string}
     */
    function eottae_board_seo_resolve($board, $write)
    {
        if (!is_array($write)) {
            $write = array();
        }

        $subject = isset($write['wr_subject']) ? trim(get_text(strip_tags((string) $write['wr_subject']))) : '';
        $board_label = eottae_board_seo_board_label($board);
        $site_name = eottae_board_seo_site_name();
        $category = isset($write['ca_name']) ? trim(get_text((string) $write['ca_name'])) : '';

        if ($subject !== '' && $board_label !== '') {
            $meta_title = $subject.' > '.$board_label;
        } elseif ($subject !== '') {
            $meta_title = $subject;
        } else {
            $meta_title = $board_label;
        }
        if ($meta_title !== '' && $site_name !== '') {
            $meta_title .= ' | '.$site_name;
        } elseif ($meta_title === '' && $site_name !== '') {
            $meta_title = $site_name;
        }

        $meta_description = '';
        if (!empty($write['wr_9'])) {
            $stored = trim(get_text(strip_tags((string) $write['wr_9'])));
            if ($stored !== '' && !preg_match('/^-?\d+(\.\d+)?$/', $stored)) {
                $meta_description = eottae_board_seo_excerpt($stored, 160);
            }
        }

        if ($meta_description === '' && !empty($write['wr_content'])) {
            $meta_description = eottae_board_seo_excerpt($write['wr_content'], 160);
        }

        if ($meta_description === '' && $subject !== '') {
            $lead = $subject;
            if ($category !== '') {
                $lead = '['.$category.'] '.$lead;
            }
            $meta_description = $board_label !== ''
                ? $board_label.' — '.$lead.'. '.$site_name.'에서 자세한 내용을 확인하세요.'
                : $lead.'. '.$site_name.'에서 자세한 내용을 확인하세요.';
            $meta_description = eottae_board_seo_excerpt($meta_description, 160);
        }

        return array(
            'meta_title'       => $meta_title,
            'meta_description' => $meta_description,
        );
    }
}

if (!function_exists('eottae_board_seo_view_url')) {
    function eottae_board_seo_view_url($bo_table, $wr_id)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return '';
        }

        if (function_exists('get_pretty_url')) {
            return get_pretty_url($bo_table, $wr_id);
        }

        return G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;
    }
}

if (!function_exists('eottae_board_seo_apply_page')) {
    function eottae_board_seo_apply_page($board, $write)
    {
        if (!eottae_board_seo_is_target_board($board)) {
            return;
        }
        if (!is_array($write) || empty($write['wr_id'])) {
            return;
        }

        global $page_title, $page_description, $page_canonical, $page_og_image, $page_schema_type, $g5;

        $resolved = eottae_board_seo_resolve($board, $write);

        if ($resolved['meta_title'] !== '') {
            $page_title = $resolved['meta_title'];
            $g5['title'] = strip_tags($resolved['meta_title']);
        }

        if ($resolved['meta_description'] !== '') {
            $page_description = $resolved['meta_description'];
        }

        $canonical = eottae_board_seo_view_url($board['bo_table'], (int) $write['wr_id']);
        if ($canonical !== '') {
            $page_canonical = $canonical;
        }

        if (!function_exists('get_list_thumbnail') && is_file(G5_LIB_PATH.'/thumbnail.lib.php')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }
        if (function_exists('get_list_thumbnail')) {
            $thumb = get_list_thumbnail($board['bo_table'], (int) $write['wr_id'], 1200, 630, false, true);
            if (!empty($thumb['src'])) {
                $page_og_image = $thumb['src'];
            }
        }

        $page_schema_type = 'Article';

        if (is_file(G5_PATH.'/components/seo-meta.php')) {
            include_once G5_PATH.'/components/seo-meta.php';
        }
        if (function_exists('g5b_seo_init')) {
            g5b_seo_init();
        }
    }
}

if (!function_exists('eottae_board_seo_sync_write')) {
    /**
     * 글 등록·수정·iCRM 연동 등 — wr_seo_title·메타 요약(wr_9) 보강
     */
    function eottae_board_seo_sync_write($bo_table, $wr_id)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return false;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch("
            SELECT wr_id, wr_subject, wr_content, wr_seo_title, wr_9, ca_name
            FROM `{$write_table}`
            WHERE wr_id = '{$wr_id}'
              AND wr_is_comment = 0
            LIMIT 1
        ", false);

        if (!is_array($write) || empty($write['wr_id'])) {
            return false;
        }

        $updates = array();

        $seo_title = isset($write['wr_seo_title']) ? trim((string) $write['wr_seo_title']) : '';
        if ($seo_title === '' && !empty($write['wr_subject'])) {
            if (function_exists('eottae_icrm_ensure_wr_seo_title')) {
                include_once G5_LIB_PATH.'/eottae-icrm.lib.php';
                $ensure = eottae_icrm_ensure_wr_seo_title($bo_table, $wr_id);
                if (!empty($ensure['ok'])) {
                    $seo_title = trim((string) ($ensure['wr_seo_title'] ?? ''));
                }
            } elseif (function_exists('generate_seo_title') && function_exists('exist_seo_title_recursive')) {
                include_once G5_LIB_PATH.'/uri.lib.php';
                $seo_title = exist_seo_title_recursive('bbs', generate_seo_title($write['wr_subject']), $write_table, $wr_id);
                $updates[] = " wr_seo_title = '".sql_escape_string($seo_title)."' ";
            }
        }

        $stored_desc = isset($write['wr_9']) ? trim((string) $write['wr_9']) : '';
        $can_store_wr9 = ($stored_desc === '' || preg_match('/^-?\d+(\.\d+)?$/', $stored_desc));
        if ($can_store_wr9) {
            $board = sql_fetch(" SELECT * FROM {$g5['board_table']} WHERE bo_table = '".sql_escape_string($bo_table)."' LIMIT 1 ", false);
            if (!is_array($board)) {
                $board = array('bo_table' => $bo_table);
            }
            $resolved = eottae_board_seo_resolve($board, $write);
            if ($resolved['meta_description'] !== '') {
                $updates[] = " wr_9 = '".sql_escape_string($resolved['meta_description'])."' ";
            }
        }

        if (empty($updates)) {
            return true;
        }

        return sql_query(" UPDATE `{$write_table}` SET ".implode(', ', $updates)." WHERE wr_id = '{$wr_id}' ") !== false;
    }
}
