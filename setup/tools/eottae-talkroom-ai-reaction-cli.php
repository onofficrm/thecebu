<?php
/**
 * CLI — AI 리액션(댓글) 테스트
 *
 * 실행:    php setup/tools/eottae-talkroom-ai-reaction-cli.php --post-id=123
 * dry-run:  php setup/tools/eottae-talkroom-ai-reaction-cli.php --post-id=123 --dry-run
 * 강제:    php setup/tools/eottae-talkroom-ai-reaction-cli.php --post-id=123 --force
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
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-talkroom-ai-reaction-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-talkroom-ai-reaction-cli.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-reaction.lib.php';

if (!defined('_GNUBOARD_')) {
    fwrite(STDERR, "GNUBoard bootstrap failed\n");
    exit(1);
}

$argv = $argv ?? array();
$post_id = 0;
$dry_run = in_array('--dry-run', $argv, true);
$force = in_array('--force', $argv, true);

foreach ($argv as $arg) {
    if (strpos($arg, '--post-id=') === 0) {
        $post_id = (int) substr($arg, 10);
    }
}

if ($post_id < 1) {
    fwrite(STDERR, "Usage: php setup/tools/eottae-talkroom-ai-reaction-cli.php --post-id=WR_ID [--dry-run] [--force]\n");
    exit(1);
}

$result = eottae_talkroom_ai_run_reaction_for_post($post_id, array(
    'dry_run' => $dry_run,
    'force'   => $force,
));

echo "=== talkroom AI reaction ===\n";
echo 'post_id: '.(int) ($result['post_id'] ?? 0)."\n";
echo 'room_id: '.(int) ($result['room_id'] ?? 0)."\n";
echo 'status: '.(string) ($result['status'] ?? '')."\n";
echo 'reason: '.(string) ($result['reason'] ?? '')."\n";
echo 'message: '.(string) ($result['message'] ?? '')."\n";

if (!empty($result['reaction_type'])) {
    echo 'reaction_type: '.$result['reaction_type']."\n";
}
if (!empty($result['content'])) {
    echo "content:\n".$result['content']."\n";
}
if (!empty($result['comment_id'])) {
    echo 'comment_id: '.(int) $result['comment_id']."\n";
}

exit(($result['status'] ?? '') === 'success' ? 0 : 1);
