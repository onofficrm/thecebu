<?php
/**
 * CLI: iCRM URL helper 동작 확인 (로컬 DB 필요)
 *
 * php setup/tools/eottae-icrm-url-test.php community 123
 * php setup/tools/eottae-icrm-url-test.php community 123 --duplicate-check
 */
$g5_root = dirname(__DIR__, 2);
chdir($g5_root);
include_once $g5_root.'/common.php';
include_once G5_LIB_PATH.'/eottae-icrm.lib.php';

$bo_table = isset($argv[1]) ? (string) $argv[1] : '';
$wr_id = isset($argv[2]) ? (int) $argv[2] : 0;
$duplicate_check = in_array('--duplicate-check', $argv, true);

if ($bo_table === '' || $wr_id < 1) {
    fwrite(STDERR, "Usage: php setup/tools/eottae-icrm-url-test.php {bo_table} {wr_id} [--duplicate-check]\n");
    exit(1);
}

if ($duplicate_check) {
    global $g5;

    eottae_icrm_load_uri_lib();
    $write_table = $g5['write_prefix'].eottae_icrm_normalize_bo_table($bo_table);
    $subject = 'icrm-dup-'.date('YmdHis');
    $base_slug = generate_seo_title($subject);

    $slug_a = exist_seo_title_recursive('bbs', $base_slug, $write_table, 900000001);
    $slug_b = exist_seo_title_recursive('bbs', $base_slug, $write_table, 900000002);

    echo json_encode(array(
        'test'   => 'duplicate-slug-generation',
        'base'   => $base_slug,
        'slug_a' => $slug_a,
        'slug_b' => $slug_b,
        'unique' => $slug_a !== '' && $slug_b !== '' && $slug_a !== $slug_b,
    ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";

    exit($slug_a !== $slug_b ? 0 : 1);
}

$result = eottae_icrm_resolve_post($bo_table, $wr_id);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
exit(empty($result['ok']) ? 1 : 0);
