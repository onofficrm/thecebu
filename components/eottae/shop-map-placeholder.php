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
$map_embed_url = '';
if (!$map_has_key && function_exists('eottae_shop_map_embed_url')) {
    $map_embed_url = eottae_shop_map_embed_url(
        isset($map_cfg['default_lat']) ? $map_cfg['default_lat'] : '',
        isset($map_cfg['default_lng']) ? $map_cfg['default_lng'] : '',
        'Cebu, Philippines'
    );
}
$map_use_embed = !$map_has_key && $map_embed_url !== '';
?>

<section class="shop-map-panel<?php echo $map_has_key ? ' shop-map-panel--api' : ($map_use_embed ? ' shop-map-panel--embed is-live' : ''); ?>"
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
        <p class="shop-map-panel__placeholder shop-map-panel__placeholder--hint">지도에 표시할 업체 좌표를 준비 중입니다. 주소 또는 대표지역이 있는 업체는 자동 보정됩니다.</p>
        <?php } ?>
        <?php } elseif ($map_use_embed) { ?>
        <iframe
            class="shop-map-panel__embed"
            src="<?php echo htmlspecialchars($map_embed_url, ENT_QUOTES, 'UTF-8'); ?>"
            title="세부 지역 지도"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen></iframe>
        <?php } else { ?>
        <p class="shop-map-panel__placeholder"><?php echo get_text($map_cfg['placeholder_title']); ?></p>
        <?php if (!empty($map_cfg['placeholder_desc'])) { ?>
        <p class="shop-map-panel__placeholder shop-map-panel__placeholder--desc"><?php echo get_text($map_cfg['placeholder_desc']); ?></p>
        <?php } ?>
        <?php } ?>
    </div>
    <?php if ($map_has_key) { ?>
    <button type="button" class="shop-map-panel__locate" id="shopMapLocateBtn" title="내 위치">
        <span aria-hidden="true">⌖</span>
    </button>
    <?php } ?>
</section>
