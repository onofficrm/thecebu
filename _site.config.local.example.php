<?php
/**
 * _site.config.local.php 예시 — 복사 후 키 입력 (Git에 커밋하지 마세요)
 *
 * AI 키는 data/eottae-secrets.local.php 전용 파일 사용을 권장합니다.
 * (data/eottae-secrets.local.example.php 참고, FTP로 /public_html/data/ 업로드)
 *
 * GitHub Actions 배포는 이 파일·data/ 비밀 파일을 덮어쓰지 않습니다.
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$site_config_override = array(
    /* 필수 — 내주변·업체등록 지도 */
    'google_maps_api_key' => '',

    /* 업체등록 AI 자동작성 */
    'ai_generate_enabled' => true,
    'ai_generate_api_key' => '',
    'ai_generate_model'   => 'gpt-4o-mini',
    'ai_generate_image_model' => 'gpt-image-1',

    /*
     * Google 로그인 (선택) — Console 승인 리디렉션 URI:
     * https://도메인/plugin/social/?hauth.done=google
     */
    'google_oauth_client_id' => '',
    'google_oauth_client_secret' => '',
);
