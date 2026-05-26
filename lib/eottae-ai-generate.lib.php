<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_ai_generate_bootstrap_config')) {
    /**
     * 업체 등록·톡방 신청 등 OpenAI 텍스트/이미지 생성 공통 설정
     *
     * @return array{enabled:bool,api_key:string,model:string,image_model:string}
     */
    function eottae_ai_generate_bootstrap_config()
    {
        static $cached = null;

        if (is_array($cached)) {
            return $cached;
        }

        if (!isset($GLOBALS['site_config']) && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        if (function_exists('eottae_merge_runtime_secrets')) {
            eottae_merge_runtime_secrets();
        }

        $api_key = function_exists('g5site_cfg')
            ? trim((string) g5site_cfg('ai_generate_api_key', ''))
            : '';

        if ($api_key === '') {
            foreach (array('OPENAI_API_KEY', 'EOTTAE_OPENAI_API_KEY') as $env_key) {
                $env_val = getenv($env_key);
                if ($env_val !== false && trim((string) $env_val) !== '') {
                    $api_key = trim((string) $env_val);
                    break;
                }
            }
        }

        $enabled = function_exists('g5site_cfg_bool')
            ? g5site_cfg_bool('ai_generate_enabled', false)
            : false;

        if ($api_key !== '') {
            $enabled = true;
        }

        $model = function_exists('g5site_cfg')
            ? trim((string) g5site_cfg('ai_generate_model', 'gpt-4o-mini'))
            : 'gpt-4o-mini';
        if ($model === '') {
            $model = 'gpt-4o-mini';
        }

        $image_model = function_exists('g5site_cfg')
            ? trim((string) g5site_cfg('ai_generate_image_model', 'gpt-image-1'))
            : 'gpt-image-1';
        if ($image_model === '') {
            $image_model = 'gpt-image-1';
        }

        $cached = array(
            'enabled'      => $enabled,
            'api_key'      => $api_key,
            'model'        => $model,
            'image_model'  => $image_model,
        );

        return $cached;
    }
}

if (!function_exists('eottae_ai_generate_require_ready')) {
    /**
     * @return array{enabled:bool,api_key:string,model:string,image_model:string}
     */
    function eottae_ai_generate_require_ready()
    {
        $cfg = eottae_ai_generate_bootstrap_config();

        if (!$cfg['enabled'] || $cfg['api_key'] === '') {
            if (function_exists('eottae_json_send')) {
                eottae_json_send(array(
                    'success' => false,
                    'message' => 'AI 자동생성 API 키가 아직 설정되지 않았습니다. _site.config.local.php 또는 GitHub Secret OPENAI_API_KEY를 설정해 주세요.',
                ));
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'success' => false,
                'message' => 'AI 자동생성 API 키가 아직 설정되지 않았습니다. _site.config.local.php 또는 GitHub Secret OPENAI_API_KEY를 설정해 주세요.',
            ), JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!function_exists('curl_init')) {
            if (function_exists('eottae_json_send')) {
                eottae_json_send(array('success' => false, 'message' => '서버 PHP cURL 확장이 필요합니다.'));
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => false, 'message' => '서버 PHP cURL 확장이 필요합니다.'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        return $cfg;
    }
}

if (!function_exists('eottae_ai_generate_openai_error_message')) {
    function eottae_ai_generate_openai_error_message($http_code, $raw, $curl_error = '')
    {
        $http_code = (int) $http_code;
        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        $api_message = '';

        if (is_array($decoded) && isset($decoded['error']['message'])) {
            $api_message = trim((string) $decoded['error']['message']);
        }

        if ($api_message !== '') {
            return 'AI 자동생성 요청에 실패했습니다. ('.$api_message.')';
        }

        if ($curl_error !== '') {
            return 'AI 자동생성 요청에 실패했습니다. ('.$curl_error.')';
        }

        if ($http_code === 401) {
            return 'AI 자동생성 API 키가 올바르지 않습니다. OPENAI_API_KEY를 확인해 주세요.';
        }

        if ($http_code === 429) {
            return 'AI 사용 한도에 도달했습니다. 잠시 후 다시 시도해 주세요.';
        }

        return 'AI 자동생성 요청에 실패했습니다.'.($http_code > 0 ? ' (HTTP '.$http_code.')' : '');
    }
}
