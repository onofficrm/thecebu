<?php
/**
 * 세부톡방 유지보수 API (배포·크론용, data/eottae-maintenance.local.php 키 필요)
 *
 * GET /proc/eottae-talkroom-maintenance.php?action=approve_pending_rooms&key=SECRET
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

header('Content-Type: text/plain; charset=utf-8');

$provided_key = isset($_GET['key']) ? trim((string) $_GET['key']) : '';

if (!eottae_talkroom_maintenance_verify_key($provided_key)) {
    http_response_code(403);
    exit("Forbidden\n");
}

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : '';

if ($action === 'approve_pending_rooms') {
    $dry_run = !empty($_GET['dry_run']);
    $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($config['cf_admin'] ?? 'admin'));
    $pending_count = eottae_talkroom_pending_count();

    echo "=== approve_pending_rooms ===\n";
    echo 'time: '.G5_TIME_YMDHIS."\n";
    echo 'pending_count: '.$pending_count."\n";
    echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n\n";

    if ($pending_count < 1) {
        echo "No pending room applications.\n";
        exit;
    }

    if ($dry_run) {
        $applications = eottae_talkroom_admin_resolve_applications('pending', 500);
        foreach ($applications as $application) {
            echo sprintf(
                "[DRY-RUN] room #%d — %s\n",
                (int) ($application['room_id'] ?? 0),
                (string) ($application['room_name'] ?? '')
            );
        }
        exit;
    }

    $result = eottae_talkroom_approve_all_pending_rooms($admin_mb_id);
    echo (string) ($result['message'] ?? '')."\n\n";
    foreach ($result['items'] as $item) {
        echo sprintf(
            "[%s] room #%d — %s (%s)\n",
            !empty($item['ok']) ? 'OK' : 'FAIL',
            (int) ($item['room_id'] ?? 0),
            (string) ($item['room_name'] ?? ''),
            (string) ($item['message'] ?? '')
        );
    }
    exit;
}

http_response_code(400);
echo "Unsupported action\n";
