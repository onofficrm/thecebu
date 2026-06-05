<?php
define('EOTTae_SITEMAP', true);

$g5_path = realpath(__DIR__.'/..');
chdir($g5_path);

$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'thecebu.co.kr';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'thecebu.co.kr';
$_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '443';
$_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? 'on';
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/proc/eottae-sitemap.php';
$_SERVER['SCRIPT_NAME'] = '/proc/eottae-sitemap.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

include_once $g5_path.'/_common.php';
include_once G5_LIB_PATH.'/eottae-sitemap.lib.php';

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 300;
$entries = eottae_sitemap_entries($limit);
$xml = eottae_sitemap_render_xml($entries);

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
echo $xml;
