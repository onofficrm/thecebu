<?php
/**
 * 업체 리뷰 더보기
 * GET /proc/eottae-shop-reviews-more.php?shop_wr_id=9&offset=10&limit=10
 */
define('EOTTae_SHOP_REVIEWS_MORE', true);

chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';

header('Content-Type: application/json; charset=utf-8');

if (!defined('_GNUBOARD_')) {
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-review-delete.lib.php';
eottae_review_delete_ensure_schema();
eottae_load_component('review-section');

$shop_wr_id = isset($_GET['shop_wr_id']) ? (int) $_GET['shop_wr_id'] : 0;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$page_size = defined('EOTTae_REVIEW_LIST_PAGE_SIZE') ? (int) EOTTae_REVIEW_LIST_PAGE_SIZE : 10;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : $page_size;
$limit = max(1, min(20, $limit));
$offset = max(0, $offset);

if ($shop_wr_id < 1) {
    echo json_encode(array('success' => false, 'message' => '업체 정보가 올바르지 않습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$summary = eottae_get_shop_review_summary($shop_wr_id);
$reviews = eottae_get_shop_reviews($shop_wr_id, $limit, $offset);

$show_biz_reply = false;
$reply_token = '';
$delete_token = '';
$show_super_delete = $is_admin === 'super';
$show_biz_delete_request = false;
$pending_delete_ids = array();

if ($is_member && eottae_is_business_member($member) && eottae_business_owns_shop($member['mb_id'], $shop_wr_id)) {
    $show_biz_reply = true;
    $reply_token = eottae_review_reply_token(false);
    if ($is_admin !== 'super') {
        $show_biz_delete_request = true;
        $delete_token = eottae_review_delete_token(false);
        $pending_delete_ids = eottae_review_delete_pending_ids_for_shop($shop_wr_id);
    }
}
if ($show_super_delete) {
    $delete_token = eottae_review_delete_token(false);
    $pending_delete_ids = eottae_review_delete_pending_ids_for_shop($shop_wr_id);
}

$card_opts = array(
    'show_reply_btn' => $show_biz_reply,
    'reply_token' => $reply_token,
    'shop_wr_id' => $shop_wr_id,
    'show_super_delete' => $show_super_delete,
    'show_biz_delete_request' => $show_biz_delete_request,
    'delete_token' => $delete_token,
);

$html = '';
if (function_exists('eottae_review_cards_html')) {
    foreach ($reviews as $review) {
        $opts = $card_opts;
        $opts['delete_pending'] = !empty($pending_delete_ids[(int) $review['wr_id']]);
        $html .= eottae_review_card_html($review, $opts);
    }
} else {
    eottae_load_component('review-card');
    foreach ($reviews as $review) {
        $opts = $card_opts;
        $opts['delete_pending'] = !empty($pending_delete_ids[(int) $review['wr_id']]);
        $html .= eottae_review_card_html($review, $opts);
    }
}

$loaded = $offset + count($reviews);
$total = (int) $summary['count'];

echo json_encode(array(
    'success'  => true,
    'html'     => $html,
    'loaded'   => $loaded,
    'total'    => $total,
    'has_more' => $loaded < $total,
), JSON_UNESCAPED_UNICODE);
