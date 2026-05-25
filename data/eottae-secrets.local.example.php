<?php
/**
 * 운영 서버 전용 비밀 설정 (FTP data/ 폴더에 업로드, Git·배포 대상 아님)
 *
 * 1. 이 파일을 data/eottae-secrets.local.php 로 복사
 * 2. google_maps_api_key 등 입력
 * 3. 서버 /public_html/data/eottae-secrets.local.php 로 업로드
 *
 * data/ 는 FTP 배포에서 제외되어 배포 후에도 유지됩니다.
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$eottae_secrets_override = array(
    'google_maps_api_key' => '',
);
