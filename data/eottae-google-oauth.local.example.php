<?php
/**
 * Google 로그인 전용 (Git·FTP 배포 제외)
 *
 * eottae-secrets.local.php 와 분리해 OAuth만 올릴 때 사용합니다.
 * Google Cloud Console → 승인된 리디렉션 URI:
 *   https://thecebu.co.kr/plugin/social/?hauth.done=google
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$eottae_oauth_override = array(
    'google_oauth_client_id'     => '',
    'google_oauth_client_secret' => '',
);
