<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_location_picker_load_assets')) {
    include_once G5_LIB_PATH.'/eottae-location.lib.php';
}
eottae_location_picker_load_assets(true);

$location_picker = isset($location_picker) && is_array($location_picker) ? $location_picker : array();
$lp_id = preg_replace('/[^a-z0-9_-]/i', '', (string) ($location_picker['id'] ?? 'eottaeLocationPicker'));
if ($lp_id === '') {
    $lp_id = 'eottaeLocationPicker';
}
$lp_title = (string) ($location_picker['title'] ?? '위치');
$lp_desc = (string) ($location_picker['desc'] ?? '정확한 집 주소보다는 근처 랜드마크 기준으로 등록하는 것을 추천합니다.');
$lp_placeholder = (string) ($location_picker['placeholder'] ?? '예: IT Park 근처, 아얄라몰 근처, 막탄 뉴타운 근처');
$lp_required = !empty($location_picker['required']);
$lp_values = isset($location_picker['values']) && is_array($location_picker['values']) ? $location_picker['values'] : array();
$lp_fields = array_merge(array(
    'auto_area'     => 'auto_area',
    'location_text' => 'location_text',
    'latitude'      => 'latitude',
    'longitude'     => 'longitude',
    'map_visible'   => 'map_visible',
), isset($location_picker['fields']) && is_array($location_picker['fields']) ? $location_picker['fields'] : array());
$lp_auto_area = eottae_location_normalize_area($lp_values['auto_area'] ?? '');
$lp_location_text = get_text($lp_values['location_text'] ?? '');
$lp_latitude = get_text($lp_values['latitude'] ?? '');
$lp_longitude = get_text($lp_values['longitude'] ?? '');
$lp_map_visible = (string) ($lp_values['map_visible'] ?? '1') !== '0';
$lp_area_label = eottae_location_area_label($lp_auto_area);
?>

<section class="location-picker" id="<?php echo $lp_id; ?>" data-location-picker>
    <h2 class="location-picker__title"><?php echo get_text($lp_title); ?></h2>
    <?php if ($lp_desc !== '') { ?>
    <p class="location-picker__desc"><?php echo get_text($lp_desc); ?></p>
    <?php } ?>

    <div class="location-picker__field">
        <label for="<?php echo $lp_id; ?>_location">상세위치<?php echo $lp_required ? ' <span>*</span>' : ''; ?></label>
        <div class="location-picker__search-row">
            <input
                type="text"
                name="<?php echo get_text($lp_fields['location_text']); ?>"
                id="<?php echo $lp_id; ?>_location"
                value="<?php echo $lp_location_text; ?>"
                class="location-picker__input"
                placeholder="<?php echo get_text($lp_placeholder); ?>"
                data-location-address
                <?php echo $lp_required ? 'required' : ''; ?>
            >
            <button type="button" class="location-picker__btn" data-location-search>주소 검색</button>
        </div>
    </div>

    <input type="hidden" name="<?php echo get_text($lp_fields['latitude']); ?>" value="<?php echo $lp_latitude; ?>" data-location-lat>
    <input type="hidden" name="<?php echo get_text($lp_fields['longitude']); ?>" value="<?php echo $lp_longitude; ?>" data-location-lng>
    <input type="hidden" name="<?php echo get_text($lp_fields['auto_area']); ?>" value="<?php echo get_text($lp_auto_area); ?>" data-location-area>

    <p class="location-picker__status" data-location-status>
        자동분류 지역: <strong data-location-area-label><?php echo get_text($lp_area_label); ?></strong>
    </p>

    <div class="location-picker__actions">
        <button type="button" class="location-picker__btn location-picker__btn--secondary" data-location-current>현재위치 등록</button>
        <span class="location-picker__hint">지도에서 직접 핀을 찍거나 드래그할 수 있습니다.</span>
    </div>

    <div class="location-picker__map" data-location-map aria-label="<?php echo get_text($lp_title); ?> 지도"></div>

    <label class="location-picker__check">
        <input type="hidden" name="<?php echo get_text($lp_fields['map_visible']); ?>" value="0">
        <input type="checkbox" name="<?php echo get_text($lp_fields['map_visible']); ?>" value="1"<?php echo $lp_map_visible ? ' checked' : ''; ?>>
        <span>상세페이지에 지도 위치 표시</span>
    </label>
</section>
