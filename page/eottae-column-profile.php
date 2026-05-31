<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_PATH.'/components/eottae/column-author-profile.php';
include_once G5_LIB_PATH.'/eottae-column-author-exposure.lib.php';
include_once G5_PATH.'/components/eottae/column-author-activity.php';

global $is_member, $member;

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', function_exists('eottae_login_url') ? eottae_login_url(eottae_column_profile_edit_url()) : G5_BBS_URL.'/login.php');
}

eottae_column_ensure_schema();

if (!eottae_column_is_columnist($member['mb_id'])) {
    alert('승인된 칼럼니스트만 프로필을 수정할 수 있습니다.', eottae_column_mypage_url());
}

$author = eottae_column_get_author($member['mb_id']);
if (!$author) {
    alert('칼럼니스트 프로필을 찾을 수 없습니다.', eottae_column_mypage_url());
}

$areas = eottae_column_area_options();
$token = eottae_column_member_token();
$form_values = array_merge(
    array(
        'pen_name'     => $author['pen_name'] ?? '',
        'title'        => $author['title'] ?? '',
        'specialty'    => $author['specialty'] ?? '',
        'bio'          => $author['bio'] ?? '',
        'area'         => $author['area'] ?? '',
        'website_url'  => $author['website_url'] ?? '',
    ),
    eottae_column_social_from_row($author)
);
$pen_name_default = trim((string) ($form_values['pen_name'] ?? ''));
$profile_initial = function_exists('mb_substr') && $pen_name_default !== ''
    ? mb_substr($pen_name_default, 0, 1, 'UTF-8')
    : ($pen_name_default !== '' ? substr($pen_name_default, 0, 1) : '?');
$profile_preview_url = !empty($author['has_profile_image']) ? (string) ($author['profile_image_url'] ?? '') : '';
$profile_preview_visible = $profile_preview_url !== '';
$exposure_counts = eottae_column_author_exposure_item_counts($member['mb_id']);

add_stylesheet('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap">', 20);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-column.js" defer></script>', 24);

g5_page_start('컬럼리스트 프로필 설정');
?>

<main class="sebu-column-write-page sebu-column-apply-page sebu-column-profile-page sebu-column-editorial" data-sebu-column-apply>
    <header class="sebu-column-studio">
        <p class="sebu-column-page__back"><a href="<?php echo eottae_column_mypage_url(); ?>">← 내 컬럼</a></p>
        <p class="sebu-column-studio__eyebrow">Columnist Profile</p>
        <h1 class="sebu-column-studio__title">컬럼리스트 프로필 설정</h1>
        <p class="sebu-column-studio__guide">독자에게 보이는 필명·소개·SNS 정보를 관리합니다. 저장하면 공개 프로필과 이후 작성 컬럼에 반영됩니다.</p>
        <ul class="sebu-column-studio__tips">
            <li>필명을 바꿔도 이미 발행된 글의 작성자 표시는 그대로 유지됩니다.</li>
            <li>프로필 사진을 등록하지 않으면 필명 첫 글자로 자동 표시됩니다.</li>
            <li><a href="<?php echo eottae_column_author_url($member['mb_id']); ?>" target="_blank" rel="noopener noreferrer">공개 프로필 미리보기</a></li>
        </ul>
    </header>

    <form class="sebu-column-write-form sebu-column-write-form--editorial sebu-column-apply-form" method="post" action="<?php echo eottae_column_proc_url(); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_author_profile">
        <input type="hidden" name="eottae_column_token" value="<?php echo get_text($token); ?>">

        <section class="sebu-column-apply-section" aria-labelledby="sebu-column-profile-basic-title">
            <header class="sebu-column-apply-section__head">
                <h2 class="sebu-column-apply-section__title" id="sebu-column-profile-basic-title">기본 프로필</h2>
                <p class="sebu-column-apply-section__desc">독자에게 보여질 이름과 전문 분야를 입력해 주세요.</p>
            </header>

            <div class="sebu-column-apply-profile">
                <div class="sebu-column-apply-profile__media">
                    <div class="sebu-column-apply-profile__preview" data-column-apply-avatar-preview>
                        <img src="<?php echo get_text($profile_preview_url); ?>" alt="" class="sebu-column-apply-profile__preview-img<?php echo $profile_preview_visible ? ' is-visible' : ''; ?>" data-column-apply-avatar-img>
                        <span class="sebu-column-apply-profile__preview-initial" data-column-apply-avatar-initial><?php echo get_text($profile_initial); ?></span>
                    </div>
                    <label class="sebu-column-apply-profile__upload">
                        <span class="sebu-column-form__label">프로필 사진</span>
                        <input type="file" name="profile_image" class="sebu-column-form__file" accept="image/jpeg,image/png,image/gif,image/webp" data-column-apply-avatar-input>
                        <span class="sebu-column-form__hint">새 사진을 올리지 않으면 현재 프로필이 유지됩니다.</span>
                    </label>
                </div>

                <div class="sebu-column-apply-profile__fields">
                    <div class="sebu-column-form__row">
                        <label class="sebu-column-form__field">
                            <span class="sebu-column-form__label">필명 *</span>
                            <input type="text" name="pen_name" class="sebu-column-form__input" maxlength="80" required value="<?php echo get_text($form_values['pen_name']); ?>" data-column-apply-pen-name>
                        </label>
                        <label class="sebu-column-form__field">
                            <span class="sebu-column-form__label">타이틀 *</span>
                            <input type="text" name="title" class="sebu-column-form__input" maxlength="120" required placeholder="예: 교육·가족 컬럼니스트" value="<?php echo get_text($form_values['title']); ?>">
                        </label>
                    </div>

                    <label class="sebu-column-form__field">
                        <span class="sebu-column-form__label">전문 분야 *</span>
                        <input type="text" name="specialty" class="sebu-column-form__input" maxlength="200" required placeholder="예: 국제학교, 아이 병원, 가족생활" value="<?php echo get_text($form_values['specialty']); ?>">
                    </label>

                    <label class="sebu-column-form__field sebu-column-form__field--last">
                        <span class="sebu-column-form__label">소개글 *</span>
                        <textarea name="bio" class="sebu-column-form__textarea sebu-column-form__textarea--apply" rows="4" required placeholder="세부에서 어떤 경험과 정보를 전할 수 있는지 소개해 주세요."><?php echo get_text($form_values['bio']); ?></textarea>
                    </label>
                </div>
            </div>
        </section>

        <section class="sebu-column-apply-section" aria-labelledby="sebu-column-profile-sns-title">
            <header class="sebu-column-apply-section__head">
                <h2 class="sebu-column-apply-section__title" id="sebu-column-profile-sns-title">SNS · 채널 링크</h2>
                <p class="sebu-column-apply-section__desc">운영 중인 채널이 있으면 입력해 주세요.</p>
            </header>
            <?php echo eottae_column_render_social_form_fields_compact($form_values); ?>
        </section>

        <section class="sebu-column-apply-section" aria-labelledby="sebu-column-profile-extra-title">
            <header class="sebu-column-apply-section__head">
                <h2 class="sebu-column-apply-section__title" id="sebu-column-profile-extra-title">추가 정보</h2>
            </header>

            <div class="sebu-column-form__row">
                <label class="sebu-column-form__field">
                    <span class="sebu-column-form__label">활동 지역</span>
                    <select name="area" class="sebu-column-form__select">
                        <option value="">선택</option>
                        <?php foreach ($areas as $code => $label) { ?>
                        <option value="<?php echo get_text($code); ?>"<?php echo ($form_values['area'] ?? '') === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="sebu-column-form__field">
                    <span class="sebu-column-form__label">홈페이지</span>
                    <input type="url" name="website_url" class="sebu-column-form__input" placeholder="https://" value="<?php echo get_text($form_values['website_url']); ?>">
                </label>
            </div>
        </section>

        <section class="sebu-column-apply-section" aria-labelledby="sebu-column-profile-exposure-title">
            <header class="sebu-column-apply-section__head">
                <h2 class="sebu-column-apply-section__title" id="sebu-column-profile-exposure-title">프로필 자동 노출</h2>
                <p class="sebu-column-apply-section__desc">원하는 항목만 선택하면 공개 프로필에 최신 등록 정보가 자동으로 표시됩니다. 기본값은 비공개입니다.</p>
            </header>
            <?php echo eottae_column_author_exposure_form_fields_html($author, $exposure_counts); ?>
        </section>

        <div class="sebu-column-write-form__actions">
            <a href="<?php echo eottae_column_mypage_url(); ?>" class="sebu-column-btn sebu-column-btn--ghost">취소</a>
            <a href="<?php echo eottae_column_author_url($member['mb_id']); ?>" class="sebu-column-btn sebu-column-btn--outline" target="_blank" rel="noopener noreferrer">공개 프로필 보기</a>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary sebu-column-btn--editorial">저장하기</button>
        </div>
    </form>
</main>

<?php
g5_page_end();
