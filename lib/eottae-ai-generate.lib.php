<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_ai_generate_clear_config_cache')) {
    function eottae_ai_generate_clear_config_cache()
    {
        eottae_ai_generate_bootstrap_config(true);
    }
}

if (!function_exists('eottae_ai_generate_bootstrap_config')) {
    /**
     * 업체 등록·톡방 신청 등 OpenAI 텍스트/이미지 생성 공통 설정
     *
     * @param bool $force_reload
     * @return array{enabled:bool,api_key:string,model:string,image_model:string}
     */
    function eottae_ai_generate_bootstrap_config($force_reload = false)
    {
        static $cached = null;

        if ($force_reload) {
            $cached = null;
        }

        if (is_array($cached)) {
            return $cached;
        }

        if (!function_exists('eottae_secrets_load') && is_file(G5_LIB_PATH.'/eottae-secrets.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-secrets.lib.php';
        }

        if (function_exists('eottae_secrets_load')) {
            eottae_secrets_load();
        } else {
            if (!isset($GLOBALS['site_config']) && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
                include_once G5_PATH.'/_site.config.php';
            }
            if (function_exists('eottae_merge_runtime_secrets')) {
                eottae_merge_runtime_secrets();
            }
        }

        $api_key = function_exists('eottae_ai_openai_api_key')
            ? eottae_ai_openai_api_key()
            : (function_exists('g5site_cfg') ? trim((string) g5site_cfg('ai_generate_api_key', '')) : '');

        $enabled = function_exists('eottae_secrets_get_bool')
            ? eottae_secrets_get_bool('ai_generate_enabled', false)
            : false;

        if ($api_key !== '') {
            $enabled = true;
        }

        $model = function_exists('eottae_secrets_get')
            ? trim((string) eottae_secrets_get('ai_generate_model', 'gpt-4o-mini'))
            : 'gpt-4o-mini';
        if ($model === '') {
            $model = 'gpt-4o-mini';
        }

        $image_model = function_exists('eottae_secrets_get')
            ? trim((string) eottae_secrets_get('ai_generate_image_model', 'gpt-image-1'))
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
                    'message' => 'AI 자동생성 API 키가 설정되지 않았습니다. 서버 data/eottae-secrets.local.php 파일에 ai_generate_api_key를 등록해 주세요.',
                ));
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'success' => false,
                'message' => 'AI 자동생성 API 키가 설정되지 않았습니다. 서버 data/eottae-secrets.local.php 파일에 ai_generate_api_key를 등록해 주세요.',
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

if (!function_exists('eottae_ai_release_session_lock')) {
    /**
     * OpenAI 등 장시간 외부 API 호출 전 세션 잠금 해제 (동일 브라우저 요청 정체 방지)
     */
    function eottae_ai_release_session_lock()
    {
        if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }
}

if (!function_exists('eottae_ai_openai_chat_completion')) {
    /**
     * OpenAI Chat Completions — 공통 cURL (타임아웃·세션 잠금 해제)
     *
     * @param array<string, mixed> $payload chat/completions body (model, messages, …)
     * @param array<string, mixed> $options timeout(int), connect_timeout(int)
     * @return array{ok:bool,content:string,http_code:int,error:string,raw:string,model:string}
     */
    function eottae_ai_openai_chat_completion(array $payload, array $options = array())
    {
        $cfg = eottae_ai_generate_bootstrap_config();
        $api_key = isset($cfg['api_key']) ? (string) $cfg['api_key'] : '';
        $model = isset($payload['model']) ? trim((string) $payload['model']) : '';
        if ($model === '') {
            $model = isset($cfg['model']) ? (string) $cfg['model'] : 'gpt-4o-mini';
        }
        if ($model === '') {
            $model = 'gpt-4o-mini';
        }
        $payload['model'] = $model;

        if ($api_key === '' || !function_exists('curl_init')) {
            return array(
                'ok'         => false,
                'content'    => '',
                'http_code'  => 0,
                'error'      => $api_key === '' ? 'no_api_key' : 'no_curl',
                'raw'        => '',
                'model'      => $model,
            );
        }

        $timeout = max(15, min(90, (int) ($options['timeout'] ?? 45)));
        $connect_timeout = max(5, min(30, (int) ($options['connect_timeout'] ?? 10)));

        eottae_ai_release_session_lock();
        @set_time_limit($timeout + 20);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$api_key,
            ),
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_CONNECTTIMEOUT => $connect_timeout,
            CURLOPT_TIMEOUT        => $timeout,
        ));

        $raw = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $raw === '') {
            return array(
                'ok'         => false,
                'content'    => '',
                'http_code'  => $http_code,
                'error'      => $curl_error !== '' ? $curl_error : 'empty_response',
                'raw'        => '',
                'model'      => $model,
            );
        }

        $decoded = json_decode($raw, true);
        if ($http_code < 200 || $http_code >= 300) {
            $err = 'http_'.$http_code;
            if (is_array($decoded) && isset($decoded['error']['message'])) {
                $err = trim((string) $decoded['error']['message']);
            }

            return array(
                'ok'         => false,
                'content'    => '',
                'http_code'  => $http_code,
                'error'      => $err,
                'raw'        => (string) $raw,
                'model'      => $model,
            );
        }

        $content = isset($decoded['choices'][0]['message']['content'])
            ? trim((string) $decoded['choices'][0]['message']['content'])
            : '';

        return array(
            'ok'         => $content !== '',
            'content'    => $content,
            'http_code'  => $http_code,
            'error'      => $content !== '' ? '' : 'empty_content',
            'raw'        => (string) $raw,
            'model'      => $model,
        );
    }
}

if (!function_exists('eottae_ai_openai_parse_json_content')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_ai_openai_parse_json_content(array $completion)
    {
        if (empty($completion['ok']) || empty($completion['content'])) {
            return null;
        }

        $generated = json_decode((string) $completion['content'], true);

        return is_array($generated) ? $generated : null;
    }
}
