<?php
/**
 * 1회성 원격 설치 — 실행 후 즉시 삭제됩니다.
 */
$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);
include_once($g5_path.'/common.php');

if (!defined('_GNUBOARD_')) {
    http_response_code(500);
    exit('bootstrap fail');
}

$key = isset($_GET['key']) ? (string) $_GET['key'] : '';
$expected = 'thecebu.co.kr-20260524';
if (!hash_equals($expected, $key)) {
    http_response_code(403);
    exit('Forbidden');
}

include_once(__DIR__.'/eottae-install.lib.php');

header('Content-Type: text/plain; charset=utf-8');

$logs = eottae_install_run();
foreach ($logs as $entry) {
    echo ($entry['ok'] ? '[OK]' : '[FAIL]').' '.$entry['action'].': '.$entry['message']."\n";
}
echo "\ninstall complete\n";
