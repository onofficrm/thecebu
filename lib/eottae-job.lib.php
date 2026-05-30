<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('format_location_display') && is_file(G5_LIB_PATH.'/eottae-location.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-location.lib.php';
}

if (!function_exists('eottae_job_recruit_statuses')) {
    /**
     * @return array<string, string>
     */
    function eottae_job_recruit_statuses()
    {
        return array(
            'recruiting' => '모집중',
            'completed'  => '모집완료',
        );
    }
}

if (!function_exists('eottae_job_normalize_recruit_status')) {
    function eottae_job_normalize_recruit_status($status)
    {
        $status = preg_replace('/[^a-z]/', '', (string) $status);

        return array_key_exists($status, eottae_job_recruit_statuses()) ? $status : 'recruiting';
    }
}

if (!function_exists('eottae_job_recruit_status_meta')) {
    /**
     * @return array{key:string, label:string, class:string}
     */
    function eottae_job_recruit_status_meta($status)
    {
        $status = eottae_job_normalize_recruit_status($status);
        $labels = eottae_job_recruit_statuses();

        return array(
            'key'   => $status,
            'label' => $labels[$status],
            'class' => 'job-recruit-badge--'.$status,
        );
    }
}

if (!function_exists('eottae_job_recruit_status_from_row')) {
    function eottae_job_recruit_status_from_row($row)
    {
        if (!is_array($row)) {
            return 'recruiting';
        }

        return eottae_job_normalize_recruit_status($row['wr_2'] ?? '');
    }
}

if (!function_exists('eottae_job_can_change_recruit_status')) {
    function eottae_job_can_change_recruit_status(array $write, $mb_id, $is_super_admin = false)
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

if (!function_exists('eottae_job_format_shop_ref')) {
    function eottae_job_format_shop_ref($bo_table, $wr_id)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return '';
        }

        return $bo_table.':'.$wr_id;
    }
}

if (!function_exists('eottae_job_parse_shop_ref')) {
    function eottae_job_parse_shop_ref($ref)
    {
        $ref = trim((string) $ref);
        if ($ref === '') {
            return array('bo_table' => '', 'wr_id' => 0);
        }

        if (strpos($ref, ':') !== false) {
            list($bo_table, $wr_id) = explode(':', $ref, 2);
        } else {
            $bo_table = function_exists('eottae_shop_table') ? eottae_shop_table() : 'shop';
            $wr_id = $ref;
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;

        return array('bo_table' => $bo_table, 'wr_id' => $wr_id);
    }
}

if (!function_exists('eottae_job_fetch_shop')) {
    function eottae_job_fetch_shop($shop_wr_id, $shop_bo_table = '')
    {
        if (!function_exists('eottae_review_board_fetch_shop') && is_file(G5_LIB_PATH.'/eottae-review-board.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-review-board.lib.php';
        }

        if (!function_exists('eottae_review_board_fetch_shop')) {
            return null;
        }

        return eottae_review_board_fetch_shop((int) $shop_wr_id, $shop_bo_table);
    }
}

if (!function_exists('eottae_job_shop_from_row')) {
    function eottae_job_shop_from_row($row)
    {
        if (!is_array($row)) {
            return null;
        }

        $parsed = eottae_job_parse_shop_ref($row['wr_8'] ?? '');
        if ((int) ($parsed['wr_id'] ?? 0) < 1) {
            return null;
        }

        return eottae_job_fetch_shop($parsed['wr_id'], $parsed['bo_table']);
    }
}

if (!function_exists('eottae_job_shop_apply_to_post')) {
    function eottae_job_shop_apply_to_post()
    {
        $shop_wr_id = isset($_POST['eottae_job_shop_wr_id']) ? (int) $_POST['eottae_job_shop_wr_id'] : 0;
        $shop_bo = isset($_POST['eottae_job_shop_bo_table'])
            ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['eottae_job_shop_bo_table'])
            : '';

        if ($shop_wr_id < 1 && !empty($_POST['wr_8'])) {
            $parsed = eottae_job_parse_shop_ref($_POST['wr_8']);
            $shop_wr_id = (int) ($parsed['wr_id'] ?? 0);
            $shop_bo = (string) ($parsed['bo_table'] ?? $shop_bo);
        }

        if ($shop_wr_id < 1) {
            $_POST['wr_8'] = '';
            return;
        }

        $shop = eottae_job_fetch_shop($shop_wr_id, $shop_bo);
        if (!$shop) {
            $_POST['wr_8'] = '';
            return;
        }

        $shop_bo = (string) ($shop['bo_table'] ?? $shop_bo);
        $_POST['wr_8'] = eottae_job_format_shop_ref($shop_bo, (int) ($shop['wr_id'] ?? $shop_wr_id));
    }
}

if (!function_exists('eottae_job_render_recruit_badge')) {
    function eottae_job_render_recruit_badge($status, $extra_class = '')
    {
        $meta = eottae_job_recruit_status_meta($status);
        $class = 'job-recruit-badge '.$meta['class'];
        if ($extra_class !== '') {
            $class .= ' '.trim($extra_class);
        }

        return '<span class="'.$class.'">'.get_text($meta['label']).'</span>';
    }
}

if (!function_exists('eottae_job_render_list_thumb')) {
    /**
     * 구인구직 목록 썸네일 — 첨부/본문 이미지 또는 작성자 프로필 + 모집상태·활동뱃지
     *
     * @param array<string, mixed> $item
     */
    function eottae_job_render_list_thumb($item, $post_thumb_url = '', $options = array())
    {
        if (!function_exists('eottae_estate_member_thumb_url')) {
            include_once G5_LIB_PATH.'/eottae-estate.lib.php';
        }

        $options = is_array($options) ? $options : array();
        $is_view_profile = !empty($options['view_profile']);

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($item['mb_id'] ?? ''));
        $author = strip_tags((string) ($item['name'] ?? ($item['wr_name'] ?? '')));
        $recruit_status = eottae_job_recruit_status_from_row($item);
        $recruit_badge = $is_view_profile
            ? ''
            : eottae_job_render_recruit_badge($recruit_status, 'job-recruit-badge--thumb');

        $profile_badge_html = '';
        if ($mb_id !== '' && is_file(G5_PATH.'/components/eottae/member-growth-display.php')) {
            include_once G5_PATH.'/components/eottae/member-growth-display.php';
            if (function_exists('eottae_member_growth_get_profile') && function_exists('eottae_member_growth_render_profile_badge_icon')) {
                $profile = eottae_member_growth_get_profile($mb_id);
                $profile_badge_html = eottae_member_growth_render_profile_badge_icon($profile, array(
                    'href'  => '',
                    'class' => 'job-profile-thumb__badge-icon',
                ));
            }
        }

        $post_thumb_url = trim((string) $post_thumb_url);
        $member_thumb_url = $mb_id !== '' ? eottae_estate_member_thumb_url($mb_id) : '';
        $use_profile = ($post_thumb_url === '');

        $outer_class = $is_view_profile
            ? 'job-profile-thumb job-profile-thumb--view'
            : 'community-post__thumb job-profile-thumb';

        $initial = function_exists('eottae_estate_member_initial')
            ? eottae_estate_member_initial($author)
            : '?';

        ob_start();
        ?>
        <div class="<?php echo $outer_class; ?>"<?php echo $is_view_profile ? '' : ' aria-hidden="true"'; ?>>
            <div class="job-profile-thumb__media<?php echo $use_profile ? ' job-profile-thumb__media--profile' : ''; ?>">
                <?php if ($use_profile) { ?>
                    <?php if ($member_thumb_url !== '') { ?>
                <img src="<?php echo htmlspecialchars($member_thumb_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="job-profile-thumb__img" width="104" height="104" loading="lazy" decoding="async">
                    <?php } else { ?>
                <span class="job-profile-thumb__initial" aria-hidden="true"><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php } ?>
                <?php } else { ?>
                <img src="<?php echo htmlspecialchars($post_thumb_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="job-profile-thumb__img" width="104" height="104" loading="lazy" decoding="async">
                <?php } ?>
                <?php echo $recruit_badge; ?>
                <?php if ($profile_badge_html !== '') { ?>
                <span class="job-profile-thumb__badge" aria-hidden="true"><?php echo $profile_badge_html; ?></span>
                <?php } ?>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_job_list_snippet')) {
    /**
     * 구인구직 목록 요약 — 템플릿 핵심 정보 우선, 제목과 동일한 본문은 생략
     */
    function eottae_job_list_snippet($row, $subject = '', $len = 110)
    {
        $subject = trim(strip_tags((string) $subject));
        $len = max(40, (int) $len);

        $data = eottae_job_template_from_row($row);
        if (is_array($data)) {
            $parts = array();

            if (!empty($data['company'])) {
                $parts[] = get_text($data['company']);
            }
            if (!empty($data['job_type'])) {
                $parts[] = get_text($data['job_type']);
            }
            if (!empty($data['region'])) {
                $parts[] = get_text($data['region']);
            }

            $salary = trim((string) ($data['salary'] ?? ''));
            if ($salary !== '') {
                $pay_label = eottae_job_template_label('pay_type', $data['pay_type'] ?? '');
                $parts[] = $pay_label !== '' ? $pay_label.' '.$salary : $salary;
            } elseif (!empty($data['work_type'])) {
                $work_label = eottae_job_template_label('work_type', $data['work_type']);
                if ($work_label !== '') {
                    $parts[] = $work_label;
                }
            }

            if ($parts) {
                $line = implode(' · ', array_values(array_unique($parts)));
                if ($subject === '' || $line !== $subject) {
                    return function_exists('cut_str') ? cut_str($line, $len, '…') : $line;
                }
            }

            if (!empty($data['work_desc'])) {
                $desc = trim(strip_tags($data['work_desc']));
                if ($desc !== '' && $desc !== $subject) {
                    return function_exists('cut_str') ? cut_str($desc, $len, '…') : $desc;
                }
            }
        }

        if (!function_exists('eottae_community_snippet')) {
            return '';
        }

        $fallback = eottae_community_snippet(isset($row['wr_content']) ? $row['wr_content'] : '', $len);
        $fallback = trim(strip_tags($fallback));

        if ($fallback === '' || ($subject !== '' && $fallback === $subject)) {
            return '';
        }

        return $fallback;
    }
}

if (!function_exists('eottae_job_location_from_row')) {
    /**
     * @return array{auto_area:string, area_label:string, location_text:string, latitude:string, longitude:string, map_visible:bool, display:string}
     */
    function eottae_job_location_from_row($row)
    {
        if (!is_array($row)) {
            $row = array();
        }

        $auto_area = isset($row['wr_1']) ? (string) $row['wr_1'] : '';
        $location_text = trim(strip_tags((string) ($row['wr_4'] ?? '')));
        $lat = trim((string) ($row['wr_5'] ?? ''));
        $lng = trim((string) ($row['wr_6'] ?? ''));
        $map_visible = (string) ($row['wr_7'] ?? '1') !== '0';

        if ($location_text === '') {
            $tpl = function_exists('eottae_job_template_from_row') ? eottae_job_template_from_row($row) : null;
            if (is_array($tpl) && !empty($tpl['region'])) {
                $location_text = trim(strip_tags((string) $tpl['region']));
            }
        }

        $area_key = function_exists('eottae_location_normalize_area')
            ? eottae_location_normalize_area($auto_area)
            : '';
        if ($area_key === '' || $area_key === 'other') {
            $area_key = function_exists('eottae_location_auto_area')
                ? eottae_location_auto_area($location_text ?: $auto_area, $lat, $lng)
                : 'other';
        }

        $area_label = function_exists('eottae_location_area_label')
            ? eottae_location_area_label($area_key)
            : ($auto_area !== '' ? $auto_area : '기타');
        $display = function_exists('format_location_display')
            ? format_location_display(array('auto_area' => $area_key, 'location_text' => $location_text))
            : trim($area_label.' · '.$location_text, ' ·');

        return array(
            'auto_area'     => $area_key,
            'area_label'    => $area_label,
            'location_text' => $location_text,
            'latitude'      => $lat,
            'longitude'     => $lng,
            'map_visible'   => $map_visible,
            'display'       => $display,
        );
    }
}

if (!function_exists('eottae_job_map_marker_from_row')) {
    /**
     * 세부생활지도 연결용 마커 데이터 구조
     *
     * @return array<string, mixed>|null
     */
    function eottae_job_map_marker_from_row($row, $bo_table = '')
    {
        if (!is_array($row)) {
            return null;
        }

        $loc = eottae_job_location_from_row($row);
        if ($loc['latitude'] === '' || $loc['longitude'] === '' || !is_numeric($loc['latitude']) || !is_numeric($loc['longitude'])) {
            return null;
        }

        $tpl = eottae_job_template_from_row($row);
        $salary = is_array($tpl) ? trim((string) ($tpl['salary'] ?? '')) : '';
        $bo_table = $bo_table !== '' ? $bo_table : (function_exists('eottae_job_board_table') ? eottae_job_board_table() : 'job');

        return array(
            'type'      => 'job',
            'type_label'=> '구인구직',
            'wr_id'     => (int) ($row['wr_id'] ?? 0),
            'title'     => get_text($row['wr_subject'] ?? ''),
            'location'  => $loc['display'],
            'area'      => $loc['area_label'],
            'lat'       => (float) $loc['latitude'],
            'lng'       => (float) $loc['longitude'],
            'summary'   => $salary,
            'url'       => function_exists('get_pretty_url')
                ? get_pretty_url($bo_table, (int) ($row['wr_id'] ?? 0))
                : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.(int) ($row['wr_id'] ?? 0),
        );
    }
}

if (!function_exists('eottae_job_set_recruit_status')) {
    /**
     * @return array{ok:bool, message:string, status?:string, label?:string}
     */
    function eottae_job_set_recruit_status($bo_table, $wr_id, $status, $mb_id, $is_super_admin = false)
    {
        if (!function_exists('eottae_is_job_board') || !eottae_is_job_board($bo_table)) {
            return array('ok' => false, 'message' => '구인구직 게시판이 아닙니다.');
        }

        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '글 정보가 올바르지 않습니다.');
        }

        global $g5;

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '{$wr_id}' AND wr_is_comment = 0 LIMIT 1 ");
        if (!$write) {
            return array('ok' => false, 'message' => '게시글을 찾을 수 없습니다.');
        }

        if (!eottae_job_can_change_recruit_status($write, $mb_id, $is_super_admin)) {
            return array('ok' => false, 'message' => '모집 상태를 변경할 권한이 없습니다.');
        }

        $status = eottae_job_normalize_recruit_status($status);
        $status_sql = sql_escape_string($status);
        sql_query(" UPDATE `{$write_table}` SET wr_2 = '{$status_sql}' WHERE wr_id = '{$wr_id}' ", false);

        $meta = eottae_job_recruit_status_meta($status);

        return array(
            'ok'      => true,
            'message' => '모집 상태가 «'.$meta['label'].'»(으)로 변경되었습니다.',
            'status'  => $status,
            'label'   => $meta['label'],
        );
    }
}

if (!function_exists('eottae_job_template_option_labels')) {
    /**
     * @return array<string, array<string, string>>
     */
    function eottae_job_template_option_labels()
    {
        return array(
            'work_type' => array(
                'fulltime'  => '정규직',
                'contract'  => '계약직',
                'parttime'  => '파트타임',
                'part'      => '아르바이트',
                'freelance' => '프리랜서',
                'other'     => '기타',
            ),
            'pay_type' => array(
                'month' => '월급',
                'week'  => '주급',
                'day'   => '일급',
                'hour'  => '시급',
                'nego'  => '협의',
            ),
            'gender' => array(
                'any'    => '무관',
                'male'   => '남성',
                'female' => '여성',
            ),
            'career' => array(
                'any'      => '무관',
                'new'      => '신입',
                'prefer'   => '경력자 우대',
                'required' => '경력 필수',
            ),
            'language' => array(
                'any'   => '무관',
                'ko'    => '한국어',
                'en'    => '영어',
                'ceb'   => '세부아노',
                'tl'    => '타갈로그어',
                'ko_en' => '한국어+영어',
                'other' => '기타',
            ),
        );
    }
}

if (!function_exists('eottae_job_template_label')) {
    function eottae_job_template_label($group, $key)
    {
        $maps = eottae_job_template_option_labels();
        $key = (string) $key;
        if ($key === '' || $key === 'any' || !isset($maps[$group][$key])) {
            return '';
        }

        return $maps[$group][$key];
    }
}

if (!function_exists('eottae_job_template_normalize_data')) {
    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    function eottae_job_template_normalize_data($data)
    {
        if (!is_array($data)) {
            $data = array();
        }

        $keys = array(
            'company', 'job_type', 'headcount', 'region', 'job_recruit_status',
            'work_type', 'work_hours', 'salary', 'pay_type',
            'work_desc', 'qualification', 'age', 'gender', 'career', 'language',
            'benefits', 'preferred', 'apply_method', 'contact', 'kakao_id', 'email', 'deadline', 'extra',
        );

        $out = array();
        foreach ($keys as $key) {
            $out[$key] = trim(strip_tags((string) ($data[$key] ?? '')));
        }

        if (function_exists('eottae_job_template_repair_merged_values')) {
            $out = eottae_job_template_repair_merged_values($out);
        }

        $out['job_recruit_status'] = eottae_job_normalize_recruit_status($out['job_recruit_status'] ?: 'recruiting');

        return $out;
    }
}

if (!function_exists('eottae_job_template_kv_labels')) {
    /**
     * @return array<int, string>
     */
    function eottae_job_template_kv_labels()
    {
        return array(
            '업체명', '모집직종', '모집인원', '근무지역', '근무형태', '근무시간', '급여', '급여형태',
            '나이', '성별', '경력', '언어조건',
            '지원방법', '연락처', '카카오톡 ID', '이메일',
        );
    }
}

if (!function_exists('eottae_job_template_strip_embedded_labels')) {
    function eottae_job_template_strip_embedded_labels($value, ?array $labels = null)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (!is_array($labels)) {
            $labels = eottae_job_template_kv_labels();
        }

        $cut_at = null;
        foreach ($labels as $label) {
            if (preg_match('/'.preg_quote($label, '/').'\s*:/u', $value, $match, PREG_OFFSET_CAPTURE)) {
                $pos = (int) $match[0][1];
                if ($pos > 0 && ($cut_at === null || $pos < $cut_at)) {
                    $cut_at = $pos;
                }
            }
        }

        if ($cut_at !== null) {
            $value = trim(substr($value, 0, $cut_at));
        }

        return $value;
    }
}

if (!function_exists('eottae_job_template_repair_merged_values')) {
    /**
     * 한 줄로 붙어 저장된 필드값에서 뒤쪽 라벨 덩어리 제거
     *
     * @param array<string, string> $data
     * @return array<string, string>
     */
    function eottae_job_template_repair_merged_values(array $data)
    {
        $labels = eottae_job_template_kv_labels();
        foreach ($data as $key => $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }
            $data[$key] = eottae_job_template_strip_embedded_labels($value, $labels);
        }

        return $data;
    }
}

if (!function_exists('eottae_job_template_format_display_text')) {
    function eottae_job_template_format_display_text($text)
    {
        $text = trim(strip_tags((string) $text));
        if ($text === '') {
            return '';
        }

        $text = preg_replace("/\r\n|\r/u", "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/\.[ \t]+/u', ".\n", $text);
        $text = preg_replace('/\n{3,}/u', "\n\n", $text);

        return trim($text);
    }
}

if (!function_exists('eottae_job_template_decode_json')) {
    /**
     * @return array<string, string>|null
     */
    function eottae_job_template_decode_json($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '' || $raw[0] !== '{') {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return null;
        }

        return eottae_job_template_normalize_data($data);
    }
}

if (!function_exists('eottae_job_template_encode_json')) {
    function eottae_job_template_encode_json(array $data)
    {
        $data = eottae_job_template_normalize_data($data);
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return '';
        }

        if (function_exists('cut_str')) {
            return cut_str($json, 65000, '');
        }

        return $json;
    }
}

if (!function_exists('eottae_job_template_from_row')) {
    /**
     * wr_3 JSON → 템플릿 데이터 (없으면 본문 파싱 시도)
     *
     * @return array<string, string>|null
     */
    function eottae_job_template_from_row($row)
    {
        if (!is_array($row)) {
            return null;
        }

        $data = eottae_job_template_decode_json($row['wr_3'] ?? '');
        if ($data !== null && eottae_job_template_has_core_fields($data)) {
            if ($data['region'] === '' && !empty($row['wr_1'])) {
                $data['region'] = trim(strip_tags((string) $row['wr_1']));
            }
            if (!empty($row['wr_2'])) {
                $data['job_recruit_status'] = eottae_job_normalize_recruit_status($row['wr_2']);
            }

            return $data;
        }

        $content = isset($row['wr_content']) ? $row['wr_content'] : '';
        $parsed = eottae_job_template_parse_content($content);
        if ($parsed !== null) {
            if ($parsed['region'] === '' && !empty($row['wr_1'])) {
                $parsed['region'] = trim(strip_tags((string) $row['wr_1']));
            }

            return $parsed;
        }

        return null;
    }
}

if (!function_exists('eottae_job_template_has_core_fields')) {
    function eottae_job_template_has_core_fields(array $data)
    {
        foreach (array('company', 'job_type', 'work_desc', 'apply_method', 'contact') as $key) {
            if (!empty($data[$key])) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('eottae_job_template_parse_content')) {
    /**
     * 자동작성 본문([구인정보] 등) 파싱 — 기존 글 호환
     *
     * @return array<string, string>|null
     */
    function eottae_job_template_parse_content($content)
    {
        $text = trim(strip_tags((string) $content));
        if ($text === '' || strpos($text, '[구인정보]') === false) {
            return null;
        }

        $data = eottae_job_template_normalize_data(array());

        if (preg_match('/\[구인정보\](.*?)(?=\[(?:업무내용|지원자격|복리후생|우대사항|지원방법|마감일|기타))/su', $text, $m)) {
            eottae_job_template_parse_kv_block($data, $m[1], array(
                'company'    => '업체명',
                'job_type'   => '모집직종',
                'headcount'  => '모집인원',
                'region'     => '근무지역',
                'work_type'  => '근무형태',
                'work_hours' => '근무시간',
                'salary'     => '급여',
                'pay_type'   => '급여형태',
            ));
        }

        if (preg_match('/\[업무내용\]\s*(.*?)(?=\[(?:지원자격|복리후생|우대사항|지원방법|마감일|기타)|\z)/su', $text, $m)) {
            $data['work_desc'] = trim($m[1]);
        }

        if (preg_match('/\[지원자격\](.*?)(?=\[(?:복리후생|우대사항|지원방법|마감일|기타)|\z)/su', $text, $m)) {
            eottae_job_template_parse_kv_block($data, $m[1], array(
                'age'      => '나이',
                'gender'   => '성별',
                'career'   => '경력',
                'language' => '언어조건',
            ));
            $lines = preg_split('/\r\n|\r|\n/', trim($m[1]));
            $extra = array();
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || preg_match('/^(나이|성별|경력|언어조건)\s*:/u', $line)) {
                    continue;
                }
                $extra[] = $line;
            }
            if ($extra) {
                $data['qualification'] = trim(implode("\n", $extra));
            }
        }

        if (preg_match('/\[복리후생\]\s*(.*?)(?=\[(?:우대사항|지원방법|마감일|기타)|\z)/su', $text, $m)) {
            $data['benefits'] = trim($m[1]);
        }
        if (preg_match('/\[우대사항\]\s*(.*?)(?=\[(?:지원방법|마감일|기타)|\z)/su', $text, $m)) {
            $data['preferred'] = trim($m[1]);
        }
        if (preg_match('/\[지원방법\](.*?)(?=\[(?:마감일|기타)|\z)/su', $text, $m)) {
            eottae_job_template_parse_kv_block($data, $m[1], array(
                'apply_method' => '지원방법',
                'contact'      => '연락처',
                'kakao_id'     => '카카오톡 ID',
                'email'        => '이메일',
            ));
        }
        if (preg_match('/\[마감일\]\s*(.*?)(?=\[기타|\z)/su', $text, $m)) {
            $data['deadline'] = trim($m[1]);
        }
        if (preg_match('/\[기타 안내사항\]\s*(.*)/su', $text, $m)) {
            $data['extra'] = trim($m[1]);
        }

        return eottae_job_template_has_core_fields($data) ? $data : null;
    }
}

if (!function_exists('eottae_job_template_parse_kv_block')) {
    function eottae_job_template_parse_kv_block(array &$data, $block, array $map)
    {
        $labels = array_values($map);

        foreach ($map as $field => $label) {
            $other_labels = array_values(array_filter($labels, function ($candidate) use ($label) {
                return $candidate !== $label;
            }));

            if ($other_labels) {
                $stop_parts = array_map(function ($candidate) {
                    return preg_quote($candidate, '/').'\s*:';
                }, $other_labels);
                $lookahead = '(?='.implode('|', $stop_parts).'|\r?\n|$)';
            } else {
                $lookahead = '(?=\r?\n|$)';
            }

            $pattern = '/'.preg_quote($label, '/').'\s*:\s*(.+?)'.$lookahead.'/us';
            if (preg_match($pattern, $block, $match)) {
                $data[$field] = trim((string) $match[1]);
            }
        }
    }
}

if (!function_exists('eottae_job_template_build_title')) {
    function eottae_job_template_build_title(array $data)
    {
        $data = eottae_job_template_normalize_data($data);
        $region = $data['region'] !== '' ? $data['region'] : '세부';
        $job_type = $data['job_type'] !== '' ? $data['job_type'] : '채용';
        $salary_part = $data['salary'] !== '' ? $data['salary'] : '급여 협의';

        if ($data['pay_type'] === 'nego' && stripos($salary_part, '협의') === false) {
            $salary_part = '급여 협의';
        } elseif ($data['pay_type'] !== '' && $data['pay_type'] !== 'nego') {
            $pay_label = eottae_job_template_label('pay_type', $data['pay_type']);
            if ($pay_label !== '' && stripos($salary_part, $pay_label) === false) {
                $salary_part = $pay_label.' '.$salary_part;
            }
        }

        return '['.$region.'] '.$job_type.' 모집 / '.$salary_part;
    }
}

if (!function_exists('eottae_job_template_build_body')) {
    function eottae_job_template_build_body(array $data)
    {
        $data = eottae_job_template_normalize_data($data);
        $lines = array();

        $info = array(
            '업체명: '.$data['company'],
            '모집직종: '.$data['job_type'],
            '모집인원: '.$data['headcount'],
            '근무지역: '.$data['region'],
            '근무형태: '.eottae_job_template_label('work_type', $data['work_type']),
            '근무시간: '.$data['work_hours'],
            '급여: '.$data['salary'],
            '급여형태: '.eottae_job_template_label('pay_type', $data['pay_type']),
        );
        $info = array_filter($info, function ($row) {
            return preg_match('/:\s*.+/u', $row);
        });
        if ($info) {
            $lines[] = '[구인정보]';
            $lines[] = '';
            $lines = array_merge($lines, array_values($info));
            $lines[] = '';
        }

        if ($data['work_desc'] !== '') {
            $lines[] = '[업무내용]';
            $lines[] = $data['work_desc'];
            $lines[] = '';
        }

        $qual = array(
            '나이: '.$data['age'],
            '성별: '.eottae_job_template_label('gender', $data['gender']),
            '경력: '.eottae_job_template_label('career', $data['career']),
            '언어조건: '.eottae_job_template_label('language', $data['language']),
        );
        $qual = array_filter($qual, function ($row) {
            return preg_match('/:\s*.+/u', $row);
        });
        if ($qual || $data['qualification'] !== '') {
            $lines[] = '[지원자격]';
            $lines[] = '';
            $lines = array_merge($lines, array_values($qual));
            if ($data['qualification'] !== '') {
                if ($qual) {
                    $lines[] = '';
                }
                $lines[] = $data['qualification'];
            }
            $lines[] = '';
        }

        if ($data['benefits'] !== '') {
            $lines[] = '[복리후생]';
            $lines[] = $data['benefits'];
            $lines[] = '';
        }
        if ($data['preferred'] !== '') {
            $lines[] = '[우대사항]';
            $lines[] = $data['preferred'];
            $lines[] = '';
        }

        $apply = array(
            '지원방법: '.$data['apply_method'],
            '연락처: '.$data['contact'],
            '카카오톡 ID: '.$data['kakao_id'],
            '이메일: '.$data['email'],
        );
        $apply = array_filter($apply, function ($row) {
            return preg_match('/:\s*.+/u', $row);
        });
        if ($apply) {
            $lines[] = '[지원방법]';
            $lines[] = '';
            $lines = array_merge($lines, array_values($apply));
            $lines[] = '';
        }

        if ($data['deadline'] !== '') {
            $lines[] = '[마감일]';
            $lines[] = $data['deadline'];
            $lines[] = '';
        }
        if ($data['extra'] !== '') {
            $lines[] = '[기타 안내사항]';
            $lines[] = $data['extra'];
            $lines[] = '';
        }

        return trim(implode("\n", $lines));
    }
}

if (!function_exists('eottae_job_template_apply_to_post')) {
    /**
     * 글 저장 시 템플릿 JSON 반영 (본문·제목·wr_3)
     */
    function eottae_job_template_apply_to_post()
    {
        $raw = isset($_POST['job_template_json']) ? (string) $_POST['job_template_json'] : '';
        if ($raw === '' && !empty($_POST['wr_3']) && is_string($_POST['wr_3']) && $_POST['wr_3'][0] === '{') {
            $raw = (string) $_POST['wr_3'];
        }

        $data = eottae_job_template_decode_json($raw);
        if ($data === null || !eottae_job_template_has_core_fields($data)) {
            return;
        }

        $_POST['wr_3'] = eottae_job_template_encode_json($data);
        $_POST['wr_1'] = $data['region'];
        $_POST['wr_2'] = $data['job_recruit_status'];

        $body = eottae_job_template_build_body($data);
        $content_plain = trim(strip_tags((string) ($_POST['wr_content'] ?? '')));

        if ($body !== '' && ($content_plain === '' || strpos($content_plain, '[구인정보]') === false)) {
            $_POST['wr_content'] = $body;
        }

        $subject = trim(strip_tags((string) ($_POST['wr_subject'] ?? '')));
        if ($subject === '' || ($data['job_type'] !== '' && strpos($subject, $data['job_type']) === false)) {
            $_POST['wr_subject'] = eottae_job_template_build_title($data);
        }
    }
}

if (!function_exists('eottae_job_template_view_rows')) {
    /**
     * 상세 패널 표시용 행
     *
     * @return array<int, array{section:string, rows:array<int, array{label:string, value:string}>}>
     */
    function eottae_job_template_view_rows(array $data)
    {
        $data = eottae_job_template_normalize_data($data);
        $sections = array();

        $basic = array();
        eottae_job_template_push_row($basic, '업체명', $data['company'], 'job_company', 'job.field.company');
        eottae_job_template_push_row($basic, '모집직종', $data['job_type'], 'job_job_type', 'job.field.job_type');
        eottae_job_template_push_row($basic, '모집인원', $data['headcount'], 'job_headcount', 'job.field.headcount');
        eottae_job_template_push_row($basic, '근무지역', $data['region'], 'job_region', 'job.field.region');
        eottae_job_template_push_row($basic, '근무형태', eottae_job_template_label('work_type', $data['work_type']), 'job_work_type', 'job.field.work_type');
        eottae_job_template_push_row($basic, '근무시간', $data['work_hours'], 'job_work_hours', 'job.field.work_hours');
        eottae_job_template_push_row($basic, '급여', $data['salary'], 'job_salary', 'job.field.salary');
        eottae_job_template_push_row($basic, '급여형태', eottae_job_template_label('pay_type', $data['pay_type']), 'job_pay_type', 'job.field.pay_type');
        if ($basic) {
            $sections[] = array(
                'section'     => '구인정보',
                'section_key' => 'job.section.info',
                'rows'        => $basic,
            );
        }

        if ($data['work_desc'] !== '') {
            $sections[] = array(
                'section'     => '업무내용',
                'section_key' => 'job.section.work_desc',
                'rows'        => array(array(
                    'label'      => '',
                    'value'      => $data['work_desc'],
                    'multiline'  => true,
                    'extra_key'  => 'job_work_desc',
                    'label_key'  => '',
                )),
            );
        }

        $qual = array();
        eottae_job_template_push_row($qual, '나이', $data['age'], 'job_age', 'job.field.age');
        eottae_job_template_push_row($qual, '성별', eottae_job_template_label('gender', $data['gender']), 'job_gender', 'job.field.gender');
        eottae_job_template_push_row($qual, '경력', eottae_job_template_label('career', $data['career']), 'job_career', 'job.field.career');
        eottae_job_template_push_row($qual, '언어조건', eottae_job_template_label('language', $data['language']), 'job_language', 'job.field.language');
        if ($data['qualification'] !== '') {
            $qual[] = array(
                'label'     => '',
                'value'     => $data['qualification'],
                'multiline' => true,
                'extra_key' => 'job_qualification',
                'label_key' => '',
            );
        }
        if ($qual) {
            $sections[] = array(
                'section'     => '지원자격',
                'section_key' => 'job.section.qualification',
                'rows'        => $qual,
            );
        }

        if ($data['benefits'] !== '') {
            $sections[] = array(
                'section'     => '복리후생',
                'section_key' => 'job.section.benefits',
                'rows'        => array(array(
                    'label'     => '',
                    'value'     => $data['benefits'],
                    'multiline' => true,
                    'extra_key' => 'job_benefits',
                    'label_key' => '',
                )),
            );
        }
        if ($data['preferred'] !== '') {
            $sections[] = array(
                'section'     => '우대사항',
                'section_key' => 'job.section.preferred',
                'rows'        => array(array(
                    'label'     => '',
                    'value'     => $data['preferred'],
                    'multiline' => true,
                    'extra_key' => 'job_preferred',
                    'label_key' => '',
                )),
            );
        }

        $apply = array();
        eottae_job_template_push_row($apply, '지원방법', $data['apply_method'], 'job_apply_method', 'job.field.apply_method');
        eottae_job_template_push_row($apply, '연락처', $data['contact'], '', 'job.field.contact');
        eottae_job_template_push_row($apply, '카카오톡 ID', $data['kakao_id'], '', 'job.field.kakao_id');
        eottae_job_template_push_row($apply, '이메일', $data['email'], '', 'job.field.email');
        if ($apply) {
            $sections[] = array(
                'section'     => '지원방법',
                'section_key' => 'job.section.apply',
                'rows'        => $apply,
            );
        }

        if ($data['deadline'] !== '') {
            $sections[] = array(
                'section'     => '마감일',
                'section_key' => 'job.section.deadline',
                'rows'        => array(array(
                    'label'     => '',
                    'value'     => $data['deadline'],
                    'multiline' => false,
                    'extra_key' => 'job_deadline',
                    'label_key' => '',
                )),
            );
        }
        if ($data['extra'] !== '') {
            $sections[] = array(
                'section'     => '기타 안내',
                'section_key' => 'job.section.extra',
                'rows'        => array(array(
                    'label'     => '',
                    'value'     => $data['extra'],
                    'multiline' => true,
                    'extra_key' => 'job_extra',
                    'label_key' => '',
                )),
            );
        }

        return $sections;
    }
}

if (!function_exists('eottae_job_template_push_row')) {
    function eottae_job_template_push_row(array &$rows, $label, $value, $extra_key = '', $label_key = '')
    {
        $value = trim((string) $value);
        if ($value === '') {
            return;
        }
        $rows[] = array(
            'label'     => $label,
            'value'     => $value,
            'multiline' => false,
            'extra_key' => (string) $extra_key,
            'label_key' => (string) $label_key,
        );
    }
}

if (!function_exists('eottae_job_translation_extra_labels')) {
    /**
     * 구인구직 번역 extras 키 → 원문 라벨 (OpenAI 프롬프트용)
     *
     * @return array<string, string>
     */
    function eottae_job_translation_extra_labels()
    {
        return array(
            'job_company'        => '업체명',
            'job_job_type'       => '모집직종',
            'job_headcount'      => '모집인원',
            'job_region'         => '근무지역',
            'job_work_type'      => '근무형태',
            'job_work_hours'     => '근무시간',
            'job_salary'         => '급여',
            'job_pay_type'       => '급여형태',
            'job_work_desc'      => '업무내용',
            'job_age'            => '나이',
            'job_gender'         => '성별',
            'job_career'         => '경력',
            'job_language'       => '언어조건',
            'job_qualification'  => '지원자격',
            'job_benefits'       => '복리후생',
            'job_preferred'      => '우대사항',
            'job_apply_method'   => '지원방법',
            'job_deadline'       => '마감일',
            'job_extra'          => '기타 안내',
            'job_location_area'  => '근무 지역',
            'job_location_text'  => '근무 상세위치',
        );
    }
}

if (!function_exists('eottae_job_translation_extras_from_write')) {
    /**
     * 구인구직 wr_3 템플릿 → 번역 API extras
     *
     * @param array<string, mixed> $write
     * @return array<string, string>
     */
    function eottae_job_translation_extras_from_write(array $write)
    {
        $data = eottae_job_template_from_row($write);
        if (!is_array($data)) {
            return array();
        }

        $extras = array();
        $put = function ($key, $value) use (&$extras) {
            $value = trim((string) $value);
            if ($key !== '' && $value !== '') {
                $extras[$key] = $value;
            }
        };

        $put('job_company', $data['company'] ?? '');
        $put('job_job_type', $data['job_type'] ?? '');
        $put('job_headcount', $data['headcount'] ?? '');
        $put('job_region', $data['region'] ?? '');
        $put('job_work_type', eottae_job_template_label('work_type', $data['work_type'] ?? ''));
        $put('job_work_hours', $data['work_hours'] ?? '');
        $put('job_salary', $data['salary'] ?? '');
        $put('job_pay_type', eottae_job_template_label('pay_type', $data['pay_type'] ?? ''));
        $put('job_work_desc', $data['work_desc'] ?? '');
        $put('job_age', $data['age'] ?? '');
        $put('job_gender', eottae_job_template_label('gender', $data['gender'] ?? ''));
        $put('job_career', eottae_job_template_label('career', $data['career'] ?? ''));
        $put('job_language', eottae_job_template_label('language', $data['language'] ?? ''));
        $put('job_qualification', $data['qualification'] ?? '');
        $put('job_benefits', $data['benefits'] ?? '');
        $put('job_preferred', $data['preferred'] ?? '');
        $put('job_apply_method', $data['apply_method'] ?? '');
        $put('job_deadline', $data['deadline'] ?? '');
        $put('job_extra', $data['extra'] ?? '');

        if (function_exists('eottae_job_location_from_row')) {
            $location = eottae_job_location_from_row($write);
            if (is_array($location)) {
                $put('job_location_area', $location['area_label'] ?? '');
                $put('job_location_text', $location['location_text'] ?? '');
            }
        }

        return $extras;
    }
}

if (!function_exists('eottae_job_view_should_hide_body')) {
    /**
     * 구인구직 상세 — 템플릿 패널이 있으면 wr_content 본문은 숨김 (중복 방지)
     *
     * @param array<string, mixed>      $view
     * @param array<string, string>|null $job_template_data
     */
    function eottae_job_view_should_hide_body($view, $job_template_data)
    {
        if (!is_array($job_template_data)) {
            return false;
        }

        if (function_exists('eottae_icrm_content_should_preserve_html')
            && eottae_icrm_content_should_preserve_html($view['wr_content'] ?? '')) {
            return false;
        }

        return true;
    }
}

if (!function_exists('eottae_job_view_has_map')) {
    function eottae_job_view_has_map($job_location)
    {
        if (!is_array($job_location) || empty($job_location['map_visible'])) {
            return false;
        }

        $lat = trim((string) ($job_location['latitude'] ?? ''));
        $lng = trim((string) ($job_location['longitude'] ?? ''));

        return $lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng);
    }
}

if (!function_exists('eottae_job_view_inquiry_opts')) {
    /**
     * @param array<string, mixed>      $view
     * @param array<string, mixed>|null $job_location
     * @param array<string, string>|null $job_template_data
     * @return array<string, string>
     */
    function eottae_job_view_inquiry_opts($view, $bo_table, $job_location = null, $job_template_data = null)
    {
        if (!is_array($view)) {
            return array();
        }

        if (!is_array($job_location) && function_exists('eottae_job_location_from_row')) {
            $job_location = eottae_job_location_from_row($view);
        }
        if (!is_array($job_template_data) && function_exists('eottae_job_template_from_row')) {
            $job_template_data = eottae_job_template_from_row($view);
        }

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) ($view['wr_id'] ?? 0);
        $share_url = ($bo_table !== '' && $wr_id > 0 && function_exists('get_pretty_url'))
            ? get_pretty_url($bo_table, $wr_id)
            : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;

        $phone = '';
        if (is_array($job_template_data) && !empty($job_template_data['contact'])) {
            $phone = trim((string) $job_template_data['contact']);
        }

        return array(
            'phone'       => $phone,
            'owner_mb_id' => trim((string) ($view['mb_id'] ?? '')),
            'shop_name'   => get_text($view['wr_subject'] ?? ''),
            'lat'         => is_array($job_location) ? trim((string) ($job_location['latitude'] ?? '')) : '',
            'lng'         => is_array($job_location) ? trim((string) ($job_location['longitude'] ?? '')) : '',
            'address'     => is_array($job_location) ? trim((string) ($job_location['location_text'] ?? '')) : '',
            'share_url'   => $share_url,
        );
    }
}

if (!function_exists('eottae_job_view_apply_method_label')) {
    function eottae_job_view_apply_method_label($job_template_data)
    {
        if (!is_array($job_template_data)) {
            return '';
        }

        $parts = array_filter(array(
            trim((string) ($job_template_data['apply_method'] ?? '')),
            trim((string) ($job_template_data['contact'] ?? '')),
            trim((string) ($job_template_data['kakao_id'] ?? '')),
            trim((string) ($job_template_data['email'] ?? '')),
        ));

        return implode(' · ', $parts);
    }
}
