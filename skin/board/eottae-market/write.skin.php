<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-market.lib.php';
include_once G5_LIB_PATH.'/eottae-location.lib.php';
eottae_market_load_assets(true);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$market_status = eottae_market_normalize_status($write['wr_2'] ?? 'selling');
$market_region = eottae_market_normalize_region($write['wr_3'] ?? '');
$market_location = get_text($write['wr_4'] ?? '');
$market_lat = get_text($write['wr_5'] ?? '');
$market_lng = get_text($write['wr_6'] ?? '');
$market_contact = get_text($write['wr_7'] ?? '');
$market_offer = (string) ($write['wr_8'] ?? '1') === '1';
$market_map_show = (string) ($write['wr_9'] ?? '1') !== '0';
$market_price = preg_replace('/[^0-9]/', '', (string) ($write['wr_1'] ?? ''));
$market_content = isset($content) ? $content : '';
$market_free_mode = (isset($_GET['free']) && (string) $_GET['free'] === '1')
    || eottae_market_is_free_giveaway(array(
        'wr_1'  => $write['wr_1'] ?? 0,
        'wr_10' => $write['wr_10'] ?? '',
    ));
$market_price_ai_url = G5_URL.'/proc/eottae-market-price-ai.php';
?>

<div class="market-write board-wrap board-wrap--eottae-market" id="bo_w" style="width:<?php echo $width; ?>">
    <header class="market-write__header">
        <a href="<?php echo $list_href; ?>" class="market-write__back">← 목록으로</a>
        <h1 class="market-write__title"><?php echo $w === 'u' ? '상품 수정' : ($market_free_mode ? '무료나눔 등록' : '중고물품 등록'); ?></h1>
        <p class="market-write__desc"><?php echo $market_free_mode
            ? '필요 없는 물건을 무료로 나눠 주세요. 사진, 거래 위치, 연락방법만 입력하면 됩니다.'
            : '사진, 가격, 거래 위치만 빠르게 입력해보세요. 정확한 집 주소보다 근처 장소 중심으로 등록하는 것을 권장합니다.'; ?></p>
    </header>

    <form name="fwrite" id="fwrite" class="market-write__form" action="<?php echo $action_url; ?>" onsubmit="return market_write_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" data-market-price-ai-url="<?php echo get_text($market_price_ai_url); ?>">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
    <input type="hidden" name="sca" value="<?php echo $sca; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="html" value="html2">

    <?php include G5_PATH.'/components/eottae/board-write-options.php'; ?>

    <section class="market-write-card">
        <h2 class="market-write-card__title">상품 정보</h2>
        <div class="market-field">
            <label for="wr_subject">상품명 <span>*</span></label>
            <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" required maxlength="255" class="market-input" placeholder="예: 삼성 냉장고 판매합니다">
        </div>
        <div class="market-field market-field--split">
            <div class="market-price-mode" data-market-price-mode>
                <span class="market-price-mode__label">등록 유형</span>
                <div class="market-price-mode__buttons" role="group" aria-label="등록 유형 선택">
                    <button type="button" class="market-price-mode__btn<?php echo !$market_free_mode ? ' is-active' : ''; ?>" data-market-price-mode="sell" aria-pressed="<?php echo !$market_free_mode ? 'true' : 'false'; ?>">판매</button>
                    <button type="button" class="market-price-mode__btn market-price-mode__btn--free<?php echo $market_free_mode ? ' is-active' : ''; ?>" data-market-price-mode="free" aria-pressed="<?php echo $market_free_mode ? 'true' : 'false'; ?>">무료나눔</button>
                </div>
                <input type="hidden" name="market_free_giveaway" id="market_free_giveaway" value="<?php echo $market_free_mode ? '1' : '0'; ?>">
            </div>
            <div class="market-price-fields<?php echo $market_free_mode ? ' is-hidden' : ''; ?>" data-market-price-fields>
                <label for="wr_1">가격 <span>*</span></label>
                <div class="market-price-input">
                    <span>₱</span>
                    <input type="number" name="wr_1" id="wr_1" value="<?php echo $market_free_mode ? '' : $market_price; ?>"<?php echo $market_free_mode ? '' : ' required'; ?> min="1" step="1" class="market-input" placeholder="3000">
                </div>
                <div class="market-price-ai" data-market-price-ai>
                    <button type="button" class="market-price-ai__btn eottae-ai-btn" data-market-price-ai-trigger>AI 가격 참고</button>
                    <div class="market-price-ai__result" data-market-price-ai-result hidden></div>
                </div>
            </div>
            <div>
                <label for="wr_2">거래상태</label>
                <select name="wr_2" id="wr_2" class="market-select">
                    <?php foreach (eottae_market_statuses() as $key => $label) { ?>
                    <option value="<?php echo get_text($key); ?>"<?php echo $market_status === $key ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="market-field">
            <label for="wr_content">상품설명 <span>*</span></label>
            <textarea name="wr_content" id="wr_content" class="market-textarea" required placeholder="상품 상태, 사용 기간, 거래 가능 시간 등을 간단히 적어주세요."><?php echo get_text($market_content, 0); ?></textarea>
        </div>
    </section>

    <section class="market-write-card">
        <?php
        $location_picker = array(
            'id'          => 'marketLocationPicker',
            'title'       => '거래 위치',
            'desc'        => '정확한 집 주소 대신 건물명·몰·동네처럼 근처 장소를 입력해 주세요.',
            'placeholder' => '예: IT Park 근처, Ayala Center Cebu 근처',
            'required'    => true,
            'fields'      => array(
                'auto_area'     => 'wr_3',
                'location_text' => 'wr_4',
                'latitude'      => 'wr_5',
                'longitude'     => 'wr_6',
                'map_visible'   => 'wr_9',
            ),
            'values'      => array(
                'auto_area'     => $market_region,
                'location_text' => $market_location,
                'latitude'      => $market_lat,
                'longitude'     => $market_lng,
                'map_visible'   => $market_map_show ? '1' : '0',
            ),
        );
        include G5_PATH.'/components/eottae/location-picker-fields.php';
        ?>
    </section>

    <section class="market-write-card">
        <h2 class="market-write-card__title">거래 조건</h2>
        <div class="market-field">
            <label for="wr_7">연락방법 <span>*</span></label>
            <input type="text" name="wr_7" id="wr_7" value="<?php echo $market_contact; ?>" required class="market-input" maxlength="200" placeholder="카카오톡 ID / 전화번호 / 댓글문의 / 오픈채팅 링크">
            <p class="market-field__help">연락방법은 현재 상세페이지에 표시됩니다. 추후 회원전용 노출로 변경할 수 있습니다.</p>
        </div>
        <label class="market-check" data-market-offer-wrap<?php echo $market_free_mode ? ' hidden' : ''; ?>>
            <input type="checkbox" name="wr_8" value="1" id="wr_8"<?php echo $market_offer ? ' checked' : ''; ?><?php echo $market_free_mode ? ' disabled' : ''; ?>>
            <span>가격제안 가능</span>
        </label>
    </section>

    <?php if ($file_count > 0) { ?>
    <section class="market-write-card">
        <h2 class="market-write-card__title">사진 첨부</h2>
        <p class="market-write-card__hint">첫 번째 사진이 목록 대표 이미지로 표시됩니다.</p>
        <div class="market-photo-grid">
            <?php for ($i = 0; $i < $file_count; $i++) { ?>
            <label class="market-photo-slot" for="bf_file_<?php echo $i + 1; ?>">
                <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1; ?>" accept="image/*" class="market-photo-slot__input" data-photo-preview>
                <span class="market-photo-slot__placeholder">+</span>
                <img src="" alt="" class="market-photo-slot__preview" hidden>
                <?php if ($w === 'u' && isset($file[$i]['file']) && $file[$i]['file']) { ?>
                <span class="market-photo-slot__current"><?php echo get_text($file[$i]['source']); ?></span>
                <label class="market-photo-slot__delete">
                    <input type="checkbox" name="bf_file_del[<?php echo $i; ?>]" value="1"> 삭제
                </label>
                <?php } ?>
            </label>
            <?php } ?>
        </div>
    </section>
    <?php } ?>

    <?php if ($is_use_captcha) { ?>
    <div class="market-captcha"><?php echo $captcha_html; ?></div>
    <?php } ?>

    <div class="market-write__actions">
        <a href="<?php echo $list_href; ?>" class="market-write__cancel">취소</a>
        <button type="submit" id="btn_submit" class="market-write__submit"><?php echo $w === 'u' ? '수정하기' : ($market_free_mode ? '무료나눔 등록' : '상품 등록'); ?></button>
    </div>
    </form>
</div>

<script>
function market_write_submit(f) {
    <?php echo $editor_js; ?>

    if (!f.wr_subject.value.trim()) {
        alert('상품명을 입력해 주세요.');
        f.wr_subject.focus();
        return false;
    }
    if (!document.getElementById('market_free_giveaway') || document.getElementById('market_free_giveaway').value !== '1') {
        if (!f.wr_1.value || Number(f.wr_1.value) < 1) {
            alert('가격을 입력해 주세요.');
            f.wr_1.focus();
            return false;
        }
    }
    if (!f.wr_4.value.trim()) {
        alert('거래 상세위치를 입력해 주세요.');
        f.wr_4.focus();
        return false;
    }
    if (!f.wr_content.value.trim()) {
        alert('상품설명을 입력해 주세요.');
        f.wr_content.focus();
        return false;
    }
    if (!f.wr_7.value.trim()) {
        alert('연락방법을 입력해 주세요.');
        f.wr_7.focus();
        return false;
    }

    <?php echo $captcha_js; ?>
    document.getElementById('btn_submit').disabled = true;
    return true;
}
</script>
