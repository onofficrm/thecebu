<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-coupon.lib.php';

if (!function_exists('eottae_business_coupon_ensure_schema')) {
    function eottae_business_coupon_ensure_schema()
    {
        global $g5;

        if (!eottae_coupon_ensure_ready()) {
            return false;
        }

        eottae_coupon_bootstrap_tables();
        $coupon = $g5['eottae_coupon_table'];
        $issue = $g5['eottae_coupon_issue_table'];

        $coupon_cols = array(
            'cp_owner_mb_id' => " varchar(20) NOT NULL DEFAULT '' ",
            'cp_benefit_type' => " varchar(30) NOT NULL DEFAULT '' ",
            'cp_percent' => " int(11) NOT NULL DEFAULT '0' ",
            'cp_free_item' => " varchar(255) NOT NULL DEFAULT '' ",
            'cp_min_amount' => " varchar(100) NOT NULL DEFAULT '' ",
            'cp_condition_menu' => " varchar(255) NOT NULL DEFAULT '' ",
            'cp_order_benefit' => " varchar(20) NOT NULL DEFAULT 'percent' ",
            'cp_expires_at' => " datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ",
        );
        foreach ($coupon_cols as $col => $def) {
            $exists = sql_fetch(" SHOW COLUMNS FROM `{$coupon}` LIKE '{$col}' ");
            if (empty($exists)) {
                sql_query(" ALTER TABLE `{$coupon}` ADD `{$col}` {$def} ", false);
            }
        }

        $issue_cols = array(
            'ci_code' => " varchar(20) NOT NULL DEFAULT '' ",
            'ci_issued_by_mb_id' => " varchar(20) NOT NULL DEFAULT '' ",
            'ci_redeemed_by_mb_id' => " varchar(20) NOT NULL DEFAULT '' ",
        );
        foreach ($issue_cols as $col => $def) {
            $exists = sql_fetch(" SHOW COLUMNS FROM `{$issue}` LIKE '{$col}' ");
            if (empty($exists)) {
                sql_query(" ALTER TABLE `{$issue}` ADD `{$col}` {$def} ", false);
            }
        }

        $idx = sql_fetch(" SHOW INDEX FROM `{$issue}` WHERE Key_name = 'ci_code' ");
        if (empty($idx)) {
            sql_query(" ALTER TABLE `{$issue}` ADD KEY `ci_code` (`ci_code`) ", false);
        }

        return true;
    }
}

if (!function_exists('eottae_business_coupon_benefit_types')) {
    function eottae_business_coupon_benefit_types()
    {
        return array(
            'percent' => '할인율 (00% 할인)',
            'visit_free' => '방문 혜택 (방문시 000 무료)',
            'order_discount' => '주문 조건 (000 이상 · 메뉴 조건 + 할인/무료)',
        );
    }
}

if (!function_exists('eottae_business_coupon_generate_code')) {
    function eottae_business_coupon_generate_code()
    {
        global $g5;

        eottae_coupon_bootstrap_tables();
        $issue = $g5['eottae_coupon_issue_table'];

        for ($i = 0; $i < 8; $i++) {
            $code = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));
            $row = sql_fetch(" select ci_id from {$issue} where ci_code = '".sql_escape_string($code)."' limit 1 ");
            if (empty($row['ci_id'])) {
                return $code;
            }
        }

        return strtoupper(substr(md5(G5_TIME_YMDHIS), 0, 8));
    }
}

if (!function_exists('eottae_business_coupon_build_copy')) {
    function eottae_business_coupon_build_copy($data)
    {
        $type = isset($data['cp_benefit_type']) ? (string) $data['cp_benefit_type'] : '';
        $percent = isset($data['cp_percent']) ? (int) $data['cp_percent'] : 0;
        $free_item = isset($data['cp_free_item']) ? trim((string) $data['cp_free_item']) : '';
        $min_amount = isset($data['cp_min_amount']) ? trim((string) $data['cp_min_amount']) : '';
        $condition_menu = isset($data['cp_condition_menu']) ? trim((string) $data['cp_condition_menu']) : '';
        $order_benefit = isset($data['cp_order_benefit']) ? (string) $data['cp_order_benefit'] : 'percent';

        if ($type === 'percent') {
            $title = $percent.'% 할인 쿠폰';
            $desc = '매장 방문 시 '.$percent.'% 할인이 적용됩니다.';
            return array('title' => $title, 'desc' => $desc);
        }

        if ($type === 'visit_free') {
            $item = $free_item !== '' ? $free_item : '혜택';
            $title = '방문시 '.$item.' 무료';
            $desc = '매장 방문 시 '.$item.'을(를) 무료로 드립니다.';
            return array('title' => $title, 'desc' => $desc);
        }

        if ($type === 'order_discount') {
            $cond = array();
            if ($min_amount !== '') {
                $cond[] = $min_amount.' 이상';
            }
            if ($condition_menu !== '') {
                $cond[] = $condition_menu.' 주문시';
            }
            $prefix = !empty($cond) ? implode(' ', $cond).' ' : '';

            if ($order_benefit === 'free' && $free_item !== '') {
                $title = $prefix.$free_item.' 무료';
                $desc = trim($prefix).'주문하시면 '.$free_item.'을(를) 무료로 드립니다.';
            } else {
                $title = $prefix.$percent.'% 할인';
                $desc = trim($prefix).'주문하시면 '.$percent.'% 할인이 적용됩니다.';
            }

            return array('title' => $title, 'desc' => $desc);
        }

        return array('title' => '사업자 할인 쿠폰', 'desc' => '매장에서 사용 가능한 할인 쿠폰입니다.');
    }
}

if (!function_exists('eottae_business_coupon_format_benefit')) {
    function eottae_business_coupon_format_benefit($coupon)
    {
        if (!is_array($coupon)) {
            return '';
        }

        if (isset($coupon['cp_type']) && $coupon['cp_type'] !== 'business') {
            return isset($coupon['cp_desc']) ? get_text($coupon['cp_desc']) : '';
        }

        $copy = eottae_business_coupon_build_copy($coupon);
        return $copy['desc'];
    }
}

if (!function_exists('eottae_coupon_shop_name_for_coupon')) {
    /**
     * 쿠폰에 표시할 업체명 (사업자 등록 업소 → 닉네임 순)
     */
    function eottae_coupon_shop_name_for_coupon(array $coupon)
    {
        $owner_mb_id = isset($coupon['cp_owner_mb_id']) ? trim((string) $coupon['cp_owner_mb_id']) : '';
        if ($owner_mb_id !== '' && function_exists('eottae_business_shop_posts')) {
            $posts = eottae_business_shop_posts($owner_mb_id, 1);
            if (!empty($posts[0]['subject'])) {
                return get_text($posts[0]['subject']);
            }
            $owner = get_member($owner_mb_id);
            if (!empty($owner['mb_nick'])) {
                return get_text($owner['mb_nick']);
            }
        }

        if (isset($coupon['cp_type']) && $coupon['cp_type'] === 'business') {
            return '제휴 업체';
        }

        return function_exists('g5site_cfg') ? g5site_cfg('site_name', '세부어때') : '세부어때';
    }
}

if (!function_exists('eottae_coupon_visual_present')) {
    /**
     * 쿠폰 티켓 UI용 표시 데이터
     *
     * @param array<string, mixed> $coupon
     * @param array<string, mixed> $opts used(bool)
     * @return array<string, string>
     */
    function eottae_coupon_visual_present(array $coupon, array $opts = array())
    {
        $is_used = !empty($opts['used']) || (isset($coupon['ci_status']) && $coupon['ci_status'] === 'used');
        $cp_type = isset($coupon['cp_type']) ? trim((string) $coupon['cp_type']) : '';
        $is_business = ($cp_type === 'business');

        $present = array(
            'shop_name'        => eottae_coupon_shop_name_for_coupon($coupon),
            'headline'         => '',
            'headline_suffix'  => '',
            'benefit_label'    => '',
            'detail_line'      => '',
            'variant'          => 'general',
            'badge'            => $is_business ? '업체 쿠폰' : '세부어때 쿠폰',
            'code'             => isset($coupon['ci_code']) ? get_text($coupon['ci_code']) : '',
            'meta_line'        => '',
            'is_used'          => $is_used ? '1' : '0',
            'compact_headline' => '0',
        );

        if ($is_business) {
            $type = isset($coupon['cp_benefit_type']) ? trim((string) $coupon['cp_benefit_type']) : '';
            $percent = isset($coupon['cp_percent']) ? (int) $coupon['cp_percent'] : 0;
            $free_item = isset($coupon['cp_free_item']) ? trim((string) $coupon['cp_free_item']) : '';
            $min_amount = isset($coupon['cp_min_amount']) ? trim((string) $coupon['cp_min_amount']) : '';
            $condition_menu = isset($coupon['cp_condition_menu']) ? trim((string) $coupon['cp_condition_menu']) : '';
            $order_benefit = isset($coupon['cp_order_benefit']) ? (string) $coupon['cp_order_benefit'] : 'percent';

            if ($type === 'percent' && $percent > 0) {
                $present['headline'] = (string) $percent;
                $present['headline_suffix'] = '%';
                $present['benefit_label'] = '할인';
                $present['variant'] = 'percent';
            } elseif ($type === 'visit_free') {
                $item = $free_item !== '' ? $free_item : '혜택';
                $present['headline'] = $item;
                $present['benefit_label'] = '무료';
                $present['variant'] = 'free';
                $present['compact_headline'] = (function_exists('mb_strlen') ? mb_strlen($item, 'UTF-8') : strlen($item)) > 8 ? '1' : '0';
            } elseif ($type === 'order_discount') {
                $cond = array();
                if ($min_amount !== '') {
                    $cond[] = $min_amount.' 이상';
                }
                if ($condition_menu !== '') {
                    $cond[] = $condition_menu;
                }
                $present['detail_line'] = !empty($cond) ? implode(' · ', $cond) : '';

                if ($order_benefit === 'free' && $free_item !== '') {
                    $present['headline'] = $free_item;
                    $present['benefit_label'] = '무료';
                    $present['variant'] = 'free';
                    $present['compact_headline'] = (function_exists('mb_strlen') ? mb_strlen($free_item, 'UTF-8') : strlen($free_item)) > 8 ? '1' : '0';
                } elseif ($percent > 0) {
                    $present['headline'] = (string) $percent;
                    $present['headline_suffix'] = '%';
                    $present['benefit_label'] = '할인';
                    $present['variant'] = 'percent';
                } else {
                    $present['headline'] = '혜택';
                    $present['benefit_label'] = '쿠폰';
                    $present['variant'] = 'general';
                }
            } else {
                $copy = eottae_business_coupon_build_copy($coupon);
                $present['headline'] = $copy['title'];
                $present['benefit_label'] = '쿠폰';
                $present['variant'] = 'general';
                $present['compact_headline'] = '1';
            }

            if ($present['detail_line'] === '') {
                $present['detail_line'] = eottae_business_coupon_format_benefit($coupon);
            }
        } else {
            $title = isset($coupon['cp_title']) ? trim((string) $coupon['cp_title']) : '';
            $desc = isset($coupon['cp_desc']) ? trim((string) $coupon['cp_desc']) : '';

            if ($cp_type === 'welcome') {
                $present['variant'] = 'welcome';
                $present['headline'] = 'WELCOME';
                $present['benefit_label'] = '가입 혜택';
            } elseif ($cp_type === 'review') {
                $present['variant'] = 'review';
                $present['headline'] = 'REVIEW';
                $present['benefit_label'] = '리뷰 감사';
            } elseif (preg_match('/(\d+)\s*%/u', $title, $m)) {
                $present['variant'] = 'percent';
                $present['headline'] = $m[1];
                $present['headline_suffix'] = '%';
                $present['benefit_label'] = '할인';
            } elseif (preg_match('/무료/u', $title.' '.$desc)) {
                $present['variant'] = 'free';
                $present['headline'] = 'FREE';
                $present['benefit_label'] = '무료 혜택';
            } else {
                $present['headline'] = $title !== '' ? $title : 'COUPON';
                $present['benefit_label'] = '혜택';
                $present['compact_headline'] = '1';
            }

            $present['detail_line'] = $desc !== '' ? get_text($desc) : get_text($title);
        }

        if ($present['code'] === '' && !empty($coupon['cp_code'])) {
            $present['code'] = strtoupper(get_text($coupon['cp_code']));
        }

        return $present;
    }
}

if (!function_exists('eottae_business_coupon_issue_count')) {
    function eottae_business_coupon_issue_count($cp_id)
    {
        global $g5;

        eottae_coupon_bootstrap_tables();
        $cp_id = (int) $cp_id;
        if ($cp_id < 1) {
            return 0;
        }

        $row = sql_fetch(" select count(*) as cnt from {$g5['eottae_coupon_issue_table']} where cp_id = '{$cp_id}' ");
        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_business_coupon_get')) {
    function eottae_business_coupon_get($cp_id, $owner_mb_id = '')
    {
        global $g5;

        eottae_business_coupon_ensure_schema();
        $cp_id = (int) $cp_id;
        if ($cp_id < 1) {
            return array();
        }

        $where = " cp_id = '{$cp_id}' and cp_type = 'business' ";
        if ($owner_mb_id !== '') {
            $where .= " and cp_owner_mb_id = '".sql_escape_string((string) $owner_mb_id)."' ";
        }

        $row = sql_fetch(" select * from {$g5['eottae_coupon_table']} where {$where} limit 1 ");
        return is_array($row) ? $row : array();
    }
}

if (!function_exists('eottae_business_coupon_create')) {
    function eottae_business_coupon_create($owner_mb_id, $data = array())
    {
        global $g5;

        eottae_business_coupon_ensure_schema();
        $owner_mb_id = trim((string) $owner_mb_id);
        if ($owner_mb_id === '') {
            return array('ok' => false, 'message' => '사업자 정보가 없습니다.');
        }

        $type = isset($data['cp_benefit_type']) ? trim((string) $data['cp_benefit_type']) : '';
        $types = eottae_business_coupon_benefit_types();
        if ($type === '' || !isset($types[$type])) {
            return array('ok' => false, 'message' => '쿠폰 유형을 선택해 주세요.');
        }

        $percent = isset($data['cp_percent']) ? max(0, min(100, (int) $data['cp_percent'])) : 0;
        $free_item = isset($data['cp_free_item']) ? trim(strip_tags((string) $data['cp_free_item'])) : '';
        $min_amount = isset($data['cp_min_amount']) ? trim(strip_tags((string) $data['cp_min_amount'])) : '';
        $condition_menu = isset($data['cp_condition_menu']) ? trim(strip_tags((string) $data['cp_condition_menu'])) : '';
        $order_benefit = isset($data['cp_order_benefit']) && $data['cp_order_benefit'] === 'free' ? 'free' : 'percent';
        $max_issue = isset($data['cp_max_issue']) ? max(1, min(10000, (int) $data['cp_max_issue'])) : 100;
        $expires_at = isset($data['cp_expires_at']) ? trim((string) $data['cp_expires_at']) : '';
        $custom_title = isset($data['cp_title']) ? trim(strip_tags((string) $data['cp_title'])) : '';

        if ($type === 'percent' && $percent < 1) {
            return array('ok' => false, 'message' => '할인율을 입력해 주세요.');
        }
        if ($type === 'visit_free' && $free_item === '') {
            return array('ok' => false, 'message' => '무료 제공 항목을 입력해 주세요.');
        }
        if ($type === 'order_discount') {
            if ($min_amount === '' && $condition_menu === '') {
                return array('ok' => false, 'message' => '주문 조건(금액 또는 메뉴)을 입력해 주세요.');
            }
            if ($order_benefit === 'free' && $free_item === '') {
                return array('ok' => false, 'message' => '무료 제공 항목을 입력해 주세요.');
            }
            if ($order_benefit === 'percent' && $percent < 1) {
                return array('ok' => false, 'message' => '할인율을 입력해 주세요.');
            }
        }

        $payload = array(
            'cp_benefit_type' => $type,
            'cp_percent' => $percent,
            'cp_free_item' => $free_item,
            'cp_min_amount' => $min_amount,
            'cp_condition_menu' => $condition_menu,
            'cp_order_benefit' => $order_benefit,
        );
        $copy = eottae_business_coupon_build_copy($payload);
        $title = $custom_title !== '' ? $custom_title : $copy['title'];
        $desc = $copy['desc'];

        if (function_exists('cut_str')) {
            $title = cut_str($title, 255, '');
            $free_item = cut_str($free_item, 255, '');
            $min_amount = cut_str($min_amount, 100, '');
            $condition_menu = cut_str($condition_menu, 255, '');
        }

        $cp_code = 'biz_'.substr(md5($owner_mb_id.uniqid('', true)), 0, 16);
        $expires_sql = '0000-00-00 00:00:00';
        if ($expires_at !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $expires_at)) {
            $expires_sql = $expires_at.' 23:59:59';
        }

        sql_query(" insert into {$g5['eottae_coupon_table']} set
            cp_code = '".sql_escape_string($cp_code)."',
            cp_title = '".sql_escape_string($title)."',
            cp_desc = '".sql_escape_string($desc)."',
            cp_type = 'business',
            cp_owner_mb_id = '".sql_escape_string($owner_mb_id)."',
            cp_benefit_type = '".sql_escape_string($type)."',
            cp_percent = '{$percent}',
            cp_free_item = '".sql_escape_string($free_item)."',
            cp_min_amount = '".sql_escape_string($min_amount)."',
            cp_condition_menu = '".sql_escape_string($condition_menu)."',
            cp_order_benefit = '".sql_escape_string($order_benefit)."',
            cp_max_issue = '{$max_issue}',
            cp_expires_at = '{$expires_sql}',
            cp_datetime = '".G5_TIME_YMDHIS."' ");

        $cp_id = (int) sql_insert_id();
        return array('ok' => true, 'message' => '쿠폰이 생성되었습니다.', 'cp_id' => $cp_id);
    }
}

if (!function_exists('eottae_business_coupon_issue_to_member')) {
    function eottae_business_coupon_issue_to_member($owner_mb_id, $cp_id, $target_mb_id)
    {
        global $g5;

        eottae_business_coupon_ensure_schema();
        $owner_mb_id = trim((string) $owner_mb_id);
        $target_mb_id = trim((string) $target_mb_id);
        $cp_id = (int) $cp_id;

        if ($owner_mb_id === '' || $target_mb_id === '' || $cp_id < 1) {
            return array('ok' => false, 'message' => '발행 정보가 올바르지 않습니다.');
        }

        $coupon = eottae_business_coupon_get($cp_id, $owner_mb_id);
        if (empty($coupon['cp_id'])) {
            return array('ok' => false, 'message' => '쿠폰을 찾을 수 없습니다.');
        }

        if ($coupon['cp_expires_at'] !== '0000-00-00 00:00:00' && $coupon['cp_expires_at'] < G5_TIME_YMDHIS) {
            return array('ok' => false, 'message' => '만료된 쿠폰입니다.');
        }

        $issued = eottae_business_coupon_issue_count($cp_id);
        $max = (int) $coupon['cp_max_issue'];
        if ($max > 0 && $issued >= $max) {
            return array('ok' => false, 'message' => '발행 가능 수량('.number_format($max).'장)을 모두 사용했습니다.');
        }

        $target = get_member($target_mb_id);
        if (empty($target['mb_id'])) {
            return array('ok' => false, 'message' => '존재하지 않는 회원아이디입니다.');
        }

        $dup = sql_fetch(" select ci_id from {$g5['eottae_coupon_issue_table']}
            where cp_id = '{$cp_id}' and mb_id = '".sql_escape_string($target_mb_id)."' and ci_status = 'active' limit 1 ");
        if (!empty($dup['ci_id'])) {
            return array('ok' => false, 'message' => '해당 회원에게 이미 사용 가능한 동일 쿠폰이 있습니다.');
        }

        $ci_code = eottae_business_coupon_generate_code();
        sql_query(" insert into {$g5['eottae_coupon_issue_table']} set
            cp_id = '{$cp_id}',
            mb_id = '".sql_escape_string($target_mb_id)."',
            ci_status = 'active',
            ci_datetime = '".G5_TIME_YMDHIS."',
            ci_used_datetime = '0000-00-00 00:00:00',
            ci_code = '".sql_escape_string($ci_code)."',
            ci_issued_by_mb_id = '".sql_escape_string($owner_mb_id)."',
            ci_redeemed_by_mb_id = '' ");

        return array(
            'ok' => true,
            'message' => get_text($target['mb_nick']).'('.$target_mb_id.')님에게 쿠폰을 발행했습니다.',
            'ci_id' => (int) sql_insert_id(),
            'ci_code' => $ci_code,
        );
    }
}

if (!function_exists('eottae_business_coupon_issue_many')) {
    function eottae_business_coupon_issue_many($owner_mb_id, $cp_id, $target_mb_id, $quantity = 1)
    {
        $quantity = max(1, min(100, (int) $quantity));
        $success = 0;
        $last_message = '';

        for ($i = 0; $i < $quantity; $i++) {
            $result = eottae_business_coupon_issue_to_member($owner_mb_id, $cp_id, $target_mb_id);
            $last_message = $result['message'];
            if (empty($result['ok'])) {
                break;
            }
            $success++;
        }

        if ($success < 1) {
            return array('ok' => false, 'message' => $last_message !== '' ? $last_message : '발행에 실패했습니다.');
        }

        return array(
            'ok' => true,
            'message' => number_format($success).'장 발행했습니다.',
            'issued' => $success,
        );
    }
}

if (!function_exists('eottae_business_coupon_find_issue')) {
    function eottae_business_coupon_find_issue($owner_mb_id, $lookup = '')
    {
        global $g5;

        eottae_business_coupon_ensure_schema();
        $owner_mb_id = sql_escape_string(trim((string) $owner_mb_id));
        $lookup = trim((string) $lookup);
        if ($owner_mb_id === '' || $lookup === '') {
            return array();
        }

        $lookup_esc = sql_escape_string($lookup);
        $where = " c.cp_owner_mb_id = '{$owner_mb_id}' and c.cp_type = 'business' and i.ci_status = 'active' ";
        $where .= " and (i.ci_code = '{$lookup_esc}' or i.mb_id = '{$lookup_esc}') ";

        return sql_fetch(" select i.*, c.cp_title, c.cp_desc, c.cp_benefit_type, c.cp_percent, c.cp_free_item,
                c.cp_min_amount, c.cp_condition_menu, c.cp_order_benefit, c.cp_type, c.cp_owner_mb_id
            from {$g5['eottae_coupon_issue_table']} i
            inner join {$g5['eottae_coupon_table']} c on c.cp_id = i.cp_id
            where {$where}
            order by i.ci_id desc
            limit 1 ");
    }
}

if (!function_exists('eottae_business_coupon_redeem')) {
    function eottae_business_coupon_redeem($owner_mb_id, $ci_id)
    {
        global $g5;

        eottae_business_coupon_ensure_schema();
        $owner_mb_id = trim((string) $owner_mb_id);
        $ci_id = (int) $ci_id;
        if ($owner_mb_id === '' || $ci_id < 1) {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }

        $row = sql_fetch(" select i.*, c.cp_owner_mb_id, c.cp_title, c.cp_type
            from {$g5['eottae_coupon_issue_table']} i
            inner join {$g5['eottae_coupon_table']} c on c.cp_id = i.cp_id
            where i.ci_id = '{$ci_id}' limit 1 ");

        if (empty($row['ci_id'])) {
            return array('ok' => false, 'message' => '쿠폰을 찾을 수 없습니다.');
        }
        if ($row['cp_type'] !== 'business' || $row['cp_owner_mb_id'] !== $owner_mb_id) {
            return array('ok' => false, 'message' => '본인이 발행한 쿠폰만 사용 처리할 수 있습니다.');
        }
        if ($row['ci_status'] !== 'active') {
            return array('ok' => false, 'message' => '이미 사용되었거나 만료된 쿠폰입니다.');
        }

        sql_query(" update {$g5['eottae_coupon_issue_table']} set
            ci_status = 'used',
            ci_used_datetime = '".G5_TIME_YMDHIS."',
            ci_redeemed_by_mb_id = '".sql_escape_string($owner_mb_id)."'
            where ci_id = '{$ci_id}' ");

        return array('ok' => true, 'message' => '쿠폰 사용이 완료 처리되었습니다.');
    }
}

if (!function_exists('eottae_business_coupon_redeem_by_lookup')) {
    function eottae_business_coupon_redeem_by_lookup($owner_mb_id, $lookup)
    {
        $row = eottae_business_coupon_find_issue($owner_mb_id, $lookup);
        if (empty($row['ci_id'])) {
            return array('ok' => false, 'message' => '사용 가능한 쿠폰을 찾을 수 없습니다.');
        }

        return eottae_business_coupon_redeem($owner_mb_id, (int) $row['ci_id']);
    }
}

if (!function_exists('eottae_business_coupon_campaigns')) {
    function eottae_business_coupon_campaigns($owner_mb_id, $limit = 50)
    {
        global $g5;

        eottae_business_coupon_ensure_schema();
        $owner_mb_id = sql_escape_string(trim((string) $owner_mb_id));
        if ($owner_mb_id === '') {
            return array();
        }

        $limit = max(1, min(100, (int) $limit));
        $result = sql_query(" select c.*,
                (select count(*) from {$g5['eottae_coupon_issue_table']} i where i.cp_id = c.cp_id) as issued_count,
                (select count(*) from {$g5['eottae_coupon_issue_table']} i where i.cp_id = c.cp_id and i.ci_status = 'used') as used_count
            from {$g5['eottae_coupon_table']} c
            where c.cp_owner_mb_id = '{$owner_mb_id}' and c.cp_type = 'business'
            order by c.cp_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('eottae_business_coupon_issues')) {
    function eottae_business_coupon_issues($owner_mb_id, $status = '', $limit = 100)
    {
        global $g5;

        eottae_business_coupon_ensure_schema();
        $owner_mb_id = sql_escape_string(trim((string) $owner_mb_id));
        if ($owner_mb_id === '') {
            return array();
        }

        $limit = max(1, min(200, (int) $limit));
        $where = " c.cp_owner_mb_id = '{$owner_mb_id}' and c.cp_type = 'business' ";
        if ($status !== '') {
            $where .= " and i.ci_status = '".sql_escape_string($status)."' ";
        }

        $result = sql_query(" select i.*, c.cp_title, c.cp_desc, c.cp_benefit_type, c.cp_percent, c.cp_free_item,
                c.cp_min_amount, c.cp_condition_menu, c.cp_order_benefit, c.cp_type, c.cp_max_issue,
                m.mb_nick
            from {$g5['eottae_coupon_issue_table']} i
            inner join {$g5['eottae_coupon_table']} c on c.cp_id = i.cp_id
            left join {$g5['member_table']} m on m.mb_id = i.mb_id
            where {$where}
            order by i.ci_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}
