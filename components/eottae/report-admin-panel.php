<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($report_can_view_contact)) {
    return;
}

$report_flash = function_exists('eottae_report_get_flash') ? eottae_report_get_flash() : '';
$report_converted = function_exists('eottae_report_parse_converted_ref')
    ? eottae_report_parse_converted_ref($view['wr_10'] ?? '')
    : array('bo_table' => '', 'wr_id' => 0);
$report_converted_url = '';
if ($report_converted['bo_table'] !== '' && $report_converted['wr_id'] > 0) {
    $report_converted_url = function_exists('get_pretty_url')
        ? get_pretty_url($report_converted['bo_table'], $report_converted['wr_id'])
        : G5_BBS_URL.'/board.php?bo_table='.$report_converted['bo_table'].'&wr_id='.$report_converted['wr_id'];
}
$report_convert_targets = eottae_report_convert_targets();
$report_convert_subject = get_text($view['wr_subject'] ?? '');
$report_convert_content = function_exists('eottae_report_build_convert_content')
    ? eottae_report_build_convert_content($view)
    : strip_tags((string) ($view['wr_content'] ?? ''));
$report_status_proc = G5_URL.'/proc/eottae-report-status.php';
$report_convert_proc = G5_URL.'/proc/eottae-report-convert.php';
$report_csrf_token = get_token();

if (function_exists('eottae_report_board_load_assets')) {
    eottae_report_board_load_assets();
}
$report_admin_js = G5_PATH.'/js/eottae-report-admin.js';
if (is_file($report_admin_js)) {
    add_javascript('<script src="'.G5_JS_URL.'/eottae-report-admin.js?ver='.(int) filemtime($report_admin_js).'" defer></script>', 25);
}
?>

<?php if ($report_flash !== '') { ?>
<div class="report-flash" role="status"><?php echo get_text($report_flash); ?></div>
<?php } ?>

<div class="report-admin-panel" id="reportAdminPanel">
    <h2 class="report-admin-panel__title">관리자 확인</h2>

    <dl class="report-admin-panel__grid report-admin-panel__grid--contact">
        <div class="report-admin-panel__item">
            <dt>연락 가능</dt>
            <dd><?php echo !empty($report_contact_ok) ? '예' : '아니오'; ?></dd>
        </div>
        <?php if (!empty($report_contact_ok) && $report_contact !== '') { ?>
        <div class="report-admin-panel__item">
            <dt>연락처</dt>
            <dd class="report-admin-panel__contact"><?php echo $report_contact; ?></dd>
        </div>
        <?php } ?>
        <?php if ($report_converted_url !== '') { ?>
        <div class="report-admin-panel__item report-admin-panel__item--full">
            <dt>전환된 글</dt>
            <dd><a href="<?php echo htmlspecialchars($report_converted_url, ENT_QUOTES, 'UTF-8'); ?>">게시글 보기 (<?php echo get_text($report_converted['bo_table']); ?> #<?php echo (int) $report_converted['wr_id']; ?>)</a></dd>
        </div>
        <?php } ?>
    </dl>

    <form class="report-admin-form report-admin-form--status" data-report-status-form method="post" action="<?php echo htmlspecialchars($report_status_proc, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="token" value="<?php echo $report_csrf_token; ?>">
        <input type="hidden" name="bo_table" value="<?php echo get_text($bo_table); ?>">
        <input type="hidden" name="wr_id" value="<?php echo (int) $view['wr_id']; ?>">

        <h3 class="report-admin-form__title">제보 상태 변경</h3>
        <p class="report-admin-form__current">
            현재 상태: <?php echo eottae_report_render_status_badge($report_status); ?>
        </p>

        <div class="report-admin-form__field">
            <label for="report_admin_wr_8">상태 변경</label>
            <select name="wr_8" id="report_admin_wr_8" class="report-admin-form__select" required>
                <?php foreach (eottae_report_statuses() as $status_key => $status_label) { ?>
                <option value="<?php echo get_text($status_key); ?>"<?php echo $report_status === $status_key ? ' selected' : ''; ?>><?php echo get_text($status_label); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="report-admin-form__field">
            <label for="report_admin_wr_9">관리자 메모</label>
            <textarea name="wr_9" id="report_admin_wr_9" class="report-admin-form__textarea" rows="3" placeholder="내부 확인 메모 (작성자에게 노출되지 않음)"><?php echo $report_admin_memo; ?></textarea>
        </div>

        <button type="submit" class="report-admin-form__submit" data-report-status-submit>상태 저장</button>
    </form>

    <section class="report-admin-form report-admin-form--convert" data-report-convert-panel>
        <h3 class="report-admin-form__title">게시글로 전환 준비</h3>
        <p class="report-admin-form__hint">제보 원본은 유지됩니다. 복사 등록 후 wr_10에 전환 글 정보가 저장됩니다.</p>

        <?php if ($report_converted_url !== '') { ?>
        <p class="report-admin-form__warn">이미 전환된 제보입니다. 중복 전환할 수 없습니다.</p>
        <?php } else { ?>
        <div class="report-admin-form__field">
            <label for="report_target_bo_table">전환할 게시판</label>
            <select id="report_target_bo_table" class="report-admin-form__select" data-report-target-board>
                <option value="">게시판 선택</option>
                <?php foreach ($report_convert_targets as $target_key => $target_label) { ?>
                <option value="<?php echo get_text($target_key); ?>"><?php echo get_text($target_label); ?> (<?php echo get_text($target_key); ?>)</option>
                <?php } ?>
            </select>
        </div>

        <div class="report-admin-form__field">
            <label for="report_copy_subject">제목 (복사용)</label>
            <input type="text" id="report_copy_subject" class="report-admin-form__input" value="<?php echo $report_convert_subject; ?>" readonly data-report-copy-subject>
        </div>

        <div class="report-admin-form__field">
            <label for="report_copy_content">내용 (복사용)</label>
            <textarea id="report_copy_content" class="report-admin-form__textarea" rows="8" readonly data-report-copy-content><?php echo get_text($report_convert_content); ?></textarea>
            <button type="button" class="report-admin-form__ghost" data-report-copy-btn>내용 복사</button>
        </div>

        <p class="report-admin-form__hint">자동 전환이 부담될 때는 위 내용을 복사해 선택한 게시판에 직접 작성하세요.</p>

        <form method="post" action="<?php echo htmlspecialchars($report_convert_proc, ENT_QUOTES, 'UTF-8'); ?>" data-report-convert-form>
            <input type="hidden" name="token" value="<?php echo $report_csrf_token; ?>">
            <input type="hidden" name="bo_table" value="<?php echo get_text($bo_table); ?>">
            <input type="hidden" name="wr_id" value="<?php echo (int) $view['wr_id']; ?>">
            <input type="hidden" name="target_bo_table" value="" data-report-target-input>
            <button type="submit" class="report-admin-form__submit report-admin-form__submit--convert" data-report-convert-submit>선택 게시판으로 복사 등록</button>
        </form>
        <?php } ?>
    </section>
</div>
