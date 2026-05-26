<?php
/**
 * 사이트 공통 설정 (새 프로젝트마다 이 파일만 우선 수정)
 * 경로: /_site.config.php
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$site_config = array(
  /* 홈(/) URL — onoff-builder-bridge 프로젝트 ID (비우면 테마·section 메인 사용) */
  'home_builder_bridge_id' => 'thecebu-main',
  'site_name'           => '세부어때',
  'site_desc'           => '필리핀 세부 교민·관광객 위치기반 커뮤니티',
    'company_name'        => '세부어때',
    'ceo_name'            => '',
    'business_no'         => '',
    'phone'               => '0917-123-4567',
    'kakao_url'           => '#',
    'email'               => 'help@thecebu.co.kr',
    'address'             => 'Cebu, Philippines',
    'primary_color'       => '#0ea5e9',
    'secondary_color'     => '#64748b',
    'logo_path'           => '/img/logo/cebu-logo-main.png',
    'footer_logo_path'    => '/img/logo/cebu-logo-footer.png',
    'favicon_path'        => '/img/logo/favicon.ico',
    'favicon_png_path'    => '/img/logo/favicon-32x32.png',
    'favicon_16_path'     => '/img/logo/favicon-16x16.png',
    'apple_touch_icon_path' => '/img/logo/apple-touch-icon.png',
    'og_image'            => '/img/logo/cebu-logo-main.png',
    /* SEO (components/seo-meta.php) */
    'seo_title'           => '',
    'seo_description'     => '',
    'main_keyword'        => '',
    'sub_keywords'        => '',
    'robots'              => 'index,follow',
    'consultation_text'   => '상담문의',
    'footer_desc'         => '필리핀 세부 교민, 사업자, 관광객을 위한 위치기반 생활정보 커뮤니티.',
    /* 문의 폼 → inquiry 게시판 (proc/inquiry-submit.php) */
    'inquiry_bo_table'        => 'inquiry',
    'inquiry_notify_enabled'  => true,
    'inquiry_notify_email'    => 'admin@example.com',  /* 운영 시 실제 수신 주소로 변경 */
    'inquiry_notify_name'     => '관리자',
    /* 텔레그램 알림 — 운영 시 토큰·채팅 ID 입력 후 enabled true */
    'inquiry_notify_telegram_enabled'  => false,
    'inquiry_notify_telegram_bot_token' => '',
    'inquiry_notify_telegram_chat_id'   => '',
    /* 웹훅 알림 (Slack/Discord 등) — 추후 확장 */
    'inquiry_notify_webhook_enabled' => false,
    'inquiry_notify_webhook_url'     => '',
    /* 문의 접수 완료 페이지 (상대 경로) */
    'inquiry_thanks_url'      => '/page/inquiry-thanks.php',
    /* 전환·방문 추적 ID — 비우면 출력 안 함 */
    'gtm_id'              => '',
    'ga4_id'              => '',
    'meta_pixel_id'       => '',
    'naver_analytics_id'  => '',
    'kakao_pixel_id'      => '',
    /* 선택 항목 (비워 두면 기본값 사용) */
    'fax'                 => '',
    'sales_no'            => '',
    'privacy_manager'     => '',
    'kakao_map_key'       => '',
    'kakao_map_lat'       => '37.5665',
    'kakao_map_lng'       => '126.9780',
    /* Google Maps — 내 주변 찾기 (components/maps, page/map-locator.php) */
    'google_maps_api_key'       => '',
    /* 업체 등록 AI 자동생성 — 운영 키는 _site.config.local.php 또는 GitHub Secret으로 주입 */
    'ai_generate_enabled'       => false,
    'ai_generate_provider'      => 'openai',
    'ai_generate_api_key'       => '',
    'ai_generate_model'         => 'gpt-4o-mini',
    'ai_generate_image_model'   => 'gpt-image-1',
    /* 세부톡방 AI — bot 계정·크론 키 (_site.config.local.php 권장) */
    'talkroom_ai_bot_mb_id'     => 'sebu_ai',
    'talkroom_ai_cron_key'      => '',
    /* 세부어때 캘린더 — Google Calendar ID·동기화 크론 키 */
    'calendar_google_id'        => '4932d5025ebdd69d35ff4827f24d5fe976d7ac73a6020d89dd8fdc380b30c99c@group.calendar.google.com',
    'calendar_sync_cron_key'    => '',
    'talkroom_ai_summary_start_time' => '21:00:00',
    'talkroom_ai_summary_end_time'   => '23:00:00',
    'talkroom_ai_summary_min_activity' => 5,
    /* 온오프챗봇 (components/onoff-chatbot.php) */
    'onoff_chatbot_enabled'     => true,
    'onoff_chatbot_site_key'    => '967314350ee8ce35058ed2c5d0ed9039b10f200a829089af',
    'map_default_lat'           => '10.313',
    'map_default_lng'           => '123.9174',
    'map_default_zoom'          => 12,
    'map_use_current_location'  => true,
    'map_default_radius_km'     => 5,
    'map_unit'                  => 'km',
    'map_placeholder_title'     => 'Google Maps API 키가 설정되지 않았습니다.',
    'map_placeholder_desc'      => '_site.config.php에서 google_maps_api_key 값을 입력하면 지도가 표시됩니다.',
    /* Google OAuth 로그인 — 키는 _site.config.local.php (Git 제외) */
    'google_oauth_client_id'     => '',
    'google_oauth_client_secret' => '',
);

if (is_file(G5_PATH.'/_site.config.local.php')) {
    include_once G5_PATH.'/_site.config.local.php';
    if (isset($site_config_override) && is_array($site_config_override)) {
        $site_config = array_merge($site_config, $site_config_override);
    }
}

$eottae_secrets_file = (defined('G5_DATA_PATH') ? G5_DATA_PATH : G5_PATH.'/data').'/eottae-secrets.local.php';
if (is_file($eottae_secrets_file)) {
    include_once $eottae_secrets_file;
    if (isset($eottae_secrets_override) && is_array($eottae_secrets_override)) {
        $site_config = array_merge($site_config, $eottae_secrets_override);
    }
}

/**
 * 설정값 조회 (없거나 비어 있으면 $default)
 *
 * @param string $key
 * @param string $default
 * @return string
 */
if (!function_exists('g5site_cfg')) {
    function g5site_cfg($key, $default = '')
    {
        global $site_config;

        if (!isset($site_config) || !is_array($site_config)) {
            return (string) $default;
        }

        if (!array_key_exists($key, $site_config)) {
            return (string) $default;
        }

        $val = $site_config[$key];

        if ($val === null || $val === false) {
            return (string) $default;
        }

        if (is_string($val)) {
            $val = trim($val);
            return $val !== '' ? $val : (string) $default;
        }

        if (is_bool($val)) {
            return $val ? '1' : '';
        }

        return (string) $val;
    }
}

/**
 * bool 설정값 (true/false/1/0/off)
 *
 * @param string $key
 * @param bool   $default
 * @return bool
 */
if (!function_exists('g5site_cfg_bool')) {
    function g5site_cfg_bool($key, $default = false)
    {
        global $site_config;

        if (!isset($site_config) || !is_array($site_config) || !array_key_exists($key, $site_config)) {
            return (bool) $default;
        }

        $val = $site_config[$key];

        if ($val === true || $val === 1 || $val === '1' || $val === 'on' || $val === 'true') {
            return true;
        }
        if ($val === false || $val === 0 || $val === '0' || $val === 'off' || $val === 'false') {
            return false;
        }

        return (bool) $default;
    }
}

/**
 * URL 또는 사이트 루트 기준 경로
 *
 * @param string $key site_config 키 (logo_path, og_image 등)
 * @param string $default
 * @return string
 */
if (!function_exists('g5site_cfg_url')) {
    function g5site_cfg_url($key, $default = '')
    {
        $path = g5site_cfg($key, $default);

        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if (!defined('G5_URL')) {
            return $path;
        }

        if ($path[0] === '/') {
            return G5_URL . $path;
        }

        return G5_URL . '/' . $path;
    }
}

/**
 * 전화번호 → tel: 링크
 *
 * @param string $phone
 * @return string
 */
if (!function_exists('g5site_tel_link')) {
    function g5site_tel_link($phone = '')
    {
        if ($phone === '') {
            $phone = g5site_cfg('phone', '');
        }

        $digits = preg_replace('/[^0-9+]/', '', $phone);

        return $digits !== '' ? 'tel:' . $digits : '#';
    }
}
