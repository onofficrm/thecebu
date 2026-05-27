<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_PATH.'/components/eottae/column-author-profile.php';

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
$pen_name_default = trim((string) ($member['mb_nick'] ?? ''));
$profile_initial = function_exists('mb_substr') && $pen_name_default !== ''
    ? mb_substr($pen_name_default, 0, 1, 'UTF-8')
    : ($pen_name_default !== '' ? substr($pen_name_default, 0, 1) : '?');

add_stylesheet('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap">', 20);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-column.js" defer></script>', 24);

g5_page_start('컬럼리스트 신청');
?>

<main class="sebu-column-write-page sebu-column-apply-page sebu-column-editorial" data-sebu-column-apply>
    <header class="sebu-column-studio">
        <p class="sebu-column-page__back"><a href="<?php echo eottae_column_list_url(); ?>">← <?php echo eottae_column_menu_label(); ?></a></p>
        <p class="sebu-column-studio__eyebrow">Columnist Application</p>
        <h1 class="sebu-column-studio__title">컬럼리스트 신청</h1>
        <p class="sebu-column-studio__guide">세부 교민에게 실제 도움이 되는 경험과 정보를 꾸준히 전할 분을 기다립니다. 신청이 승인되면 전용 글쓰기 화면과 칼럼니스트 프로필이 열립니다.</p>
        <ul class="sebu-column-studio__tips">
            <li>필명·전문 분야·소개글은 독자가 신뢰할 수 있도록 구체적으로 작성해 주세요.</li>
            <li>운영 중인 SNS·블로그가 있으면 링크를 남기면 검토에 도움이 됩니다.</li>
            <li>승인 전까지는 신청서를 수정할 수 없으며, 결과는 알림·마이페이지에서 확인할 수 있습니다.</li>
        </ul>
    </header>

    <?php if ($latest_application) {
        $status = (string) ($latest_application['status'] ?? '');
        $status_label = eottae_column_application_status_label($status);
        $status_class = preg_replace('/[^a-z0-9_-]/', '', $status);
        ?>
    <section class="sebu-column-apply-status sebu-column-apply-status--<?php echo get_text($status_class); ?>" aria-live="polite">
        <div class="sebu-column-apply-status__head">
            <h2 class="sebu-column-apply-status__title">최근 신청 상태</h2>
            <span class="sebu-column-apply-status__badge"><?php echo get_text($status_label); ?></span>
        </div>
        <p class="sebu-column-apply-status__date">신청일 <?php echo get_text(substr((string) ($latest_application['created_at'] ?? ''), 0, 10)); ?></p>
        <?php if (!empty($latest_application['review_memo'])) { ?>
        <p class="sebu-column-apply-status__memo"><?php echo nl2br(get_text($latest_application['review_memo'])); ?></p>
        <?php } ?>
    </section>
    <?php } ?>

    <?php if ($latest_application && ($latest_application['status'] ?? '') === 'pending') { ?>
    <div class="sebu-column-apply-pending">
        <p class="sebu-column-apply-pending__title">검토 중인 신청이 있습니다</p>
        <p class="sebu-column-apply-pending__desc">관리자 확인이 끝날 때까지 새 신청서를 제출할 수 없습니다. 결과가 나오면 이 페이지에서 다시 확인해 주세요.</p>
        <a href="<?php echo eottae_column_mypage_url(); ?>" class="sebu-column-btn sebu-column-btn--outline">마이페이지로 이동</a>
    </div>
    <?php } else { ?>
    <form class="sebu-column-write-form sebu-column-write-form--editorial sebu-column-apply-form" method="post" action="<?php echo eottae_column_proc_url(); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="apply_columnist">
        <input type="hidden" name="eottae_column_token" value="<?php echo get_text($token); ?>">

        <section class="sebu-column-apply-section" aria-labelledby="sebu-column-apply-profile-title">
            <header class="sebu-column-apply-section__head">
                <h2 class="sebu-column-apply-section__title" id="sebu-column-apply-profile-title">기본 프로필</h2>
                <p class="sebu-column-apply-section__desc">독자에게 보여질 이름과 전문 분야를 입력해 주세요.</p>
            </header>

            <div class="sebu-column-apply-profile">
                <div class="sebu-column-apply-profile__media">
                    <div class="sebu-column-apply-profile__preview" data-column-apply-avatar-preview>
                        <img src="" alt="" class="sebu-column-apply-profile__preview-img" data-column-apply-avatar-img>
                        <span class="sebu-column-apply-profile__preview-initial" data-column-apply-avatar-initial><?php echo get_text($profile_initial); ?></span>
                    </div>
                    <label class="sebu-column-apply-profile__upload">
                        <span class="sebu-column-form__label">프로필 사진</span>
                        <input type="file" name="profile_image" class="sebu-column-form__file" accept="image/jpeg,image/png,image/gif,image/webp" data-column-apply-avatar-input>
                        <span class="sebu-column-form__hint">등록하지 않으면 필명 첫 글자로 프로필이 자동 생성됩니다.</span>
                    </label>
                </div>

                <div class="sebu-column-apply-profile__fields">
                    <div class="sebu-column-form__row">
                        <label class="sebu-column-form__field">
                            <span class="sebu-column-form__label">필명 *</span>
                            <input type="text" name="pen_name" class="sebu-column-form__input" maxlength="80" required value="<?php echo get_text($pen_name_default); ?>" data-column-apply-pen-name>
                        </label>
                        <label class="sebu-column-form__field">
                            <span class="sebu-column-form__label">희망 타이틀 *</span>
                            <input type="text" name="title" class="sebu-column-form__input" maxlength="120" required placeholder="예: 교육·가족 컬럼니스트">
                        </label>
                    </div>

                    <label class="sebu-column-form__field">
                        <span class="sebu-column-form__label">전문 분야 *</span>
                        <input type="text" name="specialty" class="sebu-column-form__input" maxlength="200" required placeholder="예: 국제학교, 아이 병원, 가족생활">
                    </label>

                    <label class="sebu-column-form__field sebu-column-form__field--last">
                        <span class="sebu-column-form__label">소개글 *</span>
                        <textarea name="bio" class="sebu-column-form__textarea sebu-column-form__textarea--apply" rows="4" required placeholder="세부에서 어떤 경험과 정보를 전할 수 있는지 소개해 주세요."></textarea>
                    </label>
                </div>
            </div>
        </section>

        <section class="sebu-column-apply-section" aria-labelledby="sebu-column-apply-sns-title">
            <header class="sebu-column-apply-section__head">
                <h2 class="sebu-column-apply-section__title" id="sebu-column-apply-sns-title">SNS · 채널 링크</h2>
                <p class="sebu-column-apply-section__desc">선택 사항입니다. 운영 중인 채널이 있으면 입력해 주세요.</p>
            </header>
            <?php echo eottae_column_render_social_form_fields_compact(); ?>
        </section>

        <section class="sebu-column-apply-section" aria-labelledby="sebu-column-apply-extra-title">
            <header class="sebu-column-apply-section__head">
                <h2 class="sebu-column-apply-section__title" id="sebu-column-apply-extra-title">추가 정보</h2>
                <p class="sebu-column-apply-section__desc">활동 지역과 참고 링크를 남겨 주시면 검토에 도움이 됩니다.</p>
            </header>

            <div class="sebu-column-form__row">
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
            </div>

            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">샘플 글 또는 참고 링크</span>
                <input type="url" name="sample_url" class="sebu-column-form__input" placeholder="https://">
            </label>

            <label class="sebu-column-form__field sebu-column-form__field--last">
                <span class="sebu-column-form__label">신청 메모</span>
                <textarea name="message" class="sebu-column-form__textarea sebu-column-form__textarea--apply" rows="3" placeholder="운영자에게 전하고 싶은 내용을 적어 주세요."></textarea>
            </label>
        </section>

        <div class="sebu-column-write-form__actions">
            <a href="<?php echo eottae_column_list_url(); ?>" class="sebu-column-btn sebu-column-btn--ghost">취소</a>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary sebu-column-btn--editorial">신청하기</button>
        </div>
    </form>
    <?php } ?>
</main>

<?php
g5_page_end();
