<?php
/**
 * 구인구직 글쓰기 — AI 초안 생성
 */
require_once dirname(__FILE__).'/_eottae_json_bootstrap.php';

include_once G5_LIB_PATH.'/eottae-job.lib.php';
include_once G5_LIB_PATH.'/eottae-job-write-ai.lib.php';

if (empty($is_member)) {
    eottae_json_send(array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
}

if (function_exists('eottae_ai_release_session_lock')) {
    eottae_ai_release_session_lock();
}

$input = eottae_job_write_ai_parse_input($_POST);
if ($input['company'] === '' && $input['job_type'] === '' && $input['region'] === '') {
    eottae_json_send(array(
        'success' => false,
        'message' => '업체명, 직종, 근무지역 중 하나 이상을 입력해 주세요.',
    ));
}

$result = eottae_job_write_ai_generate($input);

eottae_json_send(array(
    'success' => true,
    'data'    => $result['data'],
    'source'  => $result['source'],
));
