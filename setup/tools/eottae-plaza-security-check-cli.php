<?php
/**
 * CLI — 세부광장 보안·기능 점검
 * php setup/tools/eottae-plaza-security-check-cli.php
 */
if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

$root = realpath(__DIR__.'/../..');
$checks = array();

function plaza_sec_check($id, $label, $ok, $detail = '')
{
    global $checks;
    $checks[] = array(
        'id'     => $id,
        'label'  => $label,
        'ok'     => (bool) $ok,
        'detail' => (string) $detail,
    );
}

$files = array(
    'lib/eottae-plaza.lib.php',
    'lib/eottae-plaza-likes.lib.php',
    'lib/eottae-plaza-report.lib.php',
    'lib/eottae-plaza-ai.lib.php',
    'proc/eottae-plaza-likes.php',
    'proc/eottae-plaza-report.php',
    'proc/eottae-plaza-admin.php',
    'cron/sebu_plaza_ai_daily_question.php',
    'page/eottae-admin-plaza-posts.php',
    'page/eottae-admin-plaza-reports.php',
    'page/eottae-admin-plaza-ai.php',
);

foreach ($files as $rel) {
    $path = $root.'/'.$rel;
    plaza_sec_check('file:'.basename($rel), $rel.' 존재', is_file($path));
}

$likes = file_get_contents($root.'/proc/eottae-plaza-likes.php');
plaza_sec_check('likes:post', '공감 POST only', strpos($likes, "REQUEST_METHOD'] !== 'POST'") !== false);
plaza_sec_check('likes:csrf', '공감 CSRF', strpos($likes, 'eottae_plaza_verify_member_token') !== false);
plaza_sec_check('likes:login', '공감 로그인 체크', strpos($likes, 'empty($is_member)') !== false);

$report = file_get_contents($root.'/proc/eottae-plaza-report.php');
plaza_sec_check('report:csrf', '신고 CSRF', strpos($report, 'eottae_plaza_verify_report_token') !== false);
plaza_sec_check('report:login', '신고 로그인 체크', strpos($report, 'empty($is_member)') !== false);

$admin = file_get_contents($root.'/proc/eottae-plaza-admin.php');
plaza_sec_check('admin:super', '관리자 super only', strpos($admin, "\$is_admin !== 'super'") !== false);
plaza_sec_check('admin:csrf', '관리자 CSRF', strpos($admin, 'eottae_plaza_verify_admin_token') !== false);

$lib = file_get_contents($root.'/lib/eottae-plaza.lib.php');
plaza_sec_check('lib:hidden', '삭제글 visible SQL', strpos($lib, 'eottae_plaza_post_visible_sql') !== false);
plaza_sec_check('lib:ai-block-type', 'AI질문 수동 작성 차단', strpos($lib, "ca_name === 'AI질문'") !== false);

$extend = file_get_contents($root.'/extend/eottae.extend.php');
plaza_sec_check('extend:view-guard', '상세 hidden guard', strpos($extend, 'eottae_plaza_guard_board_view') !== false);
plaza_sec_check('extend:write-login', '글쓰기 로그인', strpos($extend, 'eottae_plaza_login_url') !== false);
plaza_sec_check('extend:ai-marker', 'AI marker POST 차단', strpos($extend, "strpos(\$marker, 'ai:')") !== false);

$ai = file_get_contents($root.'/lib/eottae-plaza-ai.lib.php');
plaza_sec_check('ai:daily-once', 'AI 하루 1회', strpos($ai, 'eottae_plaza_ai_has_success_log_on_date') !== false);
plaza_sec_check('ai:template-api', 'AI API 분리', strpos($ai, 'eottae_plaza_ai_generate_daily_question_via_api') !== false);

$failed = 0;
echo "=== 세부광장 보안 점검 ===\n\n";
foreach ($checks as $row) {
    $mark = $row['ok'] ? 'OK' : 'FAIL';
    if (!$row['ok']) {
        $failed++;
    }
    echo sprintf("[%s] %s", $mark, $row['label']);
    if ($row['detail'] !== '') {
        echo ' — '.$row['detail'];
    }
    echo "\n";
}

echo "\nTotal: ".count($checks).", failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
