<?php
/**
 * 홈 히어로 공개 단체톡방 + AI 설정 자동 준비 CLI
 *
 * Usage: php setup/tools/eottae-talkroom-public-group-provision-cli.php
 */
if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

chdir(dirname(__FILE__).'/../..');
include_once dirname(__FILE__).'/../../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';

if (!function_exists('eottae_talkroom_public_group_ensure_provisioned')) {
    fwrite(STDERR, "eottae_talkroom_public_group_ensure_provisioned() not found\n");
    exit(1);
}

eottae_talkroom_public_group_ensure_provisioned();

$room_id = eottae_talkroom_public_group_room_id();
$room = $room_id > 0 ? eottae_talkroom_public_group_room() : null;

echo "Public hero talk room provision complete\n";
echo "room_id: {$room_id}\n";
if ($room) {
    echo "room_name: ".($room['room_name'] ?? '')."\n";
    echo "status: ".($room['status'] ?? '')."\n";
    echo "visibility: ".($room['visibility'] ?? '')."\n";
}

if ($room_id > 0 && function_exists('eottae_talkroom_ai_get_settings')) {
    include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
    $ai = eottae_talkroom_ai_get_settings($room_id);
    echo "ai_enabled: ".(!empty($ai['ai_enabled']) ? '1' : '0')."\n";
    echo "reaction_enabled: ".(!empty($ai['reaction_enabled']) ? '1' : '0')."\n";
}

exit($room_id > 0 ? 0 : 1);
