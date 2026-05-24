<?php
/**
 * CLI — 유튜브 게시판 URL 일괄 등록
 * php setup/tools/eottae-seed-youtube-cli.php
 * php setup/tools/eottae-seed-youtube-cli.php "https://youtu.be/VIDEO_ID"
 */
if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);

$_SERVER['SERVER_NAME'] = 'thecebu.co.kr';
$_SERVER['HTTP_HOST'] = 'thecebu.co.kr';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

include_once $g5_path.'/common.php';
include_once __DIR__.'/eottae-seed.lib.php';

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
if ($argc > 1) {
    $urls = array_slice($argv, 1);
}

$logs = eottae_seed_youtube_urls($urls);
foreach ($logs as $log) {
    $status = !empty($log['ok']) ? 'OK' : 'FAIL';
    echo $status.' ['.$log['action'].'] '.$log['message'].PHP_EOL;
}

echo "Done.\n";
