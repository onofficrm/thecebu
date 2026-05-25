<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('onoff_map_get_config')) {
    include_once G5_PATH.'/components/maps/map-config.php';
}

$shop_map = isset($shop_map) && is_array($shop_map) ? $shop_map : array();
$address = isset($shop_map['address']) ? get_text($shop_map['address']) : '';
$region = isset($shop_map['region']) ? get_text($shop_map['region']) : '';
$lat = isset($shop_map['lat']) ? trim((string) $shop_map['lat']) : '';
$lng = isset($shop_map['lng']) ? trim((string) $shop_map['lng']) : '';
$name = isset($shop_map['name']) ? get_text($shop_map['name']) : '업체';
$thumbnail = '';
if (isset($shop_map['thumbnail']) && is_array($shop_map['thumbnail']) && !empty($shop_map['thumbnail']['url'])) {
    $thumbnail = $shop_map['thumbnail']['url'];
}
$dir_url = eottae_maps_directions_url($lat, $lng, $address);
$map_cfg = onoff_map_get_config();
$map_has_key = onoff_map_has_api_key() && $lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng);
?>

<section class="shop-detail-map<?php echo $map_has_key ? ' shop-detail-map--api' : ''; ?>" aria-label="위치"
    <?php if ($map_has_key) { ?>
    data-eottae-shop-detail-map
    data-map-lat="<?php echo htmlspecialchars($lat, ENT_QUOTES, 'UTF-8'); ?>"
    data-map-lng="<?php echo htmlspecialchars($lng, ENT_QUOTES, 'UTF-8'); ?>"
    data-map-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
    data-map-thumbnail="<?php echo htmlspecialchars($thumbnail, ENT_QUOTES, 'UTF-8'); ?>"
    data-map-zoom="15"
    <?php } ?>>
    <div class="shop-detail-map__canvas">
        <?php if ($map_has_key) { ?>
        <div class="shop-detail-map__map" role="application" aria-label="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?> 위치"></div>
        <?php } else { ?>
        <p class="shop-detail-map__placeholder">
            <?php if ($lat === '' || $lng === '') { ?>
            위도·경도가 등록되면 지도가 표시됩니다.
            <?php } else { ?>
            <?php echo get_text($map_cfg['placeholder_title']); ?>
            <?php } ?>
        </p>
        <?php if ($lat !== '' && $lng !== '') { ?>
        <span class="shop-detail-map__pin" aria-hidden="true">📍</span>
        <?php } ?>
        <?php } ?>
    </div>
    <div class="shop-detail-map__info">
        <?php if ($region) { ?><p class="shop-detail-map__region"><?php echo $region; ?></p><?php } ?>
        <?php if ($address) { ?><p class="shop-detail-map__address"><?php echo $address; ?></p><?php } ?>
        <?php if ($dir_url !== '#') { ?>
        <a href="<?php echo $dir_url; ?>" class="shop-detail-map__link" target="_blank" rel="noopener noreferrer">Google 지도에서 길찾기</a>
        <?php } ?>
    </div>
</section>
