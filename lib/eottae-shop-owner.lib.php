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
                    'subject' => get_text($row['wr_subject']),
                    'datetime' => $row['wr_datetime'],
                    'category' => get_text($row['ca_name']),
                    'update_url' => G5_BBS_URL.'/write.php?bo_table='.$bo_table.'&amp;wr_id='.(int) $row['wr_id'].'&amp;w=u',
                    'view_url' => get_pretty_url($bo_table, (int) $row['wr_id']),
                );
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
