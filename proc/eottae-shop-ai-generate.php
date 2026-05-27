<?php
/**
 * 업체 등록 — 소개/SEO 문구 AI 자동생성
 */
require_once dirname(__FILE__).'/_eottae_json_bootstrap.php';

include_once G5_LIB_PATH.'/eottae-ai-generate.lib.php';

if (empty($is_member)) {
    eottae_json_send(array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
}

$ai_cfg = eottae_ai_generate_require_ready();
$api_key = $ai_cfg['api_key'];
$model = $ai_cfg['model'];

$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', (string) $_POST['bo_table']) : '';
if ($bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
    eottae_json_send(array('success' => false, 'message' => '업체 게시판에서만 이용할 수 있습니다.'));
}

$name = isset($_POST['name']) ? trim(strip_tags((string) $_POST['name'])) : '';
$category = isset($_POST['category']) ? trim(strip_tags((string) $_POST['category'])) : '';
$region = isset($_POST['region']) ? trim(strip_tags((string) $_POST['region'])) : '';
$address = isset($_POST['address']) ? trim(strip_tags((string) $_POST['address'])) : '';
$phone = isset($_POST['phone']) ? trim(strip_tags((string) $_POST['phone'])) : '';
$hours = isset($_POST['hours']) ? trim(strip_tags((string) $_POST['hours'])) : '';
$closed = isset($_POST['closed']) ? trim(strip_tags((string) $_POST['closed'])) : '';
$website = isset($_POST['website']) ? trim(strip_tags((string) $_POST['website'])) : '';
$instagram = isset($_POST['instagram']) ? trim(strip_tags((string) $_POST['instagram'])) : '';
$tiktok = isset($_POST['tiktok']) ? trim(strip_tags((string) $_POST['tiktok'])) : '';
$facebook = isset($_POST['facebook']) ? trim(strip_tags((string) $_POST['facebook'])) : '';
$naver_blog = isset($_POST['naver_blog']) ? trim(strip_tags((string) $_POST['naver_blog'])) : '';
$existing_intro = isset($_POST['intro']) ? trim(strip_tags((string) $_POST['intro'])) : '';
$mode = isset($_POST['mode']) ? strtolower(trim((string) $_POST['mode'])) : 'all';
$seo_only = ($mode === 'seo');

if ($name === '') {
    eottae_json_send(array('success' => false, 'message' => '업체명을 먼저 입력해 주세요.'));
}

$context = array(
    '업체명' => $name,
    '카테고리' => $category,
    '대표지역' => $region,
    '주소' => $address,
    '전화' => $phone,
    '영업시간' => $hours,
    '휴무일' => $closed,
    '웹사이트' => $website,
    '인스타그램' => $instagram,
    '틱톡' => $tiktok,
    '페이스북' => $facebook,
    '네이버블로그' => $naver_blog,
    '기존소개' => $existing_intro,
);

$lines = array();
foreach ($context as $key => $value) {
    if ($value !== '') {
        $lines[] = $key.': '.$value;
    }
}

$json_fields = $seo_only
    ? 'seo_title, seo_intro, meta_description, focus_keyword'
    : 'intro, seo_title, seo_intro, meta_description, focus_keyword';

$prompt = "필리핀 세부 지역 생활정보 웹사이트 '세부어때'의 업체 등록 문구를 작성해 주세요.\n"
    ."아래 업체 정보를 바탕으로 과장 없이 자연스러운 한국어로 작성합니다.\n"
    ."반드시 JSON만 응답하고, 마크다운/설명 문장은 넣지 마세요.\n"
    .'JSON 필드: '.$json_fields."\n";
if (!$seo_only) {
    $prompt .= "- intro: 업체 소개 본문, 350~600자, 문단 2개 정도\n";
}
$prompt .= "- seo_title: 35자 이내, 검색 제목\n"
    ."- seo_intro: 80자 이내 한 줄 소개\n"
    ."- meta_description: 120~155자 검색 설명\n"
    ."- focus_keyword: 쉼표로 구분한 핵심 키워드 3~6개\n\n"
    ."업체 정보:\n".implode("\n", $lines);

$payload = array(
    'model' => $model,
    'messages' => array(
        array('role' => 'system', 'content' => 'You are a Korean SEO copywriter for local Cebu business listings. Return strict JSON only.'),
        array('role' => 'user', 'content' => $prompt),
    ),
    'temperature' => 0.7,
    'max_tokens' => 900,
    'response_format' => array('type' => 'json_object'),
);

if (!function_exists('curl_init')) {
    eottae_json_send(array('success' => false, 'message' => '서버 PHP cURL 확장이 필요합니다.'));
}

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer '.$api_key,
    ),
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 25,
));

$raw = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($raw === false || $raw === '' || $http_code < 200 || $http_code >= 300) {
    eottae_json_send(array(
        'success' => false,
        'message' => function_exists('eottae_ai_generate_openai_error_message')
            ? eottae_ai_generate_openai_error_message($http_code, $raw, $curl_error)
            : 'AI 자동생성 요청에 실패했습니다.',
    ));
}

$decoded = json_decode($raw, true);
$content = isset($decoded['choices'][0]['message']['content']) ? trim((string) $decoded['choices'][0]['message']['content']) : '';
$generated = json_decode($content, true);
if (!is_array($generated)) {
    eottae_json_send(array('success' => false, 'message' => 'AI 응답을 해석하지 못했습니다.'));
}

$result = array(
    'intro' => isset($generated['intro']) ? trim(strip_tags((string) $generated['intro'])) : '',
    'seo_title' => isset($generated['seo_title']) ? trim(strip_tags((string) $generated['seo_title'])) : '',
    'seo_intro' => isset($generated['seo_intro']) ? trim(strip_tags((string) $generated['seo_intro'])) : '',
    'meta_description' => isset($generated['meta_description']) ? trim(strip_tags((string) $generated['meta_description'])) : '',
    'focus_keyword' => isset($generated['focus_keyword']) ? trim(strip_tags((string) $generated['focus_keyword'])) : '',
);

eottae_json_send(array('success' => true, 'data' => $result));
