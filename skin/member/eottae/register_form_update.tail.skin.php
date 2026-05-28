<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!is_file(G5_LIB_PATH.'/eottae-member-profile.lib.php')) {
    return;
}

include_once G5_LIB_PATH.'/eottae-member-profile.lib.php';

$mb_id = isset($mb_id) ? preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id) : '';
if ($mb_id === '') {
    return;
}

$ai_tmp = isset($_POST['eottae_mb_img_ai']) ? basename(preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $_POST['eottae_mb_img_ai'])) : '';
$has_upload = isset($_FILES['mb_img']) && is_uploaded_file($_FILES['mb_img']['tmp_name']);
$delete_requested = !empty($_POST['del_mb_img']);

if ($ai_tmp !== '' && !$has_upload && !$delete_requested) {
    $apply = eottae_member_profile_apply_ai_tmp($mb_id, $ai_tmp);
    if (empty($apply['ok']) && !empty($apply['message'])) {
        if (!isset($msg)) {
            $msg = '';
        }
        $msg .= ($msg !== '' ? "\\r\\n" : '').$apply['message'];
    }
}
