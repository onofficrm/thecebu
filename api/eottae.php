<?php
/**
 * 세부어때 공개 JSON API
 * GET /api/eottae.php?action=featured_shops|shop_summary|events|home
 */
define('EOTTae_API', true);

chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.'));
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-api.lib.php';

$action = isset($_GET['action']) ? preg_replace('/[^a-z_]/', '', $_GET['action']) : 'home';

switch ($action) {
    case 'featured_shops':
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 6;
        eottae_api_json(array(
            'success' => true,
            'items'   => eottae_api_get_featured_shops($limit),
        ));
        break;

    case 'shop_summary':
        $shop_id = isset($_GET['shop_id']) ? (int) $_GET['shop_id'] : 0;
        $shop = eottae_api_get_shop_summary($shop_id);
        if (!$shop) {
            eottae_api_error('업체를 찾을 수 없습니다.', 404);
        }
        eottae_api_json(array('success' => true, 'shop' => $shop));
        break;

    case 'events':
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        eottae_api_json(array(
            'success' => true,
            'items'   => eottae_api_get_events($limit),
        ));
        break;

    case 'community':
        eottae_api_json(array(
            'success' => true,
            'data'    => eottae_api_get_community_home(),
        ));
        break;

    case 'home':
    default:
        eottae_api_json(array(
            'success' => true,
            'data'    => eottae_api_get_home_bundle(),
        ));
        break;
}
