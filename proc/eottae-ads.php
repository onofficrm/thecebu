<?php
/**
 * GET /proc/eottae-ads.php?slot=community_sidebar
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae-ad.lib.php';

header('Content-Type: application/json; charset=utf-8');

$slot = isset($_GET['slot']) ? trim((string) $_GET['slot']) : EOTTae_AD_SLOT_SIDEBAR;
$ads = eottae_ad_get_active($slot, 10);

echo json_encode(array(
    'ok'   => true,
    'slot' => $slot,
    'ads'  => $ads,
), JSON_UNESCAPED_UNICODE);
