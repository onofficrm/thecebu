<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('onoff_map_get_config')) {
    include_once G5_PATH.'/components/maps/map-config.php';
}

$shop_map = isset($shop_map) && is_array($shop_map) ? $shop_map : array();
$address = isset($shop_map['address']) ? get_text($shop_map['address']) : '';
$lat = isset($shop_map['lat']) ? trim((string) $shop_map['lat']) : '';
$lng = isset($shop_map['lng']) ? trim((string) $shop_map['lng']) : '';
$name = isset($shop_map['name']) ? get_text($shop_map['name']) : '업체';
$thumbnail = '';
if (isset($shop_map['thumbnail']) && is_array($shop_map['thumbnail']) && !empty($shop_map['thumbnail']['url'])) {
    $thumbnail = $shop_map['thumbnail']['url'];
}

$map_has_key = function_exists('onoff_map_has_api_key')
    && onoff_map_has_api_key()
    && $lat !== ''
    && $lng !== ''
    && is_numeric($lat)
    && is_numeric($lng);
$embed_url = function_exists('eottae_shop_map_embed_url') ? eottae_shop_map_embed_url($lat, $lng, $address) : '';
$dir_url = eottae_maps_directions_url($lat, $lng, $address);
$detail_link = '';
if (!empty($_SERVER['REQUEST_URI'])) {
    $detail_link = (defined('G5_URL') ? rtrim((string) G5_URL, '/') : '') . $_SERVER['REQUEST_URI'];
}

if (!$map_has_key && $embed_url === '') {
    return;
}
?>

<section class="shop-detail-map<?php echo $map_has_key ? ' shop-detail-map--api is-live' : ' shop-detail-map--embed is-live'; ?>" aria-label="위치"
    <?php if ($map_has_key) { ?>
    data-eottae-shop-detail-map
    data-map-lat="<?php echo htmlspecialchars($lat, ENT_QUOTES, 'UTF-8'); ?>"
    data-map-lng="<?php echo htmlspecialchars($lng, ENT_QUOTES, 'UTF-8'); ?>"
    data-map-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
    data-map-thumbnail="<?php echo htmlspecialchars($thumbnail, ENT_QUOTES, 'UTF-8'); ?>"
    data-map-link="<?php echo htmlspecialchars($detail_link, ENT_QUOTES, 'UTF-8'); ?>"
    data-map-zoom="15"
    <?php } ?>>
    <div class="shop-detail-map__canvas">
        <?php if ($map_has_key) { ?>
        <div class="shop-detail-map__map" role="application" aria-label="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?> 위치"></div>
        <?php } else { ?>
        <iframe
            class="shop-detail-map__embed"
            src="<?php echo htmlspecialchars($embed_url, ENT_QUOTES, 'UTF-8'); ?>"
            title="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?> 위치"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen></iframe>
        <?php } ?>
    </div>
    <?php if ($dir_url !== '#') { ?>
    <div class="shop-detail-map__foot">
        <a href="<?php echo $dir_url; ?>" class="shop-detail-map__link" target="_blank" rel="noopener noreferrer">Google 지도에서 길찾기</a>
    </div>
    <?php } ?>
</section>
