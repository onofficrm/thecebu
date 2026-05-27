<?php
/**
 * 사업자 회원 — 자주 쓰는 홍보 문구 CRUD
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-business-snippet.lib.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($is_member) || empty($member['mb_id'])) {
    echo json_encode(array('success' => false, 'message' => '로그인 후 이용해 주세요.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!function_exists('eottae_is_business_member') || !eottae_is_business_member($member)) {
    echo json_encode(array('success' => false, 'message' => '사업자 회원만 이용할 수 있습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$mb_id = $member['mb_id'];
$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : 'list';

if ($action === 'list') {
    echo json_encode(array(
        'success' => true,
        'data' => eottae_business_snippet_list($mb_id),
        'max' => eottae_business_snippet_max_count(),
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'save') {
    $write_bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', (string) $_POST['bo_table']) : '';
    $write_ca_name = isset($_POST['ca_name']) ? trim((string) $_POST['ca_name']) : '';
    if ($write_bo_table !== '' || $write_ca_name !== '') {
        if (!eottae_business_snippet_write_allowed($write_bo_table, $write_ca_name)) {
            echo json_encode(array(
                'success' => false,
                'message' => '홍보 문구는 분류가 광고판인 글에서만 이용할 수 있습니다.',
            ), JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    $snippet_id = isset($_POST['snippet_id']) ? (int) $_POST['snippet_id'] : 0;
    $label = isset($_POST['label']) ? (string) $_POST['label'] : '';
    $wr_subject = isset($_POST['wr_subject']) ? (string) $_POST['wr_subject'] : '';
    $wr_content = isset($_POST['wr_content']) ? (string) $_POST['wr_content'] : '';

    $saved_id = eottae_business_snippet_save($mb_id, array(
        'snippet_id' => $snippet_id,
        'label' => $label,
        'wr_subject' => $wr_subject,
        'wr_content' => $wr_content,
    ));

    if (!$saved_id) {
        echo json_encode(array(
            'success' => false,
            'message' => '저장에 실패했습니다. 내용을 입력했는지, 저장 개수('.eottae_business_snippet_max_count().'개)를 확인해 주세요.',
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(array(
        'success' => true,
        'snippet_id' => $saved_id,
        'data' => eottae_business_snippet_get($mb_id, $saved_id),
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'delete') {
    $snippet_id = isset($_POST['snippet_id']) ? (int) $_POST['snippet_id'] : 0;
    if ($snippet_id < 1) {
        echo json_encode(array('success' => false, 'message' => '삭제할 문구를 선택해 주세요.'), JSON_UNESCAPED_UNICODE);
        exit;
    }

    eottae_business_snippet_delete($mb_id, $snippet_id);
    echo json_encode(array('success' => true), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('success' => false, 'message' => '올바르지 않은 요청입니다.'), JSON_UNESCAPED_UNICODE);
