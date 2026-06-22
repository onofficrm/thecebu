<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_is_estate_board') || !eottae_is_estate_board($bo_table ?? '')) {
    return;
}

if (function_exists('eottae_property_template_load_assets')) {
    eottae_property_template_load_assets();
}

if (!function_exists('eottae_location_area_label') && is_file(G5_LIB_PATH.'/eottae-location.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-location.lib.php';
}

if (!function_exists('eottae_estate_deal_status_from_row') && is_file(G5_LIB_PATH.'/eottae-estate.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-estate.lib.php';
}

$estate_deal_status = 'trading';
$estate_region = '';
$estate_template_json = '';
$estate_template_values = array();
$estate_address_val = '';
$estate_lat_val = '';
$estate_lng_val = '';
$estate_map_visible = true;
if (isset($write) && is_array($write)) {
    if (!empty($write['wr_2'])) {
        $estate_deal_status = eottae_estate_normalize_deal_status($write['wr_2']);
    }
    if (!empty($write['wr_1'])) {
        $estate_region = get_text($write['wr_1']);
    }
    $estate_address_val = get_text($write['wr_4'] ?? '');
    $estate_lat_val = get_text($write['wr_5'] ?? '');
    $estate_lng_val = get_text($write['wr_6'] ?? '');
    $estate_map_visible = (string) ($write['wr_7'] ?? '1') !== '0';
    if (!function_exists('eottae_estate_template_from_row')) {
        include_once G5_LIB_PATH.'/eottae-estate-template.lib.php';
    }
    $decoded = function_exists('eottae_estate_template_from_row')
        ? eottae_estate_template_from_row($write)
        : null;
    if (is_array($decoded)) {
        $estate_template_values = $decoded;
        $estate_template_json = function_exists('eottae_estate_template_encode_json')
            ? eottae_estate_template_encode_json($decoded)
            : '';
        if ($estate_region === '' && !empty($decoded['region'])) {
            $estate_region = get_text($decoded['region']);
        }
        if ($estate_address_val === '' && !empty($decoded['address'])) {
            $estate_address_val = get_text($decoded['address']);
        }
        if ($estate_lat_val === '' && !empty($decoded['lat'])) {
            $estate_lat_val = get_text($decoded['lat']);
        }
        if ($estate_lng_val === '' && !empty($decoded['lng'])) {
            $estate_lng_val = get_text($decoded['lng']);
        }
        if (!empty($decoded['estate_deal_status'])) {
            $estate_deal_status = eottae_estate_normalize_deal_status($decoded['estate_deal_status']);
        }
    }
}

if (!function_exists('eottae_estate_template_field_value')) {
    function eottae_estate_template_field_value($key, $values = array())
    {
        return isset($values[$key]) ? get_text($values[$key]) : '';
    }
}

$sebu_property_types = array(
    ''           => '선택',
    'condo'      => '콘도',
    'house'      => '하우스',
    'villa'      => '빌라',
    'office'     => '오피스',
    'commercial' => '상가',
    'land'       => '토지',
    'other'      => '기타',
);

$sebu_deal_types = array(
    ''      => '선택',
    'month' => '월세',
    'sale'  => '매매',
    'short' => '단기임대',
    'long'  => '장기임대',
);

$sebu_furnishing_types = array(
    ''           => '선택',
    'full'       => '풀퍼니처',
    'semi'       => '세미퍼니처',
    'unfurnished'=> '비가구',
    'nego'       => '협의',
);
?>

<section class="sebu-property-template" id="sebuPropertyTemplate" aria-labelledby="sebuPropertyTemplateTitle">
    <header class="sebu-property-template__head">
        <h2 class="sebu-property-template__title" id="sebuPropertyTemplateTitle">부동산 템플릿 작성</h2>
        <p class="sebu-property-template__desc">간단한 매물 정보를 입력하면 부동산 게시글 제목과 본문이 자동으로 정리됩니다.</p>
    </header>

    <div class="sebu-property-template__body">
        <fieldset class="sebu-property-template__group">
            <legend class="sebu-property-template__legend">기본정보</legend>
            <div class="sebu-property-template__grid">
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">매물종류 <span class="sebu-property-template__req" aria-hidden="true">*</span></span>
                    <select class="sebu-property-template__select" data-property-field="property_type">
                        <?php foreach ($sebu_property_types as $val => $label) { ?>
                        <option value="<?php echo get_text($val); ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">거래유형 <span class="sebu-property-template__req" aria-hidden="true">*</span></span>
                    <select class="sebu-property-template__select" data-property-field="deal_type">
                        <?php foreach ($sebu_deal_types as $val => $label) { ?>
                        <option value="<?php echo get_text($val); ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">거래 상태</span>
                    <select class="sebu-property-template__select" data-property-field="estate_deal_status" id="estate_deal_status">
                        <?php foreach (eottae_estate_deal_statuses() as $val => $label) { ?>
                        <option value="<?php echo get_text($val); ?>"<?php echo $estate_deal_status === $val ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                    <label class="sebu-property-template__check">
                        <input type="checkbox" id="estate_deal_completed_checkbox"<?php echo $estate_deal_status === 'completed' ? ' checked' : ''; ?>>
                        <span>판매완료로 표시</span>
                    </label>
                </label>
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">지역 <span class="sebu-property-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-property-template__input" data-property-field="region" id="estate_region_field" maxlength="120" value="<?php echo htmlspecialchars($estate_region, ENT_QUOTES, 'UTF-8'); ?>" placeholder="예) 세부시티, IT Park, 아얄라 근처">
                </label>
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">매물명 / 건물명</span>
                    <input type="text" class="sebu-property-template__input" data-property-field="building_name" maxlength="160" placeholder="예) Avida Towers, 38 Park Avenue">
                </label>
                <label class="sebu-property-template__field sebu-property-template__field--full">
                    <span class="sebu-property-template__label">가격 <span class="sebu-property-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-property-template__input" data-property-field="price" maxlength="120" placeholder="예) 월 35,000페소, 매매 800만페소, 협의 가능">
                </label>
            </div>
        </fieldset>

        <fieldset class="sebu-property-template__group sebu-property-template__group--location">
            <legend class="sebu-property-template__legend">매물 위치</legend>
            <?php
            $location_picker = array(
                'id'          => 'estateLocationPicker',
                'title'       => '매물 위치',
                'desc'        => '정확한 호수나 집 주소보다 근처 랜드마크 기준으로 입력해 주세요. 세부생활지도 표시를 위해 좌표가 함께 저장됩니다.',
                'placeholder' => '예: IT Park 근처, Mactan Newtown 근처, Parkmall 근처',
                'required'    => true,
                'fields'      => array(
                    'auto_area'     => 'estate_location_auto_area',
                    'location_text' => 'wr_4',
                    'latitude'      => 'wr_5',
                    'longitude'     => 'wr_6',
                    'map_visible'   => 'wr_7',
                ),
                'values'      => array(
                    'auto_area'     => function_exists('eottae_location_normalize_area') ? eottae_location_normalize_area($estate_region) : $estate_region,
                    'location_text' => $estate_address_val !== '' ? $estate_address_val : $estate_region,
                    'latitude'      => $estate_lat_val,
                    'longitude'     => $estate_lng_val,
                    'map_visible'   => $estate_map_visible ? '1' : '0',
                ),
            );
            include G5_PATH.'/components/eottae/location-picker-fields.php';
            ?>
        </fieldset>

        <fieldset class="sebu-property-template__group">
            <legend class="sebu-property-template__legend">매물정보</legend>
            <div class="sebu-property-template__grid">
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">방 개수</span>
                    <input type="text" class="sebu-property-template__input" data-property-field="rooms" maxlength="40" placeholder="예) 스튜디오, 1BR, 2BR">
                </label>
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">화장실 개수</span>
                    <input type="text" class="sebu-property-template__input" data-property-field="bathrooms" maxlength="40" placeholder="예) 1개, 2개">
                </label>
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">가구 여부</span>
                    <select class="sebu-property-template__select" data-property-field="furnishing">
                        <?php foreach ($sebu_furnishing_types as $val => $label) { ?>
                        <option value="<?php echo get_text($val); ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">입주 가능일</span>
                    <input type="text" class="sebu-property-template__input" data-property-field="move_in" maxlength="80" placeholder="예) 즉시입주, 6월 1일부터 가능">
                </label>
            </div>
        </fieldset>

        <fieldset class="sebu-property-template__group">
            <legend class="sebu-property-template__legend">상세내용</legend>
            <div class="sebu-property-template__grid">
                <label class="sebu-property-template__field sebu-property-template__field--full">
                    <span class="sebu-property-template__label">매물 설명 <span class="sebu-property-template__req" aria-hidden="true">*</span></span>
                    <textarea class="sebu-property-template__textarea" data-property-field="description" rows="4" maxlength="4000" placeholder="예) 보안이 좋고 쇼핑몰과 가까운 콘도입니다."></textarea>
                </label>
                <label class="sebu-property-template__field sebu-property-template__field--full">
                    <span class="sebu-property-template__label">주변 정보</span>
                    <textarea class="sebu-property-template__textarea" data-property-field="nearby" rows="3" maxlength="2000" placeholder="예) 아얄라몰 차량 5분, IT Park 도보 가능"></textarea>
                </label>
            </div>
        </fieldset>

        <fieldset class="sebu-property-template__group">
            <legend class="sebu-property-template__legend">연락정보</legend>
            <div class="sebu-property-template__grid">
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">연락처 <span class="sebu-property-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-property-template__input" data-property-field="contact" maxlength="80" placeholder="예) 09XX-XXX-XXXX" inputmode="tel" autocomplete="tel">
                </label>
                <label class="sebu-property-template__field">
                    <span class="sebu-property-template__label">카카오톡 ID</span>
                    <input type="text" class="sebu-property-template__input" data-property-field="kakao_id" maxlength="80" placeholder="예) cebu_estate">
                </label>
                <label class="sebu-property-template__field sebu-property-template__field--full">
                    <span class="sebu-property-template__label">기타 안내사항</span>
                    <textarea class="sebu-property-template__textarea" data-property-field="extra" rows="3" maxlength="2000" placeholder="추가 안내가 있으면 입력해 주세요"></textarea>
                </label>
            </div>
            <p class="sebu-property-template__privacy">연락처, 카카오톡 ID 등 개인정보가 포함될 수 있으니 공개 범위를 확인한 후 등록해주세요.</p>
        </fieldset>
    </div>

    <input type="hidden" name="wr_1" id="wr_1" value="<?php echo htmlspecialchars($estate_region, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="wr_2" id="wr_2" value="<?php echo htmlspecialchars($estate_deal_status, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="estate_template_json" id="estate_template_json" value="<?php echo htmlspecialchars($estate_template_json, ENT_QUOTES, 'UTF-8'); ?>">

    <p class="sebu-property-template__error" id="sebuPropertyTemplateError" role="alert" hidden>필수 정보를 입력해주세요.</p>

    <div class="sebu-property-template__actions">
        <button type="button" class="sebu-property-template__btn sebu-property-template__btn--primary" id="sebuPropertyTemplateApply">부동산 글 자동작성</button>
        <button type="button" class="sebu-property-template__btn sebu-property-template__btn--ghost" id="sebuPropertyTemplateReset">입력내용 초기화</button>
    </div>
</section>
<?php if ($estate_template_values) { ?>
<script>window.__SEBU_ESTATE_TEMPLATE_INITIAL__ = <?php echo json_encode($estate_template_values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;</script>
<?php } ?>
