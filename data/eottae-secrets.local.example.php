<?php
/**
 * 세부어때 비밀 키 전용 설정 (Git·FTP 배포 제외 — 서버에 직접 업로드)
 *
 * 【설정 방법】
 * 1. 이 파일을 같은 폴더에 eottae-secrets.local.php 로 복사
 * 2. 아래 ai_generate_api_key 에 OpenAI 키 입력 후 FTP로
 *    /public_html/data/eottae-secrets.local.php 업로드
 * 3. 업체 등록 페이지에서 AI 버튼 동작 확인
 *
 * 지도 키(google_maps_api_key)도 여기에 넣을 수 있습니다.
 * _site.config.local.php 와 함께 써도 되며, 이 파일 값이 우선합니다.
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$eottae_secrets_override = array(
    /* Google Maps — 내주변·업체등록 지도 */
    'google_maps_api_key' => '',

    /* OpenAI — 업체등록 AI 자동작성 (필수) */
    'ai_generate_enabled'       => true,
    'ai_generate_api_key'       => 'sk-proj-여기에-OpenAI-키-입력',
    'ai_generate_model'         => 'gpt-4o-mini',
    'ai_generate_image_model'   => 'gpt-image-1',

    /* Google 로그인 (선택) */
    'google_oauth_client_id'     => '',
    'google_oauth_client_secret' => '',
);
