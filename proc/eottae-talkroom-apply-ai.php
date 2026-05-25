<?php
/**
 * 톡방 개설 신청서 — AI 자동 작성
 */
require_once dirname(__FILE__).'/_eottae_json_bootstrap.php';

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-apply-ai.lib.php';

if (empty($is_member)) {
    eottae_json_send(array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
}

$input = eottae_talkroom_apply_ai_parse_input($_POST);

if ($input['room_name'] === '' && $input['topic_hint'] === '' && $input['category'] === '') {
    eottae_json_send(array(
        'success' => false,
        'message' => '톡방 이름, 주제 힌트, 카테고리 중 하나 이상을 입력해 주세요.',
    ));
}

$result = eottae_talkroom_apply_ai_generate($input);
$source = isset($result['source']) ? $result['source'] : 'template';
unset($result['source']);

eottae_json_send(array(
    'success' => true,
    'data'    => $result,
    'source'  => $source,
));
