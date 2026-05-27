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
$map_markers_api = isset($eottae_shop_list_api) ? (string) $eottae_shop_list_api : G5_URL.'/proc/eottae-shop-list.php';
$map_bo_table = isset($bo_table) ? preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table) : (defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop');
$map_near_radius_km = function_exists('eottae_shop_near_radius_km') ? eottae_shop_near_radius_km() : 1;
$map_near_active = false;
$map_user_lat = '';
$map_user_lng = '';
if (function_exists('eottae_shop_user_coords_from_request')) {
    $map_user_coords = eottae_shop_user_coords_from_request();
    if ($map_user_coords && function_exists('eottae_shop_is_near_search_request') && eottae_shop_is_near_search_request()) {
        $map_near_active = true;
        $map_user_lat = (string) $map_user_coords['lat'];
        $map_user_lng = (string) $map_user_coords['lng'];
    }
}
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
         data-map-markers-api="<?php echo htmlspecialchars($map_markers_api, ENT_QUOTES, 'UTF-8'); ?>"
         data-map-bo-table="<?php echo htmlspecialchars($map_bo_table, ENT_QUOTES, 'UTF-8'); ?>"
         data-map-lat="<?php echo htmlspecialchars($map_near_active ? $map_user_lat : (string) $map_cfg['default_lat'], ENT_QUOTES, 'UTF-8'); ?>"
         data-map-lng="<?php echo htmlspecialchars($map_near_active ? $map_user_lng : (string) $map_cfg['default_lng'], ENT_QUOTES, 'UTF-8'); ?>"
         data-map-zoom="<?php echo (int) $map_cfg['default_zoom']; ?>"
         data-map-near-radius-km="<?php echo htmlspecialchars((string) $map_near_radius_km, ENT_QUOTES, 'UTF-8'); ?>"
         data-map-near-active="<?php echo $map_near_active ? '1' : '0'; ?>"
         <?php if ($map_near_active) { ?>
         data-map-user-lat="<?php echo htmlspecialchars($map_user_lat, ENT_QUOTES, 'UTF-8'); ?>"
         data-map-user-lng="<?php echo htmlspecialchars($map_user_lng, ENT_QUOTES, 'UTF-8'); ?>"
         <?php } ?>
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
        <?php
        global $is_admin;
        if (!empty($is_admin) && function_exists('eottae_map_runtime_diagnostics')) {
            $map_diag = eottae_map_runtime_diagnostics();
            ?>
        <p class="shop-map-panel__placeholder shop-map-panel__placeholder--desc shop-map-panel__admin-hint">
            관리자: Google Maps API 키가 서버에서 읽히지 않아 임베드 지도만 표시 중입니다.
            secrets <?php echo !empty($map_diag['secrets_readable']) ? '읽기 가능' : '없음/읽기 불가'; ?>
            (<?php echo get_text($map_diag['secrets_path']); ?>).
        </p>
            <?php
        }
        ?>
        <?php } else { ?>
        <p class="shop-map-panel__placeholder"><?php echo get_text($map_cfg['placeholder_title']); ?></p>
        <?php if (!empty($map_cfg['placeholder_desc'])) { ?>
        <p class="shop-map-panel__placeholder shop-map-panel__placeholder--desc"><?php echo get_text($map_cfg['placeholder_desc']); ?></p>
        <?php } ?>
        <?php
        global $is_admin;
        if (!empty($is_admin) && function_exists('eottae_map_runtime_diagnostics')) {
            $map_diag = eottae_map_runtime_diagnostics();
            ?>
        <p class="shop-map-panel__placeholder shop-map-panel__placeholder--desc">
            관리자 안내:
            secrets 파일 <?php echo !empty($map_diag['secrets_readable']) ? '읽기 가능' : '없음/읽기 불가'; ?>
            (<?php echo get_text($map_diag['secrets_path']); ?>).
            서버 FTP로 업로드 후 새로고침하세요.
        </p>
            <?php
        }
        ?>
        <?php } ?>
    </div>
    <?php if ($map_has_key) { ?>
    <button type="button" class="shop-map-panel__locate" id="shopMapLocateBtn" title="내 위치">
        <span aria-hidden="true">⌖</span>
    </button>
    <?php } ?>
</section>
