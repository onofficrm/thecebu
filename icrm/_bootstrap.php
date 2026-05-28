<?php
/**
 * iCRM API 공통 bootstrap
 */
if (!ob_get_level()) {
    ob_start();
}

$g5_root = dirname(__DIR__);
chdir($g5_root);

include_once $g5_root.'/common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(array('ok' => false, 'message' => '시스템 초기화에 실패했습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

include_once G5_LIB_PATH.'/eottae-secrets.lib.php';
include_once G5_LIB_PATH.'/eottae-icrm.lib.php';

eottae_secrets_load();

if (!eottae_icrm_auth_configured()) {
    eottae_icrm_json(array(
        'ok'      => false,
        'message' => 'iCRM API가 설정되지 않았습니다. data/eottae-secrets.local.php 에 icrm_api_token 또는 icrm_allowed_ips 를 등록하세요.',
    ), 503);
}

if (!eottae_icrm_is_authorized()) {
    eottae_icrm_json(array(
        'ok'      => false,
        'message' => '인증에 실패했습니다. X-ICRM-Token 헤더 또는 token 파라미터, 허용 IP를 확인하세요.',
    ), 403);
}
