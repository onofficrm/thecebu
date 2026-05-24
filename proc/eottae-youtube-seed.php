<?php
/**
 * POST/GET /proc/eottae-youtube-seed.php
 * 관리자 로그인 또는 key 파라미터로 유튜브 게시판 URL 시드
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_PATH.'/setup/tools/eottae-seed.lib.php';

header('Content-Type: application/json; charset=utf-8');

$key = isset($_REQUEST['key']) ? trim((string) $_REQUEST['key']) : '';
$expected = substr(hash('sha256', G5_MYSQL_USER.G5_TABLE_PREFIX.'eottae-yt-seed-v1'), 0, 32);

if (!$is_admin && ($key === '' || !hash_equals($expected, $key))) {
    http_response_code(403);
    echo json_encode(array('ok' => false, 'message' => 'Forbidden'), JSON_UNESCAPED_UNICODE);
    exit;
}

$default_urls = array(
    'https://youtu.be/dTpAd1KFRmw?si=sMy_AX958KIKM5A7',
    'https://youtu.be/F6Wd88_Azlc?si=HcxSb3eKTB_g40Hu',
    'https://youtu.be/62cI6_Nu3Fk?si=Mc1b3quTLw02wg4p',
    'https://youtu.be/9M5LFQo_Zuw?si=IyxfIliDtiJExDYZ',
    'https://youtu.be/oMaHZlyy7Ms?si=-4lzKW0XqxRGH1ov',
    'https://youtu.be/bWh0KScQCBg?si=P1AMEe5xGAffMrxT',
    'https://youtu.be/QUtBy5YajdM?si=APQiHkS1EBiTS1rm',
);

$urls = $default_urls;
if (!empty($_REQUEST['urls'])) {
    $raw = is_array($_REQUEST['urls']) ? $_REQUEST['urls'] : explode("\n", (string) $_REQUEST['urls']);
    $urls = array_values(array_filter(array_map('trim', $raw)));
}

$logs = eottae_seed_youtube_urls($urls);
$results = array();

foreach ($logs as $log) {
    $results[] = array(
        'ok'      => !empty($log['ok']),
        'action'  => isset($log['action']) ? $log['action'] : '',
        'message' => isset($log['message']) ? $log['message'] : '',
    );
}

if (function_exists('run_event')) {
    run_event('cache_delete', 'board');
}

echo json_encode(array(
    'ok'      => true,
    'count'   => count($results),
    'results' => $results,
), JSON_UNESCAPED_UNICODE);
