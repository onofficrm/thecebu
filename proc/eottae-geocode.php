<?php
/**
 * POST/GET — 주소 → 좌표 (Google Geocoding API)
 * /proc/eottae-geocode.php
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';

header('Content-Type: application/json; charset=utf-8');

if (!function_exists('onoff_map_get_config')) {
    include_once G5_PATH.'/components/maps/map-config.php';
}

if (!function_exists('onoff_map_has_api_key') || !onoff_map_has_api_key()) {
    echo json_encode(array('ok' => false, 'error' => 'maps_key_missing'), JSON_UNESCAPED_UNICODE);
    exit;
}

$address = isset($_REQUEST['address']) ? trim(strip_tags((string) $_REQUEST['address'])) : '';
if ($address === '') {
    echo json_encode(array('ok' => false, 'error' => 'address_required'), JSON_UNESCAPED_UNICODE);
    exit;
}

$cfg = onoff_map_get_config();
$key = isset($cfg['api_key']) ? $cfg['api_key'] : '';
$query = $address;
if (stripos($query, 'cebu') === false && stripos($query, '세부') === false) {
    $query .= ', Cebu, Philippines';
}

$url = 'https://maps.googleapis.com/maps/api/geocode/json?'.http_build_query(array(
    'address' => $query,
    'key'     => $key,
    'language' => 'ko',
));

$ctx = stream_context_create(array(
    'http' => array(
        'timeout' => 8,
        'ignore_errors' => true,
    ),
));

$raw = @file_get_contents($url, false, $ctx);
if ($raw === false || $raw === '') {
    echo json_encode(array('ok' => false, 'error' => 'geocode_failed'), JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($raw, true);
if (!is_array($data) || empty($data['results'][0]['geometry']['location'])) {
    $status = isset($data['status']) ? $data['status'] : 'UNKNOWN';
    $message = isset($data['error_message']) ? $data['error_message'] : '';
    echo json_encode(array(
        'ok' => false,
        'error' => 'no_result',
        'status' => $status,
        'message' => $message,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$result = $data['results'][0];
$loc = $result['geometry']['location'];
$formatted = isset($result['formatted_address']) ? $result['formatted_address'] : '';
$components = isset($result['address_components']) && is_array($result['address_components'])
    ? $result['address_components']
    : array();
$region = function_exists('eottae_shop_detect_region')
    ? eottae_shop_detect_region($address, $components)
    : '';

echo json_encode(array(
    'ok'      => true,
    'lat'     => (float) $loc['lat'],
    'lng'     => (float) $loc['lng'],
    'address' => $formatted,
    'region'  => $region,
), JSON_UNESCAPED_UNICODE);
