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
    /* PWA / Android TWA (lib/eottae-pwa.lib.php, docs/ANDROID-APP-GUIDE.md) */
    'pwa_enabled'           => true,
    'pwa_short_name'        => '세부어때',
    'pwa_start_url'         => '/page/eottae-app-home.php',
    'pwa_display'           => 'standalone',
    'pwa_theme_color'       => '',
    'pwa_background_color'  => '#ffffff',
    'pwa_manifest_path'     => '/proc/eottae-pwa-manifest.php',
    'pwa_icon_512_path'     => '/img/logo/android-chrome-512x512.png',
    'pwa_icon_192_path'     => '/img/logo/android-chrome-192x192.png',
    'pwa_icon_maskable_path'=> '/img/logo/android-chrome-512x512-maskable.png',
    'play_store_icon_path'  => '/img/logo/play-store-icon-512.png',
    'android_app_package'   => 'kr.co.thecebu.app',
    'android_app_name'      => '세부어때',
    /* 앱/Web Push — VAPID 키는 _site.config.local.php에 설정하세요 */
    'web_push_enabled'      => true,
    'web_push_public_key'   => '',
    'web_push_private_key_pem' => '',
    'web_push_subject'      => 'mailto:help@thecebu.co.kr',
    'web_push_prompt_app_only' => true,
    'og_image'            => '/img/logo/cebu-logo-main.png',
    /* SEO (components/seo-meta.php) */
    'seo_title'           => '',
    'seo_description'     => '',
    'main_keyword'        => '',
    'sub_keywords'        => '',
    'robots'              => 'index,follow',
    /* 네이버 서치어드바이저 소유 확인 (searchadvisor.naver.com) */
    'naver_site_verification' => 'aee2b0fc0761f0fb5ab3befb9cb0d234f1f5be3b',
    /* 네이버 RSS 제출 대표 피드 — /rss/{게시판ID} */
    'seo_primary_rss_board' => 'column',
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
    /* 업체 등록 AI — 키는 data/eottae-secrets.local.php (FTP 업로드, 배포 제외) 권장 */
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
    /* 광고 플랫폼 — 만료·연장 알림 크론 (talkroom_ai_cron_key 공용 가능) */
    'ad_platform_cron_key'      => '',
    'talkroom_ai_summary_start_time' => '21:00:00',
    'talkroom_ai_summary_end_time'   => '23:00:00',
    'talkroom_ai_summary_min_activity' => 5,
    /* 온오프챗봇 (components/onoff-chatbot.php) — chat.icrm.co.kr 관리자에 https://thecebu.co.kr 허용 도메인 등록 필요 */
    'onoff_chatbot_enabled'     => true,
    'onoff_chatbot_site_key'    => '967314350ee8ce35058ed2c5d0ed9039b10f200a829089af',
    'onoff_chatbot_page_url'    => '',  /* 비우면 G5_URL 사용 — iCRM origin 검사용 */
    'onoff_chatbot_origin_check_ttl' => 300,
    'onoff_chatbot_skip_origin_check' => false,
    'map_default_lat'           => '10.313',
    'map_default_lng'           => '123.9174',
    'map_default_zoom'          => 12,
    'map_use_current_location'  => true,
    'map_default_radius_km'     => 5,
    /* 현재 위치 기준 내주변 찾기(sst=near) 지도·목록 반경 */
    'map_near_radius_km'        => 1,
    /* 공개단톡 AI — 세부 날씨 자동 수집 (Open-Meteo) */
    'public_ai_weather_lat'     => '10.3157',
    'public_ai_weather_lon'     => '123.8854',
    /* 공개단톡 AI — 내부 데이터에 답이 없을 때 외부 웹검색 보강(선택, SerpAPI) */
    'life_qa_serpapi_key'       => '',
    /* 공개단톡 AI — 일반 호스팅용: 방문·폴링 트리거 (서버 crontab 불필요) */
    'public_ai_traffic_tick_enabled'  => true,
    'public_ai_traffic_tick_interval' => 90,
    'public_ai_traffic_grace_minutes' => 45,
    /* 번역 큐 — 일반 호스팅용: 방문 트리거 + 외부 웹크론 */
    'translation_traffic_tick_enabled'  => true,
    'translation_traffic_tick_interval' => 90,
    'translation_traffic_tick_limit'    => 2,
    'translation_traffic_tick_percent'  => 5,
    'map_unit'                  => 'km',
    'map_placeholder_title'     => 'Google Maps API 키가 설정되지 않았습니다.',
    'map_placeholder_desc'      => '_site.config.php에서 google_maps_api_key 값을 입력하면 지도가 표시됩니다.',
    /* 오늘의 세부 브리핑 — 관리자 안내 문구 (메인 브리핑 상단 노출) */
    'sebu_briefing_notice'      => '',
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
if (is_file($eottae_secrets_file) && is_readable($eottae_secrets_file)) {
    $eottae_secrets_override = null;
    include $eottae_secrets_file;
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

/* Google OAuth — 배포 시 data/eottae-google-oauth.local.php (GitHub Actions·FTP, AI 키 파일과 분리) */
if (!function_exists('eottae_merge_oauth_override_file')) {
    function eottae_merge_oauth_override_file(array &$site_config, $oauth_file)
    {
        if (!is_file($oauth_file) || !is_readable($oauth_file)) {
            return false;
        }
        $eottae_oauth_override = null;
        include $oauth_file;
        if (!isset($eottae_oauth_override) || !is_array($eottae_oauth_override)) {
            return false;
        }
        foreach ($eottae_oauth_override as $ok => $ov) {
            if ($ov === null || $ov === '') {
                continue;
            }
            if (is_string($ov) && trim($ov) === '') {
                continue;
            }
            $site_config[$ok] = $ov;
        }

        return true;
    }
}

$eottae_data_dir = defined('G5_DATA_PATH') ? G5_DATA_PATH : G5_PATH.'/data';
$eottae_oauth_file = $eottae_data_dir.'/eottae-google-oauth.local.php';
$eottae_oauth_example_file = $eottae_data_dir.'/eottae-google-oauth.local.example.php';
if (!eottae_merge_oauth_override_file($site_config, $eottae_oauth_file)) {
    eottae_merge_oauth_override_file($site_config, $eottae_oauth_example_file);
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
