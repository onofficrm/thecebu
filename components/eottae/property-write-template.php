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
                    <span class="sebu-property-template__label">지역 <span class="sebu-property-template__req" aria-hidden="true">*</span></span>
                    <input type="text" class="sebu-property-template__input" data-property-field="region" maxlength="120" placeholder="예) 세부시티, IT Park, 아얄라 근처">
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

    <p class="sebu-property-template__error" id="sebuPropertyTemplateError" role="alert" hidden>필수 정보를 입력해주세요.</p>

    <div class="sebu-property-template__actions">
        <button type="button" class="sebu-property-template__btn sebu-property-template__btn--primary" id="sebuPropertyTemplateApply">부동산 글 자동작성</button>
        <button type="button" class="sebu-property-template__btn sebu-property-template__btn--ghost" id="sebuPropertyTemplateReset">입력내용 초기화</button>
    </div>
</section>
