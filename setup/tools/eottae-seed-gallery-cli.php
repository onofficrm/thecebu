<?php
/**
 * php setup/tools/eottae-seed-gallery-cli.php
 */
$g5_root = dirname(__DIR__, 2);
chdir($g5_root);

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-seed-gallery-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-seed-gallery-cli.php';

include_once $g5_root.'/common.php';
include_once __DIR__.'/eottae-seed.lib.php';

$logs = eottae_seed_gallery_samples_run();

foreach ($logs as $log) {
    $status = !empty($log['ok']) ? 'OK' : 'FAIL';
    echo $status.' ['.$log['action'].'] '.$log['message'].PHP_EOL;
}
