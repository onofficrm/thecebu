<?php
/**
 * 1회성 샘플 데이터·메뉴 seed — 실행 후 삭제
 */
$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);
include_once($g5_path.'/common.php');

if (!defined('_GNUBOARD_')) {
    http_response_code(500);
    exit('bootstrap fail');
}

$key = isset($_GET['key']) ? (string) $_GET['key'] : '';
if (!hash_equals('thecebu.co.kr-seed-20260524', $key)) {
    http_response_code(403);
    exit('Forbidden');
}

include_once(__DIR__.'/eottae-seed.lib.php');

header('Content-Type: text/plain; charset=utf-8');

$logs = eottae_seed_run();
foreach ($logs as $entry) {
    echo ($entry['ok'] ? '[OK]' : '[FAIL]').' '.$entry['action'].': '.$entry['message']."\n";
}
echo "\nseed complete\n";
