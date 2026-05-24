<?php
/**
 * 커뮤니티 샘플 글 1회 시드
 * URL: /setup/tools/eottae-community-seed-auto.php?key=eottae-community-2026&run=1
 */
$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);
include_once($g5_path.'/common.php');

if (!defined('_GNUBOARD_')) {
    exit;
}

include_once(__DIR__.'/eottae-seed.lib.php');

$expected_key = 'eottae-community-2026';
$key = isset($_GET['key']) ? trim($_GET['key']) : '';
$run = isset($_GET['run']) && $_GET['run'] === '1';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>커뮤니티 샘플 시드</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 720px; margin: 40px auto; padding: 0 16px; line-height: 1.6; }
        .ok { color: #059669; }
        .skip { color: #64748b; }
        .err { color: #dc2626; }
    </style>
</head>
<body>
    <h1>커뮤니티 샘플 글 시드</h1>

<?php
if ($key !== $expected_key) {
    echo '<p class="err">유효하지 않은 key입니다.</p>';
    echo '<p>예: <code>/setup/tools/eottae-community-seed-auto.php?key='.$expected_key.'&amp;run=1</code></p>';
    echo '</body></html>';
    exit;
}

if (!$run) {
    echo '<p><a href="?key='.htmlspecialchars($expected_key, ENT_QUOTES, 'UTF-8').'&amp;run=1">시드 실행</a></p>';
    echo '</body></html>';
    exit;
}

$logs = eottae_seed_community_samples_run();
$created = 0;
$skipped = 0;

foreach ($logs as $entry) {
    $cls = 'ok';
    if (!empty($entry['ok']) && strpos($entry['message'], 'already exists') !== false) {
        $cls = 'skip';
        $skipped++;
    } elseif (!empty($entry['ok'])) {
        $created++;
    } else {
        $cls = 'err';
    }
    $msg = isset($entry['message']) ? $entry['message'] : '';
    echo '<p class="'.$cls.'">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</p>';
}

echo '<hr><p><strong>완료.</strong> 생성 '.$created.'건, 스킵 '.$skipped.'건</p>';
echo '<p>API 확인: <a href="'.G5_URL.'/api/eottae.php?action=community">'.G5_URL.'/api/eottae.php?action=community</a></p>';
echo '<p>실행 후 <code>setup/tools/eottae-community-seed-auto.php</code> 파일을 삭제하세요.</p>';
echo '</body></html>';
