<?php
define('EOTTae_POST_TRANSLATE', true);

include_once dirname(__FILE__).'/_eottae_json_bootstrap.php';
include_once G5_LIB_PATH.'/thumbnail.lib.php';
include_once G5_LIB_PATH.'/eottae-translation.lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_json_send(array('success' => false, 'message' => 'bad_request'), 405);
}

$token = isset($_POST['token']) ? trim((string) $_POST['token']) : '';
$session_token = function_exists('get_session') ? (string) get_session('eottae_post_translation_token') : '';
if ($token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
    eottae_json_send(array('success' => false, 'message' => 'invalid_token'), 403);
}

$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
$target_language = eottae_translation_normalize_language($_POST['target_language'] ?? '', '');
$source_language = eottae_translation_normalize_language($_POST['source_language'] ?? 'ko', 'ko');

if ($bo_table === '' || $wr_id < 1 || $target_language === '' || !isset(eottae_translation_supported_languages()[$target_language])) {
    eottae_json_send(array('success' => false, 'message' => 'invalid_params'), 400);
}

if ($target_language === $source_language) {
    eottae_json_send(array('success' => false, 'message' => 'same_language'), 400);
}

$board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' limit 1 ");
if (empty($board['bo_table'])) {
    eottae_json_send(array('success' => false, 'message' => 'board_not_found'), 404);
}

$write_table = $g5['write_prefix'].$bo_table;
$write = sql_fetch(" select * from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
if (empty($write['wr_id'])) {
    eottae_json_send(array('success' => false, 'message' => 'post_not_found'), 404);
}

$view = get_view($write, $board, G5_PATH.'/skin/board/'.$board['bo_skin']);
$html = function_exists('eottae_translation_post_html_mode') ? eottae_translation_post_html_mode($view) : 0;

$source_updated_at = eottae_translation_source_updated_at($write);
$cached = eottae_translation_cache_get($bo_table, $wr_id, $target_language, $source_updated_at);
if ($cached) {
    $title = eottae_translation_sanitize_title($cached['translated_title']);
    $content = eottae_translation_sanitize_content($cached['translated_content'], $html);
    $payload = array(
        'success' => true,
        'cached' => true,
        'provider' => $cached['provider'],
        'reviewStatus' => (string) ($cached['review_status'] ?? 'auto'),
        'targetLanguage' => $target_language,
        'translatedTitle' => $title,
        'translatedContent' => eottae_translation_render_content($content, $html),
        'plainContent' => $content,
    );
    $extras = eottae_translation_sanitize_extras(eottae_translation_decode_extras($cached['translated_extras'] ?? ''), $bo_table);
    if ($extras) {
        $payload['translatedExtras'] = $extras;
    }

    eottae_json_send($payload);
}

$result = eottae_translate_post(eottae_translation_build_translate_args(
    $bo_table,
    $write,
    $source_language,
    $target_language
));

if (empty($result['success'])) {
    eottae_json_send(array(
        'success' => false,
        'message' => 'translation_failed',
        'error' => (string) ($result['message'] ?? ''),
    ), 502);
}

$translated_title = eottae_translation_sanitize_title($result['translatedTitle'] ?? '');
$translated_content = eottae_translation_sanitize_content($result['translatedContent'] ?? '', $html);
if ($translated_title === '' && $translated_content === '') {
    eottae_json_send(array('success' => false, 'message' => 'empty_translation'), 502);
}

$provider = (string) ($result['provider'] ?? eottae_translation_provider());
$translated_extras = eottae_translation_sanitize_extras($result['translatedExtras'] ?? array(), $bo_table);
eottae_translation_cache_save(
    $bo_table,
    $wr_id,
    $source_language,
    $target_language,
    $translated_title,
    $translated_content,
    $provider,
    $source_updated_at,
    $translated_extras
);

$response = array(
    'success' => true,
    'cached' => false,
    'provider' => $provider,
    'reviewStatus' => 'auto',
    'targetLanguage' => $target_language,
    'translatedTitle' => $translated_title,
    'translatedContent' => eottae_translation_render_content($translated_content, $html),
    'plainContent' => $translated_content,
);
if ($translated_extras) {
    $response['translatedExtras'] = $translated_extras;
}

eottae_json_send($response);
