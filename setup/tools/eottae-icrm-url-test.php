<?php
/**
 * CLI: iCRM URL helper 동작 확인 (로컬 DB 필요)
 *
 * php setup/tools/eottae-icrm-url-test.php community 123
 */
$g5_root = dirname(__DIR__, 2);
chdir($g5_root);
include_once $g5_root.'/common.php';
include_once G5_LIB_PATH.'/eottae-icrm.lib.php';

$bo_table = isset($argv[1]) ? (string) $argv[1] : '';
$wr_id = isset($argv[2]) ? (int) $argv[2] : 0;

if ($bo_table === '' || $wr_id < 1) {
    fwrite(STDERR, "Usage: php setup/tools/eottae-icrm-url-test.php {bo_table} {wr_id}\n");
    exit(1);
}

$result = eottae_icrm_resolve_post($bo_table, $wr_id);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
exit(empty($result['ok']) ? 1 : 0);
