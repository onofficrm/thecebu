<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-column.lib.php';

global $is_member, $member;

if (!$is_member) {
    alert('로그인 후 신청할 수 있습니다.', function_exists('eottae_login_url') ? eottae_login_url(eottae_column_apply_url()) : G5_BBS_URL.'/login.php');
}

eottae_column_ensure_schema();

if (eottae_column_is_columnist($member['mb_id'])) {
    alert('이미 칼럼니스트로 등록되어 있습니다.', eottae_column_mypage_url());
}

$latest_application = eottae_column_get_latest_application($member['mb_id']);
$areas = eottae_column_area_options();
$token = eottae_column_member_token();

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);

g5_page_start('칼럼니스트 신청');
?>

<main class="sebu-column-write-page sebu-column-apply-page">
    <header class="sebu-column-write-page__head">
        <p class="sebu-column-page__back"><a href="<?php echo eottae_column_list_url(); ?>">← 생활정보 컬럼</a></p>
        <h1 class="sebu-column-write-page__title">칼럼니스트 신청</h1>
        <p class="sebu-column-write-page__guide">세부 교민에게 실제 도움이 되는 경험과 정보를 꾸준히 전할 분을 기다립니다. 신청이 승인되면 전용 글쓰기 화면과 칼럼니스트 프로필이 열립니다.</p>
    </header>

    <?php if ($latest_application) { ?>
    <section class="sebu-column-apply-status">
        <h2 class="sebu-column-apply-status__title">최근 신청 상태</h2>
        <p>
            <?php echo get_text(eottae_column_application_status_label($latest_application['status'] ?? '')); ?>
            · <?php echo get_text(substr((string) ($latest_application['created_at'] ?? ''), 0, 10)); ?>
        </p>
        <?php if (!empty($latest_application['review_memo'])) { ?>
        <p class="sebu-column-apply-status__memo"><?php echo nl2br(get_text($latest_application['review_memo'])); ?></p>
        <?php } ?>
    </section>
    <?php } ?>

    <?php if ($latest_application && ($latest_application['status'] ?? '') === 'pending') { ?>
    <p class="sebu-column-empty">현재 검토 중인 신청서가 있습니다. 관리자 확인 후 다시 안내됩니다.</p>
    <?php } else { ?>
    <form class="sebu-column-write-form" method="post" action="<?php echo eottae_column_proc_url(); ?>">
        <input type="hidden" name="action" value="apply_columnist">
        <input type="hidden" name="eottae_column_token" value="<?php echo get_text($token); ?>">

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">필명 *</span>
            <input type="text" name="pen_name" class="sebu-column-form__input" maxlength="80" required value="<?php echo get_text($member['mb_nick'] ?? ''); ?>">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">희망 타이틀 *</span>
            <input type="text" name="title" class="sebu-column-form__input" maxlength="120" required placeholder="예: 교육·가족 컬럼니스트">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">전문 분야 *</span>
            <input type="text" name="specialty" class="sebu-column-form__input" maxlength="200" required placeholder="예: 국제학교, 아이 병원, 가족생활">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">소개글 *</span>
            <textarea name="bio" class="sebu-column-form__textarea" rows="4" required placeholder="세부에서 어떤 경험과 정보를 전할 수 있는지 소개해 주세요."></textarea>
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">활동 지역</span>
            <select name="area" class="sebu-column-form__select">
                <option value="">선택</option>
                <?php foreach ($areas as $code => $label) { ?>
                <option value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">홈페이지</span>
            <input type="url" name="website_url" class="sebu-column-form__input" placeholder="https://">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">SNS</span>
            <input type="url" name="sns_url" class="sebu-column-form__input" placeholder="https://">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">샘플 글 또는 참고 링크</span>
            <input type="url" name="sample_url" class="sebu-column-form__input" placeholder="https://">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">신청 메모</span>
            <textarea name="message" class="sebu-column-form__textarea" rows="3" placeholder="운영자에게 전하고 싶은 내용을 적어주세요."></textarea>
        </label>

        <div class="sebu-column-write-form__actions">
            <a href="<?php echo eottae_column_list_url(); ?>" class="sebu-column-btn sebu-column-btn--ghost">취소</a>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary">신청하기</button>
        </div>
    </form>
    <?php } ?>
</main>

<?php
g5_page_end();
