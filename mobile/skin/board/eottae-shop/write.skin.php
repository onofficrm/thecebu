<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
if (!function_exists('onoff_map_get_config') && is_file(G5_PATH.'/components/maps/map-config.php')) {
    include_once G5_PATH.'/components/maps/map-config.php';
}
$eottae_write_map_cfg = function_exists('onoff_map_get_config')
    ? onoff_map_get_config()
    : array('default_lat' => 10.313, 'default_lng' => 123.9174, 'default_zoom' => 14, 'api_key' => '');
$eottae_maps_has_key = trim((string) ($eottae_write_map_cfg['api_key'] ?? '')) !== '';
$eottae_geocoder_script = function_exists('eottae_google_geocoder_script') ? eottae_google_geocoder_script() : '';
$eottae_ai_btn_icon = '<span class="eottae-ai-btn__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3l1.2 4.2L17.5 8.5l-4.3 1.2L12 14l-1.2-4.3L6.5 8.5l4.3-1.3L12 3z" fill="currentColor" opacity="0.95"/><path d="M5 14l.8 2.8L8.5 17l-2.7.8L5 20.5l-.8-2.7L1.5 17l2.7-.8L5 14z" fill="currentColor" opacity="0.85"/><path d="M19 13l.7 2.3L22 16l-2.3.7L19 19l-.7-2.3L16 16l2.3-.7L19 13z" fill="currentColor" opacity="0.85"/></svg></span>';

$v = array(
    'wr_1'  => isset($write['wr_1']) ? get_text($write['wr_1']) : '',
    'wr_2'  => isset($write['wr_2']) ? get_text($write['wr_2']) : '',
    'wr_3'  => isset($write['wr_3']) ? get_text($write['wr_3']) : '',
    'wr_4'  => isset($write['wr_4']) ? get_text($write['wr_4']) : '',
    'wr_5'  => isset($write['wr_5']) ? get_text($write['wr_5']) : '',
    'wr_6'  => isset($write['wr_6']) ? get_text($write['wr_6']) : '',
    'wr_7'  => isset($write['wr_7']) ? get_text($write['wr_7']) : '',
    'wr_8'  => isset($write['wr_8']) ? get_text($write['wr_8']) : '영업중',
    'wr_9'  => isset($write['wr_9']) ? get_text($write['wr_9']) : '',
    'wr_10' => isset($write['wr_10']) ? get_text($write['wr_10']) : '',
    'wr_link1' => isset($write['wr_link1']) ? get_text($write['wr_link1']) : '',
    'wr_link2' => eottae_shop_wr_link2_raw($write),
);
$ca_value = isset($write['ca_name']) ? get_text($write['ca_name']) : ($v['wr_1'] !== '' ? $v['wr_1'] : $sca);
$board_categories = eottae_shop_board_categories($board);
if (!in_array($ca_value, $board_categories, true)) {
    $ca_value = '';
}
$category_options = eottae_shop_quick_categories($board);
$shop_region_label = $v['wr_2'] !== '' ? get_text($v['wr_2']) : '';
$business_hour_options = array(
    '09:00 - 18:00',
    '09:00 - 20:00',
    '09:00 - 22:00',
    '10:00 - 20:00',
    '10:00 - 22:00',
    '11:00 - 23:00',
    '12:00 - 24:00',
    '24시간 영업',
    '예약제 운영',
);
$closed_day_options = array(
    '연중무휴',
    '매주 월요일',
    '매주 화요일',
    '매주 수요일',
    '매주 목요일',
    '매주 금요일',
    '매주 토요일',
    '매주 일요일',
    '비정기 휴무',
);
$sns_values = array(
    'youtube' => eottae_shop_sns_value($v['wr_link2'], 'youtube'),
    'instagram' => eottae_shop_sns_value($v['wr_link2'], 'instagram'),
    'tiktok' => eottae_shop_sns_value($v['wr_link2'], 'tiktok'),
    'facebook' => eottae_shop_sns_value($v['wr_link2'], 'facebook'),
    'naver_blog' => eottae_shop_sns_value($v['wr_link2'], 'naver_blog'),
);
$map_thumb = ($w === 'u' && !empty($write['wr_id']) && function_exists('eottae_shop_map_thumb_get'))
    ? eottae_shop_map_thumb_get($bo_table, (int) $write['wr_id'])
    : array();
$shop_seo = array();
if ($w === 'u' && !empty($write['wr_id']) && function_exists('eottae_shop_seo_get')) {
    $shop_seo = eottae_shop_seo_get($bo_table, (int) $write['wr_id']);
}
$shop_seo_v = function_exists('eottae_shop_seo_resolve_for_write')
    ? eottae_shop_seo_resolve_for_write($write, $shop_seo)
    : array(
        'meta_title' => '',
        'meta_intro' => '',
        'meta_description' => '',
        'focus_keyword' => '',
    );
if (!function_exists('eottae_secrets_load') && is_file(G5_LIB_PATH.'/eottae-secrets.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-secrets.lib.php';
}
if (function_exists('eottae_secrets_load')) {
    eottae_secrets_load();
} elseif (function_exists('eottae_merge_runtime_secrets')) {
    eottae_merge_runtime_secrets();
}
if (function_exists('eottae_ai_generate_clear_config_cache')) {
    eottae_ai_generate_clear_config_cache();
}
$eottae_ai_api_key = function_exists('eottae_ai_openai_api_key')
    ? eottae_ai_openai_api_key()
    : (function_exists('g5site_cfg') ? trim((string) g5site_cfg('ai_generate_api_key', '')) : '');
$eottae_ai_enabled = $eottae_ai_api_key !== '';
$eottae_ai_hint = $eottae_ai_enabled
    ? '업체명, 카테고리, 주소를 입력한 뒤 누르면 소개와 SEO 문구를 자동으로 채웁니다.'
    : (function_exists('eottae_ai_setup_hint_html') ? eottae_ai_setup_hint_html() : 'AI 자동생성은 서버 <strong>data/eottae-secrets.local.php</strong> 에 OpenAI 키(<code>ai_generate_api_key</code>)를 등록한 후 이용할 수 있습니다.');
?>

<section class="shop-register-page board-wrap board-wrap--eottae-shop board-write" id="bo_w" style="width:<?php echo $width; ?>" data-ai-enabled="<?php echo $eottae_ai_enabled ? '1' : '0'; ?>">
    <header class="shop-register-page__header">
        <h1 class="shop-register-page__title"><?php echo $w === 'u' ? '업체 정보 수정' : '업체 등록'; ?></h1>
        <p class="shop-register-page__desc">세부 교민·여행객에게 업체를 알려보세요. 등록 후 관리자 검토를 거쳐 노출됩니다.</p>
    </header>

    <div class="shop-register-page__steps" aria-hidden="true">
        <?php for ($s = 0; $s < 7; $s++) { ?><span class="shop-register-page__step<?php echo $s === 0 ? ' is-active' : ''; ?>"></span><?php } ?>
    </div>

    <form name="fwrite" id="fwrite" action="<?php echo $action_url; ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="wr_1" id="wr_1" value="<?php echo $v['wr_1'] !== '' ? $v['wr_1'] : $ca_value; ?>">
    <?php include G5_PATH.'/components/eottae/board-write-options.php'; ?>

    <div class="shop-register-page__panel is-active" data-step="0">
        <h3>1. 업체 기본정보</h3>
        <div class="eottae-field">
            <label for="wr_subject">업체명 (필수)</label>
            <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" required maxlength="255" placeholder="업체명을 입력하세요">
        </div>
        <div class="eottae-field">
            <label for="ca_name">카테고리</label>
            <select name="ca_name" id="ca_name" class="eottae-select">
                <option value="">카테고리 선택</option>
                <?php foreach ($category_options as $cat) {
                    if ($cat['slug'] === '') {
                        continue;
                    } ?>
                <option value="<?php echo get_text($cat['slug']); ?>"<?php echo ($ca_value === $cat['slug']) ? ' selected' : ''; ?>><?php echo get_text($cat['label']); ?></option>
                <?php } ?>
            </select>
        </div>
        <?php include G5_PATH.'/components/eottae/shop-owner-assign.php'; ?>
        <div class="eottae-field eottae-field--editor">
            <?php
            $eottae_editor_label = '업체 소개';
            $eottae_editor_placeholder = '업체를 소개해 주세요';
            $eottae_editor_field_class = 'eottae-field__editor-wrap';
            include G5_PATH.'/components/eottae/board-write-editor.php';
            ?>
            <button type="button" class="eottae-ai-btn shop-register-page__ai-btn<?php echo $eottae_ai_enabled ? '' : ' is-ai-unavailable'; ?>" data-shop-ai-generate="all" data-default-label="AI로 업체소개·SEO 자동생성"><?php echo $eottae_ai_btn_icon; ?><span class="eottae-ai-btn__label">AI로 업체소개·SEO 자동생성</span></button>
            <p class="eottae-field__hint" data-shop-ai-status aria-live="polite"><?php echo $eottae_ai_hint; ?></p>
        </div>
    </div>

    <div class="shop-register-page__panel" data-step="1">
        <h3>2. 위치정보</h3>
        <input type="hidden" name="wr_2" id="wr_2" value="<?php echo $v['wr_2']; ?>">
        <div class="eottae-field">
            <label for="wr_3">주소</label>
            <input type="text" name="wr_3" id="wr_3" value="<?php echo $v['wr_3']; ?>" placeholder="영문·한글 주소 (예: Talamban, Cebu City)">
            <div class="shop-register-page__location-actions">
                <button type="button" class="btn btn--ghost shop-register-page__geocode-btn" id="shopGeocodeBtn">주소 확인 · 지역·좌표 설정</button>
                <button type="button" class="btn btn--ghost shop-register-page__geocode-btn shop-register-page__current-btn" id="shopCurrentLocationBtn">현재위치 등록</button>
            </div>
            <p class="eottae-field__hint" id="shopRegionDisplay" aria-live="polite"><?php echo $shop_region_label !== '' ? '대표 지역: '.get_text($shop_region_label) : '주소 입력 후 자동으로 대표 지역이 설정됩니다.'; ?></p>
            <p class="eottae-field__hint" id="shopGeocodeStatus" aria-live="polite"></p>
        </div>
        <details class="shop-register-page__advanced">
            <summary>좌표 직접 입력 (선택)</summary>
            <div class="eottae-field">
                <label for="wr_9">위도 (Latitude)</label>
                <input type="text" name="wr_9" id="wr_9" value="<?php echo $v['wr_9']; ?>" placeholder="10.3157">
            </div>
            <div class="eottae-field">
                <label for="wr_10">경도 (Longitude)</label>
                <input type="text" name="wr_10" id="wr_10" value="<?php echo $v['wr_10']; ?>" placeholder="123.8854">
            </div>
            <div id="shopCoordMapWrap" class="shop-register-page__coord-map-wrap">
                <?php if ($eottae_maps_has_key) { ?>
                <p class="eottae-field__hint">지도를 클릭하거나 핀을 드래그해 위치를 지정하세요. 위·경도 입력란에 자동 반영됩니다.</p>
                <div id="shopCoordMap" class="shop-register-page__coord-map" role="application" aria-label="업체 위치 지도"
                    data-default-lat="<?php echo htmlspecialchars((string) ($eottae_write_map_cfg['default_lat'] ?? '10.313'), ENT_QUOTES, 'UTF-8'); ?>"
                    data-default-lng="<?php echo htmlspecialchars((string) ($eottae_write_map_cfg['default_lng'] ?? '123.9174'), ENT_QUOTES, 'UTF-8'); ?>"
                    data-default-zoom="<?php echo (int) ($eottae_write_map_cfg['default_zoom'] ?? 14); ?>"></div>
                <?php } else { ?>
                <p class="eottae-field__hint">지도에서 핀을 찍으려면 <code>_site.config.local.php</code>에 <code>google_maps_api_key</code>를 설정해 주세요.</p>
                <?php } ?>
            </div>
        </details>
    </div>

    <div class="shop-register-page__panel" data-step="2">
        <h3>3. 연락처 · SNS</h3>
        <div class="eottae-field">
            <label for="wr_4">전화번호</label>
            <input type="tel" name="wr_4" id="wr_4" value="<?php echo $v['wr_4']; ?>" placeholder="032-123-4567">
        </div>
        <div class="eottae-field">
            <label for="wr_link1">홈페이지 URL</label>
            <input type="url" name="wr_link1" id="wr_link1" value="<?php echo $v['wr_link1']; ?>">
        </div>
        <input type="hidden" name="wr_link2" id="wr_link2" value="<?php echo htmlspecialchars($v['wr_link2'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="eottae-field">
            <label for="eottae_sns_youtube">유튜브 소개 영상 URL <span class="board-write-form__optional">(선택)</span></label>
            <input type="url" name="eottae_sns_youtube" id="eottae_sns_youtube" value="<?php echo htmlspecialchars($sns_values['youtube'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.youtube.com/watch?v=...">
            <p class="eottae-field__hint">등록하면 업체 상세 페이지 업체소개 영역에 영상이 표시됩니다.</p>
        </div>
        <div class="shop-register-page__sns-grid">
            <div class="eottae-field">
                <label for="eottae_sns_instagram">인스타그램 URL</label>
                <input type="url" name="eottae_sns_instagram" id="eottae_sns_instagram" value="<?php echo htmlspecialchars($sns_values['instagram'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.instagram.com/...">
            </div>
            <div class="eottae-field">
                <label for="eottae_sns_tiktok">틱톡 URL</label>
                <input type="url" name="eottae_sns_tiktok" id="eottae_sns_tiktok" value="<?php echo htmlspecialchars($sns_values['tiktok'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.tiktok.com/@...">
            </div>
            <div class="eottae-field">
                <label for="eottae_sns_facebook">페이스북 URL</label>
                <input type="url" name="eottae_sns_facebook" id="eottae_sns_facebook" value="<?php echo htmlspecialchars($sns_values['facebook'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.facebook.com/...">
            </div>
            <div class="eottae-field">
                <label for="eottae_sns_naver_blog">네이버블로그 URL</label>
                <input type="url" name="eottae_sns_naver_blog" id="eottae_sns_naver_blog" value="<?php echo htmlspecialchars($sns_values['naver_blog'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://blog.naver.com/...">
            </div>
        </div>
    </div>

    <div class="shop-register-page__panel" data-step="3">
        <h3>4. 영업정보</h3>
        <?php include G5_SKIN_PATH.'/board/_inc/eottae-shop-business-fields.php'; ?>
    </div>

    <div class="shop-register-page__panel" data-step="4">
        <h3>5. 이미지 · 메뉴</h3>
        <div class="shop-register-page__photos">
            <p class="shop-register-page__photos-label">업체 이미지 <span>(대표 1장<?php if ($file_count > 1) { ?> · 추가 <?php echo (int) ($file_count - 1); ?>장<?php } ?>)</span></p>
            <div class="shop-register-page__photo-grid">
            <?php for ($i = 0; $i < $file_count; $i++) {
                $is_featured = ($i === 0);
                $has_existing = ($w === 'u' && isset($file[$i]['file']) && $file[$i]['file']);
                $existing_url = $has_existing ? $file[$i]['path'].'/'.$file[$i]['file'] : '';
                $slot_class = 'shop-register-page__photo-slot'.($is_featured ? ' shop-register-page__photo-slot--featured' : '').($has_existing ? ' has-preview' : '');
                ?>
                <label class="<?php echo $slot_class; ?>" for="bf_file_<?php echo $i + 1; ?>">
                    <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1; ?>" accept="image/*" class="shop-register-page__photo-input" data-photo-input data-photo-preview>
                    <span class="shop-register-page__photo-badge"><?php echo $is_featured ? '대표' : '추가 '.$i; ?></span>
                    <span class="shop-register-page__photo-placeholder" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <span><?php echo $is_featured ? '대표 이미지' : '사진 추가'; ?></span>
                    </span>
                    <img src="<?php echo $has_existing ? get_text($existing_url) : ''; ?>" alt="" class="shop-register-page__photo-preview"<?php echo $has_existing ? '' : ' hidden'; ?>>
                    <?php if ($has_existing) { ?>
                    <span class="shop-register-page__photo-name"><?php echo get_text($file[$i]['source']); ?></span>
                    <span class="shop-register-page__photo-delete" onclick="event.preventDefault(); event.stopPropagation();">
                        <label><input type="checkbox" name="bf_file_del[<?php echo $i; ?>]" value="1"> 삭제</label>
                    </span>
                    <?php } ?>
                </label>
            <?php } ?>
            </div>
        </div>
        <div class="eottae-field shop-register-page__map-thumb-field">
            <p class="shop-register-page__photos-label">지도 표시 썸네일</p>
            <p class="eottae-field__hint">권장 사이즈: 정사각형 1024×1024px 이상. 지도 마커에서는 원형/작은 이미지로 표시됩니다. 비워 두면 대표 이미지가 사용됩니다.</p>
            <?php
            $map_thumb_has_existing = !empty($map_thumb['url']);
            $map_thumb_slot_class = 'shop-register-page__photo-slot shop-register-page__photo-slot--map'.($map_thumb_has_existing ? ' has-preview' : '');
            ?>
            <label class="<?php echo $map_thumb_slot_class; ?>" for="eottae_map_thumb">
                <input type="file" name="eottae_map_thumb" id="eottae_map_thumb" accept="image/*" class="shop-register-page__photo-input" data-photo-preview data-map-thumb-input>
                <span class="shop-register-page__photo-badge">지도</span>
                <span class="shop-register-page__photo-placeholder" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>썸네일 추가</span>
                </span>
                <img src="<?php echo $map_thumb_has_existing ? get_text($map_thumb['url']) : ''; ?>" alt="" class="shop-register-page__photo-preview" data-map-thumb-preview-img<?php echo $map_thumb_has_existing ? '' : ' hidden'; ?>>
                <?php if ($map_thumb_has_existing) { ?>
                <span class="shop-register-page__photo-name"><?php echo get_text($map_thumb['source_name']); ?></span>
                <span class="shop-register-page__photo-delete" onclick="event.preventDefault(); event.stopPropagation();">
                    <label><input type="checkbox" name="eottae_map_thumb_del" value="1"> 삭제</label>
                </span>
                <?php } ?>
            </label>
            <input type="hidden" name="eottae_map_thumb_ai_tmp" id="eottae_map_thumb_ai_tmp" value="">
            <button type="button" class="eottae-ai-btn shop-register-page__ai-btn" data-map-thumb-ai-generate data-default-label="AI 지도 썸네일 생성"><?php echo $eottae_ai_btn_icon; ?><span class="eottae-ai-btn__label">AI 지도 썸네일 생성</span></button>
            <p class="eottae-field__hint" data-map-thumb-ai-status aria-live="polite"></p>
        </div>
        <p class="eottae-field__hint">메뉴·가격 정보는 업체 소개 본문에 작성해 주세요.</p>
    </div>

    <div class="shop-register-page__panel" data-step="5">
        <h3>6. SEO · 검색 노출</h3>
        <p class="eottae-field__hint">업소 상세 페이지에 적용되는 검색·SNS 메타 정보입니다. 비워 두면 업체명·소개 본문에서 자동 생성됩니다.</p>
        <button type="button" class="eottae-ai-btn shop-register-page__ai-btn<?php echo $eottae_ai_enabled ? '' : ' is-ai-unavailable'; ?>" data-shop-ai-generate="seo" data-default-label="AI로 SEO 문구 자동생성"><?php echo $eottae_ai_btn_icon; ?><span class="eottae-ai-btn__label">AI로 SEO 문구 자동생성</span></button>
        <p class="eottae-field__hint" data-shop-ai-status aria-live="polite"><?php echo $eottae_ai_enabled ? '업체명을 입력한 뒤 누르면 SEO 타이틀·소개·메타 설명·키워드를 채웁니다.' : 'AI 자동생성은 서버에 OpenAI API 키가 설정된 후 이용할 수 있습니다.'; ?></p>
        <div class="eottae-field">
            <label for="eottae_seo_title">SEO 타이틀</label>
            <input type="text" name="eottae_seo_title" id="eottae_seo_title" value="<?php echo get_text($shop_seo_v['meta_title']); ?>" maxlength="255" placeholder="예: 세부 맛집 OO식당 | 세부어때">
        </div>
        <div class="eottae-field">
            <label for="eottae_seo_intro">업소 SEO 소개</label>
            <textarea name="eottae_seo_intro" id="eottae_seo_intro" rows="3" maxlength="500" placeholder="검색 결과에 노출될 한 줄 소개"><?php echo get_text($shop_seo_v['meta_intro']); ?></textarea>
        </div>
        <div class="eottae-field">
            <label for="eottae_seo_description">메타 디스크립션</label>
            <textarea name="eottae_seo_description" id="eottae_seo_description" rows="4" maxlength="500" placeholder="150~160자 권장. 업소 특징·위치·서비스를 요약해 주세요."><?php echo get_text($shop_seo_v['meta_description']); ?></textarea>
        </div>
        <div class="eottae-field">
            <label for="eottae_seo_keyword">포커스 키워드</label>
            <input type="text" name="eottae_seo_keyword" id="eottae_seo_keyword" value="<?php echo get_text($shop_seo_v['focus_keyword']); ?>" maxlength="255" placeholder="예: 세부 맛집, IT Park, 한식">
            <p class="eottae-field__hint">쉼표(,)로 여러 키워드를 구분할 수 있습니다.</p>
        </div>
    </div>

    <div class="shop-register-page__panel" data-step="6">
        <h3>7. 등록 확인</h3>
        <div class="shop-register-page__summary" id="shopRegisterSummary">
            <p class="shop-register-page__summary-empty">입력 내용을 확인해 주세요.</p>
        </div>
        <?php if ($is_use_captcha) { echo $captcha_html; } ?>
    </div>

    <?php
    if ($w === 'u' && !empty($wr_id)) {
        include_once G5_PATH.'/components/eottae/shop-spot-apply.php';
        eottae_render_shop_spot_apply($write, $bo_table);
    } elseif ($w === '') { ?>
    <p class="shop-spot-apply__hint shop-register-page__spot-hint">업체 등록이 완료되면 <strong>수정 화면</strong>에서 포인트로 목록 최상단 「최우수업체」 노출을 신청할 수 있습니다.</p>
    <?php } ?>

    <div class="shop-register-page__nav">
        <button type="button" class="btn btn--ghost" data-wizard="prev">이전</button>
        <button type="button" class="btn btn--primary" data-wizard="next">다음</button>
        <button type="submit" class="btn btn--primary" data-wizard="submit" id="btn_submit" accesskey="s">등록 완료</button>
    </div>
    </form>
</section>

<script>
function fwrite_submit(f) {
    if (typeof syncShopSnsFields === 'function') {
        syncShopSnsFields(f);
    }
    var ca = document.getElementById('ca_name');
    var wr1 = document.getElementById('wr_1');
    var wr2 = document.getElementById('wr_2');
    var wr3 = document.getElementById('wr_3');
    if (ca && wr1) {
        if (!ca.value && ca.options.length > 1) {
            ca.selectedIndex = 1;
        }
        wr1.value = ca.value;
    }
    if (wr2 && wr3 && !wr2.value.trim() && wr3.value.trim() && typeof shopDetectRegionFromText === 'function') {
        var autoRegion = shopDetectRegionFromText(wr3.value.trim());
        if (autoRegion) {
            wr2.value = autoRegion;
            var regionDisplay = document.getElementById('shopRegionDisplay');
            if (regionDisplay) {
                regionDisplay.textContent = '대표 지역: ' + autoRegion;
            }
        }
    }
    if (!f.wr_subject.value.trim()) {
        alert('업체명을 입력해 주세요.');
        f.wr_subject.focus();
        return false;
    }
    <?php echo $editor_js; ?>
    if (!f.wr_content.value.trim()) {
        f.wr_content.value = f.wr_subject.value.trim();
        if (typeof eottaeSetEditorContent === 'function') {
            eottaeSetEditorContent('wr_content', f.wr_content.value);
        }
    }
    <?php echo $captcha_js; ?>
    document.getElementById('btn_submit').disabled = true;
    return true;
}
</script>
<?php echo $eottae_geocoder_script; ?>
