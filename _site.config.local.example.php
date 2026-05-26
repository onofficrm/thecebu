<?php
/**
 * _site.config.local.php 예시 — 복사 후 키 입력 (Git에 커밋하지 마세요)
 *
 * 주의: AI 키만 넣고 google_maps_api_key 를 빼면 지도가 안 나옵니다.
 *       기존 FTP 설정에 AI 항목만 추가하세요.
 * GitHub Actions 배포는 이 파일을 덮어쓰지 않습니다 (FTP에서 직접 관리).
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

    /* Google 로그인 (선택) */
    'google_oauth_client_id' => '',
    'google_oauth_client_secret' => '',
);
