<?php
/**
 * 1회성 Phase2 — review 게시판 생성 + 샘플 리뷰 seed (실행 후 삭제)
 */
$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);
include_once($g5_path.'/common.php');

if (!defined('_GNUBOARD_')) {
    http_response_code(500);
    exit('bootstrap fail');
}

$key = isset($_GET['key']) ? (string) $_GET['key'] : '';
if (!hash_equals('thecebu.co.kr-phase2-20260524', $key)) {
    http_response_code(403);
    exit('Forbidden');
}

include_once(__DIR__.'/eottae-install.lib.php');
include_once(__DIR__.'/eottae-seed.lib.php');
include_once(G5_LIB_PATH.'/eottae.lib.php');

header('Content-Type: text/plain; charset=utf-8');

$logs = array();
foreach (eottae_install_get_board_defs() as $def) {
    if ($def['bo_table'] !== 'review') {
        continue;
    }
    $logs[] = eottae_install_create_board($def);
}

foreach (eottae_seed_sample_reviews() as $entry) {
    $logs[] = $entry;
}

foreach ($logs as $entry) {
    echo ($entry['ok'] ? '[OK]' : '[FAIL]').' '.$entry['action'].': '.$entry['message']."\n";
}
echo "\nphase2 setup complete\n";
