<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$shop_map = isset($shop_map) && is_array($shop_map) ? $shop_map : array();
$address = isset($shop_map['address']) ? get_text($shop_map['address']) : '';
$region = isset($shop_map['region']) ? get_text($shop_map['region']) : '';
$lat = isset($shop_map['lat']) ? trim((string) $shop_map['lat']) : '';
$lng = isset($shop_map['lng']) ? trim((string) $shop_map['lng']) : '';
$dir_url = eottae_maps_directions_url($lat, $lng, $address);
?>

<section class="shop-detail-map" aria-label="위치">
    <div class="shop-detail-map__canvas">
        <p class="shop-detail-map__placeholder">Google Maps API (연동 예정)</p>
        <?php if ($lat !== '' && $lng !== '') { ?>
        <span class="shop-detail-map__pin" aria-hidden="true">📍</span>
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
