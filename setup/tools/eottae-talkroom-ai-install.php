<?php
/**
 * 웹 — 세부톡방 AI DB 테이블 설치 (최고관리자 전용)
 *
 * URL: /setup/tools/eottae-talkroom-ai-install.php
 *      /setup/tools/eottae-talkroom-ai-install.php?action=status
 *      /setup/tools/eottae-talkroom-ai-install.php?action=rollback&confirm=1
 */
$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';

if (!defined('_GNUBOARD_')) {
    http_response_code(500);
    exit('GNUBoard bootstrap failed');
}

header('Content-Type: text/plain; charset=utf-8');

if ($is_admin !== 'super') {
    http_response_code(403);
    exit('최고관리자만 실행할 수 있습니다.');
}

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : 'install';

echo "Table prefix: ".G5_TABLE_PREFIX."\n\n";

if ($action === 'status') {
    foreach (eottae_talkroom_ai_schema_status() as $key => $row) {
        $flag = !empty($row['exists']) ? 'YES' : 'NO';
        echo sprintf("[%s] %s (%s)\n", $flag, $row['table'], $key);
    }
    exit;
}

if ($action === 'rollback') {
    if (empty($_GET['confirm'])) {
        exit("Rollback requires confirm=1\nExample: ?action=rollback&confirm=1\n");
    }

    echo "Rolling back sebu_talk_ai tables...\n";
    foreach (eottae_talkroom_ai_drop_schema() as $entry) {
        $status = !empty($entry['ok']) ? 'OK' : 'FAIL';
        echo sprintf("[%s] %s (%s) %s\n", $status, $entry['table'], $entry['key'], $entry['action']);
    }
    exit;
}

echo "Installing sebu_talk_ai tables (CREATE TABLE IF NOT EXISTS)...\n";
foreach (eottae_talkroom_ai_ensure_schema() as $entry) {
    $status = !empty($entry['ok']) ? 'OK' : 'FAIL';
    echo sprintf("[%s] %s (%s) %s\n", $status, $entry['table'], $entry['key'], $entry['action']);
}

echo "\nDone.\n";
