<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_is_report_board') || !eottae_is_report_board($bo_table ?? '')) {
    return;
}

if (function_exists('eottae_report_template_load_assets')) {
    eottae_report_template_load_assets();
}

if (!function_exists('eottae_report_types') && is_file(G5_LIB_PATH.'/eottae-report.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-report.lib.php';
}

$report_type = 'other';
$report_region = '';
$report_anonymous = false;
$report_contact_ok = false;
$report_contact = '';
$report_shop = '';
$report_link = '';

if (isset($write) && is_array($write)) {
    $report_type = eottae_report_normalize_type($write['wr_1'] ?? 'other');
    $report_region = eottae_report_normalize_region($write['wr_2'] ?? '');
    $report_anonymous = eottae_report_is_anonymous($write);
    $report_contact_ok = (string) ($write['wr_4'] ?? '') === '1';
    $report_contact = get_text($write['wr_5'] ?? '');
    $report_shop = get_text($write['wr_6'] ?? '');
    $report_link = get_text($write['wr_7'] ?? '');
}

$report_types = eottae_report_types();
$report_regions = eottae_report_regions();
$content_value = isset($content) ? $content : '';
if ($content_value !== '' && strip_tags($content_value) === $content_value) {
    $content_value = get_text($content_value);
}
?>

<section class="sebu-report-template" id="sebuReportTemplate" aria-labelledby="sebuReportTemplateTitle">
    <div class="sebu-report-template__notice" role="note">
        <p>제보해주신 내용은 관리자 확인 후 공개될 수 있습니다.</p>
        <p>허위 정보, 비방, 개인정보가 포함된 내용은 공개되지 않을 수 있습니다.</p>
        <p>익명 제보도 가능하지만, 정확한 확인이 필요한 경우 연락처를 남겨주세요.</p>
    </div>

    <div class="sebu-report-template__card">
        <h2 class="sebu-report-template__card-title" id="sebuReportTemplateTitle">제보 정보</h2>

        <div class="sebu-report-template__field">
            <label class="sebu-report-template__label" for="wr_1">제보 유형 <span class="sebu-report-template__req">*</span></label>
            <select name="wr_1" id="wr_1" class="sebu-report-template__select" required>
                <option value="">유형 선택</option>
                <?php foreach ($report_types as $type_key => $type_label) { ?>
                <option value="<?php echo get_text($type_key); ?>"<?php echo $report_type === $type_key ? ' selected' : ''; ?>><?php echo get_text($type_label); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="sebu-report-template__field">
            <label class="sebu-report-template__label" for="wr_subject">제보 제목 <span class="sebu-report-template__req">*</span></label>
            <input type="text" name="wr_subject" id="wr_subject" value="<?php echo isset($subject) ? get_text($subject) : ''; ?>" required maxlength="255" class="sebu-report-template__input" placeholder="예: 막탄 뉴타운 근처 도로 공사 중입니다">
        </div>

        <div class="sebu-report-template__field">
            <label class="sebu-report-template__label" for="wr_content">제보 내용 <span class="sebu-report-template__req">*</span></label>
            <textarea name="wr_content" id="wr_content" class="sebu-report-template__textarea" required placeholder="예: 오늘 오후부터 막탄 뉴타운 근처 도로가 막혀요. 공사 중이라 우회 추천합니다."><?php echo $content_value; ?></textarea>
        </div>

        <div class="sebu-report-template__field">
            <label class="sebu-report-template__label" for="wr_2">지역 <span class="sebu-report-template__req">*</span></label>
            <select name="wr_2" id="wr_2" class="sebu-report-template__select" required>
                <option value="">지역 선택</option>
                <?php foreach ($report_regions as $region_key => $region_label) { ?>
                <option value="<?php echo get_text($region_key); ?>"<?php echo $report_region === $region_key ? ' selected' : ''; ?>><?php echo get_text($region_label); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="sebu-report-template__card">
        <h3 class="sebu-report-template__card-title">추가 정보 <span class="sebu-report-template__opt">(선택)</span></h3>

        <div class="sebu-report-template__field">
            <label class="sebu-report-template__check">
                <input type="checkbox" name="wr_3" id="wr_3" value="1"<?php echo $report_anonymous ? ' checked' : ''; ?>>
                <span>익명으로 공개하기</span>
            </label>
        </div>

        <div class="sebu-report-template__field">
            <label class="sebu-report-template__check">
                <input type="checkbox" name="wr_4" id="wr_4" value="1" data-report-contact-toggle<?php echo $report_contact_ok ? ' checked' : ''; ?>>
                <span>세부어때에서 확인 연락 가능</span>
            </label>
            <div class="sebu-report-template__contact" data-report-contact-wrap<?php echo $report_contact_ok ? '' : ' hidden'; ?>>
                <label class="sebu-report-template__label" for="wr_5">연락처</label>
                <input type="text" name="wr_5" id="wr_5" value="<?php echo $report_contact; ?>" class="sebu-report-template__input" maxlength="120" placeholder="카카오톡 ID / 전화번호 / 이메일 등" autocomplete="off">
            </div>
        </div>

        <div class="sebu-report-template__field">
            <label class="sebu-report-template__label" for="wr_6">관련 업체명</label>
            <input type="text" name="wr_6" id="wr_6" value="<?php echo $report_shop; ?>" class="sebu-report-template__input" maxlength="120" placeholder="관련 업체가 있다면 입력해주세요">
        </div>

        <div class="sebu-report-template__field">
            <label class="sebu-report-template__label" for="wr_7">관련 링크</label>
            <input type="url" name="wr_7" id="wr_7" value="<?php echo $report_link; ?>" class="sebu-report-template__input" maxlength="255" placeholder="https://">
        </div>
    </div>
</section>

<input type="hidden" name="html" value="html2">
