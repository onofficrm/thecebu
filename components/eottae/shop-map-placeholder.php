<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('onoff_map_get_config')) {
    include_once G5_PATH.'/components/maps/map-config.php';
}

$markers = isset($shop_map_markers) && is_array($shop_map_markers) ? $shop_map_markers : array();
$map_cfg = onoff_map_get_config();
$map_has_key = onoff_map_has_api_key();
$map_locations_json = eottae_shop_map_locations_json($markers);
?>

<section class="shop-map-panel<?php echo $map_has_key ? ' shop-map-panel--api' : ''; ?>"
         aria-label="지도 영역"
         <?php if ($map_has_key) { ?>
         data-eottae-shop-map
         data-shop-locations="<?php echo htmlspecialchars($map_locations_json, ENT_QUOTES, 'UTF-8'); ?>"
         data-map-lat="<?php echo htmlspecialchars((string) $map_cfg['default_lat'], ENT_QUOTES, 'UTF-8'); ?>"
         data-map-lng="<?php echo htmlspecialchars((string) $map_cfg['default_lng'], ENT_QUOTES, 'UTF-8'); ?>"
         data-map-zoom="<?php echo (int) $map_cfg['default_zoom']; ?>"
         <?php } ?>>
    <div class="shop-map-panel__canvas" id="shopMapPlaceholder">
        <?php if ($map_has_key) { ?>
        <div class="shop-map-panel__map" id="shopMapCanvas" role="application" aria-label="업체 지도"></div>
        <?php if (empty($markers)) { ?>
        <p class="shop-map-panel__placeholder shop-map-panel__placeholder--hint">좌표가 등록된 업체가 없습니다. 업체 등록 시 위도·경도를 입력해 주세요.</p>
        <?php } ?>
        <?php } else { ?>
        <p class="shop-map-panel__placeholder"><?php echo get_text($map_cfg['placeholder_title']); ?></p>
        <?php } ?>
    </div>
    <?php if ($map_has_key) { ?>
    <button type="button" class="shop-map-panel__locate" id="shopMapLocateBtn" title="내 위치">
        <span aria-hidden="true">⌖</span>
    </button>
    <?php } ?>
</section>
