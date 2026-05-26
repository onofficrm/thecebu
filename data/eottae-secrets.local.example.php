<?php
/**
 * 운영 서버 전용 비밀 설정 (FTP data/ 폴더에 업로드, Git·배포 대상 아님)
 *
 * 1. 이 파일을 data/eottae-secrets.local.php 로 복사
 * 2. google_maps_api_key 등 입력
 * 3. 권장: /public_html/_site.config.local.php 에 지도·AI·OAuth 키를 한곳에 설정
 *    (GitHub Actions 배포는 _site.config.local.php 를 덮어쓰지 않음)
 * 4. 이 파일(data/eottae-secrets.local.php)은 선택 사항 — FTP 수동 업로드 시에만 사용
 *
 * data/ 는 FTP 배포에서 제외되어 배포 후에도 유지됩니다.
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$eottae_secrets_override = array(
    'google_maps_api_key' => '',
);
