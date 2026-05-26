<?php
/**
 * 내주변 업소 — 지도 마커·무한 스크롤 목록 API
 * GET /proc/eottae-shop-list.php?action=map_markers|list_cards&bo_table=shop
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_shop_list_api_json($payload, $code = 200)
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    eottae_shop_list_api_json(array('success' => false, 'message' => '잘못된 요청입니다.'), 405);
}

$action = isset($_GET['action']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['action']) : '';
$bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', (string) $_GET['bo_table']) : '';

if ($bo_table === '' && defined('EOTTae_SHOP_TABLE')) {
    $bo_table = EOTTae_SHOP_TABLE;
}

if ($bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
    eottae_shop_list_api_json(array('success' => false, 'message' => '업소 게시판이 올바르지 않습니다.'), 400);
}

$board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");
if (empty($board['bo_table'])) {
    eottae_shop_list_api_json(array('success' => false, 'message' => '게시판을 찾을 수 없습니다.'), 404);
}

$board_skin_url = '';
if (!empty($board['bo_skin'])) {
    $board_skin_url = G5_BBS_URL.'/skin/board/'.$board['bo_skin'];
}

$filters = eottae_shop_list_filters_from_request();
if ($filters['sst'] === '' && isset($_GET['sst'])) {
    $filters['sst'] = trim((string) $_GET['sst']);
}

if ($action === 'map_markers') {
    $bundle = eottae_shop_fetch_raw_rows($bo_table, array_merge($filters, array('max_rows' => 2000)));
    $markers = eottae_shop_map_markers($bundle['rows'], $bo_table);
    $locations = json_decode(eottae_shop_map_locations_json($markers), true);
    if (!is_array($locations)) {
        $locations = array();
    }

    eottae_shop_list_api_json(array(
        'success'   => true,
        'total'     => (int) $bundle['total'],
        'locations' => $locations,
    ));
}

if ($action === 'list_cards') {
    $offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : eottae_shop_infinite_batch_limit($offset);

    $chunk = eottae_shop_list_chunk($bo_table, $board, $board_skin_url, array_merge($filters, array(
        'offset' => $offset,
        'limit'  => $limit,
    )));

    eottae_shop_list_api_json(array(
        'success'      => true,
        'html'         => eottae_shop_render_cards_html($chunk['list'], $bo_table),
        'total'        => (int) $chunk['total_count'],
        'has_more'     => !empty($chunk['has_more']),
        'next_offset'  => (int) $chunk['next_offset'],
        'loaded_count' => count($chunk['list']),
    ));
}

eottae_shop_list_api_json(array('success' => false, 'message' => '지원하지 않는 action입니다.'), 400);
