<?php
if (!defined('_GNUBOARD_')) exit;

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
?>

<section class="shop-register-page board-wrap board-wrap--eottae-shop board-write" id="bo_w" style="width:<?php echo $width; ?>">
    <h2 class="sound_only"><?php echo $g5['title']; ?></h2>

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
    <?php echo $option_hidden; ?>

    <!-- 1. 기본정보 -->
    <div class="shop-register-page__panel is-active" data-step="0">
        <h3>1. 업체 기본정보</h3>
        <div class="eottae-field">
            <label for="wr_subject">업체명 (필수)</label>
            <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" required maxlength="255" placeholder="업체명을 입력하세요">
        </div>
        <div class="eottae-field">
            <label for="wr_1">카테고리</label>
            <input type="text" name="wr_1" id="wr_1" value="<?php echo $v['wr_1']; ?>" placeholder="예: 맛집, 카페, 미용">
        </div>
        <div class="eottae-field">
            <label for="wr_content">업체 소개</label>
            <textarea name="wr_content" id="wr_content" placeholder="업체를 소개해 주세요"><?php echo $content; ?></textarea>
        </div>
    </div>

    <!-- 2. 위치 -->
    <div class="shop-register-page__panel" data-step="1">
        <h3>2. 위치정보</h3>
        <div class="eottae-field">
            <label for="wr_2">대표 지역</label>
            <input type="text" name="wr_2" id="wr_2" value="<?php echo $v['wr_2']; ?>" placeholder="예: IT Park, Ayala">
        </div>
        <div class="eottae-field">
            <label for="wr_3">주소</label>
            <input type="text" name="wr_3" id="wr_3" value="<?php echo $v['wr_3']; ?>" placeholder="상세 주소">
        </div>
        <div class="eottae-field">
            <label for="wr_9">위도 (Latitude)</label>
            <input type="text" name="wr_9" id="wr_9" value="<?php echo $v['wr_9']; ?>" placeholder="10.3157">
        </div>
        <div class="eottae-field">
            <label for="wr_10">경도 (Longitude)</label>
            <input type="text" name="wr_10" id="wr_10" value="<?php echo $v['wr_10']; ?>" placeholder="123.8854">
        </div>
    </div>

    <!-- 3. 연락처/문의 -->
    <div class="shop-register-page__panel" data-step="2">
        <h3>3. 연락처 · 문의 연결</h3>
        <div class="eottae-field">
            <label for="wr_4">전화번호</label>
            <input type="tel" name="wr_4" id="wr_4" value="<?php echo $v['wr_4']; ?>" placeholder="032-123-4567">
        </div>
        <div class="eottae-field">
            <label for="wr_5">문의 연결 코드</label>
            <input type="text" name="wr_5" id="wr_5" value="<?php echo $v['wr_5']; ?>" placeholder="inquiry_code (내부용)">
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

    <!-- 4. 영업정보 -->
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
            <select name="wr_8" id="wr_8">
                <?php foreach (array('영업중', '휴업', '폐업', '준비중') as $st) { ?>
                <option value="<?php echo $st; ?>"<?php echo $v['wr_8'] === $st ? ' selected' : ''; ?>><?php echo $st; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <!-- 5. 이미지 -->
    <div class="shop-register-page__panel" data-step="4">
        <h3>5. 이미지 · 메뉴</h3>
        <?php for ($i = 0; $i < $file_count; $i++) { ?>
        <div class="eottae-field">
            <label for="bf_file_<?php echo $i + 1; ?>"><?php echo $i === 0 ? '대표 이미지' : '추가 이미지 '.$i; ?></label>
            <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1; ?>" accept="image/*">
            <?php if ($w === 'u' && isset($file[$i]['file'])) { ?>
            <p class="eottae-field__hint">현재: <?php echo $file[$i]['source']; ?></p>
            <?php } ?>
        </div>
        <?php } ?>
        <p class="eottae-field__hint">메뉴·가격 정보는 업체 소개 본문에 작성해 주세요.</p>
    </div>

    <!-- 6. 완료 -->
    <div class="shop-register-page__panel" data-step="5">
        <h3>6. 등록 확인</h3>
        <p>입력하신 정보를 확인한 뒤 등록을 완료해 주세요. 관리자 검토 후 노출될 수 있습니다.</p>
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
    <?php echo $editor_js; ?>
    <?php echo $captcha_js; ?>
    document.getElementById('btn_submit').disabled = true;
    return true;
}
</script>
