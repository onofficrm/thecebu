<?php
/**
 * POST/GET — 주소 → 좌표 (Google Geocoding API)
 * /proc/eottae-geocode.php
 */
include_once dirname(__DIR__).'/common.php';

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
    echo json_encode(array('ok' => false, 'error' => 'no_result', 'status' => $status), JSON_UNESCAPED_UNICODE);
    exit;
}

$loc = $data['results'][0]['geometry']['location'];
$formatted = isset($data['results'][0]['formatted_address']) ? $data['results'][0]['formatted_address'] : '';

echo json_encode(array(
    'ok'      => true,
    'lat'     => (float) $loc['lat'],
    'lng'     => (float) $loc['lng'],
    'address' => $formatted,
), JSON_UNESCAPED_UNICODE);
