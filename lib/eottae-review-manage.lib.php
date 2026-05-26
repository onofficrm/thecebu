<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_review_manage_card_opts')) {
    /**
     * 리뷰 카드 렌더 옵션 (목록·더보기 공통)
     *
     * @return array<string, mixed>
     */
    function eottae_review_manage_card_opts($shop_wr_id)
    {
        global $is_member, $member, $is_admin;

        $shop_wr_id = (int) $shop_wr_id;
        include_once G5_LIB_PATH.'/eottae-review-delete.lib.php';
        eottae_review_delete_ensure_schema();

        $current_mb_id = ($is_member && !empty($member['mb_id'])) ? (string) $member['mb_id'] : '';
        $is_super = ($is_admin === 'super');
        $owns_shop = $is_member && eottae_is_business_member($member) && eottae_business_owns_shop($member['mb_id'], $shop_wr_id);

        if ($is_super || $owns_shop) {
            $delete_token = eottae_review_delete_token(false);
        } elseif ($current_mb_id !== '') {
            $delete_token = eottae_review_delete_token(false);
        } else {
            $delete_token = '';
        }
        $manage_token = ($is_member && !$owns_shop) || $is_super ? eottae_review_token(false) : '';

        return array(
            'shop_wr_id'              => $shop_wr_id,
            'current_mb_id'           => $current_mb_id,
            'is_super'                => $is_super,
            'manage_token'            => $manage_token,
            'delete_token'            => $delete_token,
            'show_reply_btn'          => $owns_shop,
            'reply_token'             => $owns_shop ? eottae_review_reply_token(false) : '',
            'show_super_manage'       => $is_super,
            'show_biz_delete_request' => $owns_shop && !$is_super,
            'show_author_manage'      => $current_mb_id !== '' && !$owns_shop,
        );
    }
}

if (!function_exists('eottae_review_user_owns_review')) {
    function eottae_review_user_owns_review(array $review, $mb_id)
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        return trim((string) ($review['mb_id'] ?? '')) === $mb_id;
    }
}

if (!function_exists('eottae_review_user_can_edit')) {
    function eottae_review_user_can_edit(array $review, $mb_id, $is_admin = '')
    {
        if ($is_admin === 'super') {
            return true;
        }

        return eottae_review_user_owns_review($review, $mb_id);
    }
}

if (!function_exists('eottae_review_user_can_delete')) {
    function eottae_review_user_can_delete(array $review, $mb_id, $is_admin = '')
    {
        if ($is_admin === 'super') {
            return true;
        }

        return eottae_review_user_owns_review($review, $mb_id);
    }
}

if (!function_exists('eottae_review_update_execute')) {
    /**
     * @return array{ok:bool, message:string, shop_wr_id:int}
     */
    function eottae_review_update_execute($review_wr_id, $rating, $content, $mb_id, $is_admin = '')
    {
        global $g5;

        $review_wr_id = (int) $review_wr_id;
        $rating = (int) $rating;
        $content = trim(strip_tags((string) $content));
        $mb_id = trim((string) $mb_id);

        if ($review_wr_id < 1) {
            return array('ok' => false, 'message' => '리뷰 정보가 올바르지 않습니다.', 'shop_wr_id' => 0);
        }
        if ($rating < 1 || $rating > 5) {
            return array('ok' => false, 'message' => '별점을 1~5점 사이로 선택해 주세요.', 'shop_wr_id' => 0);
        }
        if ($content === '') {
            return array('ok' => false, 'message' => '리뷰 내용을 입력해 주세요.', 'shop_wr_id' => 0);
        }

        $len = function_exists('mb_strlen') ? mb_strlen($content, 'UTF-8') : strlen($content);
        if ($len < 10) {
            return array('ok' => false, 'message' => '리뷰 내용을 10자 이상 입력해 주세요.', 'shop_wr_id' => 0);
        }
        if ($len > 1000) {
            return array('ok' => false, 'message' => '리뷰 내용은 1000자 이내로 입력해 주세요.', 'shop_wr_id' => 0);
        }

        include_once G5_LIB_PATH.'/eottae-review-delete.lib.php';
        $write_table = eottae_review_write_table();
        $visible = eottae_review_visible_sql();
        $row = sql_fetch(" select * from {$write_table}
            where wr_id = '{$review_wr_id}' and wr_is_comment = 0 and {$visible} limit 1 ");
        if (empty($row['wr_id'])) {
            return array('ok' => false, 'message' => '수정할 리뷰를 찾을 수 없습니다.', 'shop_wr_id' => 0);
        }

        $review = eottae_review_from_write($row);
        if (!eottae_review_user_can_edit($review, $mb_id, $is_admin)) {
            return array('ok' => false, 'message' => '리뷰를 수정할 권한이 없습니다.', 'shop_wr_id' => 0);
        }

        $shop_wr_id = (int) ($review['shop_id'] ?? 0);
        $shop_name = trim((string) ($review['shop_name'] ?? ''));
        if ($shop_name === '' && $shop_wr_id > 0) {
            $shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
            $shop_row = sql_fetch(" select wr_subject from {$shop_table} where wr_id = '{$shop_wr_id}' limit 1 ");
            $shop_name = get_text($shop_row['wr_subject'] ?? '');
        }

        $wr_subject_raw = '['.$rating.'점] '.$shop_name.' 리뷰';
        $wr_subject = sql_escape_string(function_exists('cut_str') ? cut_str($wr_subject_raw, 255) : substr($wr_subject_raw, 0, 255));
        $wr_content = sql_escape_string($content);
        $wr_2 = sql_escape_string((string) $rating);
        $wr_3 = sql_escape_string($shop_name);

        sql_query(" update {$write_table} set
            wr_subject = '{$wr_subject}',
            wr_content = '{$wr_content}',
            wr_2 = '{$wr_2}',
            wr_3 = '{$wr_3}',
            wr_last = '".G5_TIME_YMDHIS."'
            where wr_id = '{$review_wr_id}' ");

        if ($shop_wr_id > 0 && function_exists('eottae_sync_shop_review_stats')) {
            eottae_sync_shop_review_stats($shop_wr_id);
        }

        return array(
            'ok'         => true,
            'message'    => '리뷰를 수정했습니다.',
            'shop_wr_id' => $shop_wr_id,
        );
    }
}
