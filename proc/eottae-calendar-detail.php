<?php
/**
 * 세부어때 캘린더 — 일정 상세 (JSON)
 * GET /proc/eottae-calendar-detail.php?event_id=123
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(array('ok' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
if ($event_id < 1) {
    echo json_encode(array('ok' => false, 'message' => '일정 정보가 올바르지 않습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$event = eottae_calendar_event_detail_api_payload($event_id);
if (!$event) {
    echo json_encode(array('ok' => false, 'message' => '일정을 찾을 수 없습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array(
    'ok'    => true,
    'event' => $event,
), JSON_UNESCAPED_UNICODE);
