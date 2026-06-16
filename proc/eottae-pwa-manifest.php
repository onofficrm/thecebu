<?php
define('EOTTae_PWA_MANIFEST', true);

$g5_path = realpath(__DIR__.'/..');
chdir($g5_path);

$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'thecebu.co.kr';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'thecebu.co.kr';
$_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '443';
$_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? 'on';
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/proc/eottae-pwa-manifest.php';
$_SERVER['SCRIPT_NAME'] = '/proc/eottae-pwa-manifest.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

include_once $g5_path.'/_common.php';
include_once G5_LIB_PATH.'/eottae-pwa.lib.php';

if (!eottae_pwa_enabled()) {
    header('Content-Type: text/plain; charset=utf-8', true, 404);
    echo 'pwa disabled';
    exit;
}

header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: public, max-age=3600');
echo eottae_pwa_render_manifest_json();
