<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_admin_authors_url')) {
    function eottae_column_admin_authors_url(array $params = array())
    {
        $url = G5_URL.'/page/eottae-admin-column-authors.php';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('eottae_column_admin_authors_summary')) {
    function eottae_column_admin_authors_summary()
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $table = $g5['sebu_column_authors_table'];
        $row = sql_fetch("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN is_visible = 1 THEN 1 ELSE 0 END) AS visible_count,
                SUM(CASE WHEN is_official = 1 THEN 1 ELSE 0 END) AS official_count
            FROM `{$table}`
        ", false);

        return array(
            'total'    => (int) ($row['total'] ?? 0),
            'active'   => (int) ($row['active_count'] ?? 0),
            'visible'  => (int) ($row['visible_count'] ?? 0),
            'official' => (int) ($row['official_count'] ?? 0),
        );
    }
}

if (!function_exists('eottae_column_admin_list_authors_filtered')) {
    /**
     * @param array<string, mixed> $options
     * @return array<int, array<string, mixed>>
     */
    function eottae_column_admin_list_authors_filtered(array $options = array())
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $search = trim((string) ($options['search'] ?? ''));
        $filter = preg_replace('/[^a-z_]/', '', (string) ($options['filter'] ?? 'all'));
        $sort = preg_replace('/[^a-z_]/', '', (string) ($options['sort'] ?? 'updated'));
        $limit = max(1, min(500, (int) ($options['limit'] ?? 200)));

        $table = $g5['sebu_column_authors_table'];
        $where = array('1=1');

        if ($search !== '') {
            $q = sql_escape_string($search);
            $where[] = "(a.mb_id LIKE '%{$q}%' OR a.pen_name LIKE '%{$q}%' OR m.mb_nick LIKE '%{$q}%' OR m.mb_name LIKE '%{$q}%' OR a.title LIKE '%{$q}%')";
        }

        if ($filter === 'active') {
            $where[] = 'a.is_active = 1';
        } elseif ($filter === 'inactive') {
            $where[] = 'a.is_active = 0';
        } elseif ($filter === 'visible') {
            $where[] = 'a.is_visible = 1';
        } elseif ($filter === 'hidden') {
            $where[] = 'a.is_visible = 0';
        } elseif ($filter === 'official') {
            $where[] = 'a.is_official = 1';
        }

        $order = 'a.updated_at DESC';
        if ($sort === 'name') {
            $order = 'a.pen_name ASC, m.mb_nick ASC';
        } elseif ($sort === 'mb_id') {
            $order = 'a.mb_id ASC';
        } elseif ($sort === 'created') {
            $order = 'a.created_at DESC';
        }

        $where_sql = implode(' AND ', $where);
        $result = sql_query("
            SELECT a.*, m.mb_nick, m.mb_name, m.mb_leave_date, m.mb_intercept_date, m.mb_memo_cnt
            FROM `{$table}` a
            LEFT JOIN {$g5['member_table']} m ON m.mb_id = a.mb_id
            WHERE {$where_sql}
            ORDER BY {$order}
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            $author = eottae_column_enrich_author($row);
            $author['member_left'] = trim((string) ($row['mb_leave_date'] ?? '')) !== '';
            $author['member_blocked'] = trim((string) ($row['mb_intercept_date'] ?? '')) !== '';
            $author['member_ok'] = !$author['member_left'] && !$author['member_blocked'];
            $items[] = $author;
        }

        if ($sort === 'columns') {
            usort($items, function ($a, $b) {
                $ca = (int) ($a['stats']['column_count'] ?? 0);
                $cb = (int) ($b['stats']['column_count'] ?? 0);
                if ($ca === $cb) {
                    return strcmp((string) ($a['display_name'] ?? ''), (string) ($b['display_name'] ?? ''));
                }

                return $cb <=> $ca;
            });
        } elseif ($sort === 'views') {
            usort($items, function ($a, $b) {
                $va = (int) ($a['stats']['total_views'] ?? 0);
                $vb = (int) ($b['stats']['total_views'] ?? 0);

                return $vb <=> $va;
            });
        }

        return $items;
    }
}

if (!function_exists('eottae_column_admin_author_mb_ids_for_scope')) {
    /**
     * @return array<int, string>
     */
    function eottae_column_admin_author_mb_ids_for_scope($scope, array $options = array())
    {
        $scope = preg_replace('/[^a-z_]/', '', (string) $scope);
        if ($scope === 'selected') {
            $ids = isset($options['mb_ids']) && is_array($options['mb_ids']) ? $options['mb_ids'] : array();
            $clean = array();
            foreach ($ids as $id) {
                $id = preg_replace('/[^a-z0-9_]/i', '', (string) $id);
                if ($id !== '') {
                    $clean[$id] = $id;
                }
            }

            return array_values($clean);
        }

        $filter = 'active';
        $search = '';
        if ($scope === 'all') {
            $filter = 'all';
            $search = (string) ($options['search'] ?? '');
        } elseif ($scope === 'all_active') {
            $filter = 'active';
        } elseif ($scope === 'visible') {
            $filter = 'visible';
        } elseif ($scope === 'filtered') {
            $filter = (string) ($options['filter'] ?? 'all');
            $search = (string) ($options['search'] ?? '');
        }

        $authors = eottae_column_admin_list_authors_filtered(array(
            'search' => $search,
            'filter' => $filter,
            'limit'  => 500,
        ));

        $mb_ids = array();
        foreach ($authors as $author) {
            if (empty($author['member_ok'])) {
                continue;
            }
            $mb_id = preg_replace('/[^a-z0-9_]/i', '', (string) ($author['mb_id'] ?? ''));
            if ($mb_id !== '') {
                $mb_ids[] = $mb_id;
            }
        }

        return $mb_ids;
    }
}

if (!function_exists('eottae_column_admin_send_memo')) {
    /**
     * @param array<int, string> $recv_mb_ids
     * @return array{ok: bool, message: string, sent: int, skipped: int, failed: array<int, string>}
     */
    function eottae_column_admin_send_memo($sender_mb_id, array $recv_mb_ids, $memo_body)
    {
        global $g5, $config;

        $sender_mb_id = preg_replace('/[^a-z0-9_]/i', '', (string) $sender_mb_id);
        $memo_body = trim(strip_tags((string) $memo_body));
        if ($sender_mb_id === '') {
            return array('ok' => false, 'message' => '발신자 정보가 없습니다.', 'sent' => 0, 'skipped' => 0, 'failed' => array());
        }
        if ($memo_body === '') {
            return array('ok' => false, 'message' => '메시지 내용을 입력해 주세요.', 'sent' => 0, 'skipped' => 0, 'failed' => array());
        }

        $recv_mb_ids = array_values(array_unique(array_filter(array_map(function ($id) {
            return preg_replace('/[^a-z0-9_]/i', '', (string) $id);
        }, $recv_mb_ids))));

        if (empty($recv_mb_ids)) {
            return array('ok' => false, 'message' => '수신 대상이 없습니다.', 'sent' => 0, 'skipped' => 0, 'failed' => array());
        }

        if (strlen($memo_body) > 60000) {
            $memo_body = substr($memo_body, 0, 60000);
        }

        $prefix = "[세부어때 · 생활정보 컬럼]\n\n";
        $me_memo = sql_escape_string($prefix.$memo_body);
        $sent = 0;
        $skipped = 0;
        $failed = array();

        if (!function_exists('get_memo_not_read')) {
            include_once G5_LIB_PATH.'/get_data.lib.php';
        }

        foreach ($recv_mb_ids as $recv_mb_id) {
            $row = sql_fetch("
                SELECT mb_id, mb_nick, mb_leave_date, mb_intercept_date
                FROM {$g5['member_table']}
                WHERE mb_id = '".sql_escape_string($recv_mb_id)."'
            ");

            if (empty($row['mb_id'])) {
                $failed[] = $recv_mb_id;
                continue;
            }
            if (trim((string) ($row['mb_leave_date'] ?? '')) !== '' || trim((string) ($row['mb_intercept_date'] ?? '')) !== '') {
                $skipped++;
                continue;
            }

            $sql = " INSERT INTO {$g5['memo_table']}
                ( me_recv_mb_id, me_send_mb_id, me_send_datetime, me_memo, me_read_datetime, me_type, me_send_ip )
                VALUES (
                    '".sql_escape_string($recv_mb_id)."',
                    '".sql_escape_string($sender_mb_id)."',
                    '".G5_TIME_YMDHIS."',
                    '{$me_memo}',
                    '0000-00-00 00:00:00',
                    'recv',
                    '".sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '')."'
                ) ";
            sql_query($sql, false);
            $me_id = (int) sql_insert_id();

            if ($me_id > 0) {
                sql_query(" INSERT INTO {$g5['memo_table']}
                    ( me_recv_mb_id, me_send_mb_id, me_send_datetime, me_memo, me_read_datetime, me_send_id, me_type, me_send_ip )
                    VALUES (
                        '".sql_escape_string($recv_mb_id)."',
                        '".sql_escape_string($sender_mb_id)."',
                        '".G5_TIME_YMDHIS."',
                        '{$me_memo}',
                        '0000-00-00 00:00:00',
                        '{$me_id}',
                        'send',
                        '".sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '')."'
                    ) ", false);

                $memo_cnt = get_memo_not_read($recv_mb_id);
                sql_query("
                    UPDATE {$g5['member_table']} SET
                        mb_memo_call = '".sql_escape_string($sender_mb_id)."',
                        mb_memo_cnt = '".(int) $memo_cnt."'
                    WHERE mb_id = '".sql_escape_string($recv_mb_id)."'
                ", false);
                $sent++;
            } else {
                $failed[] = $recv_mb_id;
            }
        }

        if ($sent < 1) {
            $message = '쪽지를 보낼 수 있는 회원이 없습니다.';
            if ($skipped > 0) {
                $message .= ' (탈퇴·차단 '.$skipped.'명 제외)';
            }

            return array('ok' => false, 'message' => $message, 'sent' => 0, 'skipped' => $skipped, 'failed' => $failed);
        }

        $message = number_format($sent).'명에게 쪽지를 발송했습니다.';
        if ($skipped > 0) {
            $message .= ' (탈퇴·차단 '.$skipped.'명 제외)';
        }
        if (!empty($failed)) {
            $message .= ' (실패: '.implode(', ', $failed).')';
        }

        return array('ok' => true, 'message' => $message, 'sent' => $sent, 'skipped' => $skipped, 'failed' => $failed);
    }
}

if (!function_exists('eottae_column_admin_toggle_author_flag')) {
    function eottae_column_admin_toggle_author_flag($mb_id, $field, $value)
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $mb_id = preg_replace('/[^a-z0-9_]/i', '', (string) $mb_id);
        $allowed = array('is_active', 'is_visible', 'is_official');
        if ($mb_id === '' || !in_array($field, $allowed, true)) {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }

        $author = eottae_column_get_author($mb_id);
        if (!$author) {
            return array('ok' => false, 'message' => '칼럼니스트를 찾을 수 없습니다.');
        }

        $flag = !empty($value) ? 1 : 0;
        $table = $g5['sebu_column_authors_table'];
        sql_query("
            UPDATE `{$table}` SET
                {$field} = '{$flag}',
                updated_at = '".G5_TIME_YMDHIS."'
            WHERE mb_id = '".sql_escape_string($mb_id)."'
        ", false);

        eottae_column_sync_author_badges($mb_id);

        return array('ok' => true, 'message' => '저장되었습니다.', 'field' => $field, 'value' => $flag);
    }
}
