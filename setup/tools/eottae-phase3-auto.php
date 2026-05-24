<?php
/**
 * 세부어때 3차 — 쿠폰 테이블 생성·기본 쿠폰 시드 (1회 실행)
 *
 * URL: /setup/tools/eottae-phase3-auto.php?key=eottae-phase3-2026&run=1
 * 실행 후 이 파일을 삭제하세요.
 */
$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);
include_once($g5_path.'/common.php');

if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-coupon.lib.php';

$expected_key = 'eottae-phase3-2026';
$key = isset($_GET['key']) ? trim($_GET['key']) : '';
$run = isset($_GET['run']) && $_GET['run'] === '1';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>세부어때 Phase 3 설치</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; line-height: 1.6; }
        .ok { color: #059669; }
        .err { color: #dc2626; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>세부어때 Phase 3 설치</h1>
    <p>쿠폰 테이블 생성 및 웰컴·리뷰 감사 쿠폰 시드를 적용합니다.</p>

<?php
if ($key !== $expected_key) {
    echo '<p class="err">유효하지 않은 key입니다.</p>';
    echo '<p>예: <code>/setup/tools/eottae-phase3-auto.php?key='.$expected_key.'&amp;run=1</code></p>';
    echo '</body></html>';
    exit;
}

if (!$run) {
    echo '<p><a href="?key='.htmlspecialchars($expected_key, ENT_QUOTES, 'UTF-8').'&amp;run=1">설치 실행</a></p>';
    echo '</body></html>';
    exit;
}

$logs = array();

if (eottae_install_create_coupon_tables()) {
    $logs[] = array('ok' => true, 'message' => '쿠폰 테이블 생성 완료');
} else {
    $logs[] = array('ok' => false, 'message' => '쿠폰 테이블 생성 실패');
}

$seed_logs = eottae_coupon_seed_defaults();
foreach ($seed_logs as $entry) {
    $logs[] = $entry;
}

$logs[] = array('ok' => true, 'message' => 'API 확인: '.G5_URL.'/api/eottae.php?action=home');

foreach ($logs as $entry) {
    $cls = !empty($entry['ok']) ? 'ok' : 'err';
    $msg = isset($entry['message']) ? $entry['message'] : '';
    echo '<p class="'.$cls.'">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</p>';
}

echo '<hr><p><strong>완료.</strong> 보안을 위해 <code>setup/tools/eottae-phase3-auto.php</code> 파일을 삭제하세요.</p>';
echo '</body></html>';
