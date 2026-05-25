<?php
/**
 * _site.config.local.php 예시 — 복사 후 키 입력 (Git에 커밋하지 마세요)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$site_config_override = array(
    'google_maps_api_key' => '',
    'ai_generate_enabled' => false,
    'ai_generate_api_key' => '',
    'ai_generate_model'   => 'gpt-4o-mini',
    'ai_generate_image_model' => 'gpt-image-1',
    'google_oauth_client_id' => '',
    'google_oauth_client_secret' => '',
);
