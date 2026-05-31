<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_author_exposure_field_defs')) {
    /**
     * @return array<string, array{column:string, label:string, desc:string, list_url:callable|null}>
     */
    function eottae_column_author_exposure_field_defs()
    {
        return array(
            'show_shop' => array(
                'column'   => 'show_shop',
                'label'    => '내 업체',
                'desc'     => '등록한 업체 정보를 프로필에 표시합니다.',
                'list_url' => function () {
                    $table = function_exists('eottae_shop_table') ? eottae_shop_table() : 'shop';

                    return function_exists('eottae_board_list_url')
                        ? eottae_board_list_url($table)
                        : G5_BBS_URL.'/board.php?bo_table='.$table;
                },
            ),
            'show_market' => array(
                'column'   => 'show_market',
                'label'    => '중고 매물',
                'desc'     => '판매중·예약중 중고 글을 프로필에 표시합니다.',
                'list_url' => function () {
                    if (function_exists('eottae_market_list_url')) {
                        return eottae_market_list_url();
                    }
                    $table = defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market';

                    return G5_BBS_URL.'/board.php?bo_table='.$table;
                },
            ),
            'show_estate' => array(
                'column'   => 'show_estate',
                'label'    => '부동산 매물',
                'desc'     => '거래중 부동산 글을 프로필에 표시합니다.',
                'list_url' => function () {
                    $table = defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate';

                    return function_exists('eottae_board_list_url')
                        ? eottae_board_list_url($table)
                        : G5_BBS_URL.'/board.php?bo_table='.$table;
                },
            ),
            'show_job' => array(
                'column'   => 'show_job',
                'label'    => '구인공고',
                'desc'     => '모집중 구인구직 글을 프로필에 표시합니다.',
                'list_url' => function () {
                    $table = defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job';

                    return function_exists('eottae_board_list_url')
                        ? eottae_board_list_url($table)
                        : G5_BBS_URL.'/board.php?bo_table='.$table;
                },
            ),
        );
    }
}

if (!function_exists('eottae_column_author_exposure_migrate')) {
    function eottae_column_author_exposure_migrate()
    {
        if (!function_exists('eottae_column_bootstrap_tables')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }
        eottae_column_bootstrap_tables();
        global $g5;

        $table = $g5['sebu_column_authors_table'] ?? '';
        if ($table === '' || !function_exists('eottae_column_table_exists') || !eottae_column_table_exists($table)) {
            return;
        }

        $columns = array(
            'show_shop'   => "tinyint(1) NOT NULL DEFAULT '0'",
            'show_market' => "tinyint(1) NOT NULL DEFAULT '0'",
            'show_estate' => "tinyint(1) NOT NULL DEFAULT '0'",
            'show_job'    => "tinyint(1) NOT NULL DEFAULT '0'",
        );

        foreach ($columns as $column => $definition) {
            if (!eottae_column_table_has_column($table, $column)) {
                sql_query(" ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition} ", false);
            }
        }
    }
}

if (!function_exists('eottae_column_author_exposure_enabled')) {
    function eottae_column_author_exposure_enabled(array $author, $field_key)
    {
        $defs = eottae_column_author_exposure_field_defs();
        if (!isset($defs[$field_key])) {
            return false;
        }

        $column = $defs[$field_key]['column'];

        return !empty($author[$column]);
    }
}

if (!function_exists('eottae_column_author_exposure_from_input')) {
    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed>|null $existing
     */
    function eottae_column_author_exposure_from_input(array $input, $existing = null, $member_mode = true)
    {
        $existing = is_array($existing) ? $existing : array();
        $values = array();

        foreach (eottae_column_author_exposure_field_defs() as $key => $def) {
            $column = $def['column'];
            if ($member_mode) {
                $values[$column] = !empty($input[$key]) ? 1 : 0;
            } elseif (array_key_exists($key, $input)) {
                $values[$column] = !empty($input[$key]) ? 1 : 0;
            } else {
                $values[$column] = (int) ($existing[$column] ?? 0);
            }
        }

        return $values;
    }
}

if (!function_exists('eottae_column_author_exposure_sql_set')) {
    function eottae_column_author_exposure_sql_set(array $values)
    {
        $sql = '';
        foreach ($values as $column => $value) {
            $column = preg_replace('/[^a-z_]/', '', (string) $column);
            if ($column === '') {
                continue;
            }
            $sql .= ", {$column} = '".(int) $value."'";
        }

        return $sql;
    }
}

if (!function_exists('eottae_column_author_board_view_url')) {
    function eottae_column_author_board_view_url($bo_table, $wr_id)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return '#';
        }

        return function_exists('get_pretty_url')
            ? get_pretty_url($bo_table, $wr_id)
            : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;
    }
}

if (!function_exists('eottae_column_author_exposure_write_table_exists')) {
    function eottae_column_author_exposure_write_table_exists($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return false;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $exists = sql_fetch(" show tables like '".sql_escape_string($write_table)."' ");

        return !empty($exists);
    }
}

if (!function_exists('eottae_column_author_exposure_posts')) {
    function eottae_column_author_exposure_posts($mb_id, $bo_table, $limit = 3, array $options = array())
    {
        global $g5;

        $mb_id = trim((string) $mb_id);
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $limit = max(1, min(10, (int) $limit));
        if ($mb_id === '' || $bo_table === '' || !eottae_column_author_exposure_write_table_exists($bo_table)) {
            return array();
        }

        $mb_id_sql = sql_escape_string($mb_id);
        $write_table = $g5['write_prefix'].$bo_table;
        $where = " mb_id = '{$mb_id_sql}' AND wr_is_comment = 0 AND (wr_option IS NULL OR wr_option NOT LIKE '%secret%') ";

        $status_column = trim((string) ($options['status_column'] ?? ''));
        $active_statuses = isset($options['active_statuses']) && is_array($options['active_statuses'])
            ? $options['active_statuses']
            : array();
        if ($status_column !== '' && $active_statuses) {
            $status_sql = array();
            foreach ($active_statuses as $status) {
                $status = preg_replace('/[^a-z]/', '', (string) $status);
                if ($status !== '') {
                    $status_sql[] = "'".sql_escape_string($status)."'";
                }
            }
            if ($status_sql) {
                $where .= " AND ({$status_column} IN (".implode(',', $status_sql).") OR {$status_column} = '' OR {$status_column} IS NULL) ";
            }
        }

        $result = sql_query(" SELECT * FROM `{$write_table}` WHERE {$where} ORDER BY wr_id DESC LIMIT {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('eottae_column_author_exposure_shop_items')) {
    function eottae_column_author_exposure_shop_items($mb_id, $limit = 3)
    {
        if (!function_exists('eottae_business_shop_posts')) {
            include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';
        }
        if (!function_exists('eottae_business_shop_posts')) {
            return array();
        }

        $items = array();
        foreach (eottae_business_shop_posts($mb_id, max($limit, 3)) as $shop) {
            $bo_table = (string) ($shop['bo_table'] ?? '');
            $wr_id = (int) ($shop['wr_id'] ?? 0);
            $thumb = '';
            if (function_exists('eottae_community_list_thumb')) {
                $thumb = (string) eottae_community_list_thumb($bo_table, $wr_id);
            }

            $items[] = array(
                'title'         => get_text($shop['subject'] ?? $shop['wr_subject'] ?? ''),
                'meta'          => trim((string) ($shop['category'] ?? '')),
                'status_label'  => '',
                'status_class'  => '',
                'thumb'         => $thumb,
                'url'           => (string) ($shop['view_url'] ?? eottae_column_author_board_view_url($bo_table, $wr_id)),
            );
            if (count($items) >= $limit) {
                break;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_column_author_exposure_market_items')) {
    function eottae_column_author_exposure_market_items($mb_id, $limit = 3)
    {
        if (!function_exists('eottae_market_board_table')) {
            include_once G5_LIB_PATH.'/eottae-market.lib.php';
        }

        $bo_table = eottae_market_board_table();
        $rows = eottae_column_author_exposure_posts($mb_id, $bo_table, $limit, array(
            'status_column'   => 'wr_2',
            'active_statuses' => array('selling', 'reserved'),
        ));

        $items = array();
        foreach ($rows as $row) {
            $wr_id = (int) ($row['wr_id'] ?? 0);
            $status = eottae_market_normalize_status($row['wr_2'] ?? 'selling');
            $items[] = array(
                'title'         => get_text($row['wr_subject'] ?? ''),
                'meta'          => eottae_market_format_price($row['wr_1'] ?? 0, $row['wr_10'] ?? ''),
                'status_label'  => eottae_market_status_label($status),
                'status_class'  => 'market-status-badge--'.$status,
                'thumb'         => eottae_market_thumb_url($bo_table, $wr_id, 120, 120),
                'url'           => function_exists('eottae_market_view_url')
                    ? eottae_market_view_url($wr_id)
                    : eottae_column_author_board_view_url($bo_table, $wr_id),
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_column_author_exposure_estate_items')) {
    function eottae_column_author_exposure_estate_items($mb_id, $limit = 3)
    {
        if (!function_exists('eottae_estate_deal_status_from_row')) {
            include_once G5_LIB_PATH.'/eottae-estate.lib.php';
        }

        $bo_table = defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate';
        $rows = eottae_column_author_exposure_posts($mb_id, $bo_table, $limit, array(
            'status_column'   => 'wr_2',
            'active_statuses' => array('trading'),
        ));

        $items = array();
        foreach ($rows as $row) {
            $wr_id = (int) ($row['wr_id'] ?? 0);
            $card = eottae_estate_list_card_data($row, $bo_table);
            $thumb = '';
            if (function_exists('eottae_community_list_thumb')) {
                $thumb = (string) eottae_community_list_thumb($bo_table, $wr_id, $row['wr_content'] ?? '');
            }

            $meta_parts = array_filter(array(
                trim((string) ($card['region'] ?? '')),
                trim((string) ($card['price'] ?? '')),
            ));
            $status = eottae_estate_deal_status_from_row($row);
            $status_meta = eottae_estate_deal_status_meta($status);

            $items[] = array(
                'title'         => get_text($card['title'] ?? ($row['wr_subject'] ?? '')),
                'meta'          => implode(' · ', $meta_parts),
                'status_label'  => (string) ($status_meta['label'] ?? ''),
                'status_class'  => (string) ($status_meta['class'] ?? ''),
                'thumb'         => $thumb,
                'url'           => eottae_column_author_board_view_url($bo_table, $wr_id),
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_column_author_exposure_job_items')) {
    function eottae_column_author_exposure_job_items($mb_id, $limit = 3)
    {
        if (!function_exists('eottae_job_recruit_status_from_row')) {
            include_once G5_LIB_PATH.'/eottae-job.lib.php';
        }

        $bo_table = defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job';
        $rows = eottae_column_author_exposure_posts($mb_id, $bo_table, $limit, array(
            'status_column'   => 'wr_2',
            'active_statuses' => array('recruiting'),
        ));

        $items = array();
        foreach ($rows as $row) {
            $wr_id = (int) ($row['wr_id'] ?? 0);
            $thumb = '';
            if (function_exists('eottae_community_list_thumb')) {
                $thumb = (string) eottae_community_list_thumb($bo_table, $wr_id, $row['wr_content'] ?? '');
            }

            $status = eottae_job_recruit_status_from_row($row);
            $status_meta = eottae_job_recruit_status_meta($status);
            $snippet = eottae_job_list_snippet($row, $row['wr_subject'] ?? '', 80);

            $items[] = array(
                'title'         => get_text($row['wr_subject'] ?? ''),
                'meta'          => get_text($snippet),
                'status_label'  => (string) ($status_meta['label'] ?? ''),
                'status_class'  => (string) ($status_meta['class'] ?? ''),
                'thumb'         => $thumb,
                'url'           => eottae_column_author_board_view_url($bo_table, $wr_id),
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_column_author_exposure_item_counts')) {
    function eottae_column_author_exposure_item_counts($mb_id)
    {
        return array(
            'show_shop'   => count(eottae_column_author_exposure_shop_items($mb_id, 10)),
            'show_market' => count(eottae_column_author_exposure_market_items($mb_id, 10)),
            'show_estate' => count(eottae_column_author_exposure_estate_items($mb_id, 10)),
            'show_job'    => count(eottae_column_author_exposure_job_items($mb_id, 10)),
        );
    }
}

if (!function_exists('eottae_column_author_exposure_sections')) {
    /**
     * @param array<string, mixed> $author
     * @return array<int, array<string, mixed>>
     */
    function eottae_column_author_exposure_sections($mb_id, array $author, $limit = 3)
    {
        $mb_id = trim((string) $mb_id);
        $limit = max(1, min(6, (int) $limit));
        if ($mb_id === '') {
            return array();
        }

        $fetchers = array(
            'show_shop'   => 'eottae_column_author_exposure_shop_items',
            'show_market' => 'eottae_column_author_exposure_market_items',
            'show_estate' => 'eottae_column_author_exposure_estate_items',
            'show_job'    => 'eottae_column_author_exposure_job_items',
        );

        $sections = array();
        foreach (eottae_column_author_exposure_field_defs() as $key => $def) {
            if (!eottae_column_author_exposure_enabled($author, $key)) {
                continue;
            }

            $fetcher = $fetchers[$key] ?? '';
            if ($fetcher === '' || !function_exists($fetcher)) {
                continue;
            }

            $items = $fetcher($mb_id, $limit);
            if (!$items) {
                continue;
            }

            $list_url = '';
            if (is_callable($def['list_url'])) {
                $list_url = (string) call_user_func($def['list_url']);
            }

            $sections[] = array(
                'key'      => $key,
                'label'    => $def['label'],
                'list_url' => $list_url,
                'items'    => $items,
            );
        }

        return $sections;
    }
}
