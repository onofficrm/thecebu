<?php
/**
 * 중고장터 — AI 가격 참고
 */
require_once dirname(__FILE__).'/_eottae_json_bootstrap.php';

include_once G5_LIB_PATH.'/eottae-market.lib.php';
include_once G5_LIB_PATH.'/eottae-market-price-ai.lib.php';

if (empty($is_member)) {
    eottae_json_send(array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
}

$bo_table = preg_replace('/[^a-z0-9_]/', '', (string) ($_POST['bo_table'] ?? eottae_market_board_table()));
if (!eottae_is_market_board($bo_table)) {
    eottae_json_send(array('success' => false, 'message' => '중고장터에서만 사용할 수 있습니다.'));
}

if (function_exists('eottae_ai_release_session_lock')) {
    eottae_ai_release_session_lock();
}

$input = eottae_market_price_ai_parse_input($_POST);
if ($input['subject'] === '') {
    eottae_json_send(array('success' => false, 'message' => '상품명을 먼저 입력해 주세요.'));
}

$result = eottae_market_price_ai_generate($input);

eottae_json_send(array(
    'success' => true,
    'data'    => $result['data'],
    'comps'   => $result['comps'],
    'stats'   => $result['stats'],
    'source'  => $result['source'],
));
