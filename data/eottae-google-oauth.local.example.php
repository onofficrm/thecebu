<?php
/**
 * [템플릿] Google OAuth — 운영 서버에는 이 이름으로 올리지 마세요.
 *
 * 반드시 아래 파일명으로 FTP 업로드:
 *   data/eottae-google-oauth.local.php  (← .example 없음)
 *
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
