<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_board_delete_token_session_key')) {
    function eottae_board_delete_token_session_key($bo_table, $wr_id)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return 'ss_delete_'.$bo_table.'_'.(int) $wr_id.'_token';
    }
}

if (!function_exists('eottae_board_issue_delete_token')) {
    function eottae_board_issue_delete_token($bo_table, $wr_id, $use_item_token = false)
    {
        $token = uniqid((string) time());
        if ($use_item_token) {
            set_session(eottae_board_delete_token_session_key($bo_table, $wr_id), $token);
        } else {
            set_session('ss_delete_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_board_consume_delete_token')) {
    function eottae_board_consume_delete_token($bo_table, $wr_id, $token)
    {
        $token = (string) $token;
        if ($token === '') {
            return false;
        }

        $global = (string) get_session('ss_delete_token');
        if ($global !== '' && hash_equals($global, $token)) {
            set_session('ss_delete_token', '');

            return true;
        }

        $key = eottae_board_delete_token_session_key($bo_table, $wr_id);
        $item = (string) get_session($key);
        if ($item !== '' && hash_equals($item, $token)) {
            set_session($key, '');

            return true;
        }

        return false;
    }
}

if (!function_exists('eottae_board_skip_manage_board')) {
    function eottae_board_skip_manage_board($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return true;
        }

        if (function_exists('eottae_talkroom_is_talkroom_board') && eottae_talkroom_is_talkroom_board($bo_table)) {
            return true;
        }

        if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('eottae_board_user_can_delete_post')) {
    /**
     * 글 삭제 권한 — 최고관리자 또는 작성자(회원 글)
     */
    function eottae_board_user_can_delete_post($write, $bo_table = '', $member = null, $is_admin = null)
    {
        if (!is_array($write) || empty($write['wr_id'])) {
            return false;
        }

        if (!empty($write['wr_is_comment'])) {
            return false;
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || eottae_board_skip_manage_board($bo_table)) {
            return false;
        }

        if ($is_admin === null) {
            $is_admin = $GLOBALS['is_admin'] ?? false;
        }
        if ($member === null) {
            $member = $GLOBALS['member'] ?? array();
        }

        if ($is_admin === 'super') {
            return true;
        }

        $mb_id = is_array($member) ? trim((string) ($member['mb_id'] ?? '')) : '';
        $author_id = trim((string) ($write['mb_id'] ?? ''));

        return $mb_id !== '' && $author_id !== '' && $mb_id === $author_id;
    }
}

if (!function_exists('eottae_board_user_can_manage_post')) {
    function eottae_board_user_can_manage_post($write, $bo_table = '', $member = null, $is_admin = null)
    {
        return eottae_board_user_can_delete_post($write, $bo_table, $member, $is_admin);
    }
}

if (!function_exists('eottae_board_manage_hrefs')) {
    /**
     * @return array{update_href: string, delete_href: string}
     */
    function eottae_board_manage_hrefs($write, $bo_table = '', $page = 0, $qstr = '', $delete_token = '', $use_item_token = false)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = is_array($write) && !empty($write['wr_id']) ? (int) $write['wr_id'] : 0;
        if ($bo_table === '' || $wr_id < 1) {
            return array('update_href' => '', 'delete_href' => '');
        }

        $page = (int) $page;
        $qstr = (string) $qstr;
        $delete_token = (string) $delete_token;

        $update_href = G5_BBS_URL.'/write.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.$qstr;
        if (function_exists('short_url_clean')) {
            $update_href = short_url_clean($update_href);
        }

        if ($delete_token === '') {
            $delete_token = eottae_board_issue_delete_token($bo_table, $wr_id, $use_item_token);
        }
        $delete_href = G5_BBS_URL.'/delete.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;token='.$delete_token.'&amp;page='.$page.urldecode($qstr);

        return array(
            'update_href' => $update_href,
            'delete_href' => $delete_href,
        );
    }
}

if (!function_exists('eottae_board_apply_manage_links')) {
    function eottae_board_apply_manage_links(&$write, $bo_table = '')
    {
        global $update_href, $delete_href, $page, $qstr;

        if (!eottae_board_user_can_manage_post($write, $bo_table)) {
            return;
        }

        $hrefs = eottae_board_manage_hrefs(
            $write,
            $bo_table,
            isset($page) ? (int) $page : 0,
            isset($qstr) ? (string) $qstr : ''
        );
        if ($hrefs['update_href'] !== '') {
            $update_href = $hrefs['update_href'];
        }
        if ($hrefs['delete_href'] !== '') {
            $delete_href = $hrefs['delete_href'];
        }
    }
}

if (!function_exists('eottae_board_apply_view_links')) {
    function eottae_board_apply_view_links($board, $write, $member, $is_admin, $bo_table, $wr_id, $page, $qstr, &$update_href, &$delete_href)
    {
        if (!is_array($board) || !is_array($write) || empty($board['bo_table'])) {
            return;
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || eottae_board_skip_manage_board($bo_table)) {
            return;
        }

        if (!eottae_board_user_can_manage_post($write, $bo_table, $member, $is_admin)) {
            return;
        }

        $hrefs = eottae_board_manage_hrefs($write, $bo_table, (int) $page, (string) $qstr);
        if ($hrefs['update_href'] !== '') {
            $update_href = $hrefs['update_href'];
        }
        if ($hrefs['delete_href'] !== '') {
            $delete_href = $hrefs['delete_href'];
        }
    }
}

if (!function_exists('eottae_board_list_item_manage')) {
    /**
     * 목록 1건 관리 링크·체크박스
     *
     * @return array{can_delete: bool, can_update: bool, delete_href: string, update_href: string, show_checkbox: bool}
     */
    function eottae_board_list_item_manage($write, $bo_table = '', $page = 0, $qstr = '', $allow_checkbox = true)
    {
        global $is_checkbox, $is_admin;

        $result = array(
            'can_delete' => false,
            'can_update' => false,
            'delete_href' => '',
            'update_href' => '',
            'show_checkbox' => false,
        );

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || eottae_board_skip_manage_board($bo_table)) {
            return $result;
        }

        if ($allow_checkbox && !empty($is_checkbox) && $is_admin === 'super') {
            $result['show_checkbox'] = true;
        }

        if (!eottae_board_user_can_manage_post($write, $bo_table)) {
            return $result;
        }

        $hrefs = eottae_board_manage_hrefs($write, $bo_table, (int) $page, (string) $qstr, '', true);
        $result['can_delete'] = true;
        $result['can_update'] = true;
        $result['delete_href'] = $hrefs['delete_href'];
        $result['update_href'] = $hrefs['update_href'];

        return $result;
    }
}
