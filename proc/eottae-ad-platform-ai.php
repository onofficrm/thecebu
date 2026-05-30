<?php
/**
 * 광고 플랫폼 — AI 문안·이미지 생성
 */
require_once dirname(__FILE__).'/_eottae_json_bootstrap.php';

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';
include_once G5_LIB_PATH.'/eottae-ai-generate.lib.php';

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_json_send(array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
}

if (!eottae_ad_platform_can_manage($member)) {
    eottae_json_send(array('success' => false, 'message' => '사업자회원 또는 최고관리자만 이용할 수 있습니다.'));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_json_send(array('success' => false, 'message' => '잘못된 요청입니다.'));
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : 'generate_copy';
$context = eottae_ad_platform_build_ai_context($member['mb_id'], array(
    'slot_code' => isset($_POST['slot_code']) ? $_POST['slot_code'] : '',
    'topic'     => isset($_POST['topic']) ? $_POST['topic'] : '',
    'tone'      => isset($_POST['tone']) ? $_POST['tone'] : 'friendly',
    'offer'     => isset($_POST['offer']) ? $_POST['offer'] : '',
    'target'    => isset($_POST['target']) ? $_POST['target'] : '',
));

if ($action === 'generate_image') {
    $ai_cfg = eottae_ai_generate_require_ready();
    $slot = eottae_ad_platform_get_slot_by_code(isset($_POST['slot_code']) ? $_POST['slot_code'] : '');
    $is_premium = $slot && !empty($slot['is_premium']);

    $shop_name = isset($context['shop_name']) ? $context['shop_name'] : 'Cebu business';
    $category = isset($context['category']) ? $context['category'] : 'local service';
    $region = isset($context['region']) ? $context['region'] : 'Cebu, Philippines';
    $topic = $context['topic'] !== '' ? $context['topic'] : (isset($context['shop_name']) ? $context['shop_name'].' promotion' : 'local promotion');
    $title_hint = isset($_POST['title']) ? trim(strip_tags((string) $_POST['title'])) : '';

    $style = $is_premium
        ? 'wide premium web banner, bold marketing visual, high contrast, polished commercial photography'
        : 'clean square-ish promotional banner for a community website ad slot';

    $prompt = "Create a {$style} for a Cebu local business ad.\n"
        ."No text, no logo, no watermark, no Korean letters in the image.\n"
        ."Bright natural light, trustworthy and inviting, suitable for Filipino/Korean community audience.\n"
        ."Business: {$shop_name}\n"
        ."Category: {$category}\n"
        ."Region: {$region}\n"
        ."Promotion theme: {$topic}\n";
    if ($title_hint !== '') {
        $prompt .= "Ad headline hint (do not render as text): {$title_hint}\n";
    }

    if (function_exists('eottae_ai_release_session_lock')) {
        eottae_ai_release_session_lock();
    }
    @set_time_limit(90);

    $payload = array(
        'model'  => $ai_cfg['image_model'],
        'prompt' => $prompt,
        'size'   => $is_premium ? '1536x1024' : '1024x1024',
        'n'      => 1,
    );

    $ch = curl_init('https://api.openai.com/v1/images/generations');
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$ai_cfg['api_key'],
        ),
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 80,
    ));
    $raw = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (PHP_VERSION_ID < 80500) {
        curl_close($ch);
    }

    if ($raw === false || $raw === '' || $http_code < 200 || $http_code >= 300) {
        eottae_json_send(array(
            'success' => false,
            'message' => function_exists('eottae_ai_generate_openai_error_message')
                ? eottae_ai_generate_openai_error_message($http_code, $raw, $curl_error)
                : 'AI 이미지 생성에 실패했습니다.',
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

    $saved = eottae_ad_platform_save_image_binary($bin, 'png');
    if (empty($saved['ok'])) {
        eottae_json_send(array('success' => false, 'message' => $saved['message'] ?? '이미지 저장 실패'));
    }

    eottae_json_send(array(
        'success' => true,
        'data'    => array(
            'image_url' => $saved['url'],
        ),
    ));
}

$ai_cfg = eottae_ai_generate_require_ready();
$model = $ai_cfg['model'];
$tone = $context['tone'] !== '' ? $context['tone'] : 'friendly';

$lines = array();
foreach ($context as $key => $value) {
    if ($value !== '') {
        $lines[] = $key.': '.$value;
    }
}

$prompt = "필리핀 세부 지역 커뮤니티 '세부어때' 광고 배너 문안을 작성해 주세요.\n"
    ."광고는 {$tone} 톤으로, 과장·허위 없이 클릭하고 싶게 작성합니다.\n"
    ."모바일 배너에 바로 넣을 수 있게 짧고 명확하게 작성합니다.\n"
    ."반드시 JSON만 응답: title, description, button_text\n"
    ."- title: 광고 제목 28자 이내\n"
    ."- description: 광고 설명 80~160자, 줄바꿈 가능\n"
    ."- button_text: 버튼 문구 8자 이내\n\n"
    ."입력 정보:\n".implode("\n", $lines);

$payload = array(
    'model' => $model,
    'messages' => array(
        array('role' => 'system', 'content' => 'You write Korean ad banner copy for Cebu community ads. Return strict JSON only.'),
        array('role' => 'user', 'content' => $prompt),
    ),
    'temperature' => 0.8,
    'max_tokens'  => 500,
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
            : 'AI 광고 문안 생성에 실패했습니다.',
    ));
}

eottae_json_send(array(
    'success' => true,
    'data'    => array(
        'title'       => isset($generated['title']) ? trim(strip_tags((string) $generated['title'])) : '',
        'description' => isset($generated['description']) ? trim((string) $generated['description']) : '',
        'button_text' => isset($generated['button_text']) ? trim(strip_tags((string) $generated['button_text'])) : '자세히 보기',
    ),
));
