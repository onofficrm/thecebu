<?php
/**
 * 사업자 회원 — 홍보 게시글 문구 AI 생성
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-ai-generate.lib.php';
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

if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
    include_once G5_PATH.'/_site.config.php';
}

$ai_cfg = eottae_ai_generate_require_ready();
$api_key = $ai_cfg['api_key'];
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

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer '.$api_key,
    ),
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 30,
));

$raw = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($raw === false || $raw === '' || $http_code < 200 || $http_code >= 300) {
    echo json_encode(array(
        'success' => false,
        'message' => function_exists('eottae_ai_generate_openai_error_message')
            ? eottae_ai_generate_openai_error_message($http_code, $raw, $curl_error)
            : 'AI 홍보 문구 생성에 실패했습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$decoded = json_decode($raw, true);
$content = isset($decoded['choices'][0]['message']['content']) ? trim((string) $decoded['choices'][0]['message']['content']) : '';
$generated = json_decode($content, true);
if (!is_array($generated)) {
    echo json_encode(array('success' => false, 'message' => 'AI 응답을 해석하지 못했습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$result = array(
    'label' => isset($generated['label']) ? trim(strip_tags((string) $generated['label'])) : '',
    'wr_subject' => isset($generated['wr_subject']) ? trim(strip_tags((string) $generated['wr_subject'])) : '',
    'wr_content' => isset($generated['wr_content']) ? trim((string) $generated['wr_content']) : '',
);

echo json_encode(array('success' => true, 'data' => $result), JSON_UNESCAPED_UNICODE);
