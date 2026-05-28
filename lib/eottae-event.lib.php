<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 이벤트/프로모션 게시판 (bo_table=event) — 확장필드
 *
 * wr_1  이벤트 종류 (discount|coupon|open|trial|oneplus|gift|other)
 * wr_2  연결 업체 — "bo_table:wr_id" 또는 빈 값
 * wr_3  업체명 또는 작성자명
 * wr_4  기간 설정 — period(기간 있음) | none(기간 없음)
 * wr_5  시작일 (Y-m-d, 선택)
 * wr_6  종료일 (Y-m-d, 기간 있음 시 필수)
 * wr_7  혜택 요약
 * wr_8  문의 방법 (선택)
 * wr_9  수동 종료 — 0 진행, 1 작성자/관리자 종료
 * wr_10 예비
 *
 * 진행/종료 상태는 목록·상세 출력 시 날짜·wr_9로 계산합니다(DB 일괄 갱신 없음).
 * cron으로 wr_9를 일괄 반영하려면 eottae_event_status_from_row()와 동일 로직을 배치에 사용하세요.
 */

if (!function_exists('eottae_event_types')) {
    /**
     * @return array<string, string>
     */
    function eottae_event_types()
    {
        return array(
            'discount' => '할인',
            'coupon'   => '쿠폰',
            'open'     => '오픈이벤트',
            'trial'    => '무료체험',
            'oneplus'  => '1+1',
            'gift'     => '사은품',
            'other'    => '기타',
        );
    }
}

if (!function_exists('eottae_event_normalize_type')) {
    function eottae_event_normalize_type($type)
    {
        $type = preg_replace('/[^a-z]/', '', (string) $type);

        return array_key_exists($type, eottae_event_types()) ? $type : 'other';
    }
}

if (!function_exists('eottae_event_type_label')) {
    function eottae_event_type_label($type)
    {
        $types = eottae_event_types();
        $type = eottae_event_normalize_type($type);

        return $types[$type] ?? '기타';
    }
}

if (!function_exists('eottae_event_normalize_period_mode')) {
    function eottae_event_normalize_period_mode($mode)
    {
        $mode = preg_replace('/[^a-z]/', '', (string) $mode);

        return $mode === 'none' ? 'none' : 'period';
    }
}

if (!function_exists('eottae_event_normalize_date')) {
    function eottae_event_normalize_date($date)
    {
        $date = trim((string) $date);
        if ($date === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        $ts = strtotime($date);

        return $ts ? date('Y-m-d', $ts) : '';
    }
}

if (!function_exists('eottae_event_parse_shop_ref')) {
    /**
     * @return array{bo_table:string, wr_id:int}
     */
    function eottae_event_parse_shop_ref($ref)
    {
        $ref = trim((string) $ref);
        if ($ref === '' || strpos($ref, ':') === false) {
            return array('bo_table' => '', 'wr_id' => 0);
        }

        $parts = explode(':', $ref, 2);
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) ($parts[0] ?? ''));
        $wr_id = (int) ($parts[1] ?? 0);

        if ($bo_table === '' || $wr_id < 1) {
            return array('bo_table' => '', 'wr_id' => 0);
        }

        if (function_exists('eottae_is_shop_board') && !eottae_is_shop_board($bo_table)) {
            return array('bo_table' => '', 'wr_id' => 0);
        }

        return array('bo_table' => $bo_table, 'wr_id' => $wr_id);
    }
}

if (!function_exists('eottae_event_format_shop_ref')) {
    function eottae_event_format_shop_ref($bo_table, $wr_id)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return '';
        }

        return $bo_table.':'.$wr_id;
    }
}

if (!function_exists('eottae_event_shop_from_row')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_event_shop_from_row($row)
    {
        if (!is_array($row)) {
            return null;
        }

        $ref = eottae_event_parse_shop_ref($row['wr_2'] ?? '');
        if ($ref['bo_table'] === '' || $ref['wr_id'] < 1) {
            return null;
        }

        global $g5;
        $write_table = $g5['write_prefix'].$ref['bo_table'];
        $shop_row = sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '".(int) $ref['wr_id']."' AND wr_is_comment = 0 LIMIT 1 ");
        if (!$shop_row) {
            return null;
        }

        $shop = function_exists('eottae_shop_from_write')
            ? eottae_shop_from_write($shop_row, $ref['bo_table'])
            : array();

        $name = trim((string) ($shop['name'] ?? ''));
        if ($name === '') {
            $name = get_text($shop_row['wr_subject'] ?? '');
        }

        return array(
            'bo_table'  => $ref['bo_table'],
            'wr_id'     => $ref['wr_id'],
            'name'      => $name,
            'region'    => (string) ($shop['region'] ?? ''),
            'view_url'  => function_exists('eottae_shop_view_url')
                ? eottae_shop_view_url($ref['wr_id'], $ref['bo_table'])
                : G5_BBS_URL.'/board.php?bo_table='.$ref['bo_table'].'&wr_id='.$ref['wr_id'],
        );
    }
}

if (!function_exists('eottae_event_selectable_shops')) {
    /**
     * 글쓰기 — 연결 가능 업체 목록 (선택, 필수 아님)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_event_selectable_shops($member = null, $is_super_admin = false)
    {
        $shops = array();

        if ($is_super_admin) {
            $shops = eottae_event_admin_shop_list(120);
        } elseif (is_array($member) && !empty($member['mb_id'])) {
            if (function_exists('eottae_adroom_member_shops')) {
                include_once G5_LIB_PATH.'/eottae-adroom.lib.php';
                $shops = eottae_adroom_member_shops($member['mb_id']);
            } elseif (function_exists('eottae_business_shop_posts')) {
                $posts = eottae_business_shop_posts($member['mb_id'], 30);
                foreach ($posts as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $bo_table = (string) ($row['bo_table'] ?? '');
                    $wr_id = (int) ($row['wr_id'] ?? 0);
                    if ($bo_table === '' || $wr_id < 1) {
                        continue;
                    }
                    $shops[] = array(
                        'bo_table' => $bo_table,
                        'wr_id'    => $wr_id,
                        'name'     => get_text($row['subject'] ?? ''),
                        'region'   => '',
                    );
                }
            }
        }

        return $shops;
    }
}

if (!function_exists('eottae_event_admin_shop_list')) {
    /**
     * 관리자용 최근 업체 목록 (TODO: 검색 API 연동 시 교체 가능)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_event_admin_shop_list($limit = 100)
    {
        global $g5;

        $limit = max(10, min(300, (int) $limit));
        if (!function_exists('eottae_shop_board_tables')) {
            return array();
        }

        $shops = array();
        foreach (eottae_shop_board_tables() as $bo_table) {
            $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
            if ($bo_table === '') {
                continue;
            }
            $write_table = $g5['write_prefix'].$bo_table;
            if (!sql_query(" DESCRIBE `{$write_table}` ", false)) {
                continue;
            }
            $result = sql_query("
                SELECT wr_id, wr_subject, wr_2
                FROM `{$write_table}`
                WHERE wr_is_comment = 0
                ORDER BY wr_id DESC
                LIMIT ".(int) ceil($limit / 3)."
            ");
            while ($row = sql_fetch_array($result)) {
                $name = trim(get_text($row['wr_subject'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $shops[] = array(
                    'bo_table'    => $bo_table,
                    'wr_id'       => (int) $row['wr_id'],
                    'name'        => $name,
                    'region'      => trim(get_text($row['wr_2'] ?? '')),
                    'board_label' => $bo_table,
                );
            }
        }

        return array_slice($shops, 0, $limit);
    }
}

if (!function_exists('eottae_event_manual_closed_from_row')) {
    function eottae_event_manual_closed_from_row($row)
    {
        if (!is_array($row)) {
            return false;
        }

        return (int) ($row['wr_9'] ?? 0) === 1;
    }
}

if (!function_exists('eottae_event_status_from_row')) {
    /**
     * @return string active|ended
     */
    function eottae_event_status_from_row($row)
    {
        if (!is_array($row)) {
            return 'ended';
        }

        if (eottae_event_manual_closed_from_row($row)) {
            return 'ended';
        }

        $mode = eottae_event_normalize_period_mode($row['wr_4'] ?? 'period');
        if ($mode === 'period') {
            $end = eottae_event_normalize_date($row['wr_6'] ?? '');
            if ($end !== '') {
                $today = function_exists('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');
                if ($today > $end) {
                    return 'ended';
                }
            }

            return 'active';
        }

        return 'active';
    }
}

if (!function_exists('eottae_event_status_meta')) {
    /**
     * @return array{key:string, label:string, class:string}
     */
    function eottae_event_status_meta($status)
    {
        $status = ($status === 'ended') ? 'ended' : 'active';

        return array(
            'key'   => $status,
            'label' => $status === 'ended' ? '종료' : '진행중',
            'class' => 'event-status-badge--'.$status,
        );
    }
}

if (!function_exists('eottae_event_render_status_badge')) {
    function eottae_event_render_status_badge($status, $extra_class = '')
    {
        $meta = eottae_event_status_meta($status);
        $class = 'event-status-badge '.$meta['class'];
        if ($extra_class !== '') {
            $class .= ' '.trim($extra_class);
        }

        return '<span class="'.$class.'">'.get_text($meta['label']).'</span>';
    }
}

if (!function_exists('eottae_event_render_type_badge')) {
    function eottae_event_render_type_badge($type, $extra_class = '')
    {
        $label = eottae_event_type_label($type);
        $class = 'event-type-badge';
        if ($extra_class !== '') {
            $class .= ' '.trim($extra_class);
        }

        return '<span class="'.$class.'">'.get_text($label).'</span>';
    }
}

if (!function_exists('eottae_event_period_label_from_row')) {
    function eottae_event_period_label_from_row($row)
    {
        if (!is_array($row)) {
            return '기간 없음';
        }

        $mode = eottae_event_normalize_period_mode($row['wr_4'] ?? 'period');
        if ($mode === 'none') {
            $start = eottae_event_normalize_date($row['wr_5'] ?? '');
            if ($start !== '') {
                return $start.' ~';
            }

            return '기간 없음';
        }

        $start = eottae_event_normalize_date($row['wr_5'] ?? '');
        $end = eottae_event_normalize_date($row['wr_6'] ?? '');
        if ($start !== '' && $end !== '') {
            return $start.' ~ '.$end;
        }
        if ($end !== '') {
            return '~ '.$end;
        }
        if ($start !== '') {
            return $start.' ~';
        }

        return '기간 미정';
    }
}

if (!function_exists('eottae_event_can_manual_close')) {
    function eottae_event_can_manual_close(array $write, $mb_id, $is_super_admin = false)
    {
        if ($is_super_admin) {
            return true;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        $author = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($write['mb_id'] ?? ''));

        return $author !== '' && $author === $mb_id;
    }
}

if (!function_exists('eottae_event_can_show_close_button')) {
    function eottae_event_can_show_close_button(array $write, $mb_id, $is_super_admin = false)
    {
        if (eottae_event_status_from_row($write) !== 'active') {
            return false;
        }

        if (eottae_event_normalize_period_mode($write['wr_4'] ?? '') === 'period') {
            return false;
        }

        return eottae_event_can_manual_close($write, $mb_id, $is_super_admin);
    }
}

if (!function_exists('eottae_event_set_manual_closed')) {
    /**
     * @return array{ok:bool, message:string, status?:string, label?:string}
     */
    function eottae_event_set_manual_closed($bo_table, $wr_id, $mb_id, $is_super_admin = false)
    {
        if (!function_exists('eottae_is_event_board') || !eottae_is_event_board($bo_table)) {
            return array('ok' => false, 'message' => '이벤트 게시판이 아닙니다.');
        }

        global $g5;

        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '잘못된 게시글입니다.');
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '{$wr_id}' AND wr_is_comment = 0 LIMIT 1 ");
        if (!$write) {
            return array('ok' => false, 'message' => '게시글을 찾을 수 없습니다.');
        }

        if (!eottae_event_can_manual_close($write, $mb_id, $is_super_admin)) {
            return array('ok' => false, 'message' => '종료 처리 권한이 없습니다.');
        }

        if (eottae_event_normalize_period_mode($write['wr_4'] ?? '') === 'period') {
            return array('ok' => false, 'message' => '기간이 있는 이벤트는 종료일 이후 자동 종료됩니다.');
        }

        sql_query(" UPDATE `{$write_table}` SET wr_9 = '1' WHERE wr_id = '{$wr_id}' ");

        $meta = eottae_event_status_meta('ended');

        return array(
            'ok'      => true,
            'message' => '이벤트가 종료 처리되었습니다.',
            'status'  => 'ended',
            'label'   => $meta['label'],
        );
    }
}

if (!function_exists('eottae_event_post_content_plain')) {
    function eottae_event_post_content_plain(array $post)
    {
        $html = (string) ($post['wr_content'] ?? '');
        $text = trim(strip_tags($html));
        $text = str_replace(array("\xc2\xa0", '&nbsp;'), ' ', $text);

        return trim($text);
    }
}

if (!function_exists('eottae_event_build_auto_content_plain')) {
    function eottae_event_build_auto_content_plain(array $post)
    {
        $parts = array();
        $benefit = trim(strip_tags((string) ($post['wr_7'] ?? '')));
        $contact = trim(strip_tags((string) ($post['wr_8'] ?? '')));
        $name = trim(strip_tags((string) ($post['wr_3'] ?? '')));

        if ($benefit !== '') {
            $parts[] = '[혜택 요약]'."\n".$benefit;
        }
        if ($contact !== '') {
            $parts[] = '[문의 방법]'."\n".$contact;
        }
        if ($name !== '') {
            $parts[] = '[업체/작성자]'."\n".$name;
        }

        return implode("\n\n", $parts);
    }
}

if (!function_exists('eottae_event_view_should_hide_body')) {
    /**
     * 상세 패널과 중복되는 자동 본문만 있을 때 본문 영역 숨김
     */
    function eottae_event_view_should_hide_body(array $row)
    {
        $plain = eottae_event_post_content_plain($row);
        if ($plain === '') {
            return false;
        }

        $auto = eottae_event_build_auto_content_plain($row);
        if ($auto !== '' && $plain === $auto) {
            return true;
        }

        $benefit = trim(strip_tags((string) ($row['wr_7'] ?? '')));
        if ($benefit === '') {
            return false;
        }

        return strpos($plain, '[혜택 요약]') === 0
            && strpos($plain, $benefit) !== false
            && strlen($plain) < 600;
    }
}

if (!function_exists('eottae_event_ensure_content_on_save')) {
    /**
     * 에디터 동기화 누락 시 혜택·문의 등으로 본문 보완
     */
    function eottae_event_ensure_content_on_save()
    {
        if (eottae_event_post_content_plain($_POST) !== '') {
            return;
        }

        $auto = eottae_event_build_auto_content_plain($_POST);
        if ($auto !== '') {
            $_POST['wr_content'] = $auto;
        }
    }
}

if (!function_exists('eottae_event_validate_write_post')) {
    /**
     * @return array{ok:bool, message:string}
     */
    function eottae_event_validate_write_post(array $post)
    {
        $subject = trim(strip_tags((string) ($post['wr_subject'] ?? '')));
        if ($subject === '') {
            return array('ok' => false, 'message' => '이벤트 제목을 입력해 주세요.');
        }

        $name = trim(strip_tags((string) ($post['wr_3'] ?? '')));
        if ($name === '') {
            return array('ok' => false, 'message' => '업체명 또는 작성자명을 입력해 주세요.');
        }

        $benefit = trim(strip_tags((string) ($post['wr_7'] ?? '')));
        if ($benefit === '') {
            return array('ok' => false, 'message' => '혜택 요약을 입력해 주세요.');
        }

        if (eottae_event_post_content_plain($post) === '') {
            return array('ok' => false, 'message' => '상세 내용을 입력해 주세요.');
        }

        $mode = eottae_event_normalize_period_mode($post['wr_4'] ?? 'period');
        if ($mode === 'period') {
            $start = eottae_event_normalize_date($post['wr_5'] ?? '');
            $end = eottae_event_normalize_date($post['wr_6'] ?? '');
            if ($end === '') {
                return array('ok' => false, 'message' => '기간 있음 선택 시 종료일을 입력해 주세요.');
            }
            if ($start !== '' && $end < $start) {
                return array('ok' => false, 'message' => '종료일은 시작일보다 빠를 수 없습니다.');
            }
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_event_normalize_write_post')) {
    function eottae_event_normalize_write_post(array $post, $is_update = false)
    {
        $out = array();

        $out['wr_1'] = eottae_event_normalize_type($post['wr_1'] ?? 'other');

        $shop_ref = isset($post['wr_2']) ? (string) $post['wr_2'] : '';
        $parsed = eottae_event_parse_shop_ref($shop_ref);
        $out['wr_2'] = eottae_event_format_shop_ref($parsed['bo_table'], $parsed['wr_id']);

        $out['wr_3'] = trim(strip_tags((string) ($post['wr_3'] ?? '')));
        if (function_exists('cut_str') && $out['wr_3'] !== '') {
            $out['wr_3'] = cut_str($out['wr_3'], 120, '');
        }

        $mode = eottae_event_normalize_period_mode($post['wr_4'] ?? 'period');
        $out['wr_4'] = $mode;

        $out['wr_5'] = eottae_event_normalize_date($post['wr_5'] ?? '');
        $out['wr_6'] = ($mode === 'period') ? eottae_event_normalize_date($post['wr_6'] ?? '') : '';

        $out['wr_7'] = trim(strip_tags((string) ($post['wr_7'] ?? '')));
        if (function_exists('cut_str') && $out['wr_7'] !== '') {
            $out['wr_7'] = cut_str($out['wr_7'], 200, '');
        }

        $out['wr_8'] = trim(strip_tags((string) ($post['wr_8'] ?? '')));
        if (function_exists('cut_str') && $out['wr_8'] !== '') {
            $out['wr_8'] = cut_str($out['wr_8'], 200, '');
        }

        if ($is_update) {
            $out['wr_9'] = isset($post['wr_9']) ? ((int) $post['wr_9'] === 1 ? '1' : '0') : null;
        } else {
            $out['wr_9'] = '0';
        }

        return $out;
    }
}

if (!function_exists('eottae_event_apply_write_post')) {
    function eottae_event_apply_write_post(array $normalized)
    {
        foreach ($normalized as $key => $value) {
            if ($value === null) {
                continue;
            }
            $_POST[$key] = $value;
        }
    }
}

if (!function_exists('eottae_event_ensure_board_permissions')) {
    /**
     * 이벤트 게시판 쓰기 권한 — 커뮤니티 허브와 동일하게 회원(레벨 2) 허용
     */
    function eottae_event_ensure_board_permissions()
    {
        if (!function_exists('eottae_event_board_table')) {
            return;
        }

        if (function_exists('eottae_community_hub_ensure_board_permissions')) {
            eottae_community_hub_ensure_board_permissions(eottae_event_board_table());

            return;
        }

        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        global $g5, $board;

        $bo_table = eottae_event_board_table();
        if (empty($g5['board_table'])) {
            return;
        }

        $row = sql_fetch("
            SELECT bo_write_level, bo_reply_level
            FROM {$g5['board_table']}
            WHERE bo_table = '".sql_escape_string($bo_table)."'
        ");
        if (!$row) {
            return;
        }

        $write_level = (int) ($row['bo_write_level'] ?? 2);
        if ($write_level <= 2) {
            return;
        }

        sql_query("
            UPDATE {$g5['board_table']} SET
                bo_write_level = 2,
                bo_reply_level = 2
            WHERE bo_table = '".sql_escape_string($bo_table)."'
        ", false);

        if (function_exists('run_event')) {
            run_event('cache_delete', 'board');
        }

        if (is_array($board) && isset($board['bo_table']) && $board['bo_table'] === $bo_table) {
            $board['bo_write_level'] = 2;
            $board['bo_reply_level'] = 2;
        }
    }
}

if (!function_exists('eottae_event_icrm_label_map')) {
    function eottae_event_icrm_label_map()
    {
        return array(
            '이벤트 종류'           => 'wr_1',
            '이벤트/프로모션 종류'  => 'wr_1',
            '업체명 또는 작성자명'  => 'wr_3',
            '업체/작성자'           => 'wr_3',
            '업체명'                => 'wr_3',
            '작성자'                => 'wr_3',
            '혜택 요약'             => 'wr_7',
            '혜택'                  => 'wr_7',
            '문의 방법'             => 'wr_8',
            '문의'                  => 'wr_8',
            '시작일'                => 'wr_5',
            '종료일'                => 'wr_6',
            '이벤트 기간'           => '_period',
        );
    }
}

if (!function_exists('eottae_event_resolve_type_from_text')) {
    function eottae_event_resolve_type_from_text($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        foreach (eottae_event_types() as $code => $label) {
            if ($text === $label || $text === $code) {
                return $code;
            }
        }

        return eottae_event_normalize_type($text);
    }
}

if (!function_exists('eottae_event_parse_icrm_blocks')) {
    function eottae_event_parse_icrm_blocks($html, array &$fields)
    {
        $label_map = eottae_event_icrm_label_map();
        $blocks = array();

        if (preg_match_all('/<div[^>]*\bicrm-(?:section|facility-card)\b[^>]*>(.*?)<\/div>/is', (string) $html, $sections)) {
            $blocks = $sections[1];
        }

        foreach ($blocks as $block) {
            if (!preg_match('/<strong[^>]*>\s*(.*?)\s*<\/strong>/is', $block, $label_match)) {
                continue;
            }

            $label = trim(strip_tags($label_match[1]));
            if ($label === '' || !isset($label_map[$label])) {
                continue;
            }

            $field = $label_map[$label];
            $value_html = preg_replace('/<strong[^>]*>.*?<\/strong>/is', '', $block, 1);
            $value = trim(strip_tags((string) $value_html));

            if ($field === '_period') {
                if (preg_match('/(\d{4}-\d{2}-\d{2})/', $value, $m)) {
                    if (empty($fields['wr_5'])) {
                        $fields['wr_5'] = $m[1];
                    }
                }
                if (preg_match_all('/(\d{4}-\d{2}-\d{2})/', $value, $dates) && count($dates[1]) > 1) {
                    $fields['wr_6'] = $dates[1][count($dates[1]) - 1];
                    $fields['wr_4'] = 'period';
                } elseif (stripos($value, '기간 없음') !== false) {
                    $fields['wr_4'] = 'none';
                }

                continue;
            }

            if ($field === 'wr_1') {
                $value = eottae_event_resolve_type_from_text($value);
            } elseif ($field === 'wr_5' || $field === 'wr_6') {
                $value = eottae_event_normalize_date($value);
            }

            if ($value !== '') {
                $fields[$field] = $value;
            }
        }
    }
}

if (!function_exists('eottae_event_parse_icrm_html')) {
    /**
     * iCRM HTML 본문에서 이벤트 확장필드 추출
     *
     * @return array<string, string>
     */
    function eottae_event_parse_icrm_html($html)
    {
        $html = (string) $html;
        if ($html === '' || strpos($html, '<') === false) {
            return array();
        }

        $fields = array();

        if (!function_exists('eottae_icrm_extract_embedded_json')) {
            include_once G5_LIB_PATH.'/eottae-icrm-template.lib.php';
        }

        $embedded = function_exists('eottae_icrm_extract_embedded_json')
            ? eottae_icrm_extract_embedded_json($html)
            : null;
        if ($embedded !== null) {
            $data = json_decode($embedded, true);
            if (is_array($data)) {
                foreach (array('wr_1', 'wr_2', 'wr_3', 'wr_4', 'wr_5', 'wr_6', 'wr_7', 'wr_8') as $key) {
                    if (!empty($data[$key])) {
                        $fields[$key] = trim((string) $data[$key]);
                    }
                }
            }
        }

        $is_icrm = function_exists('eottae_icrm_content_should_preserve_html')
            && eottae_icrm_content_should_preserve_html($html);
        if ($is_icrm) {
            eottae_event_parse_icrm_blocks($html, $fields);
        }

        if (preg_match_all('/data-event-field=["\'](wr_[0-9]+)["\'][^>]*\bvalue=["\']([^"\']*)["\']/i', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $fields[$match[1]] = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        if (!empty($fields['wr_1'])) {
            $fields['wr_1'] = eottae_event_resolve_type_from_text($fields['wr_1']);
        }
        if (!empty($fields['wr_4'])) {
            $fields['wr_4'] = eottae_event_normalize_period_mode($fields['wr_4']);
        }
        if (!empty($fields['wr_5'])) {
            $fields['wr_5'] = eottae_event_normalize_date($fields['wr_5']);
        }
        if (!empty($fields['wr_6'])) {
            $fields['wr_6'] = eottae_event_normalize_date($fields['wr_6']);
        }

        return $fields;
    }
}

if (!function_exists('eottae_event_row_has_panel_data')) {
    function eottae_event_row_has_panel_data(array $row)
    {
        return trim(strip_tags((string) ($row['wr_3'] ?? ''))) !== ''
            || trim(strip_tags((string) ($row['wr_7'] ?? ''))) !== ''
            || trim(strip_tags((string) ($row['wr_8'] ?? ''))) !== '';
    }
}

if (!function_exists('eottae_event_enrich_row_from_content')) {
    function eottae_event_enrich_row_from_content($row)
    {
        if (!is_array($row)) {
            return $row;
        }

        $parsed = eottae_event_parse_icrm_html($row['wr_content'] ?? '');
        if (!$parsed) {
            return $row;
        }

        foreach ($parsed as $key => $value) {
            if (!isset($row[$key]) || trim(strip_tags((string) $row[$key])) === '') {
                $row[$key] = $value;
            }
        }

        return $row;
    }
}

if (!function_exists('eottae_event_sync_fields_from_row')) {
    /**
     * wr_* 비어 있을 때 iCRM HTML에서 확장필드 백필
     */
    function eottae_event_sync_fields_from_row($bo_table, $wr_id)
    {
        if (!function_exists('eottae_is_event_board') || !eottae_is_event_board($bo_table)) {
            return;
        }

        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return;
        }

        global $g5;

        $write_table = $g5['write_prefix'].$bo_table;
        $write = get_write($write_table, $wr_id, true);
        if (empty($write['wr_id'])) {
            return;
        }

        $parsed = eottae_event_parse_icrm_html($write['wr_content'] ?? '');
        if (!$parsed) {
            return;
        }

        $sets = array();
        foreach ($parsed as $key => $value) {
            if (!preg_match('/^wr_[0-9]+$/', $key)) {
                continue;
            }
            if (trim(strip_tags((string) ($write[$key] ?? ''))) !== '') {
                continue;
            }
            $sets[] = "`{$key}` = '".sql_real_escape_string($value)."'";
        }

        if (!$sets) {
            return;
        }

        sql_query(' UPDATE `'.$write_table.'` SET '.implode(', ', $sets).' WHERE wr_id = \''.$wr_id.'\' ');
        get_write($write_table, $wr_id, false);
    }
}

if (!function_exists('eottae_event_shop_posts_cache_ref')) {
    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    function &eottae_event_shop_posts_cache_ref()
    {
        static $cache = array();

        return $cache;
    }
}

if (!function_exists('eottae_event_reset_shop_posts_cache')) {
    function eottae_event_reset_shop_posts_cache()
    {
        $cache = &eottae_event_shop_posts_cache_ref();
        $cache = array();
    }
}

if (!function_exists('eottae_event_posts_for_shop')) {
    /**
     * 업체에 연결된 이벤트/프로모션 글 목록 (최신순)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_event_posts_for_shop($bo_table, $wr_id, $active_only = true, $limit = 5)
    {
        $ref = eottae_event_format_shop_ref($bo_table, $wr_id);
        if ($ref === '') {
            return array();
        }

        $cache = &eottae_event_shop_posts_cache_ref();
        $cache_key = $active_only ? $ref.':active' : $ref.':all';
        if (array_key_exists($cache_key, $cache)) {
            return $cache[$cache_key];
        }

        if (!function_exists('eottae_event_board_table')) {
            return array();
        }

        global $g5;

        $event_bo = eottae_event_board_table();
        $write_table = $g5['write_prefix'].$event_bo;
        $limit = max(1, min(20, (int) $limit));
        $ref_sql = sql_escape_string($ref);

        $result = sql_query("
            SELECT wr_id, wr_subject, wr_1, wr_2, wr_3, wr_4, wr_5, wr_6, wr_7, wr_8, wr_9, wr_datetime
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_2 = '{$ref_sql}'
            ORDER BY wr_id DESC
            LIMIT {$limit}
        ");

        $items = array();
        while ($row = sql_fetch_array($result)) {
            if ($active_only && eottae_event_status_from_row($row) !== 'active') {
                continue;
            }
            $row['event_status'] = eottae_event_status_from_row($row);
            $row['event_type'] = eottae_event_normalize_type($row['wr_1'] ?? 'other');
            $items[] = $row;
        }

        $cache[$cache_key] = $items;

        return $items;
    }
}

if (!function_exists('eottae_event_prefetch_shop_posts_for_rows')) {
    /**
     * 업소 목록 렌더 전 일괄 조회 (썸네일 배지용)
     *
     * @param array<int, array<string, mixed>> $rows
     */
    function eottae_event_prefetch_shop_posts_for_rows(array $rows, $bo_table = '', $active_only = true)
    {
        if (!$rows) {
            return;
        }

        if ($bo_table === '') {
            $bo_table = function_exists('eottae_shop_table') ? eottae_shop_table() : 'shop';
        }
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        $refs = array();
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $wr_id = (int) ($row['wr_id'] ?? 0);
            if ($wr_id < 1) {
                continue;
            }
            $ref = eottae_event_format_shop_ref($bo_table, $wr_id);
            if ($ref !== '') {
                $refs[$ref] = true;
            }
        }

        foreach (array_keys($refs) as $ref) {
            $parsed = eottae_event_parse_shop_ref($ref);
            if ($parsed['bo_table'] === '' || $parsed['wr_id'] < 1) {
                continue;
            }
            eottae_event_posts_for_shop($parsed['bo_table'], $parsed['wr_id'], $active_only, 8);
        }
    }
}

if (!function_exists('eottae_event_render_shop_thumb_badges')) {
    /**
     * 업체 썸네일 위 이벤트/프로모션 배지 HTML
     *
     * @param array<int, array<string, mixed>>|array<string, mixed>|null $events
     */
    function eottae_event_render_shop_thumb_badges($events, $size = 'sm')
    {
        if (is_array($events) && isset($events['wr_id'])) {
            $events = array($events);
        }
        if (!is_array($events) || !$events) {
            return '';
        }

        $primary = $events[0];
        $type = eottae_event_normalize_type($primary['event_type'] ?? ($primary['wr_1'] ?? 'other'));
        $status = ($primary['event_status'] ?? eottae_event_status_from_row($primary)) === 'ended' ? 'ended' : 'active';
        $promo_kinds = array('discount', 'coupon', 'oneplus', 'gift');
        $promo_label = in_array($type, $promo_kinds, true) ? '프로모션' : '이벤트';
        $size_class = $size === 'lg' ? ' shop-thumb-event-badges--lg' : '';

        $extra = '';
        $count = count($events);
        if ($count > 1) {
            $extra = '<span class="shop-thumb-event-badge shop-thumb-event-badge--count" title="진행 중 이벤트 '.number_format($count).'건">+'.($count - 1).'</span>';
        }

        return '<span class="shop-thumb-event-badges'.$size_class.'" aria-hidden="true">'
            .'<span class="shop-thumb-event-badge shop-thumb-event-badge--promo">'.get_text($promo_label).'</span>'
            .eottae_event_render_type_badge($type, 'shop-thumb-event-badge shop-thumb-event-badge--type')
            .($status === 'active' ? '' : eottae_event_render_status_badge($status, 'shop-thumb-event-badge--status'))
            .$extra
            .'</span>';
    }
}

if (!function_exists('eottae_shop_event_thumb_badges_html')) {
    function eottae_shop_event_thumb_badges_html($bo_table, $wr_id, $size = 'sm', $active_only = true)
    {
        $events = eottae_event_posts_for_shop($bo_table, $wr_id, $active_only, 5);

        return eottae_event_render_shop_thumb_badges($events, $size);
    }
}

if (!function_exists('eottae_event_shop_list_thumb_html')) {
    /**
     * 이벤트 글 목록 — 연결 업체 썸네일 + 배지
     */
    function eottae_event_shop_list_thumb_html(array $event_row, $size = 'md')
    {
        $shop = eottae_event_shop_from_row($event_row);
        if (!$shop) {
            return '';
        }

        global $g5;
        $write_table = $g5['write_prefix'].$shop['bo_table'];
        $shop_row = sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '".(int) $shop['wr_id']."' AND wr_is_comment = 0 LIMIT 1 ");
        if (!$shop_row) {
            return '';
        }

        if (!function_exists('eottae_shop_card_thumb')) {
            include_once G5_PATH.'/components/eottae/shop-card.php';
        }

        $thumb = function_exists('eottae_shop_card_thumb') ? eottae_shop_card_thumb($shop_row, $shop['bo_table']) : '';
        if ($thumb !== '' && function_exists('eottae_map_public_url')) {
            $thumb = eottae_map_public_url($thumb);
        }

        $event_row['event_status'] = eottae_event_status_from_row($event_row);
        $event_row['event_type'] = eottae_event_normalize_type($event_row['wr_1'] ?? 'other');
        $badges = eottae_event_render_shop_thumb_badges($event_row, $size);
        $size_class = $size === 'lg' ? ' event-post__thumb-wrap--lg' : '';

        ob_start();
        ?>
        <div class="event-post__thumb-wrap<?php echo $size_class; ?>">
            <?php if ($thumb !== '') { ?>
            <img src="<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="event-post__thumb" width="168" height="94" loading="lazy" decoding="async">
            <?php } else { ?>
            <span class="event-post__thumb event-post__thumb--empty" aria-hidden="true"></span>
            <?php } ?>
            <?php echo $badges; ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
