<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

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
    'wr_link2' => isset($write['wr_link2']) ? get_text($write['wr_link2']) : '',
);
$ca_value = isset($write['ca_name']) ? get_text($write['ca_name']) : ($v['wr_1'] !== '' ? $v['wr_1'] : $sca);
$category_options = eottae_shop_quick_categories($board);
$region_options = eottae_shop_region_options();
?>

<section class="shop-register-page board-wrap board-wrap--eottae-shop board-write" id="bo_w" style="width:<?php echo $width; ?>">
    <header class="shop-register-page__header">
        <h1 class="shop-register-page__title"><?php echo $w === 'u' ? '업체 정보 수정' : '업체 등록'; ?></h1>
        <p class="shop-register-page__desc">세부 교민·여행객에게 업체를 알려보세요. 등록 후 관리자 검토를 거쳐 노출됩니다.</p>
    </header>

    <div class="shop-register-page__steps" aria-hidden="true">
        <?php for ($s = 0; $s < 6; $s++) { ?><span class="shop-register-page__step<?php echo $s === 0 ? ' is-active' : ''; ?>"></span><?php } ?>
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
    <?php echo $option_hidden; ?>

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
        <div class="eottae-field">
            <label for="wr_content">업체 소개</label>
            <textarea name="wr_content" id="wr_content" rows="6" placeholder="업체를 소개해 주세요"><?php echo $content; ?></textarea>
        </div>
    </div>

    <div class="shop-register-page__panel" data-step="1">
        <h3>2. 위치정보</h3>
        <div class="eottae-field">
            <label for="wr_2">대표 지역</label>
            <select name="wr_2" id="wr_2" class="eottae-select">
                <option value="">지역 선택</option>
                <?php foreach ($region_options as $region) { ?>
                <option value="<?php echo get_text($region); ?>"<?php echo ($v['wr_2'] === $region) ? ' selected' : ''; ?>><?php echo get_text($region); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="eottae-field">
            <label for="wr_3">주소</label>
            <input type="text" name="wr_3" id="wr_3" value="<?php echo $v['wr_3']; ?>" placeholder="상세 주소">
        </div>
        <details class="shop-register-page__advanced">
            <summary>좌표 직접 입력 (선택 · 지도 연동 전)</summary>
            <div class="eottae-field">
                <label for="wr_9">위도 (Latitude)</label>
                <input type="text" name="wr_9" id="wr_9" value="<?php echo $v['wr_9']; ?>" placeholder="10.3157">
            </div>
            <div class="eottae-field">
                <label for="wr_10">경도 (Longitude)</label>
                <input type="text" name="wr_10" id="wr_10" value="<?php echo $v['wr_10']; ?>" placeholder="123.8854">
            </div>
        </details>
    </div>

    <div class="shop-register-page__panel" data-step="2">
        <h3>3. 연락처 · 문의 연결</h3>
        <div class="eottae-field">
            <label for="wr_4">전화번호</label>
            <input type="tel" name="wr_4" id="wr_4" value="<?php echo $v['wr_4']; ?>" placeholder="032-123-4567">
        </div>
        <div class="eottae-field">
            <label for="wr_5">문의 연결 코드</label>
            <input type="text" name="wr_5" id="wr_5" value="<?php echo $v['wr_5']; ?>" placeholder="shop-code (영문·숫자)">
            <p class="eottae-field__hint">고객 화면에는 '문의하기'로만 표시됩니다.</p>
        </div>
        <div class="eottae-field">
            <label for="wr_link1">홈페이지 URL</label>
            <input type="url" name="wr_link1" id="wr_link1" value="<?php echo $v['wr_link1']; ?>">
        </div>
        <div class="eottae-field">
            <label for="wr_link2">SNS URL</label>
            <input type="url" name="wr_link2" id="wr_link2" value="<?php echo $v['wr_link2']; ?>">
        </div>
    </div>

    <div class="shop-register-page__panel" data-step="3">
        <h3>4. 영업정보</h3>
        <div class="eottae-field">
            <label for="wr_6">영업시간</label>
            <input type="text" name="wr_6" id="wr_6" value="<?php echo $v['wr_6']; ?>" placeholder="09:00 - 22:00">
        </div>
        <div class="eottae-field">
            <label for="wr_7">휴무일</label>
            <input type="text" name="wr_7" id="wr_7" value="<?php echo $v['wr_7']; ?>" placeholder="매주 월요일">
        </div>
        <div class="eottae-field">
            <label for="wr_8">영업상태</label>
            <select name="wr_8" id="wr_8" class="eottae-select">
                <?php foreach (array('영업중', '휴업', '폐업', '준비중') as $st) { ?>
                <option value="<?php echo $st; ?>"<?php echo $v['wr_8'] === $st ? ' selected' : ''; ?>><?php echo $st; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="shop-register-page__panel" data-step="4">
        <h3>5. 이미지 · 메뉴</h3>
        <div class="shop-register-page__photos">
            <?php for ($i = 0; $i < $file_count; $i++) { ?>
            <div class="shop-register-page__photo">
                <label for="bf_file_<?php echo $i + 1; ?>"><?php echo $i === 0 ? '대표 이미지' : '추가 이미지 '.$i; ?></label>
                <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1; ?>" accept="image/*" data-photo-input>
                <?php if ($w === 'u' && isset($file[$i]['file']) && $file[$i]['file']) { ?>
                <p class="eottae-field__hint">현재: <?php echo $file[$i]['source']; ?></p>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <p class="eottae-field__hint">메뉴·가격 정보는 업체 소개 본문에 작성해 주세요.</p>
    </div>

    <div class="shop-register-page__panel" data-step="5">
        <h3>6. 등록 확인</h3>
        <div class="shop-register-page__summary" id="shopRegisterSummary">
            <p class="shop-register-page__summary-empty">입력 내용을 확인해 주세요.</p>
        </div>
        <?php if ($is_use_captcha) { echo $captcha_html; } ?>
    </div>

    <div class="shop-register-page__nav">
        <button type="button" class="btn btn--ghost" data-wizard="prev">이전</button>
        <button type="button" class="btn btn--primary" data-wizard="next">다음</button>
        <button type="submit" class="btn btn--primary" data-wizard="submit" id="btn_submit" accesskey="s">등록 완료</button>
    </div>
    </form>
</section>

<script>
function fwrite_submit(f) {
    var ca = document.getElementById('ca_name');
    var wr1 = document.getElementById('wr_1');
    if (ca && wr1) {
        wr1.value = ca.value;
    }
    <?php echo $editor_js; ?>
    <?php echo $captcha_js; ?>
    document.getElementById('btn_submit').disabled = true;
    return true;
}
</script>
