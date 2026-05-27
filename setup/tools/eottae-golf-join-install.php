<?php
/**
 * 웹 — 골프조인 DB 테이블 설치 (최고관리자 전용)
 *
 * URL: /setup/tools/eottae-golf-join-install.php
 *      /setup/tools/eottae-golf-join-install.php?action=status
 *      /setup/tools/eottae-golf-join-install.php?action=rollback&confirm=1
 */
$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';

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

echo "Table prefix: ".G5_TABLE_PREFIX."\n";
echo "Member table: ".eottae_golf_join_member_table()."\n\n";

if ($action === 'status') {
    foreach (eottae_golf_join_schema_status() as $key => $row) {
        $flag = !empty($row['exists']) ? 'YES' : 'NO';
        echo sprintf("[%s] %s (%s)\n", $flag, $row['table'], $key);
    }

    $courses = eottae_golf_join_table_names()['courses'];
    if (eottae_golf_join_table_exists($courses)) {
        $cnt = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$courses}` ", false);
        echo "\nGolf courses seeded: ".(int) ($cnt['cnt'] ?? 0)."\n";
    }
    exit;
}

if ($action === 'rollback') {
    if (empty($_GET['confirm'])) {
        exit("Rollback requires confirm=1\nExample: ?action=rollback&confirm=1\n");
    }

    echo "Rolling back sebu_golf_join tables...\n";
    foreach (eottae_golf_join_drop_schema() as $entry) {
        $status = !empty($entry['ok']) ? 'OK' : 'FAIL';
        echo sprintf("[%s] %s (%s) %s\n", $status, $entry['table'], $entry['key'], $entry['action']);
    }
    exit;
}

echo "Installing sebu_golf_join tables (CREATE TABLE IF NOT EXISTS)...\n";
foreach (eottae_golf_join_ensure_schema() as $entry) {
    $status = !empty($entry['ok']) ? 'OK' : 'FAIL';
    echo sprintf("[%s] %s (%s) %s\n", $status, $entry['table'], $entry['key'], $entry['action']);
}

$courses = eottae_golf_join_table_names()['courses'];
if (eottae_golf_join_table_exists($courses)) {
    $cnt = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$courses}` ", false);
    echo "\nGolf courses in DB: ".(int) ($cnt['cnt'] ?? 0)."\n";
}

echo "\nDone.\n";
