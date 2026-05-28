<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_is_event_board') || !eottae_is_event_board($bo_table ?? '')) {
    return;
}

if (function_exists('eottae_event_template_load_assets')) {
    eottae_event_template_load_assets();
}

if (!function_exists('eottae_event_normalize_type') && is_file(G5_LIB_PATH.'/eottae-event.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-event.lib.php';
}

$event_types = eottae_event_types();
$event_type = 'other';
$event_shop_ref = '';
$event_display_name = '';
$event_period_mode = 'period';
$event_start = '';
$event_end = '';
$event_benefit = '';
$event_contact = '';

if (isset($write) && is_array($write)) {
    $event_type = eottae_event_normalize_type($write['wr_1'] ?? 'other');
    $event_shop_ref = (string) ($write['wr_2'] ?? '');
    $event_display_name = get_text($write['wr_3'] ?? '');
    $event_period_mode = eottae_event_normalize_period_mode($write['wr_4'] ?? 'period');
    $event_start = eottae_event_normalize_date($write['wr_5'] ?? '');
    $event_end = eottae_event_normalize_date($write['wr_6'] ?? '');
    $event_benefit = get_text($write['wr_7'] ?? '');
    $event_contact = get_text($write['wr_8'] ?? '');
}

$is_super = isset($is_admin) && $is_admin === 'super';
$event_shops = eottae_event_selectable_shops(isset($member) ? $member : array(), $is_super);
?>

<section class="sebu-event-template" id="sebuEventTemplate" aria-labelledby="sebuEventTemplateTitle">
    <header class="sebu-event-template__head">
        <h2 class="sebu-event-template__title" id="sebuEventTemplateTitle">이벤트·프로모션 등록</h2>
        <p class="sebu-event-template__desc">할인·쿠폰·오픈 이벤트 정보를 간단히 입력해 주세요. 업체 연결은 선택 사항입니다.</p>
    </header>

    <div class="sebu-event-template__body">
        <div class="sebu-event-template__card">
            <h3 class="sebu-event-template__card-title">기본 정보</h3>

            <div class="sebu-event-template__field">
                <label for="wr_subject">이벤트/프로모션 제목 <span class="sebu-event-template__req">*</span></label>
                <input type="text" name="wr_subject" id="wr_subject" value="<?php echo isset($subject) ? $subject : ''; ?>" required maxlength="255" class="sebu-event-template__input" placeholder="예: 막탄 마사지샵 30% 할인 이벤트">
            </div>

            <div class="sebu-event-template__field">
                <label for="wr_1">이벤트 종류 <span class="sebu-event-template__req">*</span></label>
                <select name="wr_1" id="wr_1" class="sebu-event-template__select" required>
                    <?php foreach ($event_types as $type_key => $type_label) { ?>
                    <option value="<?php echo get_text($type_key); ?>"<?php echo $event_type === $type_key ? ' selected' : ''; ?>><?php echo get_text($type_label); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="sebu-event-template__field">
                <label for="wr_2">연결 업체 <span class="sebu-event-template__opt">(선택)</span></label>
                <select name="wr_2" id="wr_2" class="sebu-event-template__select">
                    <option value="">업체 연결 안 함</option>
                    <?php foreach ($event_shops as $shop) {
                        $ref = eottae_event_format_shop_ref($shop['bo_table'] ?? '', (int) ($shop['wr_id'] ?? 0));
                        if ($ref === '') {
                            continue;
                        }
                        $shop_label = get_text($shop['name'] ?? '');
                        if (!empty($shop['region'])) {
                            $shop_label .= ' · '.get_text($shop['region']);
                        }
                        if (!empty($shop['board_label'])) {
                            $shop_label .= ' ('.get_text($shop['board_label']).')';
                        }
                        ?>
                    <option value="<?php echo get_text($ref); ?>"<?php echo $event_shop_ref === $ref ? ' selected' : ''; ?>><?php echo $shop_label; ?></option>
                    <?php } ?>
                </select>
                <p class="sebu-event-template__hint">업체 등록이 없어도 글을 작성할 수 있습니다. 연결 시 목록·상세에서 업체정보 바로가기가 표시됩니다.</p>
            </div>

            <div class="sebu-event-template__field">
                <label for="wr_3">업체명 또는 작성자명 <span class="sebu-event-template__req">*</span></label>
                <input type="text" name="wr_3" id="wr_3" value="<?php echo $event_display_name; ?>" required maxlength="120" class="sebu-event-template__input" placeholder="예: 세부OO마사지 / 개인 이벤트 / 홍길동">
            </div>
        </div>

        <div class="sebu-event-template__card">
            <h3 class="sebu-event-template__card-title">기간·혜택</h3>

            <div class="sebu-event-template__field">
                <span class="sebu-event-template__label">이벤트 기간 설정 <span class="sebu-event-template__req">*</span></span>
                <div class="sebu-event-template__radios" role="radiogroup" aria-label="이벤트 기간 설정">
                    <label class="sebu-event-template__radio">
                        <input type="radio" name="wr_4" value="period"<?php echo $event_period_mode === 'period' ? ' checked' : ''; ?>>
                        <span>기간 있음</span>
                    </label>
                    <label class="sebu-event-template__radio">
                        <input type="radio" name="wr_4" value="none"<?php echo $event_period_mode === 'none' ? ' checked' : ''; ?>>
                        <span>기간 없음</span>
                    </label>
                </div>
                <p class="sebu-event-template__hint">기간 있음: 종료일 이후 자동 종료 · 기간 없음: 작성자가 직접 종료 처리</p>
            </div>

            <div class="sebu-event-template__dates" id="sebuEventDates" data-period-mode="<?php echo get_text($event_period_mode); ?>">
                <div class="sebu-event-template__field sebu-event-template__field--half">
                    <label for="wr_5">시작일 <span class="sebu-event-template__opt">(선택)</span></label>
                    <input type="date" name="wr_5" id="wr_5" value="<?php echo $event_start; ?>" class="sebu-event-template__input">
                </div>
                <div class="sebu-event-template__field sebu-event-template__field--half sebu-event-template__field--end" id="sebuEventEndWrap">
                    <label for="wr_6">종료일 <span class="sebu-event-template__req sebu-event-template__req--end">*</span></label>
                    <input type="date" name="wr_6" id="wr_6" value="<?php echo $event_end; ?>" class="sebu-event-template__input">
                </div>
            </div>

            <div class="sebu-event-template__field">
                <label for="wr_7">혜택 요약 <span class="sebu-event-template__req">*</span></label>
                <input type="text" name="wr_7" id="wr_7" value="<?php echo $event_benefit; ?>" required maxlength="200" class="sebu-event-template__input" placeholder="예: 전 메뉴 20% 할인 / 첫 방문 마사지 300페소 할인">
            </div>
        </div>

        <div class="sebu-event-template__card">
            <h3 class="sebu-event-template__card-title">상세·문의</h3>

            <div class="sebu-event-template__field">
                <label for="wr_8">문의 방법 <span class="sebu-event-template__opt">(선택)</span></label>
                <input type="text" name="wr_8" id="wr_8" value="<?php echo $event_contact; ?>" maxlength="200" class="sebu-event-template__input" placeholder="예: 카카오톡 ID / 전화번호 / 댓글 문의 / 업체 페이지 참고">
            </div>

            <div class="sebu-event-template__field sebu-event-template__field--editor">
                <label for="wr_content">상세 내용 <span class="sebu-event-template__req">*</span></label>
                <?php
                $eottae_editor_placeholder = '이벤트 내용, 이용 방법, 주의사항을 간단히 입력해주세요.';
                include G5_PATH.'/components/eottae/board-write-editor.php';
                ?>
            </div>
        </div>
    </div>
</section>
