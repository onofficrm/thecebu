<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_shop_owner_mb_id_from_write')) {
    function eottae_shop_owner_mb_id_from_write($write)
    {
        if (!is_array($write) || empty($write['mb_id'])) {
            return '';
        }

        $owner = get_member($write['mb_id']);
        if (empty($owner['mb_id'])) {
            return '';
        }

        if (function_exists('eottae_is_business_member') && eottae_is_business_member($owner)) {
            return $owner['mb_id'];
        }

        return '';
    }
}

if (!function_exists('eottae_shop_owner_validate')) {
    function eottae_shop_owner_validate($owner_mb_id)
    {
        $owner_mb_id = trim((string) $owner_mb_id);
        if ($owner_mb_id === '') {
            return array('ok' => true, 'message' => '');
        }

        $owner = get_member($owner_mb_id);
        if (empty($owner['mb_id'])) {
            return array('ok' => false, 'message' => '존재하지 않는 회원아이디입니다.');
        }

        if (function_exists('eottae_is_business_member') && !eottae_is_business_member($owner)) {
            return array('ok' => false, 'message' => '사업자 회원 아이디만 업체 관리 권한을 부여할 수 있습니다.');
        }

        return array('ok' => true, 'message' => '', 'member' => $owner);
    }
}

if (!function_exists('eottae_shop_assign_owner')) {
    function eottae_shop_assign_owner($bo_table, $wr_id, $owner_mb_id)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        $owner_mb_id = trim((string) $owner_mb_id);

        if ($bo_table === '' || $wr_id < 1 || $owner_mb_id === '') {
            return false;
        }

        if (!function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
            return false;
        }

        $check = eottae_shop_owner_validate($owner_mb_id);
        if (empty($check['ok']) || empty($check['member'])) {
            return false;
        }

        $owner = $check['member'];
        $write_table = $g5['write_prefix'].$bo_table;
        $mb_id = sql_escape_string($owner['mb_id']);
        $wr_name = addslashes(get_text($owner['mb_nick']));
        $wr_email = addslashes(get_email_address($owner['mb_email']));

        sql_query(" update {$write_table} set
            mb_id = '{$mb_id}',
            wr_name = '{$wr_name}',
            wr_email = '{$wr_email}'
            where wr_id = '{$wr_id}' and wr_is_comment = 0 ");

        return true;
    }
}

if (!function_exists('eottae_shop_owner_member_list')) {
    /**
     * 업체 관리 회원 지정용 회원 목록 (아이디·이름·닉네임)
     *
     * @param string $selected_mb_id 현재 선택된 회원 (목록에 없으면 맨 앞에 포함)
     * @param int    $limit
     * @return array<int, array<string, string>>
     */
    function eottae_shop_owner_member_list($selected_mb_id = '', $limit = 500)
    {
        global $g5;

        $limit = max(1, min(2000, (int) $limit));
        $selected_mb_id = trim((string) $selected_mb_id);
        $rows = array();
        $seen = array();

        $sql = " select mb_id, mb_name, mb_nick
            from {$g5['member_table']}
            where mb_leave_date = '' and mb_intercept_date = ''
            order by mb_datetime desc
            limit {$limit} ";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $mb_id = trim((string) $row['mb_id']);
            if ($mb_id === '') {
                continue;
            }
            $seen[$mb_id] = true;
            $rows[] = array(
                'mb_id'   => $mb_id,
                'mb_name' => get_text($row['mb_name']),
                'mb_nick' => get_text($row['mb_nick']),
            );
        }

        if ($selected_mb_id !== '' && empty($seen[$selected_mb_id])) {
            $selected = get_member($selected_mb_id);
            if (!empty($selected['mb_id'])) {
                array_unshift($rows, array(
                    'mb_id'   => $selected['mb_id'],
                    'mb_name' => get_text($selected['mb_name']),
                    'mb_nick' => get_text($selected['mb_nick']),
                ));
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_shop_owner_member_option_label')) {
    function eottae_shop_owner_member_option_label($member_row)
    {
        if (!is_array($member_row) || empty($member_row['mb_id'])) {
            return '';
        }

        $name = trim((string) ($member_row['mb_name'] ?? ''));
        $nick = trim((string) ($member_row['mb_nick'] ?? ''));

        if ($name === '') {
            $name = '-';
        }
        if ($nick === '') {
            $nick = '-';
        }

        return get_text($member_row['mb_id']).' · '.$name.' · '.$nick;
    }
}

if (!function_exists('eottae_shop_user_can_manage')) {
    /**
     * 업체 글 수정·삭제 권한 — 최고관리자 또는 연결된 사업자 회원
     */
    function eottae_shop_user_can_manage($write, $bo_table = '')
    {
        global $member, $is_admin;

        if (!is_array($write) || empty($write['wr_id'])) {
            return false;
        }

        if (!function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
            return false;
        }

        if ($is_admin === 'super') {
            return true;
        }

        if (!empty($member['mb_id']) && function_exists('eottae_business_owns_shop')) {
            return eottae_business_owns_shop($member['mb_id'], (int) $write['wr_id'], $bo_table);
        }

        return false;
    }
}

if (!function_exists('eottae_shop_manage_hrefs')) {
    /**
     * 업체 수정·삭제 URL (삭제 토큰 포함)
     *
     * @return array{update_href: string, delete_href: string}
     */
    function eottae_shop_manage_hrefs($write, $bo_table = '', $page = 0, $qstr = '', $delete_token = '')
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
            set_session('ss_delete_token', $delete_token = uniqid((string) time()));
        }
        $delete_href = G5_BBS_URL.'/delete.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;token='.$delete_token.'&amp;page='.$page.urldecode($qstr);

        return array(
            'update_href' => $update_href,
            'delete_href' => $delete_href,
        );
    }
}

if (!function_exists('eottae_shop_apply_manage_links')) {
    function eottae_shop_apply_manage_links(&$write, $bo_table = '')
    {
        global $update_href, $delete_href, $page, $qstr;

        if (!eottae_shop_user_can_manage($write, $bo_table)) {
            return;
        }

        $hrefs = eottae_shop_manage_hrefs($write, $bo_table, isset($page) ? (int) $page : 0, isset($qstr) ? (string) $qstr : '');
        if ($hrefs['update_href'] !== '') {
            $update_href = $hrefs['update_href'];
        }
        if ($hrefs['delete_href'] !== '') {
            $delete_href = $hrefs['delete_href'];
        }
    }
}

if (!function_exists('eottae_business_shop_posts')) {
    function eottae_business_shop_posts($mb_id, $limit = 30)
    {
        global $g5;

        $mb_id = sql_escape_string(trim((string) $mb_id));
        if ($mb_id === '' || !function_exists('eottae_shop_board_tables')) {
            return array();
        }

        $limit = max(1, min(100, (int) $limit));
        $rows = array();
        $list_delete_token = '';
        set_session('ss_delete_token', $list_delete_token = uniqid((string) time()));

        foreach (eottae_shop_board_tables() as $bo_table) {
            $write_table = $g5['write_prefix'].$bo_table;
            $result = sql_query(" select wr_id, wr_subject, wr_datetime, ca_name
                from {$write_table}
                where mb_id = '{$mb_id}' and wr_is_comment = 0
                order by wr_id desc
                limit {$limit} ");
            while ($row = sql_fetch_array($result)) {
                $rows[] = array(
                    'bo_table' => $bo_table,
                    'wr_id' => (int) $row['wr_id'],
                    'wr_subject' => $row['wr_subject'],
                    'subject' => get_text($row['wr_subject']),
                    'datetime' => $row['wr_datetime'],
                    'category' => get_text($row['ca_name']),
                    'update_url' => G5_BBS_URL.'/write.php?bo_table='.$bo_table.'&amp;wr_id='.(int) $row['wr_id'].'&amp;w=u',
                    'view_url' => function_exists('eottae_shop_view_url')
                        ? eottae_shop_view_url((int) $row['wr_id'], $bo_table)
                        : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.(int) $row['wr_id'],
                    'delete_url' => '',
                );
                if (function_exists('eottae_shop_manage_hrefs')) {
                    $manage = eottae_shop_manage_hrefs($row, $bo_table, 0, '', $list_delete_token);
                    $rows[count($rows) - 1]['delete_url'] = $manage['delete_href'];
                }
            }
        }

        usort($rows, function ($a, $b) {
            return strcmp($b['datetime'], $a['datetime']);
        });

        if (count($rows) > $limit) {
            $rows = array_slice($rows, 0, $limit);
        }

        return $rows;
    }
}
