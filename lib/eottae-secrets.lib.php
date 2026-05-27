<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_secrets_file_path')) {
    /**
     * OpenAI·지도 등 비밀 키 전용 파일 (FTP 수동 업로드, Git·배포 제외)
     */
    function eottae_secrets_file_path()
    {
        if (defined('G5_DATA_PATH') && G5_DATA_PATH !== '') {
            return G5_DATA_PATH.'/eottae-secrets.local.php';
        }

        return G5_PATH.'/data/eottae-secrets.local.php';
    }
}

if (!function_exists('eottae_secrets_example_path')) {
    function eottae_secrets_example_path()
    {
        return G5_PATH.'/data/eottae-secrets.local.example.php';
    }
}

if (!function_exists('eottae_secrets_load')) {
    /**
     * _site.config.php + data/eottae-secrets.local.php 병합 (한 번만)
     */
    function eottae_secrets_load()
    {
        static $loaded = false;

        if ($loaded) {
            return;
        }
        $loaded = true;

        global $site_config;

        if (!isset($site_config) || !is_array($site_config)) {
            if (defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
                include_once G5_PATH.'/_site.config.php';
            }
        }

        if (!isset($site_config) || !is_array($site_config)) {
            $site_config = array();
        }

        $secret_file = eottae_secrets_file_path();
        if (is_file($secret_file) && is_readable($secret_file)) {
            $eottae_secrets_override = null;
            include $secret_file;
            if (isset($eottae_secrets_override) && is_array($eottae_secrets_override)) {
                foreach ($eottae_secrets_override as $sk => $sv) {
                    if ($sv === null || $sv === '') {
                        continue;
                    }
                    if (is_string($sv) && trim($sv) === '') {
                        continue;
                    }
                    $site_config[$sk] = $sv;
                }
            }
        }

        if (function_exists('onoff_map_clear_config_cache')) {
            onoff_map_clear_config_cache();
        }

        if (function_exists('eottae_ai_generate_clear_config_cache')) {
            eottae_ai_generate_clear_config_cache();
        }
    }
}

if (!function_exists('eottae_secrets_get')) {
    function eottae_secrets_get($key, $default = '')
    {
        eottae_secrets_load();

        return function_exists('g5site_cfg') ? g5site_cfg($key, $default) : $default;
    }
}

if (!function_exists('eottae_secrets_get_bool')) {
    function eottae_secrets_get_bool($key, $default = false)
    {
        eottae_secrets_load();

        return function_exists('g5site_cfg_bool') ? g5site_cfg_bool($key, $default) : (bool) $default;
    }
}

if (!function_exists('eottae_ai_openai_api_key')) {
    /**
     * OpenAI API 키 (data/eottae-secrets.local.php → _site.config.local.php → 환경변수)
     */
    function eottae_ai_openai_api_key()
    {
        eottae_secrets_load();

        $key = function_exists('g5site_cfg') ? trim((string) g5site_cfg('ai_generate_api_key', '')) : '';
        if ($key !== '') {
            return $key;
        }

        foreach (array('OPENAI_API_KEY', 'EOTTAE_OPENAI_API_KEY') as $env_key) {
            $env_val = getenv($env_key);
            if ($env_val !== false && trim((string) $env_val) !== '') {
                return trim((string) $env_val);
            }
        }

        return '';
    }
}

if (!function_exists('eottae_ai_is_ready')) {
    function eottae_ai_is_ready()
    {
        return eottae_ai_openai_api_key() !== '';
    }
}

if (!function_exists('eottae_ai_config_status')) {
    /**
     * 관리자·디버그용 — 키 값은 노출하지 않음
     *
     * @return array<string, mixed>
     */
    function eottae_ai_config_status()
    {
        eottae_secrets_load();

        $secrets_file = eottae_secrets_file_path();
        $local_file = G5_PATH.'/_site.config.local.php';

        return array(
            'ready'              => eottae_ai_is_ready(),
            'secrets_file'       => $secrets_file,
            'secrets_exists'     => is_file($secrets_file),
            'secrets_readable'   => is_file($secrets_file) && is_readable($secrets_file),
            'local_config_file'  => $local_file,
            'local_config_exists'=> is_file($local_file),
            'enabled_flag'       => eottae_secrets_get_bool('ai_generate_enabled', false),
            'model'              => eottae_secrets_get('ai_generate_model', 'gpt-4o-mini'),
        );
    }
}

if (!function_exists('eottae_ai_setup_hint_html')) {
    function eottae_ai_setup_hint_html()
    {
        $example = function_exists('eottae_secrets_example_path') ? eottae_secrets_example_path() : '';
        $target = function_exists('eottae_secrets_file_path') ? eottae_secrets_file_path() : 'data/eottae-secrets.local.php';
        $status = eottae_ai_config_status();

        $lines = array();
        $lines[] = 'OpenAI API 키는 <strong>data/eottae-secrets.local.php</strong> 파일에 등록합니다 (FTP·배포 제외, 서버에 직접 업로드).';
        if (!empty($status['secrets_exists'])) {
            $lines[] = '키 파일이 있습니다. <code>ai_generate_api_key</code> 값이 비어 있지 않은지 확인해 주세요.';
        } else {
            $lines[] = '<code>data/eottae-secrets.local.example.php</code>를 복사해 <code>'.htmlspecialchars(basename($target), ENT_QUOTES, 'UTF-8').'</code> 로 저장한 뒤 키를 입력하세요.';
        }

        return implode(' ', $lines);
    }
}
