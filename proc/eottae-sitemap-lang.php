<?php
define('EOTTae_LANG_SITEMAP', true);

$g5_path = realpath(__DIR__.'/..');
chdir($g5_path);

$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'thecebu.co.kr';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'thecebu.co.kr';
$_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '443';
$_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? 'on';
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/proc/eottae-sitemap-lang.php';
$_SERVER['SCRIPT_NAME'] = '/proc/eottae-sitemap-lang.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

include_once $g5_path.'/_common.php';
include_once G5_LIB_PATH.'/eottae-lang-seo.lib.php';

if (!function_exists('eottae_lang_seo_enabled') || !eottae_lang_seo_enabled()) {
    header('Content-Type: text/plain; charset=utf-8', true, 404);
    echo 'lang seo disabled';
    exit;
}

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 200;
$entries = eottae_lang_seo_sitemap_entries($limit);
$xml = eottae_lang_seo_render_sitemap_xml($entries);

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');
echo $xml;
