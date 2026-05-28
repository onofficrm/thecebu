<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 세부 제보함 (bo_table=report)
 *
 * wr_1  제보 유형
 * wr_2  지역
 * wr_3  익명 여부 (1|0)
 * wr_4  연락 가능 여부 (1|0)
 * wr_5  연락처 (관리자만 열람)
 * wr_6  관련 업체명
 * wr_7  관련 링크
 * wr_8  제보 상태 received|checking|published|rejected
 * wr_9  관리자 메모 (관리자만)
 * wr_10 예비
 */

if (!function_exists('eottae_report_board_table')) {
    function eottae_report_board_table()
    {
        return defined('EOTTae_REPORT_TABLE') ? EOTTae_REPORT_TABLE : 'report';
    }
}

if (!function_exists('eottae_is_report_board')) {
    function eottae_is_report_board($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === eottae_report_board_table();
    }
}

if (!function_exists('eottae_report_types')) {
    /**
     * @return array<string, string>
     */
    function eottae_report_types()
    {
        return array(
            'life'    => '생활정보',
            'shop'    => '업체정보',
            'event'   => '이벤트/할인',
            'traffic' => '교통/도로',
            'alert'   => '사건/주의',
            'lost'    => '분실/사람찾기',
            'other'   => '기타',
        );
    }
}

if (!function_exists('eottae_report_normalize_type')) {
    function eottae_report_normalize_type($type)
    {
        $type = preg_replace('/[^a-z]/', '', (string) $type);

        return array_key_exists($type, eottae_report_types()) ? $type : 'other';
    }
}

if (!function_exists('eottae_report_type_label')) {
    function eottae_report_type_label($type)
    {
        $types = eottae_report_types();

        return $types[eottae_report_normalize_type($type)] ?? '기타';
    }
}

if (!function_exists('eottae_report_regions')) {
    /**
     * @return array<string, string>
     */
    function eottae_report_regions()
    {
        return array(
            'cebu'        => '세부시티',
            'mactan'      => '막탄',
            'mandaue'     => '만다우에',
            'lapu'        => '라푸라푸',
            'talisay'     => '탈리사이',
            'consolacion' => '콘솔라시온',
            'other'       => '기타',
        );
    }
}

if (!function_exists('eottae_report_normalize_region')) {
    function eottae_report_normalize_region($region)
    {
        $region = trim(strip_tags((string) $region));
        $keys = eottae_report_regions();
        if (isset($keys[$region])) {
            return $region;
        }
        foreach ($keys as $key => $label) {
            if ($region === $label) {
                return $key;
            }
        }
        if ($region === '') {
            return '';
        }

        return 'other';
    }
}

if (!function_exists('eottae_report_region_label')) {
    function eottae_report_region_label($region)
    {
        $key = eottae_report_normalize_region($region);
        $regions = eottae_report_regions();
        if ($key !== '' && isset($regions[$key])) {
            return $regions[$key];
        }

        $region = trim(strip_tags((string) $region));

        return $region !== '' ? $region : '지역 미입력';
    }
}

if (!function_exists('eottae_report_statuses')) {
    /**
     * @return array<string, string>
     */
    function eottae_report_statuses()
    {
        return array(
            'received'  => '접수됨',
            'checking'  => '확인중',
            'published' => '공개됨',
            'rejected'  => '반려됨',
        );
    }
}

if (!function_exists('eottae_report_normalize_status')) {
    function eottae_report_normalize_status($status)
    {
        $status = preg_replace('/[^a-z]/', '', (string) $status);

        return array_key_exists($status, eottae_report_statuses()) ? $status : 'received';
    }
}

if (!function_exists('eottae_report_status_meta')) {
    /**
     * @return array{key:string, label:string, class:string}
     */
    function eottae_report_status_meta($status)
    {
        $status = eottae_report_normalize_status($status);
        $labels = eottae_report_statuses();

        return array(
            'key'   => $status,
            'label' => $labels[$status] ?? '접수됨',
            'class' => 'report-status-badge--'.$status,
        );
    }
}

if (!function_exists('eottae_report_render_status_badge')) {
    function eottae_report_render_status_badge($status, $extra_class = '')
    {
        $meta = eottae_report_status_meta($status);
        $class = 'report-status-badge '.$meta['class'];
        if ($extra_class !== '') {
            $class .= ' '.trim($extra_class);
        }

        return '<span class="'.$class.'">'.get_text($meta['label']).'</span>';
    }
}

if (!function_exists('eottae_report_render_type_badge')) {
    function eottae_report_render_type_badge($type, $extra_class = '')
    {
        $class = 'report-type-badge';
        if ($extra_class !== '') {
            $class .= ' '.trim($extra_class);
        }

        return '<span class="'.$class.'">'.get_text(eottae_report_type_label($type)).'</span>';
    }
}

if (!function_exists('eottae_report_is_anonymous')) {
    function eottae_report_is_anonymous($row)
    {
        if (!is_array($row)) {
            return false;
        }

        return (string) ($row['wr_3'] ?? '') === '1';
    }
}

if (!function_exists('eottae_report_display_author')) {
    function eottae_report_display_author($row, $fallback = '제보자')
    {
        if (eottae_report_is_anonymous($row)) {
            return '익명 제보자';
        }

        $name = '';
        if (is_array($row)) {
            $name = trim(strip_tags((string) ($row['name'] ?? ($row['wr_name'] ?? ''))));
        }

        return $name !== '' ? $name : $fallback;
    }
}

if (!function_exists('eottae_report_row_has_photo')) {
    function eottae_report_row_has_photo($bo_table, $wr_id)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return false;
        }

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM {$g5['board_file_table']}
            WHERE bo_table = '".sql_escape_string($bo_table)."'
              AND wr_id = '{$wr_id}'
              AND bf_file <> ''
        ");

        return !empty($row['cnt']) && (int) $row['cnt'] > 0;
    }
}

if (!function_exists('eottae_report_list_card_data')) {
    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    function eottae_report_list_card_data(array $item, $bo_table = '')
    {
        if ($bo_table === '') {
            $bo_table = eottae_report_board_table();
        }

        $wr_id = (int) ($item['wr_id'] ?? 0);
        $has_photo = false;
        if (!empty($item['file']['count'])) {
            $has_photo = (int) $item['file']['count'] > 0;
        } elseif ($wr_id > 0) {
            $has_photo = eottae_report_row_has_photo($bo_table, $wr_id);
        }

        return array(
            'href'        => $item['href'] ?? '',
            'subject'     => isset($item['subject']) ? strip_tags($item['subject']) : get_text($item['wr_subject'] ?? ''),
            'type'        => eottae_report_normalize_type($item['wr_1'] ?? 'other'),
            'type_label'  => eottae_report_type_label($item['wr_1'] ?? 'other'),
            'region'      => eottae_report_region_label($item['wr_2'] ?? ''),
            'status'      => eottae_report_normalize_status($item['wr_8'] ?? 'received'),
            'shop_name'   => get_text($item['wr_6'] ?? ''),
            'author'      => eottae_report_display_author($item),
            'datetime'    => isset($item['wr_datetime']) ? $item['wr_datetime'] : '',
            'time_label'  => function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($item['wr_datetime'] ?? '')
                : '',
            'has_photo'   => $has_photo,
        );
    }
}

if (!function_exists('eottae_report_board_hero')) {
    function eottae_report_board_hero($board = array())
    {
        $cebu_img = 'https://images.unsplash.com/photo-%s?auto=format&fit=crop&w=1600&q=85';

        return array(
            'kicker' => '세부어때 제보함',
            'title'  => '세부 제보함',
            'desc'   => '세부에서 본 소식, 생활정보, 이벤트, 주의사항을 제보해주세요. 작은 제보가 세부 교민과 여행자에게 큰 도움이 됩니다.',
            'image'  => sprintf($cebu_img, '1518509562904-7fc873a70436'),
        );
    }
}

if (!function_exists('eottae_report_validate_write_post')) {
    /**
     * @return array{ok:bool, message:string}
     */
    function eottae_report_validate_write_post(array $post)
    {
        if (trim((string) ($post['wr_1'] ?? '')) === '') {
            return array('ok' => false, 'message' => '제보 유형을 선택해 주세요.');
        }

        $subject = trim(strip_tags((string) ($post['wr_subject'] ?? '')));
        if ($subject === '') {
            return array('ok' => false, 'message' => '제보 제목을 입력해 주세요.');
        }

        $content = trim(strip_tags((string) ($post['wr_content'] ?? '')));
        $content = str_replace(array("\xc2\xa0", '&nbsp;'), ' ', $content);
        if (trim($content) === '') {
            return array('ok' => false, 'message' => '제보 내용을 입력해 주세요.');
        }

        $region = eottae_report_normalize_region($post['wr_2'] ?? '');
        if ($region === '') {
            return array('ok' => false, 'message' => '지역을 선택해 주세요.');
        }

        $contact_ok = !empty($post['wr_4']) && (string) $post['wr_4'] === '1';
        $contact = trim(strip_tags((string) ($post['wr_5'] ?? '')));
        if ($contact_ok && $contact === '') {
            return array('ok' => false, 'message' => '연락 가능을 선택하셨다면 연락처를 입력해 주세요.');
        }

        $link = trim((string) ($post['wr_7'] ?? ''));
        if ($link !== '' && !preg_match('#^https?://#i', $link)) {
            return array('ok' => false, 'message' => '관련 링크는 http:// 또는 https:// 로 시작해야 합니다.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_report_normalize_write_post')) {
    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed>|null $existing
     * @param bool $is_admin
     */
    function eottae_report_normalize_write_post(array $post, $existing = null, $is_admin = false, $is_update = false)
    {
        $out = array();

        $out['wr_1'] = eottae_report_normalize_type($post['wr_1'] ?? 'other');
        $out['wr_2'] = eottae_report_normalize_region($post['wr_2'] ?? '');
        $out['wr_3'] = !empty($post['wr_3']) && (string) $post['wr_3'] === '1' ? '1' : '0';
        $out['wr_4'] = !empty($post['wr_4']) && (string) $post['wr_4'] === '1' ? '1' : '0';

        $contact = '';
        if ($out['wr_4'] === '1') {
            $contact = trim(strip_tags((string) ($post['wr_5'] ?? '')));
            if (function_exists('cut_str') && $contact !== '') {
                $contact = cut_str($contact, 120, '');
            }
        }
        $out['wr_5'] = $contact;

        $shop = trim(strip_tags((string) ($post['wr_6'] ?? '')));
        if (function_exists('cut_str') && $shop !== '') {
            $shop = cut_str($shop, 120, '');
        }
        $out['wr_6'] = $shop;

        $link = trim((string) ($post['wr_7'] ?? ''));
        if ($link !== '' && function_exists('cut_str')) {
            $link = cut_str($link, 255, '');
        }
        $out['wr_7'] = $link;

        if ($is_admin) {
            $out['wr_8'] = eottae_report_normalize_status($post['wr_8'] ?? ($existing['wr_8'] ?? 'received'));
            $memo = trim(strip_tags((string) ($post['wr_9'] ?? '')));
            if (function_exists('cut_str') && $memo !== '') {
                $memo = cut_str($memo, 500, '');
            }
            $out['wr_9'] = $memo;
        } elseif ($is_update && is_array($existing)) {
            $out['wr_8'] = eottae_report_normalize_status($existing['wr_8'] ?? 'received');
            $out['wr_9'] = (string) ($existing['wr_9'] ?? '');
        } else {
            $out['wr_8'] = 'received';
            $out['wr_9'] = '';
        }

        $out['wr_10'] = '';
        if ($is_admin && isset($post['wr_10'])) {
            $out['wr_10'] = trim(strip_tags((string) $post['wr_10']));
        } elseif ($is_update && is_array($existing)) {
            $out['wr_10'] = (string) ($existing['wr_10'] ?? '');
        }

        if (!$is_update) {
            $html = isset($post['html']) ? $post['html'] : '';
            if ($html === '' || $html === 'html2') {
                $out['html'] = 'html2';
            }
        }

        return $out;
    }
}

if (!function_exists('eottae_report_apply_write_post')) {
    function eottae_report_apply_write_post(array $normalized)
    {
        foreach ($normalized as $key => $value) {
            if ($value === null) {
                continue;
            }
            $_POST[$key] = $value;
        }
    }
}

if (!function_exists('eottae_report_get_existing_write')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_report_get_existing_write($bo_table, $wr_id)
    {
        global $g5;

        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return null;
        }

        $write_table = $g5['write_prefix'].preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '{$wr_id}' AND wr_is_comment = 0 LIMIT 1 ");
    }
}

if (!function_exists('eottae_report_is_board_admin')) {
    function eottae_report_is_board_admin($is_admin = '')
    {
        return $is_admin === 'super' || $is_admin === 'group' || $is_admin === 'board';
    }
}

if (!function_exists('eottae_report_can_view_contact')) {
    function eottae_report_can_view_contact($is_admin = '')
    {
        return eottae_report_is_board_admin($is_admin);
    }
}

if (!function_exists('eottae_report_prepare_list_context')) {
    /**
     * 목록 SQL 필터용 전역 컨텍스트 (board_head_before에서 호출)
     */
    function eottae_report_prepare_list_context($board)
    {
        global $is_admin;

        if (empty($board['bo_table']) || !eottae_is_report_board($board['bo_table'])) {
            return;
        }

        $GLOBALS['eottae_report_list_prepared'] = true;
        $GLOBALS['eottae_report_list_is_admin'] = eottae_report_is_board_admin($is_admin);

        $rs = isset($_GET['rs']) ? preg_replace('/[^a-z]/', '', (string) $_GET['rs']) : '';
        if ($rs === 'all') {
            $rs = '';
        }
        if ($rs !== '' && !array_key_exists($rs, eottae_report_statuses())) {
            $rs = '';
        }
        if (empty($GLOBALS['eottae_report_list_is_admin'])) {
            $rs = '';
        }

        $GLOBALS['eottae_report_list_rs'] = $rs;
    }
}

if (!function_exists('eottae_report_list_segment_sql')) {
    /**
     * 제보함 목록 노출 정책
     *
     * - 관리자: 모든 상태 (rs 파라미터로 필터, 없으면 전체)
     * - 작성자 본인: 본인 글은 모든 상태, 타인 글은 published 만
     * - 일반 사용자/비회원: published 만
     *
     * 운영 초기에는 관리자 확인용으로 쓰고, 공개 목록은 published 중심으로 운영하는 것을 권장합니다.
     */
    function eottae_report_list_segment_sql()
    {
        if (empty($GLOBALS['eottae_report_list_prepared'])) {
            return '';
        }

        global $is_member, $member;

        $is_admin = !empty($GLOBALS['eottae_report_list_is_admin']);
        if ($is_admin) {
            $filter = (string) ($GLOBALS['eottae_report_list_rs'] ?? '');
            if ($filter !== '') {
                return " and wr_8 = '".sql_escape_string($filter)."' ";
            }

            return '';
        }

        $published = "wr_8 = 'published'";
        $mb_id = !empty($is_member) && !empty($member['mb_id'])
            ? sql_escape_string((string) $member['mb_id'])
            : '';

        if ($mb_id !== '') {
            return " and ({$published} or mb_id = '{$mb_id}') ";
        }

        return " and {$published} ";
    }
}

if (!function_exists('eottae_report_list_segment_total_count')) {
    function eottae_report_list_segment_total_count($write_table)
    {
        if (empty($GLOBALS['eottae_report_list_prepared'])) {
            return null;
        }

        $sql = eottae_report_list_segment_sql();
        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$write_table}` WHERE wr_is_comment = 0 {$sql} ");

        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_report_assert_can_read')) {
    function eottae_report_assert_can_read(array $write)
    {
        global $is_member, $member, $is_admin;

        if (eottae_report_is_board_admin($is_admin)) {
            return;
        }

        $status = eottae_report_normalize_status($write['wr_8'] ?? 'received');
        if ($status === 'published') {
            return;
        }

        $mb_id = !empty($is_member) && !empty($member['mb_id']) ? (string) $member['mb_id'] : '';
        if ($mb_id !== '' && $mb_id === (string) ($write['mb_id'] ?? '')) {
            return;
        }

        $list_url = function_exists('eottae_board_list_url')
            ? eottae_board_list_url(eottae_report_board_table())
            : G5_BBS_URL.'/board.php?bo_table='.eottae_report_board_table();

        alert('열람 권한이 없는 제보입니다.', $list_url);
    }
}

if (!function_exists('eottae_report_status_change_message')) {
    function eottae_report_status_change_message($status)
    {
        $status = eottae_report_normalize_status($status);
        $messages = array(
            'received'  => '제보 상태가 접수됨으로 변경되었습니다.',
            'checking'  => '제보 상태가 확인중으로 변경되었습니다.',
            'published' => '제보가 공개 처리되었습니다.',
            'rejected'  => '제보가 반려 처리되었습니다.',
        );

        return $messages[$status] ?? '제보 상태가 저장되었습니다.';
    }
}

if (!function_exists('eottae_report_set_flash')) {
    function eottae_report_set_flash($message)
    {
        set_session('eottae_report_flash', (string) $message);
    }
}

if (!function_exists('eottae_report_get_flash')) {
    function eottae_report_get_flash()
    {
        $message = get_session('eottae_report_flash');
        set_session('eottae_report_flash', '');

        return trim((string) $message);
    }
}

if (!function_exists('eottae_report_maybe_award_points')) {
    /**
     * TODO: 포인트 지급 연동
     *
     * 추천 정책:
     * - 제보 접수(received): 10포인트
     * - 제보 공개 채택(published): 100포인트
     * - 사진 포함 제보 채택(published + 첨부): 150포인트
     * - 중요 생활정보 채택(published + wr_1=life): 300포인트
     *
     * published 로 최초 전환될 때 insert_point() 등으로 지급하세요.
     */
    function eottae_report_maybe_award_points(array $row, $old_status, $new_status)
    {
        $old_status = eottae_report_normalize_status($old_status);
        $new_status = eottae_report_normalize_status($new_status);

        if ($new_status !== 'published' || $old_status === 'published') {
            return;
        }

        $mb_id = trim((string) ($row['mb_id'] ?? ''));
        if ($mb_id === '') {
            return;
        }

        // insert_point($mb_id, 100, '제보 공개 채택', eottae_report_board_table(), $row['wr_id'], '제보채택');
    }
}

if (!function_exists('eottae_report_on_status_changed')) {
    function eottae_report_on_status_changed(array $row, $old_status, $new_status)
    {
        eottae_report_maybe_award_points($row, $old_status, $new_status);
    }
}

if (!function_exists('eottae_report_set_status')) {
    /**
     * @return array{ok:bool, message:string, status?:string, label?:string}
     */
    function eottae_report_set_status($bo_table, $wr_id, $status, $memo, $is_admin_user)
    {
        if (!$is_admin_user) {
            return array('ok' => false, 'message' => '관리자만 상태를 변경할 수 있습니다.');
        }

        if (!eottae_is_report_board($bo_table)) {
            return array('ok' => false, 'message' => '제보함 게시판이 아닙니다.');
        }

        global $g5;

        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '잘못된 게시글입니다.');
        }

        $row = eottae_report_get_existing_write($bo_table, $wr_id);
        if (!$row) {
            return array('ok' => false, 'message' => '제보를 찾을 수 없습니다.');
        }

        $old_status = eottae_report_normalize_status($row['wr_8'] ?? 'received');
        $new_status = eottae_report_normalize_status($status);
        $memo = trim(strip_tags((string) $memo));
        if (function_exists('cut_str') && $memo !== '') {
            $memo = cut_str($memo, 500, '');
        }

        $write_table = $g5['write_prefix'].preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        sql_query("
            UPDATE `{$write_table}`
            SET wr_8 = '".sql_escape_string($new_status)."',
                wr_9 = '".sql_escape_string($memo)."'
            WHERE wr_id = '{$wr_id}' AND wr_is_comment = 0
        ");

        $row['wr_8'] = $new_status;
        $row['wr_9'] = $memo;
        eottae_report_on_status_changed($row, $old_status, $new_status);

        $meta = eottae_report_status_meta($new_status);

        return array(
            'ok'      => true,
            'message' => eottae_report_status_change_message($new_status),
            'status'  => $new_status,
            'label'   => $meta['label'],
        );
    }
}

if (!function_exists('eottae_report_convert_targets')) {
    /**
     * @return array<string, string>
     */
    function eottae_report_convert_targets()
    {
        return array(
            'community' => '생활정보',
            'event'     => '이벤트/프로모션',
            'review'    => '업체리뷰',
            'people'    => '사람찾기',
            'free'      => '자유게시판',
        );
    }
}

if (!function_exists('eottae_report_parse_converted_ref')) {
    /**
     * @return array{bo_table:string, wr_id:int}
     */
    function eottae_report_parse_converted_ref($ref)
    {
        $ref = trim((string) $ref);
        if ($ref === '' || strpos($ref, ':') === false) {
            return array('bo_table' => '', 'wr_id' => 0);
        }

        $parts = explode(':', $ref, 2);
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) ($parts[0] ?? ''));
        $wr_id = (int) ($parts[1] ?? 0);

        return array('bo_table' => $bo_table, 'wr_id' => $wr_id);
    }
}

if (!function_exists('eottae_report_format_converted_ref')) {
    function eottae_report_format_converted_ref($bo_table, $wr_id)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return '';
        }

        return $bo_table.':'.$wr_id;
    }
}

if (!function_exists('eottae_report_build_convert_content')) {
    function eottae_report_build_convert_content(array $row)
    {
        $body = trim(strip_tags((string) ($row['wr_content'] ?? '')));
        $lines = array($body, '', '---', '[세부 제보함에서 전환]');
        $lines[] = '제보 유형: '.eottae_report_type_label($row['wr_1'] ?? 'other');
        $lines[] = '지역: '.eottae_report_region_label($row['wr_2'] ?? '');
        if (trim((string) ($row['wr_6'] ?? '')) !== '') {
            $lines[] = '관련 업체: '.trim(strip_tags((string) $row['wr_6']));
        }
        if (trim((string) ($row['wr_7'] ?? '')) !== '') {
            $lines[] = '관련 링크: '.trim((string) $row['wr_7']);
        }
        $lines[] = '원본 제보 #'.(int) ($row['wr_id'] ?? 0);

        return implode("\n", $lines);
    }
}

if (!function_exists('eottae_report_copy_to_board')) {
    /**
     * @return array{ok:bool, message:string, target_bo_table?:string, target_wr_id?:int, view_url?:string}
     */
    function eottae_report_copy_to_board($report_bo_table, $report_wr_id, $target_bo_table, $is_admin_user)
    {
        if (!$is_admin_user) {
            return array('ok' => false, 'message' => '관리자만 전환할 수 있습니다.');
        }

        if (!eottae_is_report_board($report_bo_table)) {
            return array('ok' => false, 'message' => '제보함 게시판이 아닙니다.');
        }

        $targets = eottae_report_convert_targets();
        $target_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $target_bo_table);
        if ($target_bo_table === '' || !array_key_exists($target_bo_table, $targets)) {
            return array('ok' => false, 'message' => '전환할 게시판을 선택해 주세요.');
        }

        global $g5, $member;

        $report_wr_id = (int) $report_wr_id;
        $report_row = eottae_report_get_existing_write($report_bo_table, $report_wr_id);
        if (!$report_row) {
            return array('ok' => false, 'message' => '제보를 찾을 수 없습니다.');
        }

        $existing_ref = eottae_report_parse_converted_ref($report_row['wr_10'] ?? '');
        if ($existing_ref['bo_table'] !== '' && $existing_ref['wr_id'] > 0) {
            return array(
                'ok'      => false,
                'message' => '이미 다른 게시판으로 전환된 제보입니다.',
            );
        }

        $target_board = get_board_db($target_bo_table, true);
        if (!$target_board) {
            return array('ok' => false, 'message' => '대상 게시판을 찾을 수 없습니다.');
        }

        $target_write_table = $g5['write_prefix'].$target_bo_table;
        $subject = sql_escape_string(trim(strip_tags((string) ($report_row['wr_subject'] ?? ''))));
        if ($subject === '') {
            return array('ok' => false, 'message' => '제목이 없어 전환할 수 없습니다.');
        }

        $content_plain = eottae_report_build_convert_content($report_row);
        $content = sql_escape_string(nl2br(clean_xss_tags($content_plain)));

        $mb_id = sql_escape_string((string) ($member['mb_id'] ?? ''));
        $wr_name = sql_escape_string((string) ($member['mb_nick'] ?? $member['mb_name'] ?? '관리자'));
        $wr_email = sql_escape_string((string) ($member['mb_email'] ?? ''));
        $wr_homepage = sql_escape_string((string) ($member['mb_homepage'] ?? ''));
        $wr_ip = sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '');
        $now = G5_TIME_YMDHIS;

        sql_query("
            INSERT INTO `{$target_write_table}` SET
                wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM `{$target_write_table}` AS sq),
                wr_reply = '',
                wr_comment = 0,
                ca_name = '',
                wr_option = 'html2',
                wr_subject = '{$subject}',
                wr_content = '{$content}',
                wr_link1 = '',
                wr_link2 = '',
                wr_link1_hit = 0,
                wr_link2_hit = 0,
                wr_hit = 0,
                wr_good = 0,
                wr_nogood = 0,
                mb_id = '{$mb_id}',
                wr_password = '',
                wr_name = '{$wr_name}',
                wr_email = '{$wr_email}',
                wr_homepage = '{$wr_homepage}',
                wr_datetime = '{$now}',
                wr_last = '{$now}',
                wr_ip = '{$wr_ip}'
        ");

        $new_wr_id = (int) sql_insert_id();
        if ($new_wr_id < 1) {
            return array('ok' => false, 'message' => '게시글 복사에 실패했습니다.');
        }

        sql_query(" UPDATE `{$target_write_table}` SET wr_parent = '{$new_wr_id}' WHERE wr_id = '{$new_wr_id}' ");
        sql_query(" UPDATE `{$g5['board_table']}` SET bo_count_write = bo_count_write + 1 WHERE bo_table = '".sql_escape_string($target_bo_table)."' ");

        $ref = eottae_report_format_converted_ref($target_bo_table, $new_wr_id);
        $report_write_table = $g5['write_prefix'].preg_replace('/[^a-z0-9_]/', '', (string) $report_bo_table);
        sql_query("
            UPDATE `{$report_write_table}`
            SET wr_10 = '".sql_escape_string($ref)."'
            WHERE wr_id = '{$report_wr_id}' AND wr_is_comment = 0
        ");

        $view_url = function_exists('get_pretty_url')
            ? get_pretty_url($target_bo_table, $new_wr_id)
            : G5_BBS_URL.'/board.php?bo_table='.$target_bo_table.'&wr_id='.$new_wr_id;

        return array(
            'ok'              => true,
            'message'         => $targets[$target_bo_table].' 게시판에 글이 복사되었습니다.',
            'target_bo_table' => $target_bo_table,
            'target_wr_id'    => $new_wr_id,
            'view_url'        => $view_url,
        );
    }
}

if (!function_exists('eottae_report_list_filter_tabs')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_report_list_filter_tabs($bo_table, $is_admin_user)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $current = (string) ($GLOBALS['eottae_report_list_rs'] ?? '');
        $base = function_exists('eottae_board_list_url')
            ? eottae_board_list_url($bo_table)
            : G5_BBS_URL.'/board.php?bo_table='.$bo_table;

        $tabs = array();
        if ($is_admin_user) {
            $tabs[] = array('key' => '', 'label' => '전체', 'href' => $base, 'active' => $current === '');
            foreach (eottae_report_statuses() as $key => $label) {
                $tabs[] = array(
                    'key'    => $key,
                    'label'  => $label,
                    'href'   => $base.(strpos($base, '?') !== false ? '&' : '?').'rs='.$key,
                    'active' => $current === $key,
                );
            }
        }

        return $tabs;
    }
}
