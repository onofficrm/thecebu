<?php
/**
 * 사업자 회원 — 홍보 게시글 문구 AI 생성
 */
require_once dirname(__FILE__).'/_eottae_json_bootstrap.php';

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-ai-generate.lib.php';
include_once G5_LIB_PATH.'/eottae-business-snippet.lib.php';

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_json_send(array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
}

if (!function_exists('eottae_is_business_member') || !eottae_is_business_member($member)) {
    eottae_json_send(array('success' => false, 'message' => '사업자 회원만 이용할 수 있습니다.'));
}

$write_bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', (string) $_POST['bo_table']) : '';
$write_ca_name = isset($_POST['ca_name']) ? trim((string) $_POST['ca_name']) : '';
if ($write_bo_table !== '' || $write_ca_name !== '') {
    if (!eottae_business_snippet_write_allowed($write_bo_table, $write_ca_name)) {
        eottae_json_send(array(
            'success' => false,
            'message' => '홍보 문구는 분류가 광고판인 글에서만 이용할 수 있습니다.',
        ));
    }
}

$ai_cfg = eottae_ai_generate_require_ready();
$model = $ai_cfg['model'];

$topic = isset($_POST['topic']) ? trim(strip_tags((string) $_POST['topic'])) : '';
$tone = isset($_POST['tone']) ? trim(strip_tags((string) $_POST['tone'])) : 'friendly';
$shop = eottae_business_primary_shop($member['mb_id']);

$context = array();
if (!empty($shop['name'])) {
    $context['업체명'] = $shop['name'];
}
if (!empty($shop['category'])) {
    $context['카테고리'] = $shop['category'];
}
if (!empty($shop['region'])) {
    $context['지역'] = $shop['region'];
}
if (!empty($shop['address'])) {
    $context['주소'] = $shop['address'];
}
if (!empty($shop['phone'])) {
    $context['전화'] = $shop['phone'];
}
if (!empty($shop['hours'])) {
    $context['영업시간'] = $shop['hours'];
}
if (!empty($shop['content'])) {
    $context['업체소개'] = strip_tags($shop['content']);
}
if ($topic !== '') {
    $context['홍보 주제'] = $topic;
}

$lines = array();
foreach ($context as $key => $value) {
    if ($value !== '') {
        $lines[] = $key.': '.$value;
    }
}

$prompt = "필리핀 세부 지역 커뮤니티 '세부어때'에 올릴 사업자 홍보 게시글 초안을 작성해 주세요.\n"
    ."모바일에서 바로 붙여 넣을 수 있게 자연스러운 한국어로 작성합니다.\n"
    ."과장·허위 정보 없이, 친근하고 신뢰감 있는 {$tone} 톤으로 작성합니다.\n"
    ."반드시 JSON만 응답: label, wr_subject, wr_content\n"
    ."- label: 문구 목록에 보일 짧은 이름 20자 이내\n"
    ."- wr_subject: 게시글 제목 40자 이내\n"
    ."- wr_content: 본문 250~450자, 줄바꿈 포함, 이모지 1~3개 가능\n\n"
    ."업체 정보:\n".implode("\n", $lines);

$payload = array(
    'model' => $model,
    'messages' => array(
        array('role' => 'system', 'content' => 'You write Korean community promotion posts for Cebu businesses. Return strict JSON only.'),
        array('role' => 'user', 'content' => $prompt),
    ),
    'temperature' => 0.75,
    'max_tokens' => 900,
    'response_format' => array('type' => 'json_object'),
);

$completion = eottae_ai_openai_chat_completion($payload, array('timeout' => 45, 'connect_timeout' => 10));
$generated = eottae_ai_openai_parse_json_content($completion);

if (!is_array($generated)) {
    eottae_json_send(array(
        'success' => false,
        'message' => function_exists('eottae_ai_generate_openai_error_message')
            ? eottae_ai_generate_openai_error_message(
                (int) ($completion['http_code'] ?? 0),
                $completion['raw'] ?? '',
                $completion['error'] ?? ''
            )
            : 'AI 홍보 문구 생성에 실패했습니다.',
    ));
}

$result = array(
    'label' => isset($generated['label']) ? trim(strip_tags((string) $generated['label'])) : '',
    'wr_subject' => isset($generated['wr_subject']) ? trim(strip_tags((string) $generated['wr_subject'])) : '',
    'wr_content' => isset($generated['wr_content']) ? trim((string) $generated['wr_content']) : '',
);

eottae_json_send(array('success' => true, 'data' => $result));
