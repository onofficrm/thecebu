<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-report.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

eottae_challenge_ensure_schema();

$challenges = eottae_challenge_list(array('limit' => 100, 'include_hidden' => true));
$badges = eottae_challenge_badge_options();
$statuses = eottae_challenge_status_options();
$pending_reports = eottae_challenge_list_pending_reports(20);
$admin_token = eottae_talkroom_admin_token();
$proc_url = eottae_challenge_admin_proc_url();

$edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$edit = $edit_id > 0 ? eottae_challenge_get($edit_id, true) : null;

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-challenge.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-challenge-admin.js" defer></script>', 24);

g5_page_start('세부어때 챌린지 관리');
?>

<main class="sebu-challenge-admin">
    <header class="sebu-challenge-admin__header">
        <a href="<?php echo eottae_challenge_list_url(); ?>" class="sebu-challenge-admin__back">← 챌린지 보기</a>
        <h1 class="sebu-challenge-admin__title">세부어때 챌린지 관리</h1>
        <p class="sebu-challenge-admin__desc">챌린지 생성·수정, 참여글 관리, 우수글 선정, 신고 처리</p>
    </header>

    <section class="sebu-challenge-admin__panel" aria-labelledby="challenge-form-title">
        <h2 id="challenge-form-title" class="sebu-challenge-admin__panel-title"><?php echo $edit ? '챌린지 수정' : '챌린지 생성'; ?></h2>
        <form class="sebu-challenge-admin-form" id="sebuChallengeAdminForm" enctype="multipart/form-data" data-proc-url="<?php echo get_text($proc_url); ?>" data-admin-token="<?php echo get_text($admin_token); ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="challenge_id" value="<?php echo $edit ? (int) $edit['challenge_id'] : 0; ?>">

            <label class="sebu-challenge-form__field">
                <span class="sebu-challenge-form__label">챌린지 제목 *</span>
                <input type="text" name="title" class="sebu-challenge-form__input" required maxlength="200" value="<?php echo $edit ? get_text($edit['title'] ?? '') : ''; ?>">
            </label>

            <label class="sebu-challenge-form__field">
                <span class="sebu-challenge-form__label">설명</span>
                <textarea name="description" class="sebu-challenge-form__textarea" rows="3"><?php echo $edit ? get_text($edit['description'] ?? '') : ''; ?></textarea>
            </label>

            <div class="sebu-challenge-admin-form__row">
                <label class="sebu-challenge-form__field">
                    <span class="sebu-challenge-form__label">시작일 *</span>
                    <input type="date" name="start_date" class="sebu-challenge-form__input" required value="<?php echo $edit ? get_text($edit['start_date'] ?? '') : date('Y-m-d'); ?>">
                </label>
                <label class="sebu-challenge-form__field">
                    <span class="sebu-challenge-form__label">종료일 *</span>
                    <input type="date" name="end_date" class="sebu-challenge-form__input" required value="<?php echo $edit ? get_text($edit['end_date'] ?? '') : date('Y-m-d', strtotime('+30 days')); ?>">
                </label>
                <label class="sebu-challenge-form__field">
                    <span class="sebu-challenge-form__label">상태</span>
                    <select name="status" class="sebu-challenge-form__select">
                        <?php foreach ($statuses as $code => $label) { ?>
                        <option value="<?php echo get_text($code); ?>"<?php echo $edit && ($edit['status'] ?? '') === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
            </div>

            <div class="sebu-challenge-admin-form__row">
                <label class="sebu-challenge-form__field">
                    <span class="sebu-challenge-form__label">아이콘 (이모지)</span>
                    <input type="text" name="icon" class="sebu-challenge-form__input" maxlength="20" value="<?php echo $edit ? get_text($edit['icon'] ?? '') : '🏆'; ?>">
                </label>
                <label class="sebu-challenge-form__field">
                    <span class="sebu-challenge-form__label">대표 이미지</span>
                    <input type="file" name="challenge_image" class="sebu-challenge-form__file" accept="image/*">
                </label>
            </div>

            <label class="sebu-challenge-form__field">
                <span class="sebu-challenge-form__label">참여 방법</span>
                <textarea name="how_to_join" class="sebu-challenge-form__textarea" rows="4"><?php echo $edit ? get_text($edit['how_to_join'] ?? '') : ''; ?></textarea>
            </label>

            <label class="sebu-challenge-form__field">
                <span class="sebu-challenge-form__label">참여 조건</span>
                <textarea name="conditions_text" class="sebu-challenge-form__textarea" rows="2"><?php echo $edit ? get_text($edit['conditions_text'] ?? '') : ''; ?></textarea>
            </label>

            <label class="sebu-challenge-form__field">
                <span class="sebu-challenge-form__label">주의사항</span>
                <textarea name="notice_text" class="sebu-challenge-form__textarea" rows="2"><?php echo $edit ? get_text($edit['notice_text'] ?? '') : ''; ?></textarea>
            </label>

            <label class="sebu-challenge-form__field">
                <span class="sebu-challenge-form__label">보상 설명</span>
                <textarea name="reward_text" class="sebu-challenge-form__textarea" rows="3"><?php echo $edit ? get_text($edit['reward_text'] ?? '') : ''; ?></textarea>
            </label>

            <div class="sebu-challenge-admin-form__row">
                <label class="sebu-challenge-form__field">
                    <span class="sebu-challenge-form__label">지급 포인트</span>
                    <input type="number" name="reward_point" class="sebu-challenge-form__input" min="0" value="<?php echo $edit ? (int) ($edit['reward_point'] ?? 0) : 100; ?>">
                </label>
                <label class="sebu-challenge-form__field">
                    <span class="sebu-challenge-form__label">지급 뱃지</span>
                    <select name="reward_badge" class="sebu-challenge-form__select">
                        <option value="">없음</option>
                        <?php foreach ($badges as $code => $label) { ?>
                        <option value="<?php echo get_text($code); ?>"<?php echo $edit && ($edit['reward_badge'] ?? '') === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
            </div>

            <div class="sebu-challenge-admin-form__checks">
                <label><input type="checkbox" name="select_best" value="1"<?php echo !$edit || !empty($edit['select_best']) ? ' checked' : ''; ?>> 우수 참여글 선정</label>
                <label><input type="checkbox" name="is_featured" value="1"<?php echo !$edit || !empty($edit['is_featured']) ? ' checked' : ''; ?>> 메인 노출</label>
            </div>

            <p class="sebu-challenge-admin-form__status" data-admin-status role="status"></p>
            <button type="submit" class="sebu-challenge-btn sebu-challenge-btn--primary"><?php echo $edit ? '수정 저장' : '챌린지 생성'; ?></button>
            <?php if ($edit) { ?>
            <a href="<?php echo eottae_challenge_admin_url(); ?>" class="sebu-challenge-btn sebu-challenge-btn--ghost">새로 만들기</a>
            <?php } ?>
        </form>
    </section>

    <section class="sebu-challenge-admin__panel">
        <h2 class="sebu-challenge-admin__panel-title">챌린지 목록</h2>
        <ul class="sebu-challenge-admin-list">
            <?php foreach ($challenges as $item) { ?>
            <li class="sebu-challenge-admin-list__item">
                <div>
                    <strong><?php echo get_text($item['title'] ?? ''); ?></strong>
                    <span class="<?php echo get_text($item['display_status_class'] ?? ''); ?>"><?php echo get_text($item['display_status_label'] ?? ''); ?></span>
                    <p><?php echo get_text($item['period_label'] ?? ''); ?> · 참여 <?php echo number_format((int) ($item['participant_count'] ?? 0)); ?> · 인증 <?php echo number_format((int) ($item['entry_count'] ?? 0)); ?></p>
                </div>
                <div class="sebu-challenge-admin-list__actions">
                    <a href="<?php echo eottae_challenge_view_url((int) $item['challenge_id']); ?>" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm">보기</a>
                    <a href="<?php echo eottae_challenge_admin_url(); ?>?edit=<?php echo (int) $item['challenge_id']; ?>" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm">수정</a>
                    <button type="button" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm" data-challenge-delete="<?php echo (int) $item['challenge_id']; ?>">숨김</button>
                </div>
            </li>
            <?php } ?>
        </ul>
    </section>

    <?php if (!empty($pending_reports)) { ?>
    <section class="sebu-challenge-admin__panel">
        <h2 class="sebu-challenge-admin__panel-title">신고 대기 (<?php echo count($pending_reports); ?>)</h2>
        <ul class="sebu-challenge-admin-reports">
            <?php foreach ($pending_reports as $report) { ?>
            <li class="sebu-challenge-admin-reports__item">
                <p><strong><?php echo get_text($report['entry_title'] ?? ''); ?></strong> — <?php echo get_text($report['reason_label'] ?? ''); ?></p>
                <p class="sebu-challenge-admin-reports__meta">신고자 <?php echo get_text($report['reporter_mb_id'] ?? ''); ?> · <?php echo get_text($report['created_at'] ?? ''); ?></p>
                <div class="sebu-challenge-admin-list__actions">
                    <a href="<?php echo eottae_challenge_entry_url((int) $report['entry_id']); ?>" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm">글 보기</a>
                    <button type="button" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm" data-report-handle="<?php echo (int) $report['report_id']; ?>" data-report-action="review">확인</button>
                    <button type="button" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm" data-report-handle="<?php echo (int) $report['report_id']; ?>" data-report-action="hide_entry">글 숨김</button>
                    <button type="button" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm" data-report-handle="<?php echo (int) $report['report_id']; ?>" data-report-action="reject">기각</button>
                </div>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <?php if ($edit) {
        $admin_entries = eottae_challenge_list_entries((int) $edit['challenge_id'], array('limit' => 50, 'include_private' => true));
        if (!empty($admin_entries)) { ?>
    <section class="sebu-challenge-admin__panel">
        <h2 class="sebu-challenge-admin__panel-title">「<?php echo get_text($edit['title'] ?? ''); ?>」 참여글 관리</h2>
        <ul class="sebu-challenge-admin-entries">
            <?php foreach ($admin_entries as $entry) { ?>
            <li class="sebu-challenge-admin-entries__item">
                <div>
                    <strong><?php echo get_text($entry['title'] ?? ''); ?></strong>
                    <?php if (!empty($entry['is_best'])) { ?><span class="sebu-challenge-badge sebu-challenge-badge--best">우수</span><?php } ?>
                    <p><?php echo get_text($entry['writer_name'] ?? ''); ?> · <?php echo get_text($entry['time_label'] ?? ''); ?></p>
                </div>
                <div class="sebu-challenge-admin-list__actions">
                    <a href="<?php echo $entry['href']; ?>" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm">보기</a>
                    <button type="button" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm" data-entry-best="<?php echo (int) $entry['entry_id']; ?>" data-is-best="<?php echo empty($entry['is_best']) ? '1' : '0'; ?>"><?php echo empty($entry['is_best']) ? '우수 선정' : '우수 해제'; ?></button>
                    <button type="button" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm" data-entry-hide="<?php echo (int) $entry['entry_id']; ?>">숨김</button>
                </div>
            </li>
            <?php } ?>
        </ul>
    </section>
        <?php }
    } ?>
</main>

<script>
window.eottaeChallengeAdminProcUrl = <?php echo json_encode($proc_url, JSON_UNESCAPED_UNICODE); ?>;
window.eottaeChallengeAdminToken = <?php echo json_encode($admin_token, JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php
g5_page_end();
