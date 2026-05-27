<?php
/**
 * eottae JSON API 공통 bootstrap — HTML/경고 출력 없이 JSON만 반환
 */
if (!defined('EOTTae_JSON_BOOTSTRAP')) {
    define('EOTTae_JSON_BOOTSTRAP', true);
}

if (!ob_get_level()) {
    ob_start();
}

@include_once dirname(__FILE__).'/../_common.php';

if (!function_exists('eottae_json_send')) {
    function eottae_json_send($payload, $http_code = 200)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        if ($http_code >= 400) {
            http_response_code($http_code);
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!defined('_GNUBOARD_')) {
    eottae_json_send(array(
        'success' => false,
        'message' => '시스템 초기화에 실패했습니다.',
    ), 500);
}

if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
    include_once G5_PATH.'/_site.config.php';
}

if (!function_exists('eottae_is_shop_board') && is_file(G5_LIB_PATH.'/eottae.lib.php')) {
    include_once G5_LIB_PATH.'/eottae.lib.php';
}

if (!function_exists('eottae_secrets_load') && is_file(G5_LIB_PATH.'/eottae-secrets.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-secrets.lib.php';
}

if (!function_exists('eottae_ai_generate_bootstrap_config') && is_file(G5_LIB_PATH.'/eottae-ai-generate.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-ai-generate.lib.php';
}

if (function_exists('eottae_secrets_load')) {
    eottae_secrets_load();
} elseif (function_exists('eottae_merge_runtime_secrets')) {
    eottae_merge_runtime_secrets();
}
