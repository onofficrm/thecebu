<?php
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';

eottae_ad_platform_ensure_schema();

$ad_id = isset($_GET['ad_id']) ? (int) $_GET['ad_id'] : 0;
$redirect = isset($_GET['redirect']) ? trim((string) $_GET['redirect']) : G5_URL;

if ($ad_id < 1) {
    goto_redirect($redirect);
}

$target = eottae_ad_platform_record_click($ad_id);
if ($target === '') {
    goto_redirect($redirect);
}

if (!preg_match('#^https?://#i', $target)) {
    $target = G5_URL.'/'.ltrim($target, '/');
}

goto_url($target);
