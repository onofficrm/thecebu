<?php
/**
 * iCRM — 저장된 글의 wr_seo_title · final_url 조회/확정
 *
 * GET /icrm/final-url.php?bo_table=community&wr_id=123
 * POST 동일 파라미터 지원
 *
 * Header: X-ICRM-Token: {icrm_api_token}
 */
include_once __DIR__.'/_bootstrap.php';

$bo_table = '';
$wr_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bo_table = isset($_POST['bo_table']) ? (string) $_POST['bo_table'] : '';
    $wr_id = isset($_POST['wr_id']) ? $_POST['wr_id'] : 0;
} else {
    $bo_table = isset($_GET['bo_table']) ? (string) $_GET['bo_table'] : '';
    $wr_id = isset($_GET['wr_id']) ? $_GET['wr_id'] : 0;
}

$result = eottae_icrm_resolve_post($bo_table, $wr_id);

if (empty($result['ok'])) {
    $http = !empty($result['not_found']) ? 404 : 400;
    eottae_icrm_json($result, $http);
}

unset($result['seo_created']);
eottae_icrm_json($result);
