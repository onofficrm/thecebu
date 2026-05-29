<?php
/**
 * 업체리뷰 글쓰기 — 업체 검색 API
 * GET /proc/eottae-review-shop-search.php?q=키워드&limit=30
 */
define('EOTTae_REVIEW_SHOP_SEARCH', true);

chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';

header('Content-Type: application/json; charset=utf-8');

if (!defined('_GNUBOARD_')) {
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.', 'items' => array()), JSON_UNESCAPED_UNICODE);
    exit;
}

include_once G5_LIB_PATH.'/eottae-review-board.lib.php';

$keyword = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 30;

$items = eottae_review_board_search_shops($keyword, $limit);

echo json_encode(array(
    'success' => true,
    'items'   => $items,
    'count'   => count($items),
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
