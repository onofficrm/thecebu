<?php
/**
 * OpenAI·비밀키 설정 진단 (최고관리자 전용, 키 값은 노출하지 않음)
 * GET /proc/eottae-ai-config-status.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';

header('Content-Type: application/json; charset=utf-8');

if ($is_admin !== 'super') {
    http_response_code(403);
    echo json_encode(array('ok' => false, 'message' => 'Forbidden'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!function_exists('eottae_secrets_load') && is_file(G5_LIB_PATH.'/eottae-secrets.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-secrets.lib.php';
}
if (function_exists('eottae_secrets_load')) {
    eottae_secrets_load();
} elseif (function_exists('eottae_merge_runtime_secrets')) {
    eottae_merge_runtime_secrets();
}
if (function_exists('eottae_ai_generate_clear_config_cache')) {
    eottae_ai_generate_clear_config_cache();
}

$status = function_exists('eottae_ai_config_status')
    ? eottae_ai_config_status()
    : array();

$api_key = function_exists('eottae_ai_openai_api_key')
    ? eottae_ai_openai_api_key()
    : (function_exists('g5site_cfg') ? trim((string) g5site_cfg('ai_generate_api_key', '')) : '');

echo json_encode(array(
    'ok'                 => true,
    'ready'              => $api_key !== '',
    'api_key_length'     => strlen($api_key),
    'api_key_prefix'     => $api_key !== '' ? substr($api_key, 0, 7).'…' : '',
    'enabled_flag'       => function_exists('g5site_cfg_bool') ? g5site_cfg_bool('ai_generate_enabled', false) : false,
    'model'              => function_exists('g5site_cfg') ? g5site_cfg('ai_generate_model', '') : '',
    'secrets_file'       => isset($status['secrets_file']) ? $status['secrets_file'] : '',
    'secrets_exists'     => !empty($status['secrets_exists']),
    'secrets_readable'   => !empty($status['secrets_readable']),
    'secrets_mtime'      => !empty($status['secrets_file']) && is_file($status['secrets_file'])
        ? date('Y-m-d H:i:s', (int) filemtime($status['secrets_file']))
        : null,
    'local_config_exists'=> !empty($status['local_config_exists']),
    'g5_data_path'       => defined('G5_DATA_PATH') ? G5_DATA_PATH : '',
), JSON_UNESCAPED_UNICODE);
