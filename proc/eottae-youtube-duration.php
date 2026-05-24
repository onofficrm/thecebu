<?php
/**
 * POST/GET /proc/eottae-youtube-duration.php
 * 유튜브 게시판 wr_3(재생시간) 일괄 갱신
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_PATH.'/setup/tools/eottae-seed.lib.php';
include_once G5_SKIN_PATH.'/board/_inc/g5b-youtube.php';

header('Content-Type: application/json; charset=utf-8');

$key = isset($_REQUEST['key']) ? trim((string) $_REQUEST['key']) : '';
$expected = substr(hash('sha256', G5_MYSQL_USER.G5_TABLE_PREFIX.'eottae-yt-duration-v1'), 0, 32);

if (!$is_admin && ($key === '' || !hash_equals($expected, $key))) {
    http_response_code(403);
    echo json_encode(array('ok' => false, 'message' => 'Forbidden'), JSON_UNESCAPED_UNICODE);
    exit;
}

$bo_table = defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube';
$write_table = $g5['write_prefix'].$bo_table;
$force = !empty($_REQUEST['force']);

$where = " wr_is_comment = 0 ";
if (!$force) {
    $where .= " and (wr_3 = '' or wr_3 = '0') ";
}

$result = sql_query(" select wr_id, wr_1, wr_content, wr_3 from {$write_table} where {$where} order by wr_id desc ");
$results = array();

while ($row = sql_fetch_array($result)) {
    $wr_id = (int) $row['wr_id'];
    $video_id = g5b_youtube_id_from_write($row);
    if ($video_id === '') {
        $results[] = array('ok' => false, 'wr_id' => $wr_id, 'message' => 'video id missing');
        continue;
    }

    $ok = g5b_youtube_save_duration($bo_table, $wr_id, $video_id);
    $updated = sql_fetch(" select wr_3 from {$write_table} where wr_id = '{$wr_id}' ");
    $seconds = is_array($updated) ? (int) $updated['wr_3'] : 0;

    $results[] = array(
        'ok'      => $ok && $seconds > 0,
        'wr_id'   => $wr_id,
        'seconds' => $seconds,
        'label'   => g5b_youtube_format_duration($seconds),
        'message' => $seconds > 0 ? 'updated' : 'fetch failed',
    );

    usleep(300000);
}

if (function_exists('run_event')) {
    run_event('cache_delete', 'board');
}

echo json_encode(array(
    'ok'      => true,
    'count'   => count($results),
    'results' => $results,
), JSON_UNESCAPED_UNICODE);
