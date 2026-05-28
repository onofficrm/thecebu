<?php
/**
 * iCRM — 글 등록·DB 직접 INSERT 후 wr_seo_title 확정 및 final_url 반환
 *
 * POST bo_table, wr_id (필수)
 * Header: X-ICRM-Token: {icrm_api_token}
 *
 * 응답 예:
 * {"ok":true,"bo_table":"community","wr_id":123,"wr_seo_title":"my-post","final_url":"https://thecebu.co.kr/community/my-post/"}
 */
include_once __DIR__.'/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_icrm_json(array(
        'ok'      => false,
        'message' => 'POST 요청만 허용됩니다.',
    ), 405);
}

$bo_table = isset($_POST['bo_table']) ? (string) $_POST['bo_table'] : '';
$wr_id = isset($_POST['wr_id']) ? $_POST['wr_id'] : 0;

$result = eottae_icrm_resolve_post($bo_table, $wr_id);

if (empty($result['ok'])) {
    $http = !empty($result['not_found']) ? 404 : 400;
    eottae_icrm_json($result, $http);
}

$response = array(
    'ok'           => true,
    'bo_table'     => $result['bo_table'],
    'wr_id'        => $result['wr_id'],
    'wr_seo_title' => $result['wr_seo_title'],
    'final_url'    => $result['final_url'],
);

if (!empty($result['seo_created'])) {
    $response['seo_created'] = true;
}

eottae_icrm_json($response);
