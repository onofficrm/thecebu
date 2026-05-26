<?php
/**
 * 업체 등록 — 지도 썸네일 AI 생성
 */
require_once dirname(__FILE__).'/_eottae_json_bootstrap.php';

include_once G5_LIB_PATH.'/eottae-ai-generate.lib.php';

if (empty($is_member)) {
    eottae_json_send(array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
}

if (!function_exists('curl_init')) {
    eottae_json_send(array('success' => false, 'message' => '서버 PHP cURL 확장이 필요합니다.'));
}

$ai_cfg = eottae_ai_generate_require_ready();
$api_key = $ai_cfg['api_key'];
$model = $ai_cfg['image_model'];

$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', (string) $_POST['bo_table']) : '';
if ($bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
    eottae_json_send(array('success' => false, 'message' => '업체 게시판에서만 이용할 수 있습니다.'));
}

$name = isset($_POST['name']) ? trim(strip_tags((string) $_POST['name'])) : '';
$category = isset($_POST['category']) ? trim(strip_tags((string) $_POST['category'])) : '';
$region = isset($_POST['region']) ? trim(strip_tags((string) $_POST['region'])) : '';
$address = isset($_POST['address']) ? trim(strip_tags((string) $_POST['address'])) : '';
$intro = isset($_POST['intro']) ? trim(strip_tags((string) $_POST['intro'])) : '';

if ($name === '') {
    eottae_json_send(array('success' => false, 'message' => '업체명을 먼저 입력해 주세요.'));
}

$prompt = "Create a clean square map marker thumbnail for a Cebu local business listing.\n"
    ."Style: modern travel/local directory thumbnail, photorealistic but polished, bright natural light, no text, no logo, no watermark.\n"
    ."Composition: centered subject, simple background, readable at small marker size, suitable for Google Maps pin.\n"
    ."Business name: {$name}\n"
    ."Category: {$category}\n"
    ."Region: {$region}\n"
    ."Address: {$address}\n"
    ."Description: {$intro}";

$payload = array(
    'model' => $model,
    'prompt' => $prompt,
    'size' => '1024x1024',
    'n' => 1,
);

$ch = curl_init('https://api.openai.com/v1/images/generations');
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer '.$api_key,
    ),
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 60,
));

$raw = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($raw === false || $raw === '' || $http_code < 200 || $http_code >= 300) {
    eottae_json_send(array(
        'success' => false,
        'message' => 'AI 썸네일 생성에 실패했습니다.',
        'debug' => $curl_error !== '' ? $curl_error : 'HTTP '.$http_code,
    ));
}

$decoded = json_decode($raw, true);
$b64 = isset($decoded['data'][0]['b64_json']) ? (string) $decoded['data'][0]['b64_json'] : '';
if ($b64 === '') {
    eottae_json_send(array('success' => false, 'message' => 'AI 이미지 응답을 해석하지 못했습니다.'));
}

$bin = base64_decode($b64, true);
if ($bin === false || $bin === '') {
    eottae_json_send(array('success' => false, 'message' => 'AI 이미지 변환에 실패했습니다.'));
}

if (!function_exists('eottae_shop_map_thumb_tmp_dir')) {
    eottae_json_send(array('success' => false, 'message' => '지도 썸네일 모듈을 불러오지 못했습니다.'));
}

$dir = eottae_shop_map_thumb_tmp_dir();
if (!is_dir($dir)) {
    @mkdir($dir, G5_DIR_PERMISSION, true);
    @chmod($dir, G5_DIR_PERMISSION);
}

$file = 'ai_'.date('YmdHis').'_'.substr(md5(uniqid('', true)), 0, 12).'.png';
if (@file_put_contents($dir.'/'.$file, $bin) === false) {
    eottae_json_send(array('success' => false, 'message' => 'AI 이미지 저장에 실패했습니다.'));
}
@chmod($dir.'/'.$file, G5_FILE_PERMISSION);

eottae_json_send(array(
    'success' => true,
    'data' => array(
        'tmp' => $file,
        'url' => eottae_shop_map_thumb_tmp_url_base().'/'.$file,
    ),
));
