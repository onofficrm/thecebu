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
include_once G5_LIB_PATH.'/eottae-review-manage.lib.php';
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

$card_opts = eottae_review_manage_card_opts($shop_wr_id);
$pending_delete_ids = ($card_opts['show_biz_delete_request'] || $card_opts['show_super_manage'])
    ? eottae_review_delete_pending_ids_for_shop($shop_wr_id)
    : array();

$html = '';
eottae_load_component('review-card');
foreach ($reviews as $review) {
    $opts = $card_opts;
    $opts['delete_pending'] = !empty($pending_delete_ids[(int) $review['wr_id']]);
    $html .= eottae_review_card_html($review, $opts);
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
